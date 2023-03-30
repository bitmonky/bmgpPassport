<?php
$mkySQLLocal = true;

include_once("../mkysess.php");
ini_set('display_errors',1);
error_reporting(E_ALL);
include_once("displayMQryObjInc.php");
include_once("passportDispInc.php");
$time_pre = microtime(true);

$qry=safeGET('search');

echo "<div align='right'>";
echo "<input type='button' value=' View Trending Channels ' onclick='doSendTrendingReq();'/> ";
echo "<input type='button' value=' Store Fronts ' onclick='doSendStoresReq();'/>";
echo "</div>";


$SQL  = "SELECT count(*)nRec  from (";
$SQL .= "select acmeACID FROM ICDirectSQL.tblActivityMemories group by acmeACID)R";
$res = mkyMyqry($SQL);
$rec = mkyMyFetch($res);
echo "<h2>Total Memories Stored: ".mkyNumFormat($rec['nRec'])."</h2>";

if($qry){
  $qry = left(prepWords($qry),500);

  //*Log serch to the top qry file.
  $SQL = "select pqryID from ICDirectSQL.tblPeerMemTopQrys where pqryQry = '".$qry."'";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if ($rec){
    $SQL = "update ICDirectSQL.tblPeerMemTopQrys set pqryNQrys = pqryNQrys + 1 where pqryID = ".$rec['pqryID'];
  }
  else {
    $SQL = "insert into ICDirectSQL.tblPeerMemTopQrys (pqryQry,pqryNQrys) values ('".$qry."',1)";
  }
  mkyMyqry($SQL);

  $SQL = "select pmacPMemOwner from ICDirectSQL.tblPeerMemoryAcc";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  $mbrMUID = $rec['pmacPMemOwner'];

  //echo "Start Search:".(microtime(true) - $time_pre);
  $j = ptreeSearchMem($mbrMUID,$qry,$type=null);
  //echo "<br/>End Search:".(microtime(true) - $time_pre);

  $j = mkyStrReplace('"{','{',$j);
  $j = mkyStrReplace('}"','}',$j);
  $j = mkyStrReplace('\\"','"',$j);
  $j = mkyStrReplace('NULL','',$j);
  
  $r = json_decode($j);
  
  if ($r->result){
    echo "<h2>Search Result</h2>";
    $nrec = sizeof($r->data) -1;
    if ($nrec > 0){
      echo "Records found: <span class='mkyNumber'>".sizeof($r->data)."</span><p/>";
    }
    //echo "Start Render:".(microtime(true) - $time_pre);
    $n=1;
    $ACIDs = [];

    $fstr = '';
    forEach($r->data as $rec){
      $fstr .= $rec->pmcMemObjID."\t";
      $fstr .= $rec->pmcMemObjNWords."\t";
      $fstr .= $rec->nMatches."\t";
      $fstr .= $rec->score."'\r\n";
      $n=$n+1;
      if ($n > 20){
        break;
      }

    }	      
    if (!file_put_contents('/var/www/www.bitmonky.com/wzAdmin/peerQry.txt',$fstr)){
      exit('fail to write query result file');
    }      
    $SQL  = "TRUNCATE TABLE ICDirectSQL.tmpPeerQry; ";
    $qres = mkyMyqry($SQL);
    $SQL  = "LOAD DATA LOCAL INFILE '/var/www/www.bitmonky.com/wzAdmin/peerQry.txt' INTO TABLE ICDirectSQL.tmpPeerQry;";
    $qres = mkyMyqry($SQL);

    $SQL = "select firstname,mbrMUID,tags,contentOwnerID,activityID,websiteID, acCode, acLink,acItemID, ";
    $SQL .= "tblwzUserJoins.wzUserID,firstname,age,sex ";
    $SQL .= "from tblActivityFeed ";
    $SQL .= "left join tblActivityMemories on acmeACID = activityID ";
    $SQL .= "inner join tmpPeerQry on acmeMemHash = pmcMemObjID ";
    $SQL .= "left join tblwzUserJoins on tblwzUserJoins.wzUserID = tblActivityFeed.wzUserID ";
    $SQL .= "order by score desc ";
    $qres = mkyMyqry($SQL);
    $qrec = mkyMyFetch($qres);
    $n = 1;
    if (!$qrec){
      echo "<br/> - No Results Found";
    }
    $n = 0;
    echo "<div class='row'>";
    $n = 1;
    $maxn = 5;
    while ($qrec && $n < 15){
      if ($n == 1 ){
        echo "<div class='column'>";
      }
      echo "<div class='infoCardClear' style='background:black;'>";
      display($qrec);
      formatHashTagsNUPS($qrec['tags'],0);
      echo "<br/>ActivityID: ".$qrec['activityID']." - ".$qrec['tags'];
      echo "</div>";
      $qrec = mkyMyFetch($qres);
      $n = $n + 1;
      if ($n == $maxn){
        $n=1;
        echo "</div>";
      }
    }
    if ($n != 1){
      echo "</div>";
    }
    //echo "End Render:".(microtime(true) - $time_pre);
  }
}
echo "</div><p/>Job Complete:\n";
$time_post = microtime(true);
$exec_time = $time_post - $time_pre;
echo "Job Run Time: ".$exec_time."\n";
echo "</div>";

