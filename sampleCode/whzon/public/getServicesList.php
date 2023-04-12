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

$serviceSearch = " 1=1 ";
$SQL = "SELECT psrvID,psrvWallet,psrvHost,psrvPort,psrvApiPath,psrvLogin,psrvName,psrvStatus,";
$SQL .= "psrvActiveDate,left(psrvDesc,800)psrvDesc FROM ICDirectSQL.tblPassportService ";
$SQL .= "where ".$serviceSearch." ";
$SQL .= "order by psrvActiveDate desc limit ".$limit;
$result = mkyMsqry($SQL);
$tRec = mkyMsFetch($result);

echo "<div align='right'>";
echo "<input type='button' value=' View Trending Channels ' onclick='doSendTrendingReq();'/>";
echo "</div>";
echo "<h3 style='color:darkKhaki;'>".getTRxt('Passport Services Directory')."</h3>";
if(!$tRec){
  echo "<p/>".getTRxt('No Services Found')."...";
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
  echo "href='javascript:alert(\"Service Pending\");'>";
  
  $psrvID = $tRec['psrvID'];
  $sport  = $tRec['psrvPort'];
  if ($sport){
    $sport = ":".$sport;
  }
  $srvLogin = "https://".$tRec['psrvLogin'];
  $srvRoot  = "https://".$tRec['psrvHost'].$sport."/";

  $img  = $srvRoot.'psrvBanner.png';
  $icon = $srvRoot.'psrvIcon.png';

  $newsImgStr = "";
  $newsImgStr = "<img ID='mshare".$psrvID."'onerror='swapFailedmshareImg(".$psrvID.")' ";
  $newsImgStr .= $imgs; // "style='height:80px;' ";
  $newsImgStr .= "src='".$img."'>";

  echo "<a style='color:#eeeeee;' ";
  if ($tRec['psrvStatus'] == 'pending'){
    echo "href='javascript:alert(\"Service Pending\");'>";
  }
  else {
    $port = $tRec['psrvPort'];
    if (!$port){
      $port = "";
    }
    $psrv = new stdClass;
    $psrv->host = $tRec['psrvHost'];
    $psrv->port = $port;
    $psrv->endPoint = $tRec['psrvApiPath'];
    echo "href=\"javascript:doServiceLogin('".urlencode(json_encode($psrv))."')\";>";
  }
  echo $newsImgStr;
  echo "<div style='clip-path:inset(0% 0%);background:black;padding:.5em;'>";
  echo "<img src='".$icon."' ";
  echo   "style='float:right;width:3.5em;height:3.5em;border-radius:50%;margin:0.5em;'/>";
  echo "<h2>";
  echo $tRec['psrvName']."</h2><span style='color:gray;font-size:smaller;'>".$tRec['psrvDesc']." <p/>".$tRec['psrvStatus']."</span></div></a>";

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
echo "</div><div class='infoCardClear'style='background:#222324;margin:0.6em;padding:.6em;'>";
echo "Read How To Write A Bitmonky Web Service for your website ";
echo "on Github : <a target='bitGit' href='https://github.com/bitmonky/bmgpPassport/blob/main/README.md'>github.com/bitmonky/bmgpPassport</a>";
echo "<div align='right'><input type='button' value=' Register Your Service ' onclick='doSendRegServiceFrm();'/></div>";
echo "</div>"
?>

</div>

