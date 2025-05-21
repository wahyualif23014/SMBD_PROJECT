<?php

include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}

if (isset($_POST['send'])) {
   $name = trim($_POST['name']);
   $email = trim($_POST['email']);
   $number = trim($_POST['number']);
   $msg = trim($_POST['message']);

   $errors = [];

   if (empty($name) || strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
      $errors[] = 'Name must be at least 3 characters and only contain letters and spaces.';
   }

   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = 'Invalid email address.';
   }

   if (!preg_match("/^[0-9]{10,12}$/", $number)) {
      $errors[] = 'Number must be between 10 to 12 digits.';
   }

   if (empty($msg) || strlen($msg) < 10) {
      $errors[] = 'Message must be at least 10 characters.';
   }

   if (empty($errors)) {
      $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email' AND number = '$number' AND message = '$msg'") or die('Query failed');

      if (mysqli_num_rows($select_message) > 0) {
         $message[] = 'Message already sent!';
      } else {
         mysqli_query($conn, "INSERT INTO `message`(user_id, name, email, number, message) VALUES('$user_id', '$name', '$email', '$number', '$msg')") or die('Insert query failed');
         $message[] = 'Message sent successfully!';
      }
   } else {
      foreach ($errors as $error) {
         $message[] = $error;
      }
   }
}


// if (isset($_POST['send'])) {
//    $name = trim($_POST['name']);
//    $email = trim($_POST['email']);
//    $number = trim($_POST['number']);
//    $msg = trim($_POST['message']);

//    // Validasi dasar
//    if (strlen($name) < 3 || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
//       $message[] = 'Invalid name.';
//    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//       $message[] = 'Invalid email.';
//    } else if (!preg_match("/^[0-9]{10,15}$/", $number)) {
//       $message[] = 'Invalid number.';
//    } else if (strlen($msg) < 5) {
//       $message[] = 'Message too short.';
//    } else {
//       $stmt = $conn->prepare("CALL insert_message(?, ?, ?, ?, ?)");
//       $stmt->bind_param("issss", $user_id, $name, $email, $number, $msg);
//       if ($stmt->execute()) {
//          $message[] = 'Message sent successfully!';
//       } else {
//          $message[] = 'Failed to send message.';
//       }
//       $stmt->close();
//    }
// }



?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>contact</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<canvas id="background-canvas"></canvas>

<?php include 'header.php'; ?>

<div class="heading">
   <h3>contact us</h3>
   <p> <a href="home.php">home</a> / contact </p>
</div>

<section class="contact">

   <form action="" method="post">
      <h3>say something!</h3>
      <input type="text" name="name" required placeholder="enter your name" class="box">
      <input type="email" name="email" required placeholder="enter your email" class="box">
      <input type="number" name="number" required placeholder="enter your number" class="box">
      <textarea name="message" class="box" placeholder="enter your message" id="" cols="30" rows="10"></textarea>
      <input type="submit" value="send message" name="send" class="btn">
   </form>

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>