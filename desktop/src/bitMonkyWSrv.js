/****************************
BitMonky Wallet Server
****************************
*/

const webCon  = require('http');
const fs      = require('fs');
const url     = require('url');
const EC      = require('elliptic').ec;
const ec      = new EC('secp256k1');
const bitcoin = require('bitcoinjs-lib');
const crypto  = require('crypto');
const port    = 80;
const wfile   = 'keys/myBMGPWallet.key';
const wconf   = 'keys/wallet.conf';

const { generateKeyPairSync } = require('crypto')

class mkyRSAMail {
  constructor(pPhrase,keys=null){
    this.passPhrase = pPhrase;
    if (keys){
      this.publicKey = keys.publicKey;
      this.privateKey = keys.privateKey;
    }
  } 
  encryptString(toEncrypt) {
    var buffer = Buffer.from(toEncrypt);
    var encrypted = crypto.publicEncrypt(this.publicKey, buffer);
    return encrypted.toString("base64");
  };

  decryptString(toDecrypt) {
    var buffer = Buffer.from(toDecrypt, "base64");
    const decrypted = crypto.privateDecrypt(
      {
        key: this.privateKey, 
        passphrase: this.passPhrase,
      },
      buffer,
    )
    return decrypted.toString("utf8");
  };
  generateKeys() {
    const { publicKey, privateKey } = generateKeyPairSync('rsa', 
    {
      modulusLength: 4096,
      namedCurve: 'secp256k1', 
      publicKeyEncoding: {
        type: 'spki',
        format: 'pem'     
      },     
      privateKeyEncoding: {
        type: 'pkcs8',
        format: 'pem',
        cipher: 'aes-256-cbc',
        passphrase: this.passPhrase
      } 
    });
    this.publicKey  = publicKey;
    this.privateKey = privateKey;
    return { publicKey : publicKey, privateKey}
  }
};

function urldecode(msg){
  msg = msg.replace(/\+/g,' ');
  msg = decodeURI(msg);
  msg = msg.replace(/%3A/g,':');
  msg = msg.replace(/%2C/g,',');
  msg = msg.replace(/%2F/g,'/');  
  msg = msg.replace(/\\%2F/g,'/');
  return msg;
}
class bitMonkyWSrv {
  constructor(){
    this.wallet = new bitMonkyWallet();
    console.log(this.wallet);
    this.allow = ["127.0.0.1"];
    this.recPort = 1385;
    this.readConfigFile();
    this.srv = webCon.createServer( async (req, res) => {
     var pathname = url.parse(req.url).pathname;
     if (req.method === 'GET' && pathname === '/favicon.ico') {
       res.setHeader('Content-Type', 'image/x-icon');
       fs.createReadStream('favicon.ico').pipe(res);
       return;
     }
     
     if (req.url == '/keyGEN'){
        res.writeHead(200);
        res.end('KeyGEN not available on netCon');
      }
      else {
        if (req.url.indexOf('/netREQ/msg=') == 0){
          res.writeHead(200);
          var msg = req.url.replace('/netREQ/msg=','');
          msg = urldecode(msg);
          this.handleRequest(msg,res);
        }
        else {
        if (req.url.indexOf('/netREQ') == 0){
  	    if (req.method == 'POST') {
            var body = '';
            req.on('data', (data)=>{
              body += data;
              // Too much POST data, kill the connection!
              //console.log('body.length',body.length);
              if (body.length > 300000000){
                console.log('max datazize exceeded');
                req.connection.destroy();
              }
            });
            req.on('end', ()=>{
              handleRequest(body,res);
            });
          }	
        }
        else { 
          res.setHeader("Set-Cookie", "SameSite=None; Secure");
          res.setHeader("Content-Type", "text/html");
          res.writeHead(200);
          fs.createReadStream('html/index.html').pipe(res);
          return;
        }}
      }
    });
    this.srv.on('connection', (sock)=> {
      console.log(sock.remoteAddress,this.allow);
      if (this.allow.indexOf(sock.remoteAddress) < 0){
        sock.end('HTTP/1.1 400 Bad Request\r\n\r\n');
      } 
    });

    this.srv.listen(port,'localhost');
    console.log('bitMonky Wallet Server running at http://localhost:'+port);
  }
  handleRequest(msg,res){
     var j = null;
          
     try {
       j = JSON.parse(msg);
       console.log(j);
       if (j.req){
         if (j.req == 'useNewWallet'){
           this.wallet.changeWallet(j,res);
           return;
         }
         this.wallet.doMakeReq(j.req,res,j.parms,j.service);
         return;
       } 
       if (j.what == 'getNode'){
         var report = this.sendReport();
         res.end(report);
       }
       else { 
         res.end("No Handler Found For:\n\n "+JSON.stringify(j));
       } 
    }
     catch(err) {
       //console.log("json parse error:",err);
       res.end("JSON PARSE Errors: \n\n"+msg+"\n\n"+err);
     }
  }
  readConfigFile(){
     var conf = null;
     try {conf =  fs.readFileSync(wconf);}
     catch {console.log('no config file found');}
     if (conf){
       try {
         conf = conf.toString();
         const j = JSON.parse(conf);
         this.recPort       = j.receptor.port;
         this.allow         = j.receptor.allow;
       }
       catch(err) {
         console.log('conf file not valid', err);
         this.recPort = 1385;
         this.allow = ["127.0.0.1"];
       }
     }
  }
};

