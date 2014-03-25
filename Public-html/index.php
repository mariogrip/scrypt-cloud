<?php
ini_set('display_errors', 'On');
require_once 'inc/jsonRPCClient.php';
require_once 'inc/db.php';
require_once 'inc/functions.php';


// Include database connection and functions here.
sec_session_start();

if (isset($_POST['buyamount'])){
if ($stmt2 = $mysqli->prepare("SELECT pph, akh FROM data LIMIT 1")) { 
      $stmt2->execute(); 
      $stmt2->store_result();
      $stmt2->bind_result($pph, $akh);
      $stmt2->fetch();
      }
      $amount = floatval($_POST['buyamount']);
      if ($akh >= $amount){
      $price = $amount * $pph;
      $balance = $bitcoin->getbalance($_SESSION['user_id'],0);
      if ($balance >= $price){
      

      $bitcoin->move($_SESSION['user_id'], "admin", $price);
      if ($balance == $bitcoin->getbalance($_SESSION['user_id'],0)){
      header('Location: index.php?error=3');
      }else{
	if ($insert_stmt = $mysqli->prepare("UPDATE members SET kh = (?) WHERE id = (?) LIMIT 1")) {    
	$insert_stmt->bind_param('ss', $amount, $_SESSION['user_id']); 
	$insert_stmt->execute();
	}
	header('Location: index.php?c=1');
	}
	}else{
	header('Location: index.php?error=2');
	}
      }else{
      header('Location:  index.php?error=1');
      }
} 



if (isset($_POST['sellamount'])){
if ($stmt2 = $mysqli->prepare("SELECT pph, altc FROM data LIMIT 1")) { 
      $stmt2->execute(); 
      $stmt2->store_result();
      $stmt2->bind_result($pph, $altc);
      $stmt2->fetch();
      }  
      if ($stmt = $mysqli->prepare("SELECT kh FROM members WHERE id = ? LIMIT 1")){ 
      $stmt->bind_param('s', $_SESSION['user_id']); 
      $stmt->execute(); 
      $stmt->store_result();
      $stmt->bind_result($kh); 
      $stmt->fetch();
 }
      $amount = floatval($_POST['sellamount']);
      $price = $amount * $pph;
      $adminbalance = $bitcoin->getbalance("admin",0);
      $balance = $bitcoin->getbalance($_SESSION['user_id'],0);

      if ($adminbalance >= $price){

      if ($kh >= $amount){
      

      $bitcoin->move("admin", $_SESSION['user_id'], $price);
      $total = $kh - $amount;
      if ($balance == $bitcoin->getbalance($_SESSION['user_id'],0)){
      header('Location: index.php?error=3');
      }else{
	if ($insert_stmt = $mysqli->prepare("UPDATE members SET kh = (?) WHERE id = (?) LIMIT 1")) {    
	$insert_stmt->bind_param('ss', $total, $_SESSION['user_id']); 
	$insert_stmt->execute();
	}
	header('Location: index.php?c=1');
	}
	}else{
	header('Location: index.php?error=2');
	}
      }else{
      header('Location:  index.php?error=1');
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

  <link href="dist/css/bootstrap.min.css" rel="stylesheet">

  
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script>
</script>
    
</head>
<body>
<div class="navbar navbar-default" role="navigation">
  <div class="container">
    <a class="navbar-brand" href="/"><small>Beta</small> LTC Cloud</a>
    <ul class="nav navbar-nav">
      <li><a href="">Trade</a></li>
      <li><a href="balance">Balance</a></li>
      <li><a href="faq">FAQ</a></li>
</ul>
 <ul class="nav navbar-nav navbar-right">


<?php
if(login_check($mysqli) == true) {
?>
<li><p class="navbar-text navbar-right">Welcome <a href="/balance" class="navbar-link"><?php echo $_SESSION['user']; ?></a> | <a href="logout" class="navbar-link">Sign Out</a></p></li>
<?php
}else{
?>
<li><p class="navbar-text navbar-right"><a href="login" class="navbar-link">Sign in</a> | <a href="register" class="navbar-link">Register</a></p></li>
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

<div class="row">
<form action="" method="POST" name="buy" role="form">
  <div class="col-md-6">
  <h3 align="center">Buy KH/s</h3>
<div class="panel panel-default">
  <div class="panel-body">
  
<div class="row">
  <div class="col-md-6">
<div class="input-group">

  <input name="buyamount" type="text" class="form-control">
  <span class="input-group-addon">KH/s</span>
</div>
          <p class="help-block">
            Amount to buy
          </p>
</div>
  <div class="col-md-6">
<h4>Price: 0 LTC</h4>
</div>
</div>

<div class="row">
  <div class="col-md-6"><small>Price per KH/s: 0.00563 LTC</small></div>
  <div align="right" class="col-md-6"><button type="submit" name="buybutton"  class="btn btn-default">Buy</button></div>
 
</div>
  </div>
</div></div>
 </form>
<form action="" method="POST" name="sell" role="form">
  <div class="col-md-6">
  <h3 align="center">Sell KH/s</h3>
<div class="panel panel-default">
  <div class="panel-body">
  
<div class="row">
  <div class="col-md-6">
<div class="input-group">

  <input name="sellamount" type="text" class="form-control">
  <span class="input-group-addon">KH/s</span>
</div>
          <p class="help-block">
            Amount to Sell
          </p>
</div>
  <div class="col-md-6">
<h4>Price: 0 LTC</h4>
</div>
</div>

<div class="row">
  <div class="col-md-6"><small>Price per KH/s: 0.00563 LTC</small></div>
  <div align="right" class="col-md-6"><button type="submit" class="btn btn-default">Sell</button></div>

</div>
  </div>
</div>
</div>
  </form>
              
<footer class="container">
  <hr />
<p align="center">2014 mariogrip!</p>
</footer>



</body>
</html>