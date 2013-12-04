<?php

class Scalr_Util_CryptoTool
{
    private $cryptoAlgo,
            $cipherMode,
            $keySize,
            $blockSize;

    public function __construct($cryptoAlgo = MCRYPT_TRIPLEDES, $cipherMode = MCRYPT_MODE_CFB,
                                $keySize = 24, $blockSize = 8)
    {
        $this->cryptoAlgo = $cryptoAlgo;
        $this->cipherMode = $cipherMode;
        $this->keySize = $keySize;
        $this->blockSize = $blockSize;
    }

    private function splitKeyIv($cryptoKey)
    {
        $key = substr($cryptoKey, 0, $this->keySize); # Use first n bytes as key
        $iv = substr($cryptoKey, -$this->blockSize); # Use last m bytes as IV
        return array($key, $iv);
    }

    private function pkcs5Padding($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public function encrypt($string, $cryptoKey)
    {
        list ($key, $iv) = $this->splitKeyIv($cryptoKey);
        $string = $this->pkcs5Padding($string, $this->blockSize);
        return base64_encode(mcrypt_encrypt($this->cryptoAlgo, $key, $string, $this->cipherMode, $iv));
    }

    public function decrypt($string, $cryptoKey)
    {
        list ($key, $iv) = $this->splitKeyIv($cryptoKey);
        $ret = mcrypt_decrypt($this->cryptoAlgo, $key, base64_decode($string), $this->cipherMode, $iv);
        // Remove padding
        return trim($ret, "\x00..\x1F");
    }

    public static function hash($input)
    {
        return hash("sha256", $input);
    }

    public static function sault($length = 10)
    {
        return substr(md5(uniqid(rand(), true)), 0, $length);
    }

    public static function generateTenderMultipassToken($data)
    {
        $salted = TENDER_APIKEY . TENDER_SITEKEY;
        $hash = hash('sha1', $salted, true);
        $saltedHash = substr($hash, 0, 16);
        $iv = "OpenSSL for Ruby";

        // double XOR first block
        for ($i = 0; $i < 16; $i++) {
            $data[$i] = $data[$i] ^ $iv[$i];
        }

        $pad = 16 - (strlen($data) % 16);
        $data = $data . str_repeat(chr($pad), $pad);

        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
        mcrypt_generic_init($cipher, $saltedHash, $iv);
        $encryptedData = mcrypt_generic($cipher, $data);
        mcrypt_generic_deinit($cipher);

        return urlencode(base64_encode($encryptedData));
    }
}
