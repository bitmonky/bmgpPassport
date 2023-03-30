<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
include_once("../mkysess.php");
include_once("../gold/goldInc.php");

$muidTo = safeGET('fmuid');
$from   = safeGET('fromAdr');

$SQL  = "select muidWzUserID,cityID,goldcoins,tradeGold,purchasedGold, taxGold from ICDirectSQL.tblwzUserGold ";
$SQL .= "inner join tblwzUserJoins on tblwzUserJoins.wzUserID = tblwzUserGold.wzUserID ";
$SQL .= "inner join tblwzMUID on muidWzUserID = tblwzUserJoins.wzUserID ";
$SQL .= "where muidMUID = '".$from."'";

$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);
if (!$tRec){
  exit("<h1>Sender Not Found!</h2>");
}
$yourCity = $tRec['cityID'];
$wzUserID = $tRec['muidWzUserID'];

$SQL = "select wzUserID from tblwzUser where mbrMUID = '".$muidTo."'";
$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);
if (!$tRec){
  exit("<h1>User Not Found!</h2>");
}   
$mbrID = $tRec['wzUserID'];

  $SQL = "SELECT imgFlg, firstname,cityID FROM tblwzUser WHERE wzUserID = ".$mbrID; 
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);

  $imgFlg  = $tRec['imgFlg'];
  $fanName = $tRec['firstname'];


  if ($yourCity == $tRec['cityID']){
    $taxRate = 0;
  }
  else {
    $taxRate = $gTycoonTax;
  }
  
?>
  <div class='infoCardClear' style='background:#151617;margin-top:1.5em;'>
  <img title="Join this Members Fan Group" style='float: Left;border-radius:50%;margin: 0px;margin-right: 10px;'  
  src='<?php echo $GLOBALS['MKYC_imgsrv'];?>/getMbrImg.php?mid=<?php echo $muidTo;?>'>

  <b>You are about to send <?php echo $fanName;?> some gold:</b><p/>
  <?PHP 
  $goldOnHand = goldOnHand($wzUserID);
  $goldOnHand = floor($goldOnHand - $goldOnHand * $gTycoonTax - $goldOnHand * $goldTransferFee );
  ?>
  <form ID='bitWalletSendBMGP'>
    <input ID="sendToMUID" type="hidden" name="mbrID" value="<?php echo $muidTo;?>">
    <input ID="sendToNic" type="hidden" value="<?php echo $fanName;?>">
    <p/>Enter Amount Of Gold To Send 
    <input type="text" ID="sendBMGPAmt"  value="<?php echo mkyNumFormat($goldOnHand,4,'.','');?>"  maxlength="29" size="14"> Gold Coins
    <p/>
    <table border=0>
    <tr><td>
    <h3 style='color:darkKhaki;'>Warning You Can Not Get Your Gold Back After You Send It!</h3> 
    Confirm Send This Amount Of Gold To <b><?php echo $fanName;?></b><br/>
    <div ID='displayCost'></div>
    <input ID="sendGoldBut" type="button" value=" Send Now " onclick="doSendGoldNow()"/> 
    <input type='button' onclick='cancelSendGold();' value= ' Cancel '/>
    </td></tr>
    </table>    
  </form>
  <br>
  <br>
  <br>
  <br>
  <br>
  </div>