function inList($ac){
  forEach($GLOBALS['ACIDs'] as $acid){
    if ($acid == $ac){
      return true;
    }
  }
  return false;
}
function drawRecentSearches(){
  echo "<div ID='drawRSearches'></div>";
}
function display($tRec){
        global $sKey; 
        $acCode   = $tRec['acCode'];
        $wzUserID = $tRec['wzUserID'];
        echo "<img src='https://image.gogominer.com/getMbrImg.php?mid=".$tRec['mbrMUID']."' ";
        echo   "style='float:right;width:3.5em;height:3.5em;border-radius:50%;margin:0.5em;'/>";
	echo $tRec['firstname'];

        if ($acCode == 2 || $acCode == 12 || $acCode == 14 || $acCode == 24){
          $wsID = $tRec['acItemID'];
          if (!$wsID){
            $wsID = $tRec['websiteID'];
          }
          sayNewWS($wzUserID, $tRec['firstname'], $wsID, $acCode);
        }

        if ($acCode == 1){
          $SQL = "select profileText from tblwzUser where wzUserID = ".$wzUserID;
          $cRes = mkyMyqry($SQL);
          $cRec = mkyMyFetch($cRes);
          sayNewUser($wzUserID, $tRec['firstname'], $cRec['profileText']);
        }


        if ($acCode == 16) {
          $fresult=sayNewSong($wzUserID, $tRec['firstname'], $tRec['acLink'],$tRec['activityID']);
        }

        if ($acCode == 4) {
          $fresult = sayNewAD($wzUserID, $tRec['firstname'], $tRec['acLink'],$tRec['acItemID'],$sKey);
        }

        if ($acCode == 5) {
          $fresult = sayNewEvent($wzUserID, $tRec['firstname'], $tRec['acLink'],$tRec['acItemID'],$sKey);
        }

        if ($acCode == 6) {
          sayNews($wzUserID, $tRec['firstname'], $tRec['acLink']);
        }

        if ($acCode == 7) {
          sayNewPhoto($wzUserID, $tRec['firstname'], $tRec['acItemID'],$tRec['activityID']);
        }

        if ($acCode == 17) {
          sayNewVideoShare($wzUserID, $tRec['firstname'], $tRec['acItemID'],$tRec['activityID']);
        }
        if ($acCode == 18) {
          sayWNewsShare($wzUserID, $tRec['firstname'], $tRec['acItemID'],$sKey,$tRec['activityID']);
        }

        if ($acCode == 22) {
          sayNewChannel($wzUserID,$tRec['firstname'],$tRec['acItemID'],$tRec['activityID']);
        }

        if ($acCode == 23) {
          sayNewLiveStream($wzUserID,$tRec['firstname'],$tRec['acItemID'],$tRec['activityID']);
        }
        if ($acCode == 19) {
          saySItemShare($wzUserID, $tRec['firstname'], $tRec['acItemID'],$sKey);
        }

        if ($acCode == 8) {
          sayBLOG($wzUserID, $tRec['firstname'], $tRec['acItemID'],$sKey);
        }

        if ($acCode == 13) {
          sayInMoshBox($wzUserID, $tRec['firstname'], $tRec['acLink'] , $hisher);
        }

} 
?>

