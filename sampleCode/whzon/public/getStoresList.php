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

$storeSearch = " 1=1 ";
$SQL = "SELECT tblwzOnline.wzUserID as online,mbrMUID,storeID, storeDesc,storeTitle,storeUID,status, ";
$SQL .= " tblCity.name as city, date(lastOnline)lastOnline,tblStore.Status,coverage,storeCityID  ";
$SQL .= " from tblStore  inner join tblCity  on cityID=storeCityID ";
$SQL .= " inner join tblStoreCoverage  on CoverageID = storeCoverageID ";
$SQL .= " inner join tblwzUserJoins  on storeUID = tblwzUserJoins.wzUserID ";
$SQL .= " left join tblwzOnline  on storeUID = tblwzOnline.wzUserID ";
$SQL .= " where ".$storeSearch." ";
$SQL .= " order by activated desc, nProducts desc, lastOnline desc limit ".$limit;

$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);

echo "<div align='right'>";
echo "<input type='button' value=' View Trending Channels ' onclick='doSendTrendingReq();'/>";
echo "</div>";
echo "<h3 style='color:darkKhaki;'>".getTRxt('Store Fronts')."</h3>";
if(!$tRec){
  echo "<p/>".getTRxt('No Stores Found')."...";
}
$n = 0;
echo "<div class='row'>";
$n = 1;
$maxn = 5;
while ($tRec){
  if ($n == 1 ){
    echo "<div class='column'>";
  }

  echo "<a style='color:#eeeeee;' ";
  if ($sessISMOBILE){
    echo "href='javascript:top.wzGetPage(\"/whzon/store/storeProfile.php?wzID=".$sKey."&fstoreID=".$tRec['storeID']."\");'>";
  }
  else {
    echo "href='javascript:top.wzGetPage(\"/whzon/store/storeProfile.php?wzID=".$sKey."&fstoreID=".$tRec['storeID']."\");'>";
  }
  $storeID = $tRec['storeID'];
  $img = getStoreBannerImg($storeID);

  $newsImgStr = "";
  $newsImgStr = "<img ID='mshare".$storeID."'onerror='swapFailedmshareImg(".$storeID.")' ";
  $newsImgStr .= $imgs; // "style='height:80px;' ";
  $newsImgStr .= "src='".$img."'>";
  if ($storeID == null || $img == ''){
    $newsImgStr = null;
  }

  if ($newsImgStr){
    echo $newsImgStr;
  }
  else {
    echo "<img style='' ";
    echo "src='".$GLOBALS['MKYC_imgsrv']."/getMbrImg.php?id=".$tRec['wzUserID']."'/>";
  }
  echo "<div style='clip-path:inset(0% 0%);background:black;padding:.5em;'>";
  echo "<img src='https://image.gogominer.com/getMbrImg.php?mid=".$tRec['mbrMUID']."' ";
  echo   "style='float:right;width:3.5em;height:3.5em;border-radius:50%;margin:0.5em;'/>";
  echo "<h2>";
  echo $tRec['storeTitle']."</h2><span style='color:gray;font-size:smaller;'>".$tRec['storeDesc']." <p/>".$tRec['status']."</span></div></a>";

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
function getStoreBannerImg($storeID){
/*  $SQL = "select bannerID,height,cropMd5ID from ICDimages.tblStoreBanner ";
  $SQL .= "where bannerSID=".$storeID." order by bDate desc";

  $myresult = mkyMyqry($SQL);
  $mRec = mkyMyFetch($myresult);
  if ($mRec){
    return $GLOBALS['MKYC_imgsrv']."/getBGImg.php?mode=view&id=".$mRec['bannerID'];
  }
 */
  $SQL = "select bannerID,useSysBanID,yoffset,height,cropMd5ID from ICDimages.tblStoreBanner ";
  $SQL .= "where udefault = 1 and bannerSID=".$storeID;
  $myresult = mkyMyqry($SQL);
  $mRec = mkyMyFetch($myresult);

  if($mRec){
    if ($mRec['useSysBanID']){
      $SQL = "select bgName,yOffset from tblDefBackground where bgID = ".$mRec['useSysBanID'];
      $result = mkyMsqry($SQL);
      $tRec = mkyMsFetch($result);
      return $GLOBALS['MKYC_imgsrv'].'/img/'.$tRec['bgName'];
    }
    return  $GLOBALS['MKYC_imgsrv']."/getBGImg.php?mode=view&id=".$mRec['bannerID'];
  } 
  $SQL = "select bgID,bgName,yOffset from tblDefBackground order by rand() limit 1";
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  return $GLOBALS['MKYC_imgsrv'].'/img/'.$tRec['bgName'];
}
?>
</div>

