<?php

require_once '../inc/jsonRPCClient.php';
require_once '../inc/db.php';
require_once '../inc/functions.php';


if (isset($_POST['username']) | isset($_POST['p']) | isset($_POST['email'])){
ob_start();

require_once "../phpqrcode/qrlib.php"; 


$username =$_POST['username'];
$email = $_POST['email'];
$beta = $_POST['beta'];
$password = $_POST['p']; 
$password2 = $_POST['p2']; 


if ($beta!="beta-x62ssDv"){
	 header('Location: index.php?error=9');
}else{

if ($email==""){
	 header('Location: index.php?error=1');
}else{

if ($stmt = $mysqli->prepare("SELECT email FROM members WHERE email = ? LIMIT 1")) { 
      $stmt->bind_param('s', $email); // Bind "$email" to parameter.
      $stmt->execute(); // Execute the prepared query.
      $stmt->store_result();
      $stmt->bind_result($emailcheck); // get variables from result.
      $stmt->fetch();   
 }
if ($stmt = $mysqli->prepare("SELECT username FROM members WHERE username = ? LIMIT 1")) { 
      $stmt->bind_param('s', $username); // Bind "$email" to parameter.
      $stmt->execute(); // Execute the prepared query.
      $stmt->store_result();
      $stmt->bind_result($usernamecheck); // get variables from result.
      $stmt->fetch();   
 }

if ($emailcheck==$email){	
 header('Location: index.php?error=2');
}else{

if ($usernamecheck==$username){
 header('Location: index.php?error=3');	
}else{

if ($password!=$password2){
 header('Location: index.php?error=4');	
}else{
$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
$password = hash('sha512', $password.$random_salt);
if ($insert_stmt = $mysqli->prepare("INSERT INTO members (email, username, password, salt) VALUES (?, ?, ?, ?)")) {    
   $insert_stmt->bind_param('ssss', $email, $username, $password, $random_salt); 
   $insert_stmt->execute();
   $user=$insert_stmt->insert_id;
 	}

$wallet=$bitcoin->getnewaddress("$user");

if ($insert_stmt = $mysqli->prepare("UPDATE members SET wallet = (?) WHERE id = (?) LIMIT 1")) {    
 $insert_stmt->bind_param('ss', $wallet, $user); 
  $insert_stmt->execute();
    
 $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'phpqrcode/temp'.DIRECTORY_SEPARATOR;
 $PNG_WEB_DIR = 'phpqrcode/temp/';
 $errorCorrectionLevel = 'L';
 $matrixPointSize = 3;
 
if (isset($wallet)) { 
        $filename = $PNG_TEMP_DIR.'test'.md5($wallet.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        QRcode::png($wallet, $filename, $errorCorrectionLevel, $matrixPointSize, 2); 
  }

}
$random_hash = md5(uniqid(mt_rand(1, mt_getrandmax()), true));
$subject = "Activate your LTC cloud account";
$message = "<h3>Litecoin cloud mining</h3><br>Activate your account by pressing this link: <a href='http://cloud.mariogrip.com/beta/login/index.php?r=". $random_hash . "'>http://cloud.mariogrip.com/beta/login/index.php?r=". $random_hash;
$headers = "From: no-reply@mariogrip.com\r\n";
$headers .= "Reply-To: me@mariogrip.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
 
  
  if ($insert_stmt = $mysqli->prepare("INSERT INTO email (user, hash) VALUES (?, ?)")) {    
 $insert_stmt->bind_param('ss', $user, $random_hash); 
   $insert_stmt->execute();
   $user=$insert_stmt->insert_id;
 	}
 
mail($email,$subject,$message,$headers);
 header('Location: ../login/index.php?confirm=1');	
}
}
}
}
}
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LTC Mining cloud</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  <meta http-equiv="refresh" content="600">

  <link href="../dist/css/bootstrap.min.css" rel="stylesheet">
  
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/jquery.tablesorter.js"></script>
        <script type="text/JavaScript" src="../sha512.js"></script> 
        <script type="text/JavaScript" src="../forms2.js"></script>
</script>
 
</head>
<body>
<div class="navbar navbar-default" role="navigation">
  <div class="container">
    <a class="navbar-brand" href="/"><small>Beta</small> LTC Cloud</a>
    <ul class="nav navbar-nav">
      <li><a href="../">Trade</a></li>
      <li><a href="../balance">Balance</a></li>
      <li><a href="../faq">FAQ</a></li>
</ul>
 <ul class="nav navbar-nav navbar-right">

<li><p class="navbar-text navbar-right"><a href="../login" class="navbar-link">Sign in</a> | <a href="" class="navbar-link">Register</a></p></li>
     
   
  </div>

</div>



<div class="container">


<?php if (login_check($mysqli) == true) { ?>
    <h1 align="center">your miners!>   
<?php 
}else{ 
?>

<style>
.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-control {
  position: relative;
  height: auto;
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
  padding: 10px;
  font-size: 16px;
}
</style>


</div>

<div class="container">


<?php
if (isset($_GET['error'])) {
if ($_GET['error'] == "9"){
?>
<div class="alert alert-danger">Wrong BETA key</div>
<?php
}

if ($_GET['error'] == "1"){
?>
<div class="alert alert-danger">Please fill in your email</div>
<?php
}

if ($_GET['error'] == "2"){
?>
<div class="alert alert-danger">Email Already in Use.</div>
<?php
}

if ($_GET['error'] == "3"){
?>
<div class="alert alert-danger">Username Already in Use.</div>
<?php
}

if ($_GET['error'] == "4"){
?>
<div class="alert alert-danger">The password did not match!</div>
<?php
}
}
?>


      <form action="" method="POST" class="form-signin" name="login_form" role="form">
        <h2 class="form-signin-heading">Register</h2>
        <div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
        <input type="text" name="username" id="email" class="form-control" placeholder="Username">
</div>
<div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-send"></span></span>
        <input type="email" name="email" id="email" class="form-control" placeholder="Email address">
 </div>
<div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
        <input type="password" name="password" id="password" class="form-control" placeholder="Password">
</div>
<div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
        <input type="password" name="password2" id="password2" class="form-control" placeholder="Repeat Password">
</div>
        <input type="password" name="beta" id="beta" class="form-control" placeholder="Beta Key">
        <button class="btn btn-lg btn-primary btn-block" type="submit" onClick="formhash(this.form, this.form.password, this.form.password2);">Register</button>
      </form>
</div>
   

              
<?php 
}
?>
<footer class="container">
  <hr />
<p align="center">2014 mariogrip!</p>
</footer>



</body>
</html>