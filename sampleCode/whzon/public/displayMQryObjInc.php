<?php

include_once("../utility/acHash.php");
if ($userID == 17621){
  //ini_set('display_errors',1); 
  //error_reporting(E_ALL);
}
function sayPhotoAssignment($uID, $firstname, $acItemID,$acID){
  global $sKey;
  global $userID;
  $SQL  = "SELECT name,tycEmpID,tycJobTitle,tycJobDesc,firstname,tycEmpUID,tjobAction,tycCityID from tblwzPhoto  ";
  $SQL .= "inner join tblTycJobDesc  on tycJobID = phoJobID ";
  $SQL .= "inner join tblTycEmployee  on tycEmpUID = tblwzPhoto.wzUserID and tycEmpType = phoJobID ";
  $SQL .= "inner join tblwzUser  on tblwzUser.wzUserID = tblwzPhoto.wzUserID ";
  $SQL .= "inner join tblCity on tblCity.cityID=tycCityID ";
  $SQL .= "where Not tycJobID = 5 and photoID = ".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if (!$tRec){
    return false;
  }

  $cityName  = $tRec['name'];
  $tycCityID = $tRec['tycCityID'];
  $jTitle    = $tRec['tycJobTitle'];
  $jAction   = $tRec['tjobAction'];


  global $divWidth;
  $sayNewPhoto = 0;
  $SQL = "SELECT top 1 photoID,height,width, title, phototxt from tblwzPhoto  ";
  $SQL .= "where photoID = ".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);

  if ($tRec){
    $acItemID = $tRec['photoID'];
    $SQL = "SELECT top 1 wzUserID from tblwzUser  ";
    $SQL .= "where cityID = ".$tycCityID;
    $cresult = mkyMsqry($SQL);
    $cRec = mkyMsFetch($cresult);

    $jTycID   = $cRec['wzUserID'];

    $sImg = "//image0.bitmonky.com/getPhotoTmn.php?id=".$acItemID;
    $title = $tRec['title'];
    $story = shortenTextTo($tRec['phototxt'],500);

    formatHashTags($story,$acID);
    $imgID = "fImage".$acItemID;
    $h     = $tRec['height'];
    $w     = $tRec['width'];
    $idim  = ' onload="fixImageSizeMax(\'".$imgID."\',350);" ';
    $maxw  = 380;
    if ($divWidth){
      $maxw = $divWidth;
    }
    if  ($w){
      if ($w > $maxw){
        $scale = $w/$h;
        $w = '100%';
        $h = intval($h / $scale);
      }
      else {
        $w .= 'px';
      }
      $idim = ' style="width:'.$w.';height='.$h.'px;" ';
    }
    $jCityAnk = "javascript:top.wzGetPage(\"/whzon/mytown/myTownProfile.php?wzID=".$sKey."&fwzUserID=".$jTycID."&fscope=myCity&fmyMode=mbrs\");";
    $ank = "<a style='color:darkKhaki;' href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."');\">";
    echo "<div style='background:#222322;color:white;padding:7px;border-radius:5px;'>";
    echo getJobActivitySource($acID);
    echo "<p/><span style='color:darkKhaki;'>".getTRxt('Local '.$jAction)." For</span> <a style='color:darkKhaki;' href='".$jCityAnk."'>".$cityName."</a><p>".$story."<p/>";
    echo getTRxt('Read Full Report')."... ".$ank.getTRxt('Here')."</a>";
    echo "</div><p/>";

    echo " <a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."');\">";
    echo "<center><img ID='".$imgID."' ".$idim." src='".$GLOBALS['MKYC_imgsrv']."/getPhotoImg.php?id=".$acItemID."&fpv=".$uID."' ";
    echo "style='border-radius: .5em;border: 0px solid #777777;margin:0px;'></center></a><br/>";
 
    echo "<div style='padding:7px;'>";
    echo "<a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID;
    echo "&vPhotoID=".$acItemID."');\">".getTRxt('View Full Report Here')."...</a><br>";
    echo "</div>";
    return true;
  }
  return false;
}
function sayNewLiveStream($wzUserID,$fname,$cstrID,$acID){
   global $sKey;
   echo "<p/>".$fname." Has Started A New Live Stream";

   $src = $GLOBALS['MKYC_imgsrv'].'/img/monkyTalkfbCard.png';
   $style = "float:right;margin: 0em 0em 1em 1em;border-radius:.25em;";

   $SQL = "select bname from tblChanLiveStreams   where cstrID = ".$cstrID;
   $presult = mkyMsqry($SQL);
   $pRec = mkyMsFetch($presult);
   if ($pRec){
     $src = "/whzon/live/thumbs/".$pRec['bname'].".png";
     $style = "width:100%";
   }
   echo "<div width='100%;' >";
   echo "<img style='".$style."' src='".$src."'/>";
   echo "</div>";

   $lsLink = 'javascript:top.wzGetVideoPage("/whzon/live/chan/chanLiveStreams.php?wzID='.$sKey.'&videoID='.$cstrID.'")';
   echo "<p/><a href='".$lsLink."'>Go There Now</a>";
}
function sayTodaysWeather($cityID){
  $SQL  = "SELECT name,tycEmpID,tycJobTitle,tycJobDesc,firstname,tycEmpUID from tblTycJobDesc  "; 
  $SQL .= "inner join tblTycEmployee  on tycEmpType = tycJobID ";
  $SQL .= "inner join tblwzUser  on wzUserID = tycEmpUID ";
  $SQL .= "inner join tblCity on tblCity.cityID=tycCityID ";
  $SQL .= "where tycJobID = 6 and tycCityID = ".$cityID." ";
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if (!$tRec){
    return;
  }
  $empID    = $tRec['tycEmpUID'];
  $cityName = $tRec['name'];
  $uID      = $empID;  
  global $divWidth;
  $sayNewPhoto = 0;
  $SQL = "SELECT top 1 photoID,height,width, title, phototxt from tblwzPhoto  ";
  $SQL .= "where wzUserID=".$empID." and phoJobID = 6 and TIMESTAMPDIFF(day,date(pDate),date(now())) = 0 ";
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);

  if ($tRec){
    $acItemID = $tRec['photoID'];
    $acID     = $tRec['acID'];

    $sImg = "//image0.bitmonky.com/getPhotoTmn.php?id=".$acItemID;
    $title = $tRec['title'];
    $story = shortenTextTo($tRec['phototxt'],500);

    formatHashTags($story,$acID);
    $imgID = "fImage".$acItemID;
    $h     = $tRec['height'];
    $w     = $tRec['width'];
    $idim  = ' onload="fixImageSizeMax(\'".$imgID."\',350);" ';
    $maxw  = 380;
    if ($divWidth){
      $maxw = $divWidth;
    }
    if  ($w){
      if ($w > $maxw){
        $scale = $w/$maxw;
        $w = $maxw;
        $h = intval($h / $scale);
      }
      $idim = ' width="'.$w.'" height="'.$h.'" ';
    }
    $ank = "<a href=\"/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."\">";

    echo "<p/>".getTRxt('Todays Weather Report')." <b>".$cityName."</b><p>".$story."<p/>";
    echo getTRxt('Read Full Report')."... ".$ank.getTRxt('Here')."</a>";
    echo "<p/>";
    echo " <a href=\"/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."\">";
    echo "<br/><center><img ID='".$imgID."' ".$idim." src='".$GLOBALS['MKYC_imgsrv']."/getPhotoImg.php?id=".$acItemID."&fpv=".$uID."' ";
    echo "style='border-radius: .5em;border: 0px solid #777777;margin:0px;'></center></br></a>";
    echo " <a href=\"/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID;
    echo "&vPhotoID=".$acItemID."\">".getTRxt('View Report Here')."...</a><br>";
  }
  else {
    $sayNewPhoto = 1;
  }
}
function sayNewChannel($uID,$firstname,$chanID,$acID){
  global $sKey,$userID;
  $SQL  = "Select tblChatChannel.name,guide,storeBanID,tblChatChannel.channelID,hcoHash from tblChatChannel  ";
  $SQL .= "left join tblHashChanOwner  on tblChatChannel.channelID = hcoChatChanID ";
  $SQL .= "inner join tblwzUser  on tblwzUser.wzUserID = tblChatChannel.ownerID ";
  $SQL .= "inner join tblCity on tblCity.cityID = tblwzUser.cityID ";
  $SQL .= "where tblChatChannel.channelID=".$chanID;
  //if ($userID == 17621){echo $SQL;}
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);

  echo "<b/>".$firstname." Has Started A New Channel</b>";
  echo "<p/><span style='color:darkKhaki;'>Title:</span> ".$tRec['name'];
  echo "<br/><span style='color:darkKhaki;'>Description:</span> <br/>".$tRec['guide'];
  echo "<p/>";
  echo "<a href=\"javascript:top.wzGetPage('/whzon/public/homepg.php?wzID=".$sKey."&fhQry=".mkyUrlEncode($tRec['hcoHash'])."');\">";
  if ($tRec['storeBanID']){
    echo "<img style='width:100%;' src='".$GLOBALS['MKYC_imgsrv']."/getBGImg.php?id=".$tRec['storeBanID']."'/>";
  }
  echo "<p/>View The Channel Here</a>";
  
}
function sayNewUser($uID, $firstname, $greeting){
  global $whzdom,$sitename;
  $SQL = "select imgFlg from tblwzUser  where wzUserID=".$uID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  $imgID = "nUserImg".$uID;
    
  echo "<p/>".getTRxt('Has Joined')." ".$sitename;
  echo " <b>".getTRxt('and says').":</b><p/> ".left($greeting,250)."...<br/>";
  echo "<p/><a href=javascript:top.wzGetPage('/whzon/mbr/mbrProfile.php?fwzUserID=".$uID."')>";
  if($tRec['imgFlg'] == 1){
    echo "<center><img  src='".$GLOBALS['MKYC_imgsrv']."/getMbrPImg.php?id=".$uID."' <img ID='".$imgID."' onload=\"fixImageSizeMax('".$imgID."',350);\"  style='border-radius: .5em;border: 0px solid #777777;margin:0px;'></center></br></a>";
  }
  return 0;
}
function sayNewVideoShare($uID, $firstname, $acItemID,$acID){
  $SQL = "SELECT vTitle, vDesc, vidURL from tblwzVideo  where wzVideoID=".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  $fresult = 0;
  if ($tRec){
    $sImg = "//image0.bitmonky.com/getPhotoTmn.php?id=".$acItemID;
    $title = splitLWords(utf8str($tRec['vTitle']));
    $story = splitLWords(shortenTextTo(utf8str($tRec['vDesc']),500));
    formatHashTags($story,$acID);
    $img = $tRec['vidURL'];
    $vURL = "//".$img;
	
    $img = preg_replace('/.*youtube.com/i','youtube.com',$img);
    $img = mkyStrIReplace("www.youtube.com/v","//i2.ytimg.com/vi",$img);
    $img = mkyStrIReplace("youtube.com/v","//i2.ytimg.com/vi",$img);
    $img = $img."/hqdefault.jpg";
   
    echo "<p/>".getTRxt('Has posted a new Video'); 
    echo "<div><a href=\"javascript:top.videoShare(".$acItemID.");\">";
    echo "<img alt='profile pic' style='width:100%;padding:0px;border-radius: .5em;border: 0px solid #666666;' src='".$img."'></a>";
    echo "</div>"; // <a href=\"javascript:top.videoShare(".$acItemID.");\">View Video Here...</a><br>";
    echo "<h2 style='font-size:1.3em;'>".$title."</h2>";
    echo "<div style='color:#777777;'>".$story."</div>"; 
  }
  else {
    $fresult = 1;
  }
 return $result;
}  
function saySItemShare($uID, $firstname, $acItemID,$sKey){
  $saySItemShare = 0;
  $SQL  = "select itemStoreID,item,AdBody,storeTitle from tblClassifieds  ";
  $SQL .= "inner join tblStore  on storeID = itemStoreID where adID=".$acItemID;
  
  $result = mkyMsqry($SQL);
  $tRec   = mkyMsFetch($result);

  if ($tRec){
    $storeID = $tRec['itemStoreID'];
    $storeName = $tRec['storeTitle'];
    $iwidth = 400;
    $iheight = 250;
    $margin  = "margin: 3px 8px 3px 0px;";

    //$URL = $mRec['URL'];

    $newsImgStr  = "";
    $itemImgStr  = "<img style='width:".$iwidth."px;height:".$iheight."px;".$margin." border-radius: .5em;border: 0px solid #666666;' ";
    $itemImgStr .= "src='".$GLOBALS['MKYC_imgsrv']."/getStoreItemImg.php?ad=".$acItemID."&fs=".$storeID."'>";

    $title = $tRec['item'];

    $story = shortenTextTo($tRec['AdBody'],500);
    //formatHashTags($story,$acID);

    echo "<p/>".getTRxt('Has shared a product from').":<h3>".$storeName."</h3><p style='margin-right:12px;'><b>Item - ".$title."</b> - ".$story."</p>"; 
    echo "<a href='javascript:top.viewStoreItem(".$storeID.",".$acItemID.");'>";
    echo "<br/><center>".$itemImgStr."</center></br></a>";
    echo " <a href='javascript:top.viewStoreItem(".$storeID.",".$acItemID.");'>";
	echo getTRxt('Buy Here')."...</a> | <a href='/whzon/store/storeProfile.php?wzID=".$sKey."&fstoreID=".$storeID."'>".getTRxt('See more from this store')."</a><br/>";
  }
  else {
    $saySItemShare = 2;
  }
  return $saySItemShare;
}  
function sayWNewsShare($uID, $firstname, $acItemID,$sKey,$acID){

  $sayNewsShare = 0;
  $SQL  = "SELECT  urlLink, urlImgLink,urlTitle,urlDesc  FROM newsDirectory.tblUrlShares ";
  $SQL .= "where urlShareID = ".$acItemID;
  
  $myresult = mkyMyqry($SQL);
  if ($myresult){$tRec = mkyMyFetch($myresult);} else { $tRec=null;}

  if ($tRec){
    $oimg = $tRec['urlImgLink'];
    $margin  = "margin: 12px 20px 12px 0px;";
    $iwidth = '100%';
    $URL = $tRec['urlLink'];
    $img = fixUTubeImg($URL,$oimg);
    if ($oimg == '0.jpg'){
      $SQL  = "update newsDirectory.tblUrlShares set  urlImgLink ='".$img."' ";
      $SQL .= "where urlShareID = ".$acItemID;
      $myres = mkyMyqry($SQL);
    }
    $img = $GLOBALS['MKYC_imgsrv']."/getNShareImg.php?id=".$acItemID;
    $newsImgStr = "";
    $newsImgStr = "<img style='width:".$iwidth.";".$margin." border-radius: .5em;border: 0px solid #e0e0e0;' ";
    $newsImgStr .= "src='".$img."'>";
    $newsImgStr = acidImageDOM($newsImgStr,$acID);
    if ($img == null || $img == ''){
      $newsImgStr = null;
    }

    $title = $tRec['urlTitle'];

    $story = shortenTextTo($tRec['urlDesc'],500);
    formatHashTags($story,$acID);

    echo "<p/>".getTRxt('Has shared a web link')."<b><h2 style='font-size:1.3em;'>".$title."</h2><div style='color:#777777;'>".$story."</div><br>";
    echo " <a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewWNewsShare.php?wzID=".$sKey."&newsID=".$acItemID."');\">";
    echo "<br/><center>".$newsImgStr."</center></br></a>";
    echo " <a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewWNewsShare.php?wzID=".$sKey."&newsID=".$acItemID."');\">";
    echo getTRxt('View Full Story Here')."...</a><br/>";
  }
  else {
    $sayNewsShare = 2;
  }
  return $sayNewsShare;
}  

