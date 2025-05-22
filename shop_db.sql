-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Bulan Mei 2025 pada 12.35
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shop_db`
--
CREATE DATABASE IF NOT EXISTS `shop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shop_db`;

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_loop_admin` (IN `p_name` VARCHAR(255), IN `p_price` DECIMAL(10,2), IN `P_category` VARCHAR(50), IN `p_image` VARCHAR(255))   BEGIN
    DECLARE counter INT DEFAULT 1;

    WHILE counter <= 1 DO
        INSERT INTO products(name, price,category, image) VALUES(p_name, p_price, p_category, p_image);
        SET counter = counter + 1;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_to_cart` (IN `p_user_id` INT, IN `p_name` VARCHAR(100), IN `p_price` INT, IN `p_quantity` INT, IN `p_image` VARCHAR(100), IN `p_category` TEXT, OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE existing_id INT;

    SELECT id INTO existing_id
    FROM cart
    WHERE user_id = p_user_id AND name = p_name
    LIMIT 1;

    IF existing_id IS NOT NULL THEN
        UPDATE cart
        SET quantity = quantity + p_quantity
        WHERE id = existing_id;

        SET p_message = CONCAT('The number of products "', p_name, '" has been updated in the cart.');
    ELSE
        INSERT INTO cart (user_id, name, price, quantity, image, category)
        VALUES (p_user_id, p_name, p_price, p_quantity, p_image, p_category);

        SET p_message = CONCAT('Product "', p_name, '" successfully added to cart.');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cart_subtotal_loop` (IN `p_user_id` INT)   BEGIN
    SELECT 
        id,
        name,
        price,
        quantity,
        category,
        image,
        (price * quantity) AS sub_total
    FROM cart
    WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_order` (IN `p_order_id` INT)   BEGIN
   DELETE FROM orders 
   WHERE order_id = p_order_id AND payment_status != 'confirmed';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_product_by_id` (IN `p_id` INT)   BEGIN
   -- Hapus produk berdasarkan ID
   DELETE FROM products WHERE id = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `grand_total_loop` (IN `p_user_id` INT)   BEGIN
    SELECT 
        IFNULL(SUM(price * quantity), 0) AS grand_total
    FROM cart
    WHERE user_id = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `place_order` (IN `p_user_id` INT, IN `p_name` VARCHAR(255), IN `p_number` VARCHAR(20), IN `p_email` VARCHAR(100), IN `p_method` VARCHAR(50), IN `p_address` TEXT, IN `p_total_products` TEXT, IN `p_total_price` INT, IN `p_placed_on` VARCHAR(50), IN `p_payment_status` VARCHAR(50))   BEGIN
    -- Jika p_payment_status NULL atau kosong, set jadi 'Pending'
    IF p_payment_status IS NULL OR p_payment_status = '' THEN
        SET p_payment_status = 'Pending';
    END IF;

    INSERT INTO orders (
        user_id, name, number, email, method, address,
        total_products, total_price, placed_on, payment_status
    ) VALUES (
        p_user_id, p_name, p_number, p_email, p_method, p_address,
        p_total_products, p_total_price, p_placed_on, p_payment_status
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_order_status` (IN `p_order_id` INT, IN `p_new_status` ENUM('pending','confirmed','completed','failed','canceled'))   BEGIN
   UPDATE orders
   SET payment_status = p_new_status
   WHERE order_id = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_product_admin` (IN `p_id` INT, IN `p_category` VARCHAR(100), IN `p_name` VARCHAR(100), IN `p_price` DECIMAL(10,2))   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE cur_id INT;
    DECLARE cur CURSOR FOR SELECT id FROM products WHERE id = p_id;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO cur_id;
        IF done THEN
            LEAVE read_loop;
        END IF;
        UPDATE products
        SET category = p_category,
            name = p_name,
            price = p_price
        WHERE id = cur_id;
    END LOOP;

    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `category` text NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `name`, `price`, `quantity`, `category`, `image`) VALUES
(103, 4, 'prili cha', 567, 1, 'hoki', 'radical_gardening.jpg'),
(116, 1, 'sajara', 100000, 1, '', 'holy_ghosts.jpg'),
(117, 1, 'sora', 34566, 10, '', 'bash_and_lucy-2.jpg');

