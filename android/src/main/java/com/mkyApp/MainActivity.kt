package com.example.firstapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.view.Menu
import android.view.MenuItem
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.findNavController
import androidx.navigation.ui.AppBarConfiguration
import androidx.navigation.ui.navigateUp
import androidx.navigation.ui.setupActionBarWithNavController
import com.example.firstapp.databinding.ActivityMainBinding
import com.google.android.material.snackbar.Snackbar

import io.ktor.server.engine.*
import io.ktor.server.netty.*
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import java.io.*
import io.ktor.server.application.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import io.ktor.server.request.*
import io.ktor.client.*
import io.ktor.client.engine.cio.*
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.http.*
import java.text.SimpleDateFormat
import java.util.*
/*
Project File Imports
 */
import MkyECC
import MkyWallet
import MkyClientReq

class MainActivity : AppCompatActivity() {

    private lateinit var appBarConfiguration: AppBarConfiguration
    private lateinit var binding: ActivityMainBinding

    val mkyECC = MkyECC()
    var mkw    = MkyWallet()
    var cReq   = MkyClientReq()

    data class MkyMsg(var sessTok : String)  {
        var Address: String = ""
        var pubKey : String = ""
        var sesSig : String = ""
        var action : String = ""
        var parms  : String = ""
        fun  toJSON():String{
          return "{\"Address\":\"" + Address + "\"," +
            "\"pubKey\":\"" + pubKey + "\"," + sesSig + "," +
            "\"action\":\"" + action + "\",\"parms\":" + parms + "}"
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)

        val navController = findNavController(R.id.nav_host_fragment_content_main)
        appBarConfiguration = AppBarConfiguration(navController.graph)
        setupActionBarWithNavController(navController, appBarConfiguration)

        doOpenWallet()
        /*
        Start Local http service for Wallet
        */
        try {
          var server =  embeddedServer(Netty, port = 8080,host = "127.0.0.1") {
            routing {
              get("/") {
                call.respondText(getIndexPgHTML(), io.ktor.http.ContentType.Text.Html)
              }
              get("/netREQ/{msg}") {
                  var result = doHandleRequest(call.parameters["msg"].toString())
                  call.respondText(result)
              }
              post("/netREQ"){
                  var result = doHandleRequest(call.receiveText())
                  call.respondText(result)}
            }
          }
          CoroutineScope(Dispatchers.IO).launch {
            server.start(wait = false)
          }

          val browserIntent = Intent(Intent.ACTION_VIEW, Uri.parse("http://localhost:8080"))
          binding.fab.setOnClickListener { view ->
            Snackbar.make(view, "BitMonky For The Win!", Snackbar.LENGTH_LONG)
              .setAction("Action", null).show()
            startActivity(browserIntent)
          }
          //sayShit(binding.root,"Monky Server Shit Good!")
        }
        catch (e: Exception ){
            sayShit("Monky Server Shit Fail!")
        }
    }

