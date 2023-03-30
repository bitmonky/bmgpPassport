<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
include_once("../mkysess.php");
?>
<p><b style='color:darkKaki;'>Click On Member To Select:</b>
<?php 
  $str     = safeGET('fqry');

  $SQL = "SELECT mbrMUID, wzUserID,firstname from tblwzUser ";
  $SQL .= "where sandBox is null and not wzUserID = ".$userID." and firstname like '%".$str."%' order by lastOnline desc limit 30";
  $tRec = null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if(!$tRec){
    echo "<p/>No Members Found With That Name...";
  }
  $n = 0;
  While ($tRec){
    echo "<div ID='wzline:".$n."' onmouseover='highlight(".$n.");' onmouseout='undoHighlight(".$n.");' ";
    echo "style='background:#232425;border-radius:.25em;padding:.15em;margin:.1em;'>";
    echo "<a style='color:#888888;font-size:larger;' ";
    echo "href='javascript:getSendGoldToMbr(\"".$tRec['mbrMUID']."\");'>";
    echo "<img style='vertical-align:top;width:34px;height:44px;margin:.5em;margin-right:1.5em;border-radius:50%;border: 0px solid #74a02a;' ";
    echo "src='".$GLOBALS['MKYC_imgsrv']."/getMbrTmn.php?id=".$tRec['wzUserID']."'/>".$tRec['firstname']."</a></div>";
    $n = $n + 1;
    $tRec = mkyMsFetch($result);
  }
?>
