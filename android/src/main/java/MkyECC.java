import org.bouncycastle.util.encoders.Hex;
import org.web3j.crypto.*;
import java.math.BigInteger;
public class MkyECC {

    public static String compressPubKey(BigInteger pubKey) {
        String pubKeyYPrefix = pubKey.testBit(0) ? "03" : "02";
        String pubKeyHex = pubKey.toString(16);
        String pubKeyX = pubKeyHex.substring(0, 64);
        return pubKeyYPrefix + pubKeyX;
    }

    public MkyResult doTest() throws Exception {
        MkyResult mkr = new MkyResult();

        //BigInteger privKey = Keys.createEcKeyPair().getPrivateKey();
        BigInteger privKey = new BigInteger("e3df06c49fe3423d88ac118f6d9a096ca2dd36097c4fde4f06db7ab07030ec0e", 16);
        BigInteger pubKey = Sign.publicKeyFromPrivate(privKey);
        ECKeyPair keyPair = new ECKeyPair(privKey, pubKey);
        mkr.privateKey = privKey.toString(16);
        mkr.publicKey  = pubKey.toString(16);
        mkr.address    = compressPubKey(pubKey);

        String msg = "Message for signing";
        byte[] msgHash = Hash.sha3(msg.getBytes());
        Sign.SignatureData signature = Sign.signMessage(msgHash, keyPair, false);
        mkr.msg = msg;
        mkr.msgHash = Hex.toHexString(msgHash);
        mkr.signR = Hex.toHexString(signature.getR());
        mkr.signS = Hex.toHexString(signature.getS());
        mkr.signV = signature.getV();

        BigInteger pubKeyRecovered = Sign.signedMessageToKey(msg.getBytes(), signature);
        mkr.recoveredKey = pubKeyRecovered.toString(16);

         mkr.isValid = pubKey.equals(pubKeyRecovered);
         return mkr;
    }
}
