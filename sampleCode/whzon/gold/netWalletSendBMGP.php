<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
include_once("../mkysess.php");
include_once("../gold/goldInc.php");

$mbrID = safeGET('toMbrID');
$amt   = safeGET('famt');
$nonce = safeGET('nonce');
$addr  = safeGET('sender');

//exit('<hr>Withdraws Temporarly Halted Try Again Later...</h3>');

$SQL = "select muidWzUserID from tblwzMUID where muidMUID = '".$addr."' and muidNonce= '".$nonce."'";
$res = mkyMyqry($SQL);
$rec = mkyMyFetch($res);
if (!$rec){
  exitEr('Authentication Fail... Try again');
}  
$userID = $rec['muidWzUserID'];

if ($userID == $mbrID){
   exitEr('you can`t send gold to your self');
}
$goldOnHand = goldOnHand($userID);
if ($goldOnHand < $amt ){
   exitEr('<h3>You  don`t have enough gold to send that amount.. Try a smaller amount</h3>');
}

$SQL = "select accFrozen, TIMESTAMPDIFF(day,date(creatDate),date(now())) as nDays from tblwzUser where wzUserID=".$userID;
$tRec = null;
$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);

if ($tRec['accFrozen']){
  exitEr("<h3>Sorry... You Can't Send Them Gold At This Time.</h3>");
}

$mincoins = 1; //floor($paypalPayout/100/$goldValue);

if ($amt < $mincoins && !$trustSender){
  exitEr("<h3>Sorry Minnimum Amount You Can Send Is ".$mincoins." Gold Coins</h3>");
}

if (!mkyStartTransaction()){
  dbFail();
}
if (!spendGoldNewTax($amt,$userID)){
  dbFail("Spend BMGP Failed... Check Your Balance");
}
if(!makeGoldTransaction($mbrID,$amt,'tGold','Receive Gold',$userID)){
  dbFail('Transaction failed.. Try Later');
}

$SQL = "insert into tblGoldTradeLog (gOwnerUID,gRecipUID,amount,ownerEmail,recipEmail) ";
$SQL .= "values (".$userID.",".$mbrID.",".$amt.",'".getEmail($userID)."','".getEmail($mbrID)."')";
if (!mkyMsqry($SQL)){
  dbFail();
} 
if (!mkyCommit()){
  dbFail();
}  
sendNotificationEmail($mbrID,$userID,$amt);
respond("<h3>Transaction Complete</h3>");

function respond($msg){
  $j = new stdClass;
  $j->result = true;
  $j->msg    = $msg;
  exit(json_encode($j));
}
function dbFail($msg='Database Not Available Try Later...'){
  mkyRollback();
  $j = new stdClass;
  $j->result = false;
  $j->msg    = $msg;
  exit(json_encode($j));
}
function exitEr($msg){
  $j = new stdClass;
  $j->result = false;
  $j->msg    = $msg;
  exit(json_encode($j));
}
function sendNotificationEmail($userID,$senderID,$goldAmount){
  global $whzdom;
  
  $SQL = "select email, firstname from tblwzUser where wzUserID=".$userID;
  $tRec = null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);

  if ($tRec){
    $email = $tRec['email'];
    $firstname = $tRec['firstname'];
  }
  else {
    $email = null;
  }

  $SQL = "select firstname from tblwzUser where wzUserID=".$senderID;
  $tRec = null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  $senderName = null;  
  if ($tRec){
    $senderName = $tRec['firstname'];
  }

  if ($email){

    $m  =  "    <h1 style='font-size:16px;'>Receipt Of Gold Coins</h1>";
    $m .=  "    <p><img style='float:left;border:0px solid #777777;margin-top:8px;margin-right:10px;margin-bottom:15px;' src='https://image0.".$whzdom."/getMbrImg.php?id=".$userID."'>";
    $m .=  "    Hello ".$firstname.", ";
    $m .=  "<P/>".$senderName." has  sent you <b>".$goldAmount."</b> whzon virtural gold coins.";
    $m .=  "<p/><a href='https://www.".$whzdom."/whzon/gold/goldMgr.php'>Login To Your MonkyTalk Bank HERE.</a>";
    $m .=  "<p/>Thank you for using ".$whzdom."!";

    $m = getEHeader($m,'Gold Bank',$userID);
    wzSendMail("bitmonky.com Gold Bank<support@bitmonky.com>", $email, "You Have Receive Gold Coins From A BitMonky Member!", $m);     
  }
}
function strIsPosInt($str){
  if (is_numeric($str)){
    if (is_int(0+$str)){
      if (0+$str > 0){
        return 1;
      }
    }
  }
  return 0;
}
function getEmail($id){
  $SQL = "select email from tblwzUser where wzUserID=".$id;
  $tRec = null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  return left($tRec['email'],150);
}
?>
