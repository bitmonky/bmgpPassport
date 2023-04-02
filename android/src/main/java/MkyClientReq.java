import org.json.*;
public class MkyClientReq {
    public String req = "Not Set";
    public String parms = "null";
    public String wallet = "null";
    public String endPoint = "/whzon/gold/netWalletAPI.php";
    public String host     = "www.bitmonky.com";
    public String port     = "";
    public String service  = "https://"+host+port+endPoint;
    public String publicKey;
    public String privateKey;
    public String walletCipher;
    public String ownMUID = "null";
    public String toJSON(boolean result){
       return "{\"req\":\"'+req+'\",\"result\":"+result+",\"ownMUID\":\""+ownMUID+"\"}";
    }
    public String doParse(String j){
      try {
        JSONObject obj = new JSONObject(j);
        req = obj.getString("req");
        try {parms  = obj.getJSONObject("parms").toString();}
        catch(Exception e) {}
        try {
            String srvc = obj.getJSONObject("service").toString(); //.getString("endPoint");
            try {
                JSONObject sobj = new JSONObject(srvc);
                endPoint = sobj.getString("endPoint");
                host = sobj.getString("host");
                port = sobj.getString("port");
                service = "https://" + host + port + endPoint;
            }
            catch(Exception e) {return e.toString();}
        }
        catch(Exception e) {return e.toString()+parms;}
        try {
              publicKey    = obj.getJSONObject("wallet").getString("publicKey");
              privateKey   = obj.getJSONObject("wallet").getString("privateKey");
              walletCipher = obj.getJSONObject("wallet").getString("walletCipher");
              ownMUID      = obj.getJSONObject("wallet").getString("ownMUID");
        }
        catch(Exception e) {}
      }
      catch(Exception e) {return e.toString() + j;}
      return "OK";
    }
}