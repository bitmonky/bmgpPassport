import com.google.common.io.BaseEncoding;
import org.bitcoinj.core.ECKey;
import org.bitcoinj.core.LegacyAddress;
import org.bitcoinj.core.NetworkParameters;
import org.bitcoinj.core.Sha256Hash;
import org.bitcoinj.params.MainNetParams;
import java.security.*;
import android.util.Base64; //java.util.Base64;
import java.math.BigInteger;

public class MkyECC {
    KeyPairGenerator generator = KeyPairGenerator.getInstance("RSA");
    KeyPair pair = generator.generateKeyPair();
    class MkyRSA  {
        String pubKey = "";
        String privKey = "";
        public String  toJSON(){
            return "\"rsaKeys\": { " +
              "\"pubKey\":\"" + pubKey +
              "\",\"privKey\":\"" + privKey + "\"}";
        }
    }
    public MkyECC() throws NoSuchAlgorithmException {
    }

    public String signToken(String tok,String inPrivKey,String inPubKey) {
        BigInteger privKey = new BigInteger(inPrivKey, 16);
        ECKey signingKey = ECKey.fromPrivate(privKey);
        Sha256Hash msgHash = Sha256Hash.of(tok.getBytes());
        ECKey.ECDSASignature sig = signingKey.sign(msgHash);
        String res = "\"sesTok\":\"" + tok + "\","
          + "\"sesSig\":\"" + BaseEncoding.base16().lowerCase().encode(sig.encodeToDER())
          + "\",\"pubKey\":\"" + inPubKey + "\"";
        return res;
    }
    public String doCreateWallet() {
      NetworkParameters params = MainNetParams.get();
      ECKey myKeys = new ECKey();
      myKeys = myKeys.decompress();
      String publicKey  = myKeys.getPublicKeyAsHex();
      String privateKey = myKeys.getPrivateKeyAsHex();
      String address = LegacyAddress.fromKey(params, myKeys).toString();
      ECKey myCipher = new ECKey();
      myCipher = myCipher.decompress();
      PrivateKey rsaPrivKey = pair.getPrivate();
      PublicKey  rsaPubKey  = pair.getPublic();
      MkyRSA rsa = new MkyRSA();
      rsa.pubKey = android.util.Base64.encodeToString(rsaPubKey.getEncoded(),android.util.Base64.DEFAULT);
      rsa.privKey = android.util.Base64.encodeToString(rsaPrivKey.getEncoded(),android.util.Base64.DEFAULT);
      String cipher  = LegacyAddress.fromKey(params, myCipher).toString();
      return "{\"ownMUID\":\"" + address + "\",\"publicKey\":\"" + publicKey + "\","
              + "\"privateKey\":\"" + privateKey +"\","
              + "\"walletCipher\":\"" + cipher +"\","+rsa.toJSON()+"}";
    }
}
