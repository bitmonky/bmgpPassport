<?php
include_once("../mkysess.php");
$vmode = safeGET('mode');
$flex  = '100%';
$limit = 16;
$imgs  = null;

if($vmode == 'PC'){
  $flex = '20%';
  $limit = 20;
  $imgs = "style='height:80px;' ";
}
?>
<style>
* {
  box-sizing: border-box;
}

.header {
  text-align: center;
  padding: 32px;
}

.row {
  display: -ms-flexbox; /* IE10 */
  display: flex;
  -ms-flex-wrap: wrap; /* IE10 */
  flex-wrap: wrap;
  padding: 0 4px;
}

/* Create four equal columns that sits next to each other */
.column {
  -ms-flex: <?php echo $flex;?>; /* IE10 */
  flex: <?php echo $flex;?>;
  max-width: <?php echo $flex;?>;
  padding: 0 4px;
}

.column img {
  margin-top: 8px;
  background-color: black;
  border-radius:1.2em 1.2em 0em 0em;
  vertical-align: middle;
  width: 100%;
}
/* Responsive layout - makes a two column-layout instead of four columns */
@media screen and (max-width: 300px) {
  .column {
    -ms-flex: 50%;
    flex: 50%;
    max-width: 50%;
  }
}
</style>
<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);

$MyBrow = safeSRV('HTTP_USER_AGENT');

$sMode = safeGET('smode');
if ($sMode != null and $sMode !=''){
 $sMode = " and acCode = ".$sMode." ";
}

$SQL  = "select storeBanID,firstname,mbrMUID,tblwzUserJoins.wzUserID,hcoHash,count(*) nRec from tblHashChanOwner  ";
$SQL .= "inner join tblwzUserJoins  on tblwzUserJoins.wzUserID = hcoUID ";
$SQL .= "left join tblChatChannel  on tblChatChannel.channelID = hcoChatChanID ";
$SQL .= "inner join tblwsSearchLog  on hcoHash like search ";
$SQL .= "where  TIMESTAMPDIFF(day,date(sDate),(now())) < 24 and not hcoHash like '%.%' ";
$SQL .= "group by hcoHash,firstname,tblwzUserJoins.wzUserID,storeBanID ";
$SQL .= "order by nRec desc limit ".$limit;
$result = mkyMsqry($SQL) or die($SQL);
$tRec = mkyMsFetch($result);
if ($userID == 17621){
  //echo $SQL;
}
echo "<div align='right'>";
echo "<input type='button' value=' Store Fronts ' onclick='doSendStoresReq();'/>";
echo "</div>";
echo "<h3 style='color:darkKhaki;'>".getTRxt('Trending Topics')."</h3>";
if(!$tRec){
  echo "<p/>".getTRxt('No Posts Found')."...";
}
$n = 0;
$posts = getTRxt('Qrys');
echo "<div class='row'>";
$n = 1;
$maxn = 5;
while ($tRec){
  if ($n == 1 ){
    echo "<div class='column'>";
  }

  echo "<a style='color:#eeeeee;' ";
  if ($sessISMOBILE){
    echo "href='javascript:top.wzGetPage(\"/whzon/mblp/homepg.php?mblGetPg=on&wzID=".$sKey."&sQry=".mkyUrlEncode($tRec['hcoHash'])."\");'>";
  }
  else {
    echo "href='javascript:top.wzGetPage(\"/whzon/public/homepg.php?wzID=".$sKey."&fhQry=".mkyUrlEncode($tRec['hcoHash'])."\");'>";
  }
  $storeBanID = $tRec['storeBanID'];
  if (!$storeBanID){
    $storeBanID = getBannerImgID($tRec['hcoHash']);
  }
  $img = "//image0.bitmonky.com/img/monkyTalkfbCard.png";
  $img = "https://image0.bitmonky.com/getStoreBGTmn.php?id=".$storeBanID;

  $newsImgStr = "";
  $newsImgStr = "<img ID='mshare".$storeBanID."'onerror='swapFailedmshareImg(".$storeBanID.")' ";
  $newsImgStr .= $imgs; // "style='height:80px;' ";
  $newsImgStr .= "src='".$img."'>";
  if ($storeBanID == null || $img == ''){
    $newsImgStr = null;
  }

  if ($newsImgStr){
    echo $newsImgStr;
  }
  else {
    echo "<img style='' ";
    echo "src='https://image0.bitmonky.com/getMbrImg.php?id=".$tRec['wzUserID']."'/>";
  }
  $title = str_replace('_',' ',$tRec['hcoHash']);
  $title = str_replace('-',' ',$title);
  $title = str_replace('.',' ',$title);

  echo "<div style='clip-path:inset(0% 0%);background:black;padding:.5em;'><b style='font-size:larger;'>";
  echo "<img src='https://image.gogominer.com/getMbrImg.php?mid=".$tRec['mbrMUID']."' ";
  echo   "style='float:right;width:3.5em;height:3.5em;border-radius:50%;margin:0.5em;'/>";
  echo "<h2>".$title."</h2>";
  echo "<b>BMGP Licensee:</b> <span style='color:gray;font-size:smaller;'>".$tRec['firstname']."<p/>".$tRec['nRec']." ".$posts."</span></div></a>";

  $n = $n + 1;
  if ($n == $maxn){
    $n=1;
    echo "</div>";
  }
  $tRec = mkyMsFetch($result);
}
if ($n != 1){
  echo "</div>";
}
function getBannerImgID($tag){

  $SQL = "select hcoID as acHashID,hcoUID from tblHashChanOwner  ";
  $SQL .= "where hcoHash='".$tag."' limit 1";

  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if(!$tRec){
    return null;
  }

  $hashID = $tRec['acHashID'];
  $bannerID = 0;
  $SQL = "select bannerID,bannerUID,yoffset, height,cropMd5ID from ICDimages.tblStoreBanner ";
  $SQL .= "where bnHashHOID=".$hashID." order by bDate desc";

  $myresult = mkyMyqry($SQL);
  $mRec = mkyMyFetch($myresult);
  $bannerUID = null;
  if ($mRec){
    $bannerID  = $mRec['bannerID'];
    return $bannerID;
  }
  else {
    return null;
  }
}
?>
</div>

