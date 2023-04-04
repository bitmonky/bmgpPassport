import com.google.common.io.BaseEncoding;
import org.bitcoinj.core.ECKey;
import org.bitcoinj.core.LegacyAddress;
import org.bitcoinj.core.NetworkParameters;
import org.bitcoinj.core.Sha256Hash;
import org.bitcoinj.params.MainNetParams;

import java.io.UnsupportedEncodingException;
import java.security.*;
import javax.crypto.Cipher;
import javax.crypto.*;
import javax.crypto.NoSuchPaddingException;
import javax.crypto.spec.IvParameterSpec;
import javax.crypto.spec.SecretKeySpec;

import android.util.Base64; //java.util.Base64;
import org.json.JSONException;
import org.json.JSONObject;

import java.math.BigInteger;
import java.security.spec.AlgorithmParameterSpec;


public class MkyECC {
    String ALGO = "aes-256-cbc";
    KeyPairGenerator generator = KeyPairGenerator.getInstance("RSA");
    KeyPair pair = generator.generateKeyPair();
    public class MkyRSA  {
        String pubKey = "";
        String privKey = "";
        String esaKey = "";
        String esaIV  = "";
        String msgBody = "";
        public void setKeysTo(String pKey ,String pvKey){
            pubKey  = pKey;
            privKey = pvKey;
        }
        public String  toJSON(){
            return "\"rsaKeys\": { " +
              "\"pubKey\":\"" + pubKey +
              "\",\"privKey\":\"" + privKey + "\"}";
        }
        public String getRsaPublicKey(){
            return pubKey;
        }
        public void generateNewKey() throws NoSuchAlgorithmException, NoSuchPaddingException {
            KeyGenerator keygen = KeyGenerator.getInstance("AES");
            keygen.init(128);
            byte[] key = keygen.generateKey().getEncoded();
            esaKey = android.util.Base64.encodeToString(key,android.util.Base64.DEFAULT);

            SecureRandom random = null;
            if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.O) {
                random = SecureRandom.getInstanceStrong();
            }
            byte[] iv = new byte[Cipher.getInstance(ALGO).getBlockSize()];
            esaIV = android.util.Base64.encodeToString(iv,android.util.Base64.DEFAULT);
        }
        public String doDecode(String encodedMsg) throws JSONException {
            JSONObject obj = new JSONObject(encodedMsg);
            try {
                esaKey = obj.getJSONObject("msg").getString("esaKey");
                esaIV = obj.getJSONObject("msg").getString("esaIV");
                msgBody = obj.getJSONObject("msg").getString("body");
                decryptEsaKeys();
                return decrypt(msgBody);
            }
            catch (Exception e) {
                return e.toString();
            }
        }
        public void decryptEsaKeys(){}
        public String encrypt(String src,String key,String iv) {
            esaKey = key;
            esaIV  = iv;
            try {
                Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5Padding");
                cipher.init(Cipher.ENCRYPT_MODE, makeKey(), makeIv());
                return android.util.Base64.encodeToString(cipher.doFinal(src.getBytes()),android.util.Base64.DEFAULT);
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
        }
        public String decrypt(String src) {
            String decrypted = "";
            try {
                Cipher cipher = Cipher.getInstance("AES/CBC/PKCS5Padding");
                cipher.init(Cipher.DECRYPT_MODE, makeKey(), makeIv());
                decrypted = new String(cipher.doFinal(android.util.Base64.decode(src,android.util.Base64.DEFAULT)));
            } catch (Exception e) {
                throw new RuntimeException(e);
            }
            return decrypted;
        }
        private  AlgorithmParameterSpec makeIv() {
            try {
                return new IvParameterSpec(esaIV.getBytes("UTF-8"));
            } catch (UnsupportedEncodingException e) {
                e.printStackTrace();
            }
            return null;
        }
        private  Key makeKey() {
            try {
                MessageDigest md = MessageDigest.getInstance("SHA-256");
                byte[] key = md.digest(esaKey.getBytes("UTF-8"));
                return new SecretKeySpec(key, "aes-256-cbc");
            } catch (NoSuchAlgorithmException | UnsupportedEncodingException e) {
                e.printStackTrace();
            }
            return null;
        }

    }
    public MkyRSA rsa = new MkyRSA();
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
