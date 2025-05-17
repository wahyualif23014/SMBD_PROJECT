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
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>our shop</h3>
   <p> <a href="home.php">home</a> / shop </p>
</div>

<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

      <?php  
         $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
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
      <input type="hidden" name="product_category" value="<?php echo $fetch_products['category']; ?>">
      
      <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
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

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>