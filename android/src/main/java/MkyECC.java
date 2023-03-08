import com.google.common.io.BaseEncoding;
import org.bitcoinj.core.ECKey;
import org.bitcoinj.core.Sha256Hash;
import java.math.BigInteger;
public class MkyECC {
    public String signToken(String tok,String inPrivKey,String inPubKey) {
        BigInteger privKey = new BigInteger(inPrivKey, 16);
        ECKey signingKey = ECKey.fromPrivate(privKey);
        Sha256Hash msgHash = Sha256Hash.of(tok.getBytes());
        ECKey.ECDSASignature sig = signingKey.sign(msgHash);
        String res = "'{\"token\":\"" + tok + "\","
          + "\"signature\"" + BaseEncoding.base16().lowerCase().encode(sig.encodeToDER())
          + "\",\"publicKey\":\"" + inPubKey + "\"}";
        return res;
    }
}
