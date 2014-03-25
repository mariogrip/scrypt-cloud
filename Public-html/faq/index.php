<?php


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
        <script type="text/JavaScript" src="../js/sha512.js"></script> 
        <script type="text/JavaScript" src="../js/forms.js"></script>
</script>
     
</head>
<body>
<div class="navbar navbar-default" role="navigation">
  <div class="container">
    <a class="navbar-brand" href="/"><small>Beta</small> LTC Cloud</a>
    <ul class="nav navbar-nav">
      <li><a href="../">Trade</a></li>
      <li><a href="">Balance</a></li>
      <li><a href="../faq">FAQ</a></li>
</ul>
 <ul class="nav navbar-nav navbar-right">

<?php
if(login_check($mysqli) == true) {
?>
<li><p class="navbar-text navbar-right">Welcome <a href="../balance" class="navbar-link"><?php echo $_SESSION['user']; ?></a> | <a href="../logout" class="navbar-link">Sign Out</a></p></li>
<?php
}else{
?>
<li><p class="navbar-text navbar-right"><a href="../login" class="navbar-link">Sign in</a> | <a href="../register" class="navbar-link">Register</a></p></li>
<?php
}
?>
   
  </div>

</div>



<div class="container">
<?php
if (isset($_GET['error'])) {



}
?>



<style>
.form-signin {
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .form-signin-heading,
.form-signin .checkbox {
  margin-bottom: 10px;
}
.form-signin .checkbox {
  font-weight: normal;
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
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>

<h2>FAQ</h2>

              

<footer class="container">
  <hr />
<p align="center">2014 mariogrip!</p>
</footer>



</body>
</html>