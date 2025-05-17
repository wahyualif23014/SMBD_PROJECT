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
   
<?php include 'header.php'; ?>

<section class="home">

   <div class="content">
      <h3>Hand Picked Book to your door.</h3>
      <p>Kesini untuk mencari buku? Dan cobain dehh semua buku nya yaaa.</p>
      <a href="about.php" class="white-btn">discover more</a>
   </div>

</section>

<section class="promo-carousel">
  <h2 class="section-title">Temukan Buku Favoritmu</h2>

  <div class="carousel-container">
    <div class="carousel-track">
      <div class="carousel-item" data-title="Book 1">
        <img src="https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80" alt="Book 1">
      </div>
      <div class="carousel-item" data-title="Book 2">
        <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80" alt="Book 2">
      </div>
      <div class="carousel-item" data-title="Book 3">
        <img src="https://images.unsplash.com/photo-1516979187457-637abb4f9353?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80" alt="Book 3">
      </div>
      <div class="carousel-item" data-title="Book 4">
        <img src="https://images.unsplash.com/photo-1544717305-2782549b5136?ixlib=rb-4.0.3&auto=format&fit=crop&w=700&q=80" alt="Book 4">
      </div>
    </div>
  </div>
</section>

<!-- Modal Popup -->
<div class="modal" id="modal">
  <div class="modal-content">
    <span id="modal-close">&times;</span>
    <h3 id="modal-title"></h3>
  </div>
</div>



<section class="products">

   <h1 class="title">products</h1>

   <div class="box-container">
      <!-- view 2 -->
      <?php  
         $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
     <form action="" method="post" class="box">
      <img class="image" src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
      <div class="name"><?php echo $fetch_products['name']; ?></div>
      <div class="category"><?php echo $fetch_products['category']; ?></div>
      <div class="price">$<?php echo $fetch_products['price']; ?>/-</div>
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
         <img src="images/about-img.jpg" alt="">
      </div>

      <div class="content">
         <h3>about us</h3>
         <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Impedit quos enim minima ipsa dicta officia corporis ratione saepe sed adipisci?</p>
         <a href="about.php" class="btn">read more</a>
      </div>

   </div>

</section>

<section class="home-contact">

   <div class="content">
      <h3>have any questions?</h3>
      <p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Atque cumque exercitationem repellendus, amet ullam voluptatibus?</p>
      <a href="contact.php" class="white-btn">contact us</a>
   </div>

</section>





<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>