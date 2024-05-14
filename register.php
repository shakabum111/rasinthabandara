<?php
// Include the database configuration file
include 'config.php';

// Initialize an empty array to store error/success messages
$message = array();

// Check if the form is submitted
if(isset($_POST['submit'])){
   
   // Sanitize and retrieve form inputs
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass = md5($_POST['pass']);
   $cpass = md5($_POST['cpass']);
   $image = $_FILES['image']['name'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_size = $_FILES['image']['size'];
   $image_folder = 'uploaded_img/'.$image;

   // Check if the email already exists in the database
   $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select->execute([$email]);

   // If email already exists, add a message to the array
   if($select->rowCount() > 0){
      $message[] = 'User already exists!';
   } else {
      // If email doesn't exist, proceed with registration

      // Check if passwords match
      if($pass != $cpass){
         $message[] = 'Confirm password not matched!';
      } elseif($image_size > 2000000){
         // Check if image size is too large
         $message[] = 'Image size is too large!';
      } else {
         // Insert user data into the database
         $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image) VALUES(?,?,?,?)");
         if($insert->execute([$name, $email, $cpass, $image])){
            // If data insertion is successful, move uploaded image to the specified folder
            move_uploaded_file($image_tmp_name, $image_folder);
            // Add success message to the array
            $message[] = 'Registered successfully!';
            // Redirect to login page
            header('location:login.php');
            exit(); // Ensure script stops executing after redirection
         } else {
            // If data insertion fails, add an error message to the array
            $message[] = 'Registration failed. Please try again!';
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="style22.css">

</head>
<body>

<?php
// Display error/success messages
if(!empty($message)){
   foreach($message as $msg){
      echo '
      <div class="message">
         <span>'.$msg.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
   
<section class="form-container">
   <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
      <h3>Register Now</h3>
      <input type="text" required placeholder="Enter your username" class="box" name="name">
      <input type="email" required placeholder="Enter your email" class="box" name="email">
      <input type="password" required placeholder="Enter your password" class="box" name="pass">
      <input type="password" required placeholder="Confirm your password" class="box" name="cpass">
      <input type="file" name="image" required class="box" accept="image/jpg, image/png, image/jpeg">
      <p>Already have an account? <a href="login.php">Login now</a></p>
      <input type="submit" value="Register Now" class="btn" name="submit">
   </form>
</section>

</body>
</html>
