<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

   if(isset($_POST['add_product'])){
      $name = mysqli_real_escape_string($conn, $_POST['name']);
      $price = $_POST['price'];
      $image = $_FILES['image']['name'];
      $category = $_POST['category'];
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;

      $select_product_name = mysqli_query($conn, "SELECT name FROM products WHERE name = '$name'") or die('query failed');

      if(mysqli_num_rows($select_product_name) > 0){
         $message[] = 'product name already added';
      } else {
         // Panggil stored procedure yang pakai loop 1x
         $stmt = $conn->prepare("CALL add_product_loop_admin(?, ?, ?, ?)");
         $stmt->bind_param("sdss", $name, $price, $category, $image);
         $stmt->execute();
         $stmt->close();

         if($image_size > 2000000){
            $message[] = 'image size is too large';
         } else {
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'product added successfully!';
            header('Location: admin_products.php');
            exit;
         }
      }
   }

   if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];

   // Ambil gambar (pakai prepared statement untuk keamanan)
   $stmt_img = $conn->prepare("SELECT image FROM products WHERE id = ?");
   $stmt_img->bind_param("i", $delete_id);
   $stmt_img->execute();
   $result = $stmt_img->get_result();

   while($row = $result->fetch_assoc()){
      $image_path = 'uploaded_img/' . $row['image'];
      if(file_exists($image_path)){
         unlink($image_path); // Hapus gambar dari folder
      }
   }
   $stmt_img->close();

   // Panggil prosedur untuk hapus data produk
   $stmt_del = $conn->prepare("CALL delete_product_by_id(?)");
   $stmt_del->bind_param("i", $delete_id);
   $stmt_del->execute();
   $stmt_del->close();

   header('location:admin_products.php');
}



if(isset($_POST['update_product'])){

   $update_p_id = $_POST['update_p_id'];
   $category = $_POST['update_category'];
   $update_name = $_POST['update_name'];
   $update_price = $_POST['update_price'];

   // Cek apakah produk dengan nama & kategori sudah ada (selain produk ini)
   $check_query = mysqli_prepare($conn, "SELECT * FROM products WHERE name = ? AND category = ? AND id != ?");
   $check_query->bind_param("ssi", $category, $update_name, $update_p_id);
   $check_query->execute();
   $result = $check_query->get_result();

   if($result->num_rows > 0){
      $message[] = 'Product with this name and category already exists';
   } else {
      // Panggil stored procedure untuk update
      $stmt = $conn->prepare("CALL update_product_admin(?, ?, ?, ?)");
      $stmt->bind_param("issd", $update_p_id,  $category,$update_name, $update_price);
      $stmt->execute();
      $stmt->close();

      // Cek dan update gambar jika ada
      $update_image = $_FILES['update_image']['name'];
      $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
      $update_image_size = $_FILES['update_image']['size'];
      $update_folder = 'uploaded_img/'.$update_image;
      $update_old_image = $_POST['update_old_image'];

      if(!empty($update_image)){
         if($update_image_size > 2000000){
            $message[] = 'image file size is too large';
         }else{
            $stmt_img = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
            $stmt_img->bind_param("si", $update_image, $update_p_id);
            $stmt_img->execute();
            $stmt_img->close();

            move_uploaded_file($update_image_tmp_name, $update_folder);
            unlink('uploaded_img/'.$update_old_image);
         }
      }

      $_SESSION['message'] = 'Product updated successfully!';
      header('Location: admin_products.php');
      exit;
   }

   header('location:admin_products.php');
}

// if(isset($_GET['delete'])){
//    $delete_id = $_GET['delete'];
//    $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
//    $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
//    unlink('uploaded_img/'.$fetch_delete_image['image']);
//    mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
//    header('location:admin_products.php');
// }

// if(isset($_POST['update_product'])){

//    $update_p_id = $_POST['update_p_id'];
//    $update_category = $_POST['update_category'];
//    $update_name = $_POST['update_name'];
//    $update_price = $_POST['update_price'];

//    mysqli_query($conn, "UPDATE `products` SET name = '$update_name', price = '$update_price', category = '$update_category' WHERE id = '$update_p_id'") or die('query failed');

//    $update_image = $_FILES['update_image']['name'];
//    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
//    $update_image_size = $_FILES['update_image']['size'];
//    $update_folder = 'uploaded_img/'.$update_image;
//    $update_old_image = $_POST['update_old_image'];

//    if(!empty($update_image)){
//       if($update_image_size > 2000000){
//          $message[] = 'image file size is too large';
//       }else{
//          mysqli_query($conn, "UPDATE `products` SET image = '$update_image' WHERE id = '$update_p_id'") or die('query failed');
//          move_uploaded_file($update_image_tmp_name, $update_folder);
//          unlink('uploaded_img/'.$update_old_image);
//       }
//    }

//    header('location:admin_products.php');

// }

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<!-- product CRUD section starts  -->

<section class="add-products">

   <h1 class="title">shop products</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <h3>add product</h3>
      <input type="text" name="name" class="box" placeholder="enter product name" required>
      <input type="text" name="category" class="box" placeholder="enter product category" required>
      <input type="number" min="0" name="price" class="box" placeholder="enter product price" required>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="add product" name="add_product" class="btn">
   </form>

</section>

<!-- product CRUD section ends -->

<!-- show products  -->

<section class="show-products">

   <div class="box-container">

      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="category"><?php echo $fetch_products['category']; ?></div>
         <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
         <a href="admin_products.php?update=<?php echo $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

</section>

<section class="edit-product-form">

   <?php
      if(isset($_GET['update'])){
         $update_id = $_GET['update'];
         $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'") or die('query failed');
         if(mysqli_num_rows($update_query) > 0){
            while($fetch_update = mysqli_fetch_assoc($update_query)){
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id']; ?>">
      <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
      <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
      <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="enter product name">
      <input type="text" name="update_category" value="<?php echo $fetch_update['category']; ?>" class="box" required placeholder="enter product category">
      
      <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="enter product price">
      <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="update" name="update_product" class="btn">
      <input type="reset" value="cancel" id="close-update" class="option-btn">
   </form>
   <?php
         }
      }
      }else{
         echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
      }
   ?>

</section>







<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>