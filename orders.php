<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
   <h3>Your Orders</h3>
   <p> <a href="home.php">Home</a> / Orders </p>
</div>

<section class="placed-orders">
   <h1 class="title">Placed Orders</h1>
   <div class="box-container">

   <?php
   $order_query = mysqli_query($conn, "SELECT * FROM `view_user_orders_4` WHERE user_id = '$user_id'") or die('Query failed');

   if(mysqli_num_rows($order_query) > 0){
      while($fetch_orders = mysqli_fetch_assoc($order_query)){
         $status = strtolower($fetch_orders['payment_status']);
         $color = ($status === 'pending') ? 'red' : (($status === 'confirmed') ? 'green' : 'orange');
         $status_text = ucfirst($status);
   ?>
      <div class="box">
         <!-- <p> Order ID : <span><?php echo $fetch_orders['id']; ?></span> </p> -->
         <!-- <p> User ID : <span><?php echo $fetch_orders['user_id']; ?></span> </p> -->
         <p> Placed On : <span><?php echo $fetch_orders['placed_on']; ?></span> </p>
         <p> Name : <span><?php echo $fetch_orders['name']; ?></span> </p>
         <p> Number : <span><?php echo $fetch_orders['number']; ?></span> </p>
         <p> Email : <span><?php echo $fetch_orders['email']; ?></span> </p>
         <p> Address : <span><?php echo $fetch_orders['address']; ?></span> </p>
         <p> Payment Method : <span><?php echo $fetch_orders['method']; ?></span> </p>
         <p> Your Orders : <span><?php echo $fetch_orders['total_products']; ?></span> </p>
         <p> Total Price : <span>Rp<?php echo number_format($fetch_orders['total_price'], 0, ',', '.'); ?></span> </p>
         <p> Payment Status : <span style="color:<?php echo $color; ?>;"><?php echo $status_text; ?></span> </p>
      </div>
   <?php
      }
   } else {
      echo '<p class="empty">Belum ada pesanan.</p>';
   }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