class bitMonkyWallet{
   constructor(){
      this.publicKey   = null;
      this.privateKey  = null;
      this.signingKey  = null;
      this.rsaKeys     = null;
      this.openWallet();
   }
   calculateHash(txt) {
      const crypto = require('crypto');
      return crypto.createHash('sha256').update(txt).digest('hex');
   }
   signToken(token) {
      const sig = this.signingKey.sign(calculateHash(token), 'base64');
      const hexSig = sig.toDER('hex');
      return hexSig;
   }
   changeWallet(j,res){
     console.log('changeWallet',j.wallet.ownMUID); 
     if (j.wallet.ownMUID == 'useDefault'){
        this.openWallet();
        j.result = true;
        console.log('result',JSON.stringify(j));
        res.end(JSON.stringify(j));
        return;
      }
      
      this.publicKey     = j.wallet.publicKey;
      this.privateKey    = j.wallet.privateKey;
      this.ownMUID       = j.wallet.ownMUID;
      this.walletCipher  = j.wallet.walletCipher;
      this.signingKey    = ec.keyFromPrivate(this.privateKey);
      j.result = true;
      res.end(JSON.stringify(j));
   }
   openWallet(){
      var keypair = null;
      try {keypair =  fs.readFileSync(wfile);}
      catch {console.log('no wallet file found');}
      this.publicKey = null;
      if (keypair){
        try {
          const pair = keypair.toString();
          const j = JSON.parse(pair);
          this.publicKey     = j.publicKey;
          this.privateKey    = j.privateKey;
          this.ownMUID       = j.ownMUID;
          this.walletCipher  = j.walletCipher;
          if (j.rsaKeys)
            this.rsaKeys       = j.rsaKeys;
          else {
            const rsaMail = new mkyRSAMail(this.walletCipher);
            this.rsaKeys = rsaMail.generateKeys();
            this.writeWallet();
          }  
          this.signingKey    = ec.keyFromPrivate(this.privateKey);
        }
        catch(err) {console.log('wallet file not valid', err);process.exit();
        }
      }
      else {
        const key = ec.genKeyPair();
        this.publicKey = key.getPublic('hex');
        this.privateKey = key.getPrivate('hex');
        console.log('Generate a new wallet key pair and convert them to hex-strings');
        var mkybc = bitcoin.payments.p2pkh({ pubkey: new Buffer.from(''+this.publicKey, 'hex') });
        this.ownMUID = mkybc.address;

        const pmc = ec.genKeyPair();
        this.pmCipherKey  = pmc.getPublic('hex');

        console.log('Generate a new wallet cipher key');
        mkybc = bitcoin.payments.p2pkh({ pubkey: new Buffer.from(''+this.pmCipherKey, 'hex') });
        this.shardCipher = mkybc.address;

        const rsaMail = new mkyRSAMail(this.walletCipher);
        this.rsaKeys = rsaMail.generateKeys();
        this.writeWallet();
      }
   }
   writeWallet(){
     var wallet = '{"ownMUID":"'+ this.ownMUID+'","publicKey":"' + this.publicKey + '","privateKey":"' + this.privateKey + '",';
     wallet += '"walletCipher":"'+this.walletCipher+'","rsaKeys":'+JSON.stringify(this.rsaKeys)+'}';
     console.log(wallet);

     fs.writeFileSync(wfile, wallet);
   }
   signMsg(stok) {
     const sig = this.signingKey.sign(this.calculateHash(stok), 'base64');
     const hexSig = sig.toDER('hex');
     return hexSig;
   }
   doMakeReq(action,res,parms,service){
     const stok = this.ownMUID+Date.now(); 	   
     var msg = {
       Address : this.ownMUID,
       sesTok  : stok,
       pubKey  : this.publicKey,
       sesSig  : this.signMsg(stok),
       action  : action,
       parms   : parms
     }
     this.sendPostRequest(msg,res,service);
   }

   handleResponse(data,res){
     data.pMUID = this.ownMUID;
     console.log(data);
     if (res){
       res.end(JSON.stringify(data));
     }
   }
   sendPostRequest(msg,wres=null,service=null){
      if (service === null){
        service = {
          endPoint : '/whzon/gold/netWalletAPI.php',
          host     : 'www.bitmonky.com',
          port     : this.port
        }
      }
      const https = require('https');

      const data = JSON.stringify(msg);

      const options = {
        hostname : urldecode(service.host),
        port     : urldecode(service.port),
        path     : urldecode(service.endPoint),
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Content-Length': data.length
        } 
      }
      console.log('Service Options:->',options);
      const req = https.request(options, res => {
        var body = '';

        res.on('data', (chunk)=>{
          body = body + chunk;
        });

        res.on('end',()=>{
          if (res.statusCode != 200) {
            console.log("Api call failed with response code " + res.statusCode);
          } 
	  else {
            console.log('API Response:->',body);
            try {
              this.handleResponse(JSON.parse(body),wres);
            }
            catch(err) {console.log(err);}
          }
        });
      });
      req.on('error', error => {
         console.log(error);
      });

      req.write(data);
      req.end();
   } 
};

const myWallet = new bitMonkyWSrv();

module.exports.bitMonkyWSrv = bitMonkyWSrv;

