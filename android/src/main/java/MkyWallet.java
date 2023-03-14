import org.json.*;
public class MkyWallet {
    public Integer sPort = 8080;
    public String ownMUID = "Not Set";
    public String publicKey;
    public String privateKey;
    public String walletCipher;
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
      }
      catch(Exception e) {return e.toString();}
      return "OK";
    }
}