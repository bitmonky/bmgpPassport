<?php
ini_set('display_errors',1); 
error_reporting(E_ALL);
include_once("../mkysess.php");

$psrvAddr  = safeGET('psrvAddr');

?>
  <div class='infoCardClear' style='background:#151617;margin-top:1.5em;'>
  <b>BitMonky Passport Service Registraion:</b><p/>
  <div class='infoCardClear' style='color:darkKhaki;'>
  Note - Before you register make sure to have a 500x500 pixel Icon image <b>'psrvIcon.png'</b> and a 1200x400 banner image <b>'psrvBanner.png'</b> file
  loaded on your services root directory.
  </div>
  <form ID='bitServiceRegister'>
    <p/>Host: <input type="text" ID="psrvHost"  placeholder="yourservice.domain.com"  maxlength="85" size="45"/>
    <br/>Port: <input type="text" ID="psrvPort"  placeholder=""/> (Leave Blank For Default)
    <br/>End Point: <input type="text" ID="psrvEndPoint" placeholder="/yourAPI"/>
    <br/>Web Login: <input type="text" ID="psrvLogin"    placeholder="eg www.yourdomain.com/mbrLogin.php"  maxlength="145" size="65"/>
    <p/><input type="text" ID="psrvTitle" placeholder="Service Title"  maxlength="120"/>
    <br/>Description:<br/>
    <textarea ID='psrvDesc' style="background:#222324;color:gray;width:calc(100% - 1em);height:8em;"></textarea>
    <input ID="psrvRegBut" type="button" value=" Register Now " onclick="doSendRegServiceReq()"/> 
    <input type='button' onclick='doSendServiceListReq();' value= ' Cancel '/>
  </form>
  <br>
  <br>
  <br>
  <br>
  <br>
  </div>
