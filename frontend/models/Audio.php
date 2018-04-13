<?php
namespace frontend\models;
use yii\db\ActiveRecord;

class Audio extends ActiveRecord{

       public static function getUpload( $media_id){
            //$media_id = $_POST["media_id"];
            $access_token = self::getAccessToken();
            $path = \Yii::getAlias('@webroot').'/audio/'.date('md').'/';   //保存路径，相对当前文件的路径
            if(!is_dir($path)){
                mkdir($path);
            }

            //微 信上传下载媒体文件
            $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";

            $filename = "wxaudio_".time().rand(1111,9999).".amr";
            self::downAndSaveFile($url,$path."/".$filename);

            //$data=$outPath.$filename;
            return $filename;
            //$data["msg"] = "download record audio success!";
            // $data["url"] = $url;

           // echo json_encode($data);
        }

        //获取Token
        public static function getAccessToken() {
            //  access_token 应该全局存储与更新，以下代码以写入到文件中做示例
            $data = json_decode(file_get_contents(\Yii::getAlias('@webroot').'/access_token.json'));
            if ($data->expire_time < time()) {
                $appid = "wxec5331ded31af4c7";  //自己的appid
                $appsecret = "e1aca895f340c21512a8976aa07f3d93";  //自己的appsecret
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
                $res = json_decode(file_get_contents($url));
                var_dump($res);exit;
                $access_token = $res->access_token;
                if ($access_token) {
                    $data->expire_time = time() + 7000;
                    $data->access_token = $access_token;
                    $fp = fopen(\Yii::getAlias('@webroot').'/access_token.json', "w");
                    fwrite($fp, json_encode($data));
                    fclose($fp);
                }
            }
            else {
                $access_token = $data->access_token;
            }
            return $access_token;
        }

        //HTTP get 请求
        public static function httpGet($url) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 500);
            curl_setopt($curl, CURLOPT_URL, $url);
            $res = curl_exec($curl);
            curl_close($curl);

            return $res;
        }

        //根据URL地址，下载文件
        public static function downAndSaveFile($url,$savePath){
            ob_start();
            readfile($url);
            $img  = ob_get_contents();
            ob_end_clean();
            $size = strlen($img);
            $fp = fopen($savePath, 'a');
            fwrite($fp, $img);
            fclose($fp);
        }

}