--
-- Trigger `cart`
--
DELIMITER $$
CREATE TRIGGER `after_cart_delete` AFTER DELETE ON `cart` FOR EACH ROW BEGIN
   INSERT INTO cart_log(cart_id, action, action_time)
   VALUES (OLD.id, 'DELETE', NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_cart_insert` AFTER INSERT ON `cart` FOR EACH ROW BEGIN
   INSERT INTO cart_log(cart_id, action, action_time)
   VALUES (NEW.id, 'INSERT', NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_cart_update` AFTER UPDATE ON `cart` FOR EACH ROW BEGIN
   INSERT INTO cart_log(cart_id, action, action_time)
   VALUES (NEW.id, 'UPDATE', NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart_log`
--

CREATE TABLE `cart_log` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `action_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart_log`
--

INSERT INTO `cart_log` (`id`, `cart_id`, `action`, `action_time`) VALUES
(1, 107, 'UPDATE', '2025-05-21 14:40:18'),
(2, 107, 'UPDATE', '2025-05-21 14:40:25'),
(3, 107, 'UPDATE', '2025-05-21 14:44:47'),
(4, 108, 'INSERT', '2025-05-21 14:46:11'),
(5, 108, 'UPDATE', '2025-05-21 14:46:16'),
(6, 108, 'UPDATE', '2025-05-21 14:46:21'),
(7, 108, 'UPDATE', '2025-05-21 14:46:30'),
(8, 108, 'UPDATE', '2025-05-21 14:46:56'),
(9, 108, 'UPDATE', '2025-05-21 14:48:21'),
(10, 108, 'UPDATE', '2025-05-21 14:48:24'),
(11, 108, 'UPDATE', '2025-05-21 14:50:38'),
(12, 108, 'UPDATE', '2025-05-21 14:51:14'),
(13, 108, 'UPDATE', '2025-05-21 15:01:02'),
(14, 108, 'DELETE', '2025-05-21 15:01:06'),
(15, 109, 'INSERT', '2025-05-21 15:01:13'),
(16, 110, 'INSERT', '2025-05-21 15:01:18'),
(17, 109, 'DELETE', '2025-05-21 15:01:26'),
(18, 110, 'DELETE', '2025-05-21 15:01:26'),
(19, 111, 'INSERT', '2025-05-21 15:01:30'),
(20, 112, 'INSERT', '2025-05-21 15:01:33'),
(21, 112, 'DELETE', '2025-05-21 15:18:03'),
(22, 111, 'DELETE', '2025-05-21 15:18:36'),
(23, 113, 'INSERT', '2025-05-21 15:18:51'),
(24, 114, 'INSERT', '2025-05-21 15:18:55'),
(25, 113, 'DELETE', '2025-05-21 15:29:17'),
(26, 114, 'DELETE', '2025-05-21 15:29:17'),
(27, 115, 'INSERT', '2025-05-21 15:30:05'),
(28, 115, 'DELETE', '2025-05-21 15:32:38'),
(29, 116, 'INSERT', '2025-05-21 15:32:45'),
(30, 117, 'INSERT', '2025-05-21 19:10:31'),
(31, 117, 'UPDATE', '2025-05-21 19:10:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `message`
--

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`) VALUES
(12, 1, 'wahyu sadewa', 'zexvulca@gmail.com', '087740041124', 'PRILI CHAN JOMOK BANGETT SUKY'),
(13, 1, 'wahyu sadewa', 'zexvulca@gmail.com', '087740041124', 'aku manusia');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` varchar(50) NOT NULL,
  `payment_status` enum('pending','confirmed','failed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`) VALUES
(5, 1, 'wahyu sadewa', '087740041124', 'zexvulca@gmail.com', 'cash on delivery', 'flat no. 2312312, 31232, tuban, Indonesia - 62331', 'sdfdf (1)', 21323, '18-May-2025', 'pending'),
(6, 1, 'wahyu sadewa', '087740041124', 'zexvulca@gmail.com', 'cash on delivery', 'flat no. 21323, 3123213, tuban, Indonesia - 62331', 'sdfdf (1), ADIDAS SAMBA (1)', 255778, '18-May-2025', 'confirmed'),
(9, 1, 'wahyu sadewa', '087740041124', 'sdcscsdc@gmail.com', 'cash on delivery', 'flat no. 32, 32, tuban, Indonesia - 62331', 'sdfdf (1), sadefs (2)', 42645947, '21-May-2025', 'confirmed'),
(11, 1, 'wahyu sadewa', '087740041124', 'zexvulca@gmail.com', 'cash on delivery', 'flat no. 1223, 1223, tuban, Indonesia - 62331', 'sora (1)', 34566, '21-May-2025', 'pending');

--
-- Trigger `orders`
--
DELIMITER $$
CREATE TRIGGER `after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
   DELETE FROM cart WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_order_insert` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
   INSERT INTO order_logs (order_id, user_id, action)
   VALUES (NEW.order_id, NEW.user_id, 'Order placed');
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_logs`
--

CREATE TABLE `order_logs` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_logs`
--