function sayNewPhoto($uID, $firstname, $acItemID,$acID){
  global $divWidth;
  if (sayPhotoAssignment($uID, $firstname, $acItemID,$acID)){
    return 0;
  }
  $sayNewPhoto = 0;
  $SQL = "SELECT height,width, title, phototxt from tblwzPhoto  where photoID=".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec){
    $sImg = "//image0.bitmonky.com/getPhotoTmn.php?id=".$acItemID;
    $title = $tRec['title'];
    $story = shortenTextTo($tRec['phototxt'],500);
    formatHashTags($story,$acID);
    $imgID = "fImage".$acItemID;
    $h     = $tRec['height'];
    $w     = $tRec['width'];
    $idim  = ' onload="fixImageSizeMax(\'".$imgID."\',350);" ';
    $maxw  = 380;
    if ($divWidth){
      $maxw = $divWidth;
    }
    if  ($w){
      if ($w > $maxw){
        $scale = $w/$h;
        $w = '100%';
        $h = intval($h / $scale);
      } 
      else {
        $w .= 'px';
      }
      $idim = ' style="width:'.$w.';height='.$h.'px;" ';
    }
    $idim = ' style="width:100%;" ';
    $ank = "<a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."');\">";

    echo "<p/>".getTRxt('Has posted a new Photo')." <b>".$title."</b><p>".$story."<p/>"; 
    echo getTRxt('Read Full Story')."... ".$ank.getTRxt('Here')."</a>";
    echo "<p/>";
    echo " <a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID."&vPhotoID=".$acItemID."');\">";
    echo "<br/><center><img ID='".$imgID."' ".$idim." src='".$GLOBALS['MKYC_imgsrv']."/getPhotoImg.php?id=".$acItemID."&fpv=".$uID."' ";
    echo "style='border-radius: .5em;border: 0px solid #777777;margin:0px;'></center></br></a>";
    echo " <a href=\"javascript:top.wzGetPage('/whzon/mbr/mbrViewPhotos.php?fwzUserID=".$uID;
    echo "&vPhotoID=".$acItemID."');\">".getTRxt('View Photo Here')."...</a><br>";
  }
  else {
    $sayNewPhoto = 1;
  }
