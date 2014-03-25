<?php
require_once '../inc/jsonRPCClient.php';
require_once '../inc/db.php';
require_once '../inc/functions.php';

sec_session_start();
if(login_check($mysqli) == true) {
 header('Location: ../balance');
}
if(isset($_POST['username'], $_POST['p'])) { 
   $username = $_POST['username'];
   $password = $_POST['p']; // The hashed password.
   if(login($username, $password, $mysqli) == true) {
      if(login($username, $password, $mysqli) == "no"){
      header('Location: index.php?error=2');
   }else{
      header('Location: ../balance');
      }
   } else {
      header('Location: index.php?error=1');
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
        <script type="text/JavaScript" src="../forms.js"></script>
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

<li><p class="navbar-text navbar-right"><a href="" class="navbar-link">Sign in</a> | <a href="../register" class="navbar-link">Register</a></p></li>
     
   
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
if (isset($_GET['r'])) {
$ren =  $_GET['r'];
   if ($stmt = $mysqli->prepare("SELECT user FROM email WHERE hash = ? LIMIT 1")) { 
      $stmt->bind_param('s', $ren);
      $stmt->execute(); 
      $stmt->store_result();
      $stmt->bind_result($user);
      $stmt->fetch();

  if($stmt->num_rows == 1) {

$enable = "true";
if ($insert_stmt = $mysqli->prepare("UPDATE members SET enable = (?) WHERE id = (?) LIMIT 1")) {    
 $insert_stmt->bind_param('ss', $enable, $user); 
  $insert_stmt->execute();
echo '<div class="alert alert-success">Your account is activated, you can now login</div>';
}
}else{
echo '<div class="alert alert-danger">Cannot find any account to activate</div>';
}
}else{echo "ERROR (132), please report";}
}

if (isset($_GET['error'])) {
if ($_GET['error'] == "1"){
?>
<div class="alert alert-danger">Username and password did not match</div>
<?php
}
if ($_GET['error'] == "2"){
?>
<div class="alert alert-danger">Your account is not activated, please check your email</div>
<?php
}
}

if (isset($_GET['confirm'])) {
?>
<div class="alert alert-success">Please open the activate link that we send to your email</div>
<?php
}
?>
      <form action="" method="POST" class="form-signin" name="login_form" role="form">
        <h2 class="form-signin-heading">Please sign in</h2>
<div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
        <input type="text" name="username" id='username' class="form-control" placeholder="Username">
</div>
<div class="input-group">
  <span class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></span>
        <input type="password" name="password" id="password" class="form-control" placeholder="Password">
</div>
        <label class="checkbox">
          <input type="checkbox" value="remember-me"> Remember me
        </label>
        <button class="btn btn-lg btn-primary btn-block" type="submit" onClick="formhash(this.form, this.form.password);"">Sign in</button>
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