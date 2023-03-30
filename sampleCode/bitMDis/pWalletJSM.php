<?php
$MKYC_rootdom = 'bitmonky.com';

if (isset($_GET['sport'])){
  $sPort = $_GET['sport'];
}
else {
  $sPort = 8080;
}
if (isset($_GET['dm'])){
  $dm = $_GET['dm'];
  $mode = "PC";
}
else {
  $dm = null;
  $mode = "mobile";
}
?> 
var hasAccount = false;
var qryAction  = 'not set';
var service = {
  host : 'web.bitmonky.com',
  port : '',
  endPoint : '/whzon/gold/netWalletAPI.php'
}
function init(){
  console.log('helloworld');
  getAccountInfo();
}
function getAccountInfo(){
   var msg = {
     req : "sendAccountInfo",
   }
   sendRequest(msg);
}
function getSendGoldToMbr(muid){
   var msg = {
     req : "getSendGoldToMbr",
     parms : {
       mode : "<?php echo $mode;?>",
       muid : muid
     }
   }
   sendRequest(msg);
}
function doSendGoldNow(){
  var bmgp = document.getElementById('sendBMGPAmt').value;
  var mnic = document.getElementById('sendToNic').value;
  var muid = document.getElementById('sendToMUID').value;
  var conf = confirm("Send "+bmgp+" BMGP To "+mnic+" Now?");
  if (conf){
    var msg = {
      req : "doSendBMGP",
      parms : {
        mode : "<?php echo $mode;?>",
        address : null,
        amt     : bmgp, 
	mbrMUID : muid
      }
    }
    sendRequest(msg); 
  }
}
function doSendTrendingReq(){
   var msg = {
     req : "sendTrendingList",
     parms : {
       mode : "<?php echo $mode;?>"
     }
   }
   sendRequest(msg);
}
function doSendUseDefaultWallet(){
   var msg = {
     req : "useNewWallet",
     wallet : { ownMUID : "useDefault"},
     parms : {
       mode : "<?php echo $mode;?>"
     }
   }
   hideDiv("walletForm");
   sendRequest(msg);

}
function doSendUseWallet(w){
   var msg = {
     req : "useNewWallet",
     wallet : w,
     parms : {
       mode : "<?php echo $mode;?>"
     }
   }
   sendRequest(msg);
}
function doSendStoresReq(){
   var msg = {
     req : "sendStoresList",
     parms : {
       mode : "<?php echo $mode;?>"
     }
   }
   sendRequest(msg);
}
function doSendWalletOptions(){
   var msg = {
     req : "sendWalletOptions",
     parms : {
       mode : "<?php echo $mode;?>"
     }
   }
   sendRequest(msg);
}
function loadHashQry(n,qry){
  doSendPeerMemQry(qry);
  window.scrollTo({
    top: 0,
    left: 0,
    behavior: "smooth",
  });
}
function doSendPeerMemQry(hashStr=null){
   var qry = hashStr;
   var spot = document.getElementById('peerMemQSpot');
   var but  = document.getElementById('PeerMemBut');
   var cqry = document.getElementById('peerMemQry');
   if (!hashStr){
     qry  = cqry;
   }
   if (!qry){
     qry = '';
   }
   else {
     if(!hashStr){
       qry = qry.value;
     } 
   }
   cqry.value = qry;

   showSearching();
   var currentTime = new Date();
   var ranTime = currentTime.getMilliseconds();
   var msg = {
     req : "sendPeerQryResults",
     parms : {
       mode : "<?php echo $mode;?>",
       qry  : qry,
       xr   : '&xr=' + ranTime
     }
   }
   sendRequest(msg);
}
function showSearching(){
  var spot = document.getElementById('Searching');
  if (spot){
    spot.style.display = 'block';
  }
}
function hideSearching(){
  var spot = document.getElementById('Searching');
  if (spot){
    spot.style.display = 'none';
  }
}
function doLinkAccount(){
   var conf = confirm("Link This Wallet To Your BitMonky Account?");
   if (!conf){
     return;
   }
   butToFetching('butLinkAcc');
   var loginID  = document.getElementById('loginID').value;
   var password = document.getElementById('password').value;
   var msg = {
     req : "linkAccount",
     parms : {
       loginID  : loginID,
       password : password,
     }
   }
   console.log(msg);
   sendRequest(msg);
}
function doCreateAccount(){
   var conf = confirm("Create A BitMonky Account For This Wallet?");
   if (!conf){
     return;
   }
   butToFetching('butCreateAcc');
   var sex = 0;
   var isMale = document.getElementById('isMale')
   if (isMale.checked == 'checked'){
     sex = 1;
   }
   var msg = { 
     req : "createAccount",
     parms : {
       firstname : document.getElementById('nicname').value,
       age       : document.getElementById('age').value,
       sex       : sex,
       browser   : 'Brave Browser'
     }
   }
   console.log(msg);
   sendRequest(msg);
}
function doLogin(){
   if (!hasAccount){
     alert('No Account Found... Please create an account or use the Link Account option');
     return;
   }
   var msg = {
     req : "sendLoginToken",
   }
   butToFetching('loginBut');
   sendRequest(msg);
}
function butRestoreTo(id,name){
  var but = document.getElementById(id);
  if (but){
    but.value = name;
    but.disabled = false;
  }
}
function butToFetching(id){
  var but = document.getElementById(id);
  console.log(but,id);
  if (but){
    but.value = ' Fetching ... ';
    but.disabled = true;
  }
}
function handleResponse(j){
  console.log(j);
  butRestoreTo('butCreateAcc',' Create BitMonky Account ');
  if (j.result === false){
    doShowAccountOptions(j);
    return;
  }
  if (j.action == 'linkAccount'){
    doSaveLinkAccountInfo(j);
    getAccountInfo();
  }
  if (j.action == 'createAccount'){
    doSaveNewAccountInfo(j);
    getAccountInfo();
  }
  if (j.action == 'sendAccountInfo'){
    hasAccount = true;
    doShowAccountInfo(j);
    doSendStoresReq();
  }
  if (j.action == qryAction){
    doPutQryResults(j);
  }
  if (j.action == 'getSendGoldToMbr'){
    doPutQryResults(j);
  }
  if (j.action == 'doSendBMGP'){
    doShowSendBMGResult(j);
  }
  if (j.action == 'sendWalletOptions'){
    hideDiv('walletForm');
    doShowQryResults(j);
    var opt = {
      title  : 'Select A Member To Send BMGP To',
      promt  : 'Type Member Name',
      action : 'qryMemberSendTo'
    }
    createAutoSelect(opt);
  }
  if (j.req == 'useNewWallet'){
    if(j.result){
      alert('Wallet Changed');
      getAccountInfo();
      return;
    }
    alert('Wallet Changed Failed... Try Again');
    return;
  }
  if (j.action == 'sendTrendingList'){
    doShowTrendingList(j);
  }
  if (j.action == 'sendStoresList'){
    doShowStoresList(j);
  }
  if (j.action == 'sendPeerQryResults'){
    hideSearching();
    doShowQryResults(j);
  }
  if (j.action == 'sendLoginToken'){
    pToken = j.accToken;
    pMUID  = j.pMUID;
    var conf = confirm('Login to bitMonky Now?');
    if (conf){
      const url = 'https://web.bitmonky.com/whzon/mbr/mbrLogin.php?pToken='+pToken+'&pMUID='+pMUID;
      window.open(url,'bitMonky');
    }
    butRestoreTo('loginBut',' BitMonky Login ');
  }
}
function doSaveLinkAccountInfo(j){
}
function doSaveNewAccountInfo(j){
}
function format(value){
  const str = "<span class='mkyMoney'>"+value+"</span>";
  return str;
}
function hideDiv(id){
  spot = document.getElementById(id);
  if (spot){
    spot.style.display = 'none';
  }
}
function showDiv(id,display='block'){
  spot = document.getElementById(id);
  if (spot){
    spot.style.display = display;
  }
}
function doShowSendBMGResult(j){
  if (j.actionRes.result){
    alert('Transaction Complete');
    return;
  }
  alert('Could Not Send... Response Was: '+j.actionRes.msg);
}
function doShowLinkAccount(j){
  hideDiv('newAccountSpot');
  var spot = document.getElementById('linkAccountSpot');
  if (spot){
    htm  = "<div align='right'>";
    htm += "<input ID='butCreateAcc'  type='button' value=' Create BitMonky Account ' onclick='doShowCreateAccount();'/> ";
    htm += "<input ID='butLinkAcc'    type='button' value=' Link Account ' onclick='doLinkAccount();'/>";
    htm += "</div>";
    htm += "<input ID='loginID' type='text' placeholder='Account Login ID'/>";
    htm += "<br/><input ID='password'     type='password' placeholder='Password'/>";

    spot.innerHTML = htm;
    spot.style.display = 'block';
  }
}
function doShowCreateAccount(){
  hideDiv('linkAccountSpot');
  showDiv('newAccountSpot');
}
function doShowAccountOptions(j){
  var spot = document.getElementById('accountInfo');
  if (spot){
    var htm = "<div class='infoCardClear'>";
    //htm += "<img style='margin:0em 0em 1.5em 1.5em;float:right;border-radius:50%;' src='"+j.icon+"'/>";
    htm += "Account Owner: "+format('No BitMonky Account Found');
    htm += "<br/>"+getAddressSpot(j); 
    htm += "<br/>Balance: " + format('NA');
    htm += "<br clear='right'>";
    htm += "<div ID='linkAccountSpot' class='infoCardClear' style='background:#151515;display:none;'/></div>";
    htm += "<div ID='newAccountSpot'  class='infoCardClear' style='background:#151515;'>";
    htm += "<div align='right'>";
    htm += "<input ID='butCreateAcc'  type='button' value=' Create BitMonky Account ' onclick='doCreateAccount();'/> ";
    htm += "<input ID='butLinkAcc'    type='button' value=' Link Account ' onclick='doShowLinkAccount();'/>";
    htm += "</div>";
    htm += "<input ID='nicname' type='text' placeholder='Choose Nicname'/>";
    htm += "<br/><input ID='age'     type='text' placeholder='Age'/>";
    htm += "<br/><input ID='isMale'  type='radio' name='fsex' value='0' checked/>Male <input ID='isFemale' type='radio' name='fsex' value='1' />Female";  
    htm += "</div></div></div>";

    
    spot.innerHTML = htm;
    if (j.result === false && j.data){
      alert(j.error);
    }
  }
}
function doShowTrendingList(j){
  var spot = document.getElementById('serviceMenu');
  console.log(j);
  console.log(spot);
  if (spot){
    console.log('Updating Service DIV to Trending');
    spot.innerHTML = j.html;
  }
  else {
    console.log('Inserting Service DIV');
    spot = document.createElement('DIV');
    spot.id = 'serviceMenu';
    spot.innerHTML = j.html;
    document.body.appendChild(spot);
  }
}
function doShowStoresList(j){
  var spot = document.getElementById('serviceMenu');
  console.log(j);
  console.log(spot);
  if (spot){
    console.log('Updating Service DIV');
    spot.innerHTML = j.html;
  }
  else {
    console.log('Inserting Service DIV');
    spot = document.createElement('DIV');
    spot.id = 'serviceMenu';
    spot.innerHTML = j.html;
    document.body.appendChild(spot);
  }
}
function doShowQryResults(j){
  var spot = document.getElementById('serviceMenu');
  console.log(j);
  console.log(spot);
  if (spot){
    console.log('Updating Service DIV');
    spot.innerHTML = j.html;
  }
  else {
    console.log('Inserting Service DIV');
    spot = document.createElement('DIV');
    spot.id = 'serviceMenu';
    spot.innerHTML = j.html;
    document.body.appendChild(spot);
  }
}
function getAddressSpot(j){
  return "<div onmouseOver='showDiv(\"changeWLink\",\"inline\");' onmouseout='hideDiv(\"changeWLink\");'>Address: "+format(j.pMUID) +
    " <a ID='changeWLink' style='display:none;' href='javascript:showDiv(\"walletForm\");'>Change Wallet</a></div>" +
    "<div class='infoCardClear' ID='walletForm' style='display:none;'><form> " +
    "Change Wallet <a href='javascript:hideDiv(\"walletForm\");'>Cancel</a> | " +
    "<a href='javascript:doSendWalletOptions();'>Open</a> | " + 
    "<a href='javascript:doSendUseDefaultWallet();'>Open Default</a><br/><input onchange='changeWalletFile();' ID='wFile' type='file' >" +
    "</form></div>"; 
}
function doShowAccountInfo(j){
  var spot = document.getElementById('accountInfo');
  if (spot){
    var htm = "<div class='infoCardClear'>";
    htm += "<img style='margin:0em 0em 1.5em 1.5em;float:right;border-radius:50%;' src='"+j.icon+"'/>";
    htm += "Account Owner: "+format(j.name);
    htm += getAddressSpot(j) +
      "<br/>Balance: " + format(j.balance) +
      "<br/>Login Key "+ format(j.login) +
      "<br/>"+getSearchHTML()+"</div>"; 
    spot.innerHTML = htm;
  }
}
function readWalletFile(event) {
  var textarea = document.getElementById('viewSpot');
  var wal = event.target.result;
  try {
    wal = JSON.parse(wal);
    if (typeof wal.ownMUID == 'undefined' ||
        typeof wal.publicKey == 'undefined' ||
        typeof wal.privateKey == 'undefined'){
      alert('Not A Valid Wallet File');
      return;
    }
  }
  catch(e){
    alert('Not A Valid Wallet File');
    return;
  }
  console.log('Wallet Loaded: ',wal.ownMUID);
  hideDiv('walletForm');
  doSendUseWallet(wal);
}
function changeWalletFile() {
  input = document.getElementById('wFile');
  var file = input.files[0];
  if (file.size > 500){
    alert(file.name+' Is Not A Wallet File!');
    return;
  }
  var reader = new FileReader();
  reader.addEventListener('load', readWalletFile);
  reader.readAsText(file);
}
function getSearchHTML(){
  var htm = "<form onsubmit='doSendPeerMemQry();return false;'>" +
    "<input style='width:60%;font-size:larger;' onkeypress='return noenter();' ID='peerMemQry' " +
    " placeholder=' PeerTree Search' type='text' name='search'/>" +
    " <input ID='peerMemBut' type='button'  value=' Search ' onclick='doSendPeerMemQry();'/> " +
    "</form>" +
    "<div ID='Searching' style='display:none;'>" + 
    "<div style='padding:.5em;display:inline;height:28px;color:#777777;' ><div class='mkyloader'></div>Searching The PeerTree...</div>" +
    "</div>"
  return htm;
}
function sendRequest(msg){
    msg.service = service;
    console.log('Sending:->',msg);
    msg = JSON.stringify(msg);
    console.log(msg);
    var xml  = new XMLHttpRequest();

    var url = 'http://localhost:<?php echo $sPort;?>/netREQ/msg='+msg;
    xml.timeout   = 20*1000;
    xml.ontimeout = function (){
      alert('Network Timeout Try Again Later');
      document.location.reload();
    }
    xml.onerror   = function (){
      alert('Http Access Error - Try Again Later');
      document.location.reload();
    }
    xml.open("GET", url, true);
    xml.onreadystatechange = function(){
      if (xml.readyState == 4){
        if(xml.status  == 200){
          //alert(xml.responseText);
          var j = null;
          try {j = JSON.parse(xml.responseText); }
          catch(err) {
            alert('pars json failed!',xml.responseText);
            return;
          }
          handleResponse(j);
          return;
        }
      }
    };
    xml.send(null);
}
function videoShare(id){
  var pg = '/whzon/mbr/vidView/viewVideoPg.php?wzID=0&videoID=' + id;
  <?php
  $app = "mblp/wzMbl.php";
  if ($dm){
    $app = "wzApp.php";
  }
  ?>
  window.open("https://web.<?php echo $GLOBALS['MKYC_rootdom']."/whzon/".$app;?>?furl="+encodeURIComponent(pg),'bitMonky');
}
function wzGetPage(pg){
  <?php
  $app = "mblp/wzMbl.php";
  if ($dm){
    $app = "wzApp.php";
  }
  ?>
  window.open("https://web.<?php echo $GLOBALS['MKYC_rootdom']."/whzon/".$app;?>?furl="+encodeURIComponent(pg),'bitMonky');
}
function mkyTrim(str) {
  return str.replace(/^\s+|\s+$/g,"");
}
function highlight(row){
   var wzoutput = document.getElementById("wzline:" + row);
   wzoutput.style.background="darkOliveGreen";
}
function undoHighlight(row){
   var wzoutput = document.getElementById("wzline:" + row);
   wzoutput.style.background="#232425";
}
function doClick(e,action) {
   var key;
   if(window.event)
     key = window.event.keyCode;     //IE
   else
     key = e.which;     //firefox
   getMatchingList(action);
}
function getMatchingList(action){
   var qry  = document.getElementById("getLocation").elements["flocation"].value;
   qry = mkyTrim(qry);
   qry=qry.replace(/,/g,'');
   qry=qry.replace(/-/g,'');
   qry=qry.replace(/  /g,' ');
   if (qry!='' ){
     var msg = {
       req : action,
       parms : {
         mode : "<?php echo $mode;?>",
         qry : qry 
       }
     }
     sendRequest(msg);
   }
   else {
     var wzoutput = document.getElementById("putQryResults");
     wzoutput.innerHTML='';
   }
}
function doPutQryResults(j){
  var spot = document.getElementById('putQryResults');
  console.log(j);
  console.log(spot);
  if (spot){
    console.log('Updating AutoSelect DIV');
    spot.innerHTML = j.html;
  }
}
function createAutoSelect(opt){
   spot = document.getElementById('autoSelSpot');
   console.log('autoSelSpot',spot);
   if (spot){
     qryAction = opt.action;
     console.log('qryAction',qryAction);
     spot.innerHTML = '<h2><span style="padding:6px;background:#111111;border-radius: .5em;">'+opt.title+'</h2>' +
       '<form ID="getLocation" name="wzLocationFrm" >' +
       '<b>'+opt.promt+'</b> ' +
       '<input type="text"  name="flocation" oninput="doClick(event,\''+opt.action+'\');">' +
       '<div ID="putQryResults"></div>';
   } 
}