    override fun onCreateOptionsMenu(menu: Menu): Boolean {
        // Inflate the menu; this adds items to the action bar if it is present.
        menuInflater.inflate(R.menu.menu_main, menu)
        return true
    }
    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        return when (item.itemId) {
            R.id.action_settings -> true
            else -> super.onOptionsItemSelected(item)
        }
    }
    override fun onSupportNavigateUp(): Boolean {
        val navController = findNavController(R.id.nav_host_fragment_content_main)
        return navController.navigateUp(appBarConfiguration)
                || super.onSupportNavigateUp()
    }
    private fun mkyDate():String {
        val currentDate: String = SimpleDateFormat("dd/MM/yyyy", Locale.getDefault()).format(Date())
        val currentTime: String = SimpleDateFormat("HH:mm:ss", Locale.getDefault()).format(Date())
        return currentDate + currentTime
    }
    private fun doOpenWallet(){
        var fileName = "bmgpWallet.txt"
        try {
            var fWal: File = getFileStreamPath(fileName) //File(getExternalStorageDirectory() ,fileName)

            var myWal: String? = null
            if (fWal.exists()) {
                myWal = doReadWallet(fWal)
                mkw.doParse(myWal)
                sayShit("Passport: " + mkw.ownMUID)
            } else {
                try {
                    var fileContent = mkyECC.doCreateWallet()
                    val fileOutPutStream = FileOutputStream(fWal)
                    fileOutPutStream.write(fileContent.toByteArray())
                    fileOutPutStream.close()
                    mkw.doParse(fileContent)
                    sayShit("Monky Write Wallet OK!")
                } catch (e: IOException) {
                    sayShit(e.toString())
                    e.printStackTrace()
                }
            }
        }
        catch(e: Exception) {sayShit(e.toString())
             return
        }
    }
    private fun doReadWallet(f:File):String? {
        var fileInputStream =FileInputStream(f)
        var inputStreamReader: InputStreamReader = InputStreamReader(fileInputStream)
        val bufferedReader: BufferedReader = BufferedReader(inputStreamReader)
        val sb: StringBuilder = StringBuilder()
        var text: String? = null
        while ({ text = bufferedReader.readLine(); text }() != null) {
            sb.append(text)
        }
        fileInputStream.close()
        return sb.toString()
    }
    private suspend fun doHandleRequest(inJ:String): String {
        var j = inJ.replace("msg=","")
        j =  j.replace("%3A",":")
        j = j.replace("%2C",",")
        j = j.replace("%2F","/")

        cReq.doParse(j)
        return  doMakeReq()
    }
    private suspend fun doMakeReq(): String {
        val stok = mkw.ownMUID + mkyDate()
        val msg = MkyMsg(stok)
        msg.Address = mkw.ownMUID
        msg.pubKey  = mkw.publicKey
        msg.sesSig  = mkyECC.signToken(stok,mkw.privateKey,mkw.publicKey)
        msg.action  = cReq.req
        msg.parms   = cReq.parms
        return doSendPostRequest(msg) //+ msg.toJSON()
        //return "OK: "+msg.toJSON();
    }
    private suspend fun doSendPostRequest(msg: MkyMsg):String {
      try {
          val client = HttpClient(CIO)

          val response: HttpResponse = client.post() {
              url(cReq.service)
              contentType(io.ktor.http.ContentType.Application.Json)
              setBody(msg.toJSON())
          }
          client.close()
          return addMUID(response.bodyAsText())
      }
      catch(e: Exception) {return e.toString()}
    }
    private fun addMUID(str: String): String{
        return "{\"pMUID\":\"" + mkw.ownMUID + "\"" + str.replaceFirst("{",",")
    }
    private fun sayShit(shit: String): Int {
      Snackbar.make(binding.root, shit, 8000)
        .setAction("Action", null).show()
      return 1
    }
    private fun getIndexPgHTML(): String {
        var mkyWalletHTML = "<!doctype html>"
        mkyWalletHTML += "<html class=\"pgHTML\" lang=\"en\">"
        mkyWalletHTML += "  <head>"
        mkyWalletHTML += "    <meta charset=\"utf-8\"/>"
        mkyWalletHTML += "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=2, user-scalable=1,target-densitydpi=device-dpi\" />"
        mkyWalletHTML += "    <link rel=\"stylesheet\" href=\"https://www.bitmonky.com/whzon/mblp/phone.css?v=1.0\"/>"
        mkyWalletHTML += "    <script src=\"https://www.bitmonky.com/bitMDis/pWalletJSM.php\"></script>"
        mkyWalletHTML += "  </head>"
        mkyWalletHTML += "<body class=\"pgBody\" style=\"background:#343434;margin:5%;padding:1.5em;\" onload=\"init();\">"
        mkyWalletHTML += "  <img style=\"float:left;margin:-3em 1em 1.5em -1em;height:6.5em;width:6.5em;border-radius:50%;\" "
        mkyWalletHTML += "       src=\"https://image0.bitmonky.com/img/bitGoldCoin.png\">"
        mkyWalletHTML += "  <div align=\"right\" ID=\"loginSpot\"><input ID=\"loginBut\" type=\"button\" value=\" BitMonky Login \" "
        mkyWalletHTML += "       onClick=\"doLogin()\"/></div>"
        mkyWalletHTML += "  <br clear=\"left\"/><div class=\"infoCardClear\" "
        mkyWalletHTML += "  style=\"background:#222324;margin-bottom:0em;\">"
        mkyWalletHTML += "  <h2>BitMonky Android Passport</h2></div>\n"
        mkyWalletHTML += "  <div ID=\"accountInfo\"></div>"
        mkyWalletHTML += "</body></html"
        return mkyWalletHTML
    }
    private fun testSignCase(): String {
        val privateKey = "e3df06c49fe3423d88ac118f6d9a096ca2dd36097c4fde4f06db7ab07030ec0e"
        val publicKey = "0490f7f3f80059407bb28d0139ca3f68c18d11a9a1dd740c647984453a284ff5a98fd2e3c1f66cc25bab5a158292eae0402e68361f477f61fa7b68a05a1cd5e8aa"
        var sToken = mkyECC.signToken("Message for signing",privateKey,publicKey)
        sayShit(publicKey)
        return sToken
    }
}