return $sayNewPhoto;
}  

function sayNewSong($uID, $firstname, $linkData, $activityID){
  $sayNewSong = 0;
  $SQL = "SELECT moshPitID from tblMoshPit  where wzUserID=".$uID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec){
    $boxID = $tRec['moshPitID'];
  }
  else {
    $boxID = 1;
  }

  $SQL = "SELECT name, title, artistID, utubeCD from tblmoshSong  inner join tblMoshArtist  on artistID=moshArtistID where songID=".$linkData;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    $uCode = $tRec['utubeCD'];
    $vCode = $uCode;
    $title = $tRec['title'];
    $artist = $tRec['name'];
    $sIMG = getSongIMG($uCode);

    echo "<img alt='profile pic' style='width:100px;height:65px;margin:5px 0 15px 15px;border-radius: .5em;border: 0px solid #666666;float:right;' src='".$sIMG."'>";
    echo " ".getTRxt('Has Added')." '".$title."' By ".$artist." ".getTRxt('to their moshBOX')."<br>"; 
    echo " <a href=\"javascript:acListenToSong('".$vCode."',".$activityID.");\">Listen</a> | <a href=\"javascript:top.OpenGigBox('".$boxID."');\">".getTRxt('Join The Crowd')."</a><br>";
    echo " <div ID='acMoshViewer".$activityID."'></div>";
  }
  else {
    $sayNewNewSong = 1;
  }
