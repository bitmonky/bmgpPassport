<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
$j   = file_get_contents('php://input');
$inJ = $j;
$j   = json_decode($j);

if (!$j){
  exit('API: json required:'.$inJ);
}
include_once("../mkysess.php");
include_once("goldInc.php");
include_once("../bitMiner/bcMUIDInc.php");
include_once('../mbr/mods/pickRanMonkey.php');
include_once("../utility/mbrUtility.php");

$PTC_peerLOGIN = "https://localhost:13380";
$wAddress = clean($j->Address);
$sesTok   = clean($j->sesTok);
$pubKey   = clean($j->pubKey);
$sig      = clean($j->sesSig);
$action   = clean($j->action);


$SQL = "select muidMUID,muidPubKey from tblwzMUID  where muidMUID = '".$wAddress."' ";
$res = mkyMsqry($SQL);
$rec = mkyMsFetch($res);

if ($rec){
  $login = authenticate($wAddress,$sesTok,$pubKey,$sig);
  if (!$login){
    exitEr('log JSON fail');
  } 
  $pKey = $rec['muidPubKey'];
  if (!$pKey){
    $SQL = "update tblwzMUID set muidPubKey = '".$pKey."' where muidMUID = '".$wAddress."' ";
    mkyMyqry($SQL);
  }
  $data  = json_decode($login->data);
  if ($data->result){
    if ($action == 'sendLoginToken'){

      $newToken = makeBC_MUID(hash('sha256',$sig));
      $SQL = "update tblwzMUID set muidToken = '".$newToken."',muidTokenDate = now() where muidMUID = '".$wAddress."' ";
      mkyMyqry($SQL);
      $j = new stdClass;
      $j->action = $action;
      $j->result = true;
      $j->accToken = $newToken;
      $j->msg = 'Access Granted:';
      exit(json_encode($j));
    }
    if ($action == 'sendAccountInfo'){
      sendAccountInfo($action,$wAddress);
    }
    if ($action == 'sendStoresList'){
      sendStoresList($j,$wAddress);
    }
    if ($action == 'sendTrendingList'){
      sendTrendingList($j,$wAddress);
    }
    if ($action == 'sendPeerQryResults'){
      sendPeerQryResults($j,$wAddress);
    }
    if ($action == 'sendWalletOptions'){
      sendWalletOptions($j,$wAddress);
    }
    if ($action == 'qryMemberSendTo'){
      qryMemberSendTo($j,$wAddress);
    }
    if ($action == 'getSendGoldToMbr'){
      getSendGoldToMbr($j,$wAddress);
    }
    if ($action == 'doSendBMGP'){
      sendBMGP($j,$wAddress);
    }
  }
  exitEr('login From Peer Failed',$data);
}
if ($action == 'createAccount'){
  addNewUser($j);
}
if ($action == 'linkAccount'){
  linkUserAccount($j);
}

