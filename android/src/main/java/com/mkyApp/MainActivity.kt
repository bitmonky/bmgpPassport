package com.example.firstapp

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import android.os.Environment.getExternalStorageDirectory
import android.view.Menu
import android.view.MenuItem
import android.view.View
import androidx.appcompat.app.AppCompatActivity
import androidx.navigation.findNavController
import androidx.navigation.ui.AppBarConfiguration
import androidx.navigation.ui.navigateUp
import androidx.navigation.ui.setupActionBarWithNavController
import com.example.firstapp.databinding.ActivityMainBinding
import com.google.android.material.snackbar.Snackbar


import io.ktor.gson.*
import io.ktor.gson.gson
import io.ktor.http.HttpHeaders.ContentType
import io.ktor.server.engine.*
import io.ktor.server.netty.*
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import java.io.*

import io.ktor.http.ContentType.Text.Html
import io.ktor.http.content.*
import io.ktor.server.application.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import kotlinx.html.*
import MkyECC
import MkyResult
import io.ktor.server.request.*
import io.ktor.client.*
import io.ktor.client.engine.cio.*
import io.ktor.client.plugins.HttpCallValidator.Companion.install
import io.ktor.client.request.*
import io.ktor.client.statement.*
import io.ktor.client.plugins.api.*
import io.ktor.client.plugins.contentnegotiation.*
import io.ktor.client.utils.EmptyContent.contentType
import io.ktor.http.*
import io.ktor.serialization.gson.*
import okhttp3.OkHttp

class MainActivity : AppCompatActivity() {

    private lateinit var appBarConfiguration: AppBarConfiguration
    private lateinit var binding: ActivityMainBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setSupportActionBar(binding.toolbar)

         val navController = findNavController(R.id.nav_host_fragment_content_main)
        appBarConfiguration = AppBarConfiguration(navController.graph)
        setupActionBarWithNavController(navController, appBarConfiguration)


        val mkyECC = MkyECC()
        var mkr = MkyResult()
        mkr.publicKey = "Some Shit Woot"
        mkr = mkyECC.doTest()
        sayShit(binding.root,mkr.isValid.toString()+mkr.address)

        var fileName = "bitMonky/bmgp.wallet"
        var fileContent = "xohooeo"
        var mkyWalletHTML = "<!doctype html>"
        mkyWalletHTML += "<html class=\"pgHTML\" lang=\"en\">"
        mkyWalletHTML += "  <head>"
        mkyWalletHTML += "    <meta charset=\"utf-8\"/>"
        mkyWalletHTML += "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=2, user-scalable=1,target-densitydpi=device-dpi\" />"
        mkyWalletHTML += "    <link rel=\"stylesheet\" href=\"https://www.bitmonky.com/whzon/mblp/phone.css?v=1.0\"/>"
        mkyWalletHTML += "    <script src=\"https://www.bitmonky.com/bitMDis/pWalletJS.php\"></script>"
        mkyWalletHTML += "  </head>"
        mkyWalletHTML += "<body class=\"pgBody\" style=\"background:#343434;margin:5%;padding:1.5em;\" onload=\"init();\">"
        mkyWalletHTML += "  <img style=\"float:left;margin:-3em 1em 1.5em -1em;height:6.5em;width:6.5em;border-radius:50%;\" "
        mkyWalletHTML += "       src=\"https://image0.bitmonky.com/img/bitGoldCoin.png\">"
        mkyWalletHTML += "  <div align=\"right\" ID=\"loginSpot\"><input ID=\"loginBut\" type=\"button\" value=\" BitMonky Login \" "
        mkyWalletHTML += "       onClick=\"doLogin()\"/></div>"
        mkyWalletHTML += "  <br clear=\"left\"/><h1>Welcome To Your BitMonky Wallet Server</h1>\n"
        mkyWalletHTML += "  <div ID=\"accountInfo\"></div>"
        mkyWalletHTML += "</body></html"

        var myExternalFile:File = File(getExternalStorageDirectory() ,fileName)
        try {
            val fileOutPutStream = FileOutputStream(myExternalFile)
            fileOutPutStream.write(fileContent.toByteArray())
            fileOutPutStream.close()
        } catch (e: IOException) {
            //sayShit(binding.root,"Monky Shit Fail!")
            e.printStackTrace()
        }
        try {
          var server =  embeddedServer(Netty, port = 8080,host = "127.0.0.1") {
            routing {
              get("/") {
                call.respondText(mkyWalletHTML, io.ktor.http.ContentType.Text.Html)
              }
              get("/netREQ") {
                  var result = doHandleRequest(call.request.uri,mkr)
                  call.respondText(result)
              }
              post("/netREQ"){
                  var result = doHandleRequest(call.receiveText(),mkr)
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
            sayShit(binding.root,"Monky Server Shit Fail!")
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
    private suspend fun doHandleRequest(j:String,mkyD:MkyResult): String {
        var  result = j
        return doSendPostRequest(mkyD)
    }
    private suspend fun doSendPostRequest(mkyD:MkyResult):String {
      val client = HttpClient(CIO)
      val response: HttpResponse = client.post(){
          url("https://www.bitmonky.com/")
          contentType(io.ktor.http.ContentType.Application.Json)
          setBody(mkyD)
      }
      client.close()
      return response.bodyAsText()
    }
    private fun sayShit(mView: View, shit: String): Int {
      Snackbar.make(mView, shit, Snackbar.LENGTH_LONG)
        .setAction("Action", null).show()
      return 1
    }
}