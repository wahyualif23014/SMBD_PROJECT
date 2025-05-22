<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

// stored procedure to add product to cart
if(isset($_POST['add_to_cart'])){

    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = (int)$_POST['product_price'];
    $product_quantity = (int)$_POST['product_quantity'];
    $product_image = mysqli_real_escape_string($conn, $_POST['product_image']);
    $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);

    $user_id = (int)$_SESSION['user_id']; 

    $stmt = $conn->prepare("CALL add_to_cart(?, ?, ?, ?, ?, ?, @message)");
    
   
    $stmt->bind_param("isisss", 
        $user_id, 
        $product_name, 
        $product_price, 
        $product_quantity, 
        $product_image, 
        $product_category
    );

    if($stmt->execute()){
        $result = $conn->query("SELECT @message AS message");
        if ($result) {
            $message_data = $result->fetch_assoc();
            $message[] = $message_data['message'];
        } else {
            $message[] = "Stored procedure executed, but failed to fetch message.";
        }
    } else {
        $message[] = "Error executing procedure: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<canvas id="background-canvas"></canvas>

<?php include 'header.php'; ?>
<!-- Canvas Background -->

<section class="home">

   <div class="content">
      <h3>Hand Picked Book to your door.</h3>
      <p>Let's read books for the future, don't waste your time playing.</p>
      <a href="about.php" class="white-btn">discover more</a>
   </div>

</section>

<section class="promo-carousel">
  <h2 class="section-title">Find Your Favorite Book</h2>

  <div class="carousel-container">
    <div class="carousel-track">
      <?php
        $carousel_query = mysqli_query($conn, "SELECT * FROM view_latest_books") or die('query failed');
        if(mysqli_num_rows($carousel_query) > 0){
          while($book = mysqli_fetch_assoc($carousel_query)){
      ?>
      <div class="carousel-item" data-title="<?php echo htmlspecialchars($book['name']); ?>">
        <img src="uploaded_img/<?php echo htmlspecialchars($book['image']); ?>" alt="<?php echo htmlspecialchars($book['name']); ?>">
      </div>
      <?php
          }
        } else {
          echo '<p class="empty">No books available</p>';
        }
      ?>
    </div>
  </div>
</section>

<section class="products">

   <h1 class="title">products</h1>

   <div class="box-container">
      <!-- view 2 -->
      <?php  
         $select_products = mysqli_query($conn, "SELECT * FROM `view_home_products_3`") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
     <form action="" method="post" class="box">
      <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
      <div class="name"><?php echo $fetch_products['name']; ?></div>
      <div class="category"><?php echo $fetch_products['category']; ?></div>
      <div class="price">Rp<?php echo $fetch_products['price']; ?>/-</div>
      <input type="number" min="1" name="product_quantity" value="1" class="qty">
      <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
      <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
      <input type="hidden" name="product_category" value="<?php echo $fetch_products['category']; ?>">
      <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
      <input type="submit" value="add to cart" name="add_to_cart" class="btn">
     </form>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

   <div class="load-more" style="margin-top: 2rem; text-align:center">
      <a href="shop.php" class="option-btn">load more</a>
   </div>

</section>

<section class="about">

   <div class="flex">

      <div class="image">
         <img src="images/about-img.jpg" alt=""><style>
            .image {
               width: 100%;
               border-radius: 16px;
               height: auto;
               border-radius: 1rem;
               overflow: hidden;
               position: relative;
            }
            .image img {
               width: 100%;
               height: 100%;
               object-fit: cover;
               transition: transform 0.3s ease;
            }
            .image:hover img {
               transform: scale(1.1);
            }
         </style>
      </div>

      <div class="content">
         <h3>about us</h3>
         <p>we are here to fulfill your literacy needs with a complete and quality book collection. We are committed to providing an easy and enjoyable shopping experience, both offline and online. Join us to explore the world of books and grow a love of reading every day.</p>
         <a href="about.php" class="btn">read more</a>
      </div>

   </div>

</section>

<section class="home-contact">

   <div class="content">
      <h3>have any questions?</h3>
      <p>If you want something exciting, just ask us okay, Ask for more clarity below
</p>
      <a href="contact.php" class="white-btn">contact us</a>
   </div>

</section>





<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>