return $sayNewSong;
}  

function sayNewEvent($uID, $firstname, $linkData,$acItemID,$sKey){
  $sayNewAD = 0;
  if (!$linkData) {$linkData = $acItemID;}
  $SQL = "SELECT adID,imgFlg,item, shortDesc from tblClassifieds  where adID=".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    echo "<p/>".getTRxt('Has Posted A Classified Ad')."<p/><b>".getTRxt('Item:')."</b> ";
    echo left($tRec['item'],45)."<p/>".left($tRec['shortDesc'],100)."<p/>";
    if ($tRec['imgFlg']==1){
      echo "<div style='width:100%;'>";
      echo "<img alt='profile pic' style='width:100%;height:260;' ";
      echo "src='".$GLOBALS['MKYC_imgsrv']."/getClassImg.php?fs=".$uID."&id=".$acItemID."'>";
      echo "</div>";
    }
    echo "<p/><a href=\"/whzon/mbr/mbrViewClassified.php?wzID=".$sKey."&itemID=".$acItemID."\">".getTRxt('View Ad')."...</a><br>";
  }
  else {
    $sayNewAD = 1;
  }
  return $sayNewAD;
}
function sayNewAD($uID, $firstname, $linkData,$acItemID,$sKey){
  $sayNewAD = 0;
  if (!$linkData) {$linkData = $acItemID;}
  $SQL = "SELECT adID,imgFlg,item, shortDesc from tblClassifieds  where adID=".$acItemID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    echo "<p/>".getTRxt('Has Posted A Classified Ad')."<p/><b>".getTRxt('Item:')."</b> ";
    echo left($tRec['item'],45)."<p/>".left($tRec['shortDesc'],100)."<p/>"; 
    if ($tRec['imgFlg']==1){
      echo "<div style='width:100%;'>";
      echo "<img alt='profile pic' style='width:100%;height:260;' ";
      echo "src='".$GLOBALS['MKYC_imgsrv']."/getClassImg.php?fs=".$uID."&id=".$acItemID."'>";
      echo "</div>";
    }
    echo "<p/><a href=\"/whzon/mbr/mbrViewClassified.php?wzID=".$sKey."&itemID=".$acItemID."\">".getTRxt('View Ad')."...</a><br>";
  } 
  else {
    $sayNewAD = 1;
  }
  return $sayNewAD;
}  


