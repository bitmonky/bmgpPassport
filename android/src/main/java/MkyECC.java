import com.google.common.io.BaseEncoding;
import org.bitcoinj.core.ECKey;
import org.bitcoinj.core.LegacyAddress;
import org.bitcoinj.core.NetworkParameters;
import org.bitcoinj.core.Sha256Hash;
import org.bitcoinj.params.MainNetParams;

import java.math.BigInteger;
public class MkyECC {
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
      String cipher  = LegacyAddress.fromKey(params, myCipher).toString();
      return "{\"ownMUID\":\"" + address + "\",\"publicKey\":\"" + publicKey + "\","
              + "\"privateKey\":\"" + privateKey +"\","+ "\"walletCipher\":\"" + cipher +"\"}";
    }
}
