<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
include_once("../mkysess.php");
include_once("../gold/goldInc.php");
$MyBrow = "NA"; //$/_SERVER['HTTP_USER_AGENT'];

if (!goldTransfersAllowed($userID)){
  header('Location: /whzon/gold/requiresVerifiedAcc.php?wzID='.$sKey);
  exit('');
}

?>
<div ID='autoSelSpot'></div>
