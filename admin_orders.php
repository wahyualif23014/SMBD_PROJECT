<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
}

// stored procedure for updating order status admin
if(isset($_POST['update_order'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];

   $stmt = $conn->prepare("CALL update_order_status(?, ?)");
   $stmt->bind_param("is", $order_id, $payment_status);

   if($stmt->execute()){
      $message[] = 'Payment status has been updated!';
   } else {
      $message[] = 'Failed to update payment status.';
   }

   $stmt->close();
}

// stored procedure untuk menghapus order di admin
if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];

   $stmt = $conn->prepare("CALL delete_order(?)");
   $stmt->bind_param("i", $delete_id);
   $stmt->execute();
   $stmt->close();

   header('location:admin_orders.php');
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="orders">

   <h1 class="title">placed orders</h1>

   <div class="box-container">
      <?php
      $select_orders = mysqli_query($conn, "SELECT * FROM `orders`") or die('query failed');
      if(mysqli_num_rows($select_orders) > 0){
         while($fetch_orders = mysqli_fetch_assoc($select_orders)){
      ?>
      <div class="box">
         <!-- <p> user id : <span><?php echo $fetch_orders['user_id']; ?></span> </p> -->
         <p> placed on : <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
         <p> name : <span><?php echo $fetch_orders['name']; ?></span> </p>
         <p> number : <span><?php echo $fetch_orders['number']; ?></span> </p>
         <p> email : <span><?php echo $fetch_orders['email']; ?></span> </p>
         <p> address : <span><?php echo $fetch_orders['address']; ?></span> </p>
         <p> total products : <span><?php echo $fetch_orders['total_products']; ?></span> </p>
         <p> total price : <span>$<?php echo $fetch_orders['total_price']; ?>/-</span> </p>
         <p> payment method : <span><?php echo $fetch_orders['method']; ?></span> </p>
         <form action="" method="post">
         <input type="hidden" name="order_id" value="<?php echo $fetch_orders['order_id']; ?>">
         <select name="payment_status">
            <option value="" selected disabled><?php echo $fetch_orders['payment_status']; ?></option>
            <option value="pending">pending</option>
            <option value="confirmed">confirmed</option>
         </select>
         <input type="submit" value="update" name="update_order" class="option-btn">
         <a href="admin_orders.php?delete=<?php echo $fetch_orders['order_id']; ?>" onclick="return confirm('delete this order?');" class="delete-btn">delete</a>
      </form>

      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      ?>
   </div>

</section>










<!-- custom admin js file link  -->
<script src="js/admin_script.js"></script>

</body>
</html>