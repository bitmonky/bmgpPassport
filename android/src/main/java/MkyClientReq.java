import org.json.*;
public class MkyClientReq {
    public String req = "Not Set";
    public String parms = "null";
    public String endPoint = "/whzon/gold/netWalletAPI.php";
    public String host     = "www.bitmonky.com";
    public String port     = "";
    public String service  = "https://"+host+port+endPoint;
    public String publicKey;
    public String privateKey;
    public String walletCipher;
    public String doParse(String j){
      try {
        JSONObject obj = new JSONObject(j);
        req = obj.getString("req");
        try {parms  = obj.getJSONObject("parms").toString();}
        catch(Exception e) {}
        try {
            endPoint = obj.getJSONObject("service").getString("endPoint");
            host     = obj.getJSONObject("service").getString("host");
            port     = obj.getJSONObject("service").getString("port");
            service  = "https://"+host+port+endPoint;
        }
        catch(Exception e) {}
      }
      catch(Exception e) {return e.toString() + j;}
      return "OK";
    }
}