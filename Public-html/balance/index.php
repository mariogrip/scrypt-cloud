<?php

require_once '../inc/jsonRPCClient.php';
require_once '../inc/db.php';
require_once '../inc/functions.php';
ini_set('display_errors', 'On');
// Include database connection and functions here.
sec_session_start();
if(login_check($mysqli) == true) {
 

require_once "../phpqrcode/qrlib.php"; 

/*
if (isset($_POST['outadd'] && isset($_POST['outamount'])){

$outadd = $_POST['outadd'];
$outamount = $_POST['outamount'];
$balance=$bitcoin->getbalance($wallet, 1);

if ($balance >= $outamount){
$outa = floatval($outamount-0.0005);

	if ($outa>0){
		$bitcoin->sendfrom($_SESSION['user_id'], $outadd, $outa, 0);
	} else {
	header('Location index.php?error=1');
	}
	}else{
	header('Location index.php?error=2');
	}
}
*/

$walletid=$_SESSION['user_id'];
$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'../phpqrcode/temp'.DIRECTORY_SEPARATOR;
$PNG_WEB_DIR = '../phpqrcode/temp/';
$errorCorrectionLevel = 'L';
$matrixPointSize = 3;
$filename = $PNG_TEMP_DIR.'test'.md5($_SESSION['wallet'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';

if ($stmt = $mysqli->prepare("SELECT kh FROM members WHERE id = ? LIMIT 1")){ 
      $stmt->bind_param('s', $_SESSION['user_id']); 
      $stmt->execute(); 
      $stmt->store_result();
      $stmt->bind_result($kh); 
      $stmt->fetch();
 }

 
} else {
header('Location: ../login');
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
<li><p class="navbar-text navbar-right">Welcome <a href="" class="navbar-link"><?php echo $_SESSION['user']; ?></a> | <a href="../logout" class="navbar-link">Sign Out</a></p></li>
<?php
}else{
?>
<li><p class="navbar-text navbar-right"><a href="" class="navbar-link">Sign in</a> | <a href="" class="navbar-link">Register</a></p></li>
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
<h2>Balance</h2>
    <div class="row">
  <div class="col-md-6">
<div class="panel panel-default">
  <div class="panel-body">
<h3><center>LTC: <?php echo $bitcoin->getbalance($walletid, 0); ?></center></h3>
<div class="btn-group btn-group-lg"><button type="button" class="btn btn-primary">Withdrawal</button></div>
</div>
</div>
</div>
  <div class="col-md-6">
<div class="panel panel-default">
  <div class="panel-body">
<h3><center>KH/s: <?php echo $kh; ?></center></h3>

</div>
</div>
</div>
  </div>
<div class="panel panel-default">
  <div class="panel-body">
    <div class="row">
  <div class="col-md-8">
Your LTC adress:
<div class="panel panel-default">
  <div class="panel-body">
    <center><strong><?php echo $_SESSION['wallet']; ?></strong></center>
  </div>
</div>
</div>
  <div class="col-md-4">
<center>
<?php echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" />'; ?></center><br>
It may take up to few minutes for the Litecoin network to confirm the transaction. We require only 3 network confirmations.</div>
</div>
  </div>
</div>
              

<footer class="container">
  <hr />
<p align="center">2014 mariogrip!</p>
</footer>



</body>
</html>