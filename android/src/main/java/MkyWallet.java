import org.json.*;
public class MkyWallet {
    public Integer sPort = 8080;
    public String ownMUID = "Not Set";
    public String publicKey;
    public String privateKey;
    public String walletCipher;
    public String rsaPubKey = "Not Set";
    public String rsaPrivKey;
    public String setPort(Integer inPort){
        sPort=inPort;
        return sPort.toString();
    }
    public String doParse(String j){
      try {
          JSONObject obj = new JSONObject(j);
          ownMUID  = obj.getString("ownMUID");
          publicKey  = obj.getString("publicKey");
          privateKey = obj.getString("privateKey");
          walletCipher = obj.getString("walletCipher");
          try {
            rsaPubKey = obj.getJSONObject("rsaKeys").getString("pubKey");
            rsaPrivKey = obj.getJSONObject("rsaKeys").getString("privKey");
          }
          catch(Exception e) {}//create new keys and write to fileher
      }
      catch(Exception e) {ownMUID = e.toString();}
      return "OK";
    }
}