INSERT INTO `order_logs` (`log_id`, `order_id`, `user_id`, `action`, `created_at`) VALUES
(1, 4, 1, 'Order placed', '2025-05-18 09:07:09'),
(2, 5, 1, 'Order placed', '2025-05-18 09:37:28'),
(3, 6, 1, 'Order placed', '2025-05-18 10:00:55'),
(4, 7, 1, 'Order placed', '2025-05-18 14:51:54'),
(5, 8, 1, 'Order placed', '2025-05-20 07:16:17'),
(6, 9, 1, 'Order placed', '2025-05-21 01:59:50'),
(7, 10, 1, 'Order placed', '2025-05-21 08:29:17'),
(8, 11, 1, 'Order placed', '2025-05-21 08:32:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` text NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image`) VALUES
(1, 'filosofi', 'pejuy', 45555, 'the_girl_of_ink_and_stars.jpg'),
(3, 'sora', '', 34566, 'bash_and_lucy-2.jpg'),
(4, 'sajara', '', 100000, 'holy_ghosts.jpg'),
(5, 'weloca', '', 34555, 'history_of_modern_architecture.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` varchar(20) NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `user_type`) VALUES
(1, 'wahyu', 'zexvulca@gmail.com', '202cb962ac59075b964b07152d234b70', 'user'),
(2, 'wahyu', 'zexvulca@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', 'admin'),
(4, 'suky nchan', 'coba@gmail.com', '01cfcd4f6b8770febfb40cb906715822', 'user');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_all_messages`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_all_messages` (
`id` int(100)
,`user_id` int(100)
,`user_name` varchar(100)
,`sender_name` varchar(100)
,`email` varchar(100)
,`number` varchar(12)
,`message` varchar(500)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_home_products_3`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_home_products_3` (
`id` int(100)
,`name` varchar(100)
,`category` text
,`price` int(100)
,`image` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_latest_books`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_latest_books` (
`id` int(100)
,`name` varchar(100)
,`image` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_latest_products_2`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_latest_products_2` (
`id` int(100)
,`name` varchar(100)
,`category` text
,`price` int(100)
,`image` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_products_1`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_products_1` (
`id` int(100)
,`name` varchar(100)
,`price` int(100)
,`category` text
,`image` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `view_user_orders_4`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `view_user_orders_4` (
`id` int(11)
,`user_id` int(100)
,`placed_on` varchar(50)
,`name` varchar(100)
,`number` varchar(12)
,`email` varchar(100)
,`method` varchar(50)
,`address` varchar(500)
,`total_products` varchar(1000)
,`total_price` int(100)
,`payment_status` enum('pending','confirmed','failed')
);

-- --------------------------------------------------------

--
-- Struktur untuk view `view_all_messages`
--
DROP TABLE IF EXISTS `view_all_messages`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_all_messages`  AS SELECT `m`.`id` AS `id`, `m`.`user_id` AS `user_id`, `u`.`name` AS `user_name`, `m`.`name` AS `sender_name`, `m`.`email` AS `email`, `m`.`number` AS `number`, `m`.`message` AS `message` FROM (`message` `m` left join `users` `u` on(`m`.`user_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_home_products_3`
--
DROP TABLE IF EXISTS `view_home_products_3`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_home_products_3`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`category` AS `category`, `products`.`price` AS `price`, `products`.`image` AS `image` FROM `products` ORDER BY `products`.`id` DESC LIMIT 0, 8 ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_latest_books`
--
DROP TABLE IF EXISTS `view_latest_books`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_latest_books`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`image` AS `image` FROM `products` ORDER BY `products`.`id` DESC LIMIT 0, 6 ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_latest_products_2`
--
DROP TABLE IF EXISTS `view_latest_products_2`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_latest_products_2`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`category` AS `category`, `products`.`price` AS `price`, `products`.`image` AS `image` FROM `products` ORDER BY `products`.`id` DESC ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_products_1`
--
DROP TABLE IF EXISTS `view_products_1`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_products_1`  AS SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`price` AS `price`, `products`.`category` AS `category`, `products`.`image` AS `image` FROM `products` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `view_user_orders_4`
--
DROP TABLE IF EXISTS `view_user_orders_4`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_orders_4`  AS SELECT `orders`.`order_id` AS `id`, `orders`.`user_id` AS `user_id`, `orders`.`placed_on` AS `placed_on`, `orders`.`name` AS `name`, `orders`.`number` AS `number`, `orders`.`email` AS `email`, `orders`.`method` AS `method`, `orders`.`address` AS `address`, `orders`.`total_products` AS `total_products`, `orders`.`total_price` AS `total_price`, `orders`.`payment_status` AS `payment_status` FROM `orders` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `cart_log`
--
ALTER TABLE `cart_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indeks untuk tabel `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT untuk tabel `cart_log`
--
ALTER TABLE `cart_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT untuk tabel `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