function sayInMoshBOX($uID, $firstname, $linkData , $hisher){
  $himher = $hisher;
  if ($himher == "his") {
    $himher = "him";
  }
  if ($himher == "his/her") {
    $himher = "Them";
  }

  $SQL = "select gigID,moshPitID from tblMoshPit  where wzuserID=".$uID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec){
    $gigID = $tRec['gigID'];
    $boxID = $tRec['moshPitID'];
    $SQL = "select  utubeCD,artistID, tblMoshArtist.name, title,tblMoshArtist.img from tblMoshPerformance  ";
    $SQL .= "inner join tblmoshSong  on tblmoshSong.songID=tblMoshPerformance.songID "; 
    $SQL .= "inner join tblMoshArtist  on tblmoshSong.ArtistID=tblMoshArtist.moshArtistID ";
    $SQL .= "where moshPerformanceID=".$gigID;
    $result = mkyMsqry($SQL);
    $tRec = mkyMsFetch($result);
    if ($tRec) {
      $artistID = $tRec['artistID'];
      $artist   = $tRec['name'];
      $utubeCD  = $tRec['utubeCD'];
      $title    = $tRec['title'];
      if (is_null($tRec['img'])){
        $img=0;
      }
      else {
        $img=1;
      }
      ?><a href="javascript:top.OpenGigBox('<?php echo $boxID;?>');">
        <img alt='artist' title='On stage now: <?php echo $artist;?>' align='right'
        style='width:100px;height:65px;margin-bottom:9px;margin:5px 0 15px 15px;border: 0px solid #aaaaaa;'
        src='<?php echo getSongIMG($utubeCD);?>'>
      <?php 
      $musicIcon = "<img style='border:0px;vertical-align:top;margin:5px 0 15px 15px;height:10px;width:13px;' ";
      $musicIcon .= "src='".$GLOBALS['MKYC_imgsrv']."/img/musicIcon.png'>";

      echo "</a> ".getTRxt('Has Entered')." ".getTRxt($hisher)." moshBOX<br>"; 
      echo " ".getTRxt('and is listening to')." <b>".$title."</b> By <b>".$artist;
      echo "</b><br> <a href=\"javascript:top.OpenGigBox('".$boxID."');\">Join ".$himher.$musicIcon."</a><br>";
    }
  }
}  