exitEr('No BitMonky Account On File For This Wallet',$j);
function sendContacts($action,$wAddress){
  $j = new stdClass;
  $j->action = $action;
  $j->result = false;

  $SQL = "select wzUserID,firstname,mbrMUID,email from tblwzMUID ";
  $SQL .= "inner join tblwzUser on wzUserID = muidWzUserID ";
  $SQL .= "where muidMUID = '".$wAddress."'";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if ($rec){
    $j->result = true;
    $j->name    = $rec['firstname'];
    $j->icon    = $GLOBALS['MKYC_imgsrv']."/getMbrImg.php?mid=".$rec['mbrMUID'];
    $j->login   = $rec['email'];
    $j->balance = number_format(goldOnHand($rec['wzUserID']),9)." BMGP";
  }
  if ($j->ruslt === false){
    $j->sql = $SQL;
  }
  exit(json_encode($j));
}
function linkUserAccount($j){
  if (!validate($j->parms->loginID,$j->parms->password)){
    exitEr('Account Not Found Or Credentials Not Valid',$j);
  }
  $SQL = "select wzUserID from tblwzUser where email = '".$j->parms->loginID."'";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if (!$rec){
    exitEr('No Account Found For UserID/Password',$j);
  }
  $wzUserID = $rec['wzUserID'];

  $SQL  = "select count(*)nRec from tblwzMUID where muidWzUserID = ".$rec['wzUserID']." or muidMUID = '".$j->Address."'"; 
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if (!$rec){
    exitEr('Link Failed On Lookup... Try Again Later',$j);
  }
  if ($rec['nRec'] > 0){
    exitEr('Sorry Account Is Already linked To Another Wallet'.$j);
  }
  $SQL  = "insert into tblwzMUID (muidWzUserID,muidMUID,muidPubKey) ";
  $SQL .= "values(".$wzUserID.",'".$j->Address."','".$j->pubKey."')";
  if (!mkyMyqry($SQL)){
    exitEr('Account Link Failed Try Again Later'.$SQL,$j);
  }
  $j->result = true;
  exit(json_encode($j));
}
function validateNewUser($j){
  if ($j->parms->firstname == "" ){
    exitEr('Nicname Is Required',$j);
  }

  if ($j->parms->sex != ""){
    if ($j->parms->age == ""){
      exitEr('Sex And Age Is Required',$j);
    }
  }
  $result = is_numeric($j->parms->age);

  if ($result === false){
    exitEr('Invalid Age',$j);
  }
  if ($j->parms->age < 16){
    exitEr('Under Age Accounts Not Allowed',$j);
  }

  $j->parms->firstname = clean($j->parms->firstname);
  $nicNoEmo = left(stripEmotes($j->parms->firstname),60);

  $SQL = "SELECT count(nicNoEmo) as nMem From tblwzUser where nicNoEmo='".$nicNoEmo."';";
  $tRec=null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  $nFN  = $tRec['nMem'];

  if ($nFN != 0){
    exitEr('Sorry Nicname Is Taken... Try Another',$j);
  }

  $SQL = "SELECT count(mkdNic) as nMem From tblMkyDating where mkdNic='".$j->parms->firstname."';";
  $tRec=null;
  $result = mkyMsqry($SQL);
  $tRec = mkyMsFetch($result);
  $nFN  = $tRec['nMem'];

  if ($nFN != 0){
    exitEr('Sorry Nicname Is Taken... Try Another',$j);
  }
}
function addNewUser($j){
   validateNewUser($j);
   if (!mkyStartTransaction()){
    exitEr('Database Not Available... Try Again Later',$j);
   }
   $password  = randomKeygenString(12);
   $userAgent = $j->parms->browser;
   if (trim($userAgent) == "" || $userAgent === null) {
     $userAgent = "Blank";
     $uaMD5     = $userAgent;
   }
   else {
     $uaMD5     = md5($userAgent);
   }
   $email      = '';
   $nicNoEmo   = left(stripEmotes($j->parms->firstname),60);
   $invitation = randomKeyFill(8);

   $SQL = "insert into  tblwzUser (firstname,email,password,checkWord,xruFlg,rCode,IP,";
   $SQL .= "cityID,orgCityID,sex,age,uaMd5,nicNoEmo,inChatChanID,verifyWord) ";
   $SQL .= "values('".$j->parms->firstname."'";
   $SQL .= ",'".$email."'";
   $SQL .= ",'".$password."'";
   $SQL .= ",'".$password."'";
   $SQL .= ",'".$email."'";
   $SQL .= ",'Wallet'";
   $SQL .= ",'localhost'";
   $SQL .= ",0";
   $SQL .= ",0";
   $SQL .= ",".$j->parms->sex;
   $SQL .= ",".$j->parms->age;
   $SQL .= ",'".$uaMD5."'";
   $SQL .= ",'".$nicNoEmo."',1,'".$invitation."')";

   if (!mkyMyqry($SQL)){
     exitDbEr($j,$SQL);
   }
   $wzUserID =  mkyMyLastID();

   $SQL = "update tblGoldLastSignup set lastSignup = now()";
   $result = mkyMsqry($SQL);
   if (!mkyMyqry($SQL)){
     exitDbEr($j,$SQL);
   }
   // Create Gold Bank For New User
   $SQL = "insert into tblwzUserGold (wzUserID,goldcoins,tradeGold,purchasedGold,taxGold) ";
   $SQL .= "values (".$wzUserID.",0,0,0,0)";
   if (!mkyMsqry($SQL)){
     exitDbEr($j);
   }


   $newbro = new mkyUser($wzUserID);
   $email  = $newbro->createLoginID();
   if (!$email){
     exitDbEr($j,$email.'Email Create Failed... Try Again later');
   }

   picRandomMonky($wzUserID);
   secure($wzUserID);
   getRandomCity($wzUserID);
   
   $SQL = "insert into tblwzMUID (muidWzUserID,muidMUID,muidPubKey) ";
   $SQL .= "values (".$wzUserID.",'".$j->Address."','".$j->pubKey."')"; 
   if (!mkyMsqry($SQL)){
     exitDbEr($j,"Insert to tblwzMUID failed");
   }

   if (!mkyCommit()){
     exitDbEr($j,'Databse Commit Fail... Try Again Later');
   }
   $j->result = true;
   exit(json_encode($j));
}
function createNonce($adr){
  $now = DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
  $nonce = hash('sha256',$GLOBALS['sig'].$now->format("m-d-Y H:i:s.u"));  
  $SQL = "update tblwzMUID set muidNonce = '".$nonce."' where muidMUID = '".$adr."'";
  if (mkyMyqry($SQL)){
    return $nonce;
  }
  return null;
}
function getUIDbyAddress($adr){
  if (!$adr){
    return null;
  }
  $SQL = "SELECT muidWzUserID FROM ICDirectSQL.tblwzMUID where muidMUID = '".clean($adr)."'";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if (!$rec){   
    return null;
  }
  return $rec['muidWzUserID'];
}
function sendBMGP($inJ,$wAddress){
  $fromID = getUIDbyAddress($wAddress);

  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->actionRes = false;
  $nonce = createNonce($wAddress);
  if (!$nonce){
    $j->msg = "Create Session Failed... Try Later";	  
    exit(json_encode($j));
  }
  $toUID = getUIDbyAddress($inJ->parms->address);
  if (!$toUID){
    $toUID = getUserID($inJ->parms->mbrMUID);
    if (!$toUID){
      $j->actionRes = false;
      $j->msg    = "Send To Wallet Address Not Valid";
      exit(json_encode($j));
    }
  }
  $parms  = "?toMbrID=".$toUID."&famt=".$inJ->parms->amt."&nonce=".$nonce."&sender=".$wAddress."&from=".$fromID;
  $s = new stdClass;
  $s->url = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/gold/netWalletSendBMGP.php".$parms;
  $f = tryJFetchURL($s);
  $j->actionRes = json_decode($f->data);
  exit(json_encode($j));
}
function qryMemberSendTo($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/qrySendGoldTo.php?mode=".$inJ->parms->mode."&fqry=".urlencode($inJ->parms->qry);
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function getSendGoldToMbr($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/frmSendGoldToMbr.php";
  $j->url   .= "?mode=".$inJ->parms->mode."&fmuid=".$inJ->parms->muid."&fromAdr=".$wAddress;
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function sendWalletOptions($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/sendWalletOptions.php?mode=".$inJ->parms->mode;
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function sendTrendingList($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/getTrendingList.php?mode=".$inJ->parms->mode;
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function sendStoresList($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/getStoresList.php?mode=".$inJ->parms->mode;
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function sendPeerQryResults($inJ,$wAddress){
  $j = new stdClass;
  $j->action = $inJ->action;
  $j->result = true;
  $j->url    = "https://web.".$GLOBALS['MKYC_rootdom']."/whzon/public/doPeerMemQry.php?mode=".$inJ->parms->mode.'&search='.urlencode($inJ->parms->qry);
  $f = tryJFetchURL($j);
  $j->html = $f->data;
  exit(json_encode($j));
}
function sendAccountInfo($action,$wAddress){
  $j = new stdClass;
  $j->action = $action;
  $j->result = false;

  $SQL = "select wzUserID,firstname,mbrMUID,email from tblwzMUID ";
  $SQL .= "inner join tblwzUser on wzUserID = muidWzUserID ";
  $SQL .= "where muidMUID = '".$wAddress."'";
  $res = mkyMyqry($SQL);
  $rec = mkyMyFetch($res);
  if ($rec){
    $j->result = true;
    $j->name    = $rec['firstname'];
    $j->icon    = $GLOBALS['MKYC_imgsrv']."/getMbrImg.php?mid=".$rec['mbrMUID'];
    $j->login   = $rec['email'];
    $j->balance = number_format(goldOnHand($rec['wzUserID']),9)." BMGP";
  }
  if ($j->result === false){
    $j->sql = $SQL;
  }
  exit(json_encode($j));
}
function respond($msg,$data){
  $j = new stdClass;
  $j->result = true;
  $j->msg = $msg;
  $j->data = $data;
  exit(json_encode($j));
}
function exitDbEr($data=null,$msg='Database Fail... Try Again Later'){
  $j = new stdClass;
  $j->result = false;
  $j->error  = $msg;
  $j->data   = $data;
  mkyRollback();
  exit(json_encode($j));
}
function exitEr($msg,$data=null){
  $j = new stdClass;
  $j->result = false;
  $j->error  = $msg;
  $j->data   = $data;
  exit(json_encode($j));
}
function authenticate($wAddress,$sesTok,$pubKey,$sig){
  $login = new stdClass;
  $login->ownMUID = $wAddress;
  $login->pubKey  = $pubKey;
  $login->sesTok  = $sesTok;
  $login->sig     = $sig;

  $post = new stdClass;
  $post->url   = $GLOBALS['PTC_peerLOGIN']."/netREQ";
  $post->postd = '{"msg":{"req":"verifyLogin","login":'.json_encode($login).'}}';

  $bcRes = tryJFetchURL($post,'POST');
  return $bcRes;
}
?>
