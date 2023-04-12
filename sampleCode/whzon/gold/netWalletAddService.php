<?php
//ini_set('display_errors',1); 
//error_reporting(E_ALL);
include_once("../mkysess.php");

$own   = safeGET('owner');
$nonce = safeGET('nonce');
$host  = safeGET('shost');
$port  = safeGET('sport');
$point = safeGET('point');
$login = safeGET('login');
$title = safeGET('title');
$desc  = safeGET('sdesc');

if (!$own || !$nonce){
  exitEr('Invalid User Credentials...');
}

if (!$host || trim($host) == ""){
  exitEr('Service Host Name Is Required');
}
if (!$port || trim($port) == ""){
  $port = "null";
}
if (!$point || trim($point) == ""){
  exitEr('End Point Is Required');
}
if (!$login || trim($login) == ""){
  exitEr('Web Login Url Is Required');
}
if (stripos($login,'http://') !== false){
  exitEr('Web Login Url Must Use https Protocol');
}
if (!$title || trim($title) == ""){
  exitEr('Title Is Required');
}
if (!$desc || trim($desc) == ""){
  exitEr('Description Is Required');
}
$login = str_ireplace('http://','',$login);
$login = str_ireplace('https://','',$login);

$sport = ":".$port;
if ($port == "null"){
  $sport = null;
}  
$img  = 'https://'.$host.$sport.'/';
$icon = checkImg($img.'psrvIncon.png',500,500); 
if ($icon != 'OK'){
  exitEr($icon);
}	  
$banner = checkImg($img.'psrvBanner.png',1200,400);
if ($banner != 'OK'){
  exitEr($banner);
}

$SQL = "select muidWzUserID from tblwzMUID where muidMUID = '".$own."' and muidNonce= '".$nonce."'";
$res = mkyMyqry($SQL);
$rec = mkyMyFetch($res);
if (!$rec){
  exitEr('Authentication Fail... Try again');
}  
$userID = $rec['muidWzUserID'];

$SQL = "select accFrozen, TIMESTAMPDIFF(day,date(creatDate),date(now())) as nDays from tblwzUser where wzUserID=".$userID;
$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);

if (!mkyStartTransaction()){
  dbFail();
}

$SQL = "insert into ICDirectSQL.tblPassportService (";
$SQL .= "psrvWallet,psrvHost,psrvPort,psrvApiPath,psrvLogin,psrvName,psrvStatus,";
$SQL .= "psrvActiveDate,psrvDesc) ";
$SQL .= "values ('".$own."','".$host."',".$port.",'".$point."','".$login."','".left($title,120)."','pending',now(),'".left($desc,8000)."')";
if (!mkyMsqry($SQL)){
  dbFail();
} 
if (!mkyCommit()){
  dbFail();
}  
//sendNotificationEmail($mbrID,$userID,$amt);
respond("<h3>Transaction Complete</h3>");

function checkImg($url,$minw,$minh){
  $imgdata= tryLFetchURL($url);
  if ($imgdata === False or $imgdata == ''){
    return 'FAIL image files not found.'.$url;
  }
  if (mkyStripos($url,'.png') === false){
    return 'FAIL image files must be png format';
  }
  return 'OK';

  $im = imagecreatefromstring($imgdata);
  if ($im === false){
    return 'FAIL on Create Image';
  }
  $x = imagesx($im);
  $y = imagesy($im);
  if ($x < $minw || $y < $minh){
    imagedestroy($im);
    return 'FAIL required png image must be '.$imgw.'pixels by '.$imgh.'pixels in size';
  }
  imagedestroy($im);
  return 'OK';
}
function tryLFetchURL($myURL){
  $crl = curl_init();
  $timeout = 5;
  curl_setopt ($crl, CURLOPT_URL,$myURL);
  curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt ($crl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt ($crl, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt ($crl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt ($crl, CURLOPT_USERAGENT,safeSRV('HTTP_USER_AGENT'));
  curl_setopt ($crl, CURLOPT_MAXREDIRS,5);
  curl_setopt ($crl, CURLOPT_REFERER, 'https://bitMonky/');
  $ret = curl_exec($crl);
  curl_getinfo($crl, CURLINFO_EFFECTIVE_URL);
  curl_close($crl);
  return $ret;
}
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
