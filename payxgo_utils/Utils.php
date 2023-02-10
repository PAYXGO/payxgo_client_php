<?php

class Utils{
    
    /**
     * 生成随机字符串
     * @param unknown_type $length
     * @return Ambigous <NULL, string>
     */
    public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];
        }

        return $str;
    }

    //将单行的公钥字符串转成标准的公钥格式内容
    public  function convertPubkey($keyStr){
        // $keyStr = base64_decode($keyStr);
        $pem = chunk_split($keyStr, 64, "\n");
        $pem = "-----BEGIN PUBLIC KEY-----\n" . $pem . "-----END PUBLIC KEY-----\n";
        return $pem;
    }
    
    //将单行的私钥字符串转成标准的私钥格式内容
    public  function convertPrikey($keyStr){
        // $keyStr = base64_decode($keyStr);
        $pem = chunk_split($keyStr, 64, "\n");
        $pem = "-----BEGIN PRIVATE KEY-----\n" . $pem . "-----END PRIVATE KEY-----\n";
        return $pem;
    }
    
}