function sayNewWS($uID, $firstname, $wsID, $acCode){
  global $sKey;
  $sayNewWS = 0;
  if ($acCode == 2 ) {
    $acName = " ".getTRxt('Has Posted A New Website Listing')." ";
  } 
  else {
    if ($acCode == 14) {
      $acName = " Has Request To List This Website";
    } 
    else {
      if ($acCode == 24){
        $acName = " Has Reviewed This Website Listing ";
      }
      else {
        $acName = " Has Recommended This Website ";  
      }
    }
  }
  $SQL = "SELECT Url,wsimgFlg,title,Description, height,width,tblCity.name city,tblState.name state, ";
  $SQL .= "Category2.name cat,tblCountry.name country from tblWebsites  ";
  $SQL .= "left join tblCity  on tblCity.CityID = tblWebsites.cityID ";
  $SQL .= "left join tblState  on tblCity.StateID = tblState.stateID ";
  $SQL .= "left join tblCountry  on tblCity.CountryID = tblCountry.countryID ";
  $SQL .= "left join Category2 on categoryID = oldCategoryID ";
  $SQL .= "where websiteID=".$wsID;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    $h = $tRec['height'];
    $w = $tRec['width'];
    echo $acName;
    echo "<p/><div style='width:100%;'>";
    if ($tRec['wsimgFlg'] == 1) {
      echo "<img alt='website pic' style='width:100%;' ";
      echo "src='".$GLOBALS['MKYC_imgsrv']."/getWsImg.php?id=".$wsID."'>";
    }
    
    echo "</div><p/>";
    echo "<div class='infoCardClear'>";
    echo "<b style='color:darkKhaki'>Title:</b> ".$tRec['title']." ";
    echo "<p/><b style='color:darkKhaki'>Description:</b><br/>".$tRec['Description'];
    echo "<p/><b style='color:darkKhaki'>Category: </b>".$tRec['cat'];
    echo "<p/><b style='color:darkKhaki'>Location:</b> ".$tRec['city'].', '.$tRec['state'].', '.$tRec['country'];
    if ($acCode == 14 && 2==1) {
      //echo "<br><a href=\"javascript:top.wzGetPageOS('/whozon/listorditch.asp?fwebsiteID=".$wsID."');\">Should We List it or Ditch It?</a><br>";
    } 
    else {
      echo "<br><a href='".fetchWebsiteURL($wsID)."'>".getTRxt('View Site')."...</a><br>";
    }
    echo "</div>";
  } 
  else {
    $sayNewWS = 1;
  }
  return $sayNewWS;
}  

