<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['update_cart'])){
   $cart_id = mysqli_real_escape_string($conn, $_POST['cart_id']);
   $cart_quantity = mysqli_real_escape_string($conn, $_POST['cart_quantity']);

   // Ambil quantity lama dari database
   $check_query = "SELECT quantity FROM cart WHERE id = '$cart_id' LIMIT 1";
   $check_result = mysqli_query($conn, $check_query);

   if(mysqli_num_rows($check_result) > 0){
       $row = mysqli_fetch_assoc($check_result);
       $old_quantity = $row['quantity'];

       // Hanya update jika quantity berubah
       if($old_quantity != $cart_quantity){
           $update_query = "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id'";
           if(mysqli_query($conn, $update_query)){
               $message[] = 'Cart quantity updated!';
           } else {
               $message[] = 'Failed to update cart: ' . mysqli_error($conn);
           }
       } else {
           $message[] = 'Quantity is unchanged.';
       }
   } else {
       $message[] = 'Cart item not found.';
   }
}



if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id'") or die('query failed');
   header('location:cart.php');
}

if(isset($_GET['delete_all'])){
   mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   header('location:cart.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>cart</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>shopping cart</h3>
   <p> <a href="home.php">home</a> / cart </p>
</div>

<section class="shopping-cart">

   <h1 class="title">products added</h1>
   <!-- view 6 -->
   <div class="box-container">
<?php
   $grand_total = 0;

   $stmt = $conn->prepare("CALL cart_subtotal_loop(?)");
   $stmt->bind_param("i", $user_id);
   $stmt->execute();
   $result = $stmt->get_result();

   if($result->num_rows > 0){
      while($fetch_cart = $result->fetch_assoc()){   
?>
   <div class="box">
      <a href="cart.php?delete=<?php echo $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
      <img src="uploaded_img/<?php echo $fetch_cart['image']; ?>" alt="">
      <div class="name"><?php echo $fetch_cart['name']; ?></div>
      <div class="category"><?php echo $fetch_cart['category']; ?></div>
      <div class="price">$<?php echo $fetch_cart['price']; ?>/-</div>
      <form action="" method="post">
         <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
         <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
         <input type="submit" name="update_cart" value="update" class="option-btn">
      </form>
      <div class="sub-total"> sub total : <span>$<?php echo $fetch_cart['sub_total']; ?>/-</span> </div>
   </div>
<?php
      $grand_total += $fetch_cart['sub_total'];
      }
      $stmt->close();
      $conn->next_result();

      // Get grand total stored procedure
   $stmt = $conn->prepare("CALL grand_total_loop(?)");
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      $grand_total = $row['grand_total'];
      $stmt->close();
      $conn->next_result();
   }else{
      echo '<p class="empty">your cart is empty</p>';
      $grand_total = 0;
   }
?>
</div>


   <div style="margin-top: 2rem; text-align:center;">
      <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('delete all from cart?');">delete all</a>
   </div>

   <div class="cart-total">
      <p>grand total : <span>$<?php echo $grand_total; ?>/-</span></p>
      <div class="flex">
         <a href="shop.php" class="option-btn">continue shopping</a>
         <a href="checkout.php" class="btn <?php echo ($grand_total > 1)?'':'disabled'; ?>">proceed to checkout</a>
      </div>
   </div>

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>