function sayNews($uID, $firstname, $linkData){
  $sayNews = 0;
  $SQL = "SELECT miniNewsID,websiteID,linkimgFlg,newsTxt,linkDesc, articleID from tblMiniNews  where miniNewsID=".$linkData;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    $linkDesc = $tRec['linkDesc'];
    $articleID = $tRec['articleID'];
    if (!is_null($articleID)) {
      $SQL = "SELECT imgFlg from tblMiniNewsArticle  where newsArticleID=".$articleID;
      $result2 = mkyMsqry($SQL);
      $tRec2 = mkyMsFetch($result);
      if ($tRec2['imgFlg'] == 1){
	      echo "<img alt='profile pic' style='border-radius: .5em;border: 0px solid #666666;float:right;margin:5px 0 15px 15px;' ";
	      echo "src='".$GLOBALS['MKYC_imgsrv']."/miniNewsGetImgTN.php?farticleID=".$articleID."'>";
      } 
    }

    if (!is_null($linkDesc)){
      $linkdesc = left($linkDesc,100);
    }
    if ($tRec['linkimgFlg'] == 1) {
	    echo "<img alt='news pic' style='border-radius: .5em;border: 0px solid #666666;float:right;margin:5px 0 15px 15px;' ";
	    echo "src='".$GLOBALS['MKYC_imgsrv']."/miniNewsGetImgTN.php?flinkID=".$linkData."'>";
    }
    echo " Has Posted miniNews<br><b>Head Line:</b> "; 
    echo left($tRec['newsTxt'],30)."<br><span style='color:#999999;'>".$linkDesc."</span>";
    if ($tRec['websiteID'] ==0){
      echo " <a href=\"javascript:top.wzGetPageOS('/whozon/MbrMiniNews.asp?fwzUserID=".$uID;
      echo "&fnewsID=".$linkData."');\">more...</a><br>";
    } 
    else {
      echo " <a href=\"javascript:top.wzGetPageOS('/whozon/MiniNews.asp?fwebsiteID=".$tRec['websiteID'];
      echo "&fnewsID=".$linkData."');\">more...</a><br>";
    }
  }
}
  
function sayBLOG($uID, $firstname, $linkData,$sKey){

  $sayBLOG = 0;
  $SQL = "SELECT CAST(imgFlg  AS integer) as iFlg,title,Entry,mBlogTopicID from tblMBlogEntry  where mBlogEntryID=".$linkData;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  if ($tRec) {
    $linkDesc = left($tRec['Entry'],550);
    $topicID= $tRec['mBlogTopicID'];
    if (!is_null($linkDesc)){
      $linkdesc = left($linkDesc,100); 
    }
    echo "<p>".getTRxt('Has A New Blog Entry and says').":<h3> "; 
    echo left($tRec['title'],50)."</h3>";
    if ($tRec['iFlg'] == 1){
      $SQL = "select width, height from ICDimages.tblmBlogImg where mBlogEntryID = ".$linkData;
      $mytresult = mkyMyqry($SQL);
      $mtRec = mkyMyFetch($mytresult);
			
      $iw = $mtRec['width'];
      $ih = $mtRec['height'];
      if ($iw >= $ih){
        if ($iw > 300){
	  $ratio =  1 - ($iw - 300.0)/$iw;
	  $iw = 300;
	  $ih = floor($ih * $ratio);
	}
      }
      else {
        if ($iw > 200){
	  $ratio = 1 - ($iw -200.0)/$iw;
	  $iw    = 200;
	  $ih    = floor($ih * $ratio);
	}
      }

      echo "<center><img alt='blog pic' style='width:".$iw."px;height:".$ih."px;border-radius: .5em;border: 0px solid #666666;margin:5px 0 15px 0px;' ";
      echo "src='".$GLOBALS['MKYC_imgsrv']."/getmBlogImg.php?fs=".$uID."&id=".$linkData."'></center>";
    }
    echo wzFormatTxt($linkDesc);
    echo " <a href=\"javascript:wzGetPage('/whzon/mbr/blog/mbrMBLOG.php?wzID=".$sKey."&fwzUserID=".$uID."&fTopicID=".$topicID."&ftopicName=";
    echo "#mb".$linkData."');\">".getTRxt('read more')."...</a><br>";
  }
}  

		
