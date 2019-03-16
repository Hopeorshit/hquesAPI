<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:52
 * 请求和管理AccessToken
 */

namespace api\modules\v1\service;
use api\modules\CommonFunc;
use Yii;
use yii\base\Exception;


class AccessToken
{
   private $tokenUrl;
   const TOKEN_CACHED_KEY='access';//存到缓存里面，不能太频繁的访问
   const TOKEN_EXPIRE_IN='7000';//微信是7200,

    function __construct()
    {
        $url=YII::$app->params['access_token_url'];
        $wxLogin=Yii::$app->params['wxLogin'];
        $wxAppID=$wxLogin['app_id'];//从配置文件中读取
        $wxAppSecret=$wxLogin['app_secret'];
        $url=sprintf($url,$wxAppID,$wxAppSecret);
        $this->tokenUrl=$url;
    }

    //微信access_token接口获取限制的2000次/天
    public function  get(){
        $token=$this->getFormCache();//先从缓存中读取
        if(!$token){
            return $this->getFromWxServer();
        }
        else{
            return $token;
        }
    }

    private function getFormCache(){
        $cache=Yii::$app->cache;
        $token=$cache->get(self::TOKEN_CACHED_KEY);
        if($token){
            return $token['access_token'];
        }
        return null;

    }

    private function getFromWxServer(){
     $token=CommonFunc::curl_get($this->tokenUrl);
     $token=json_decode($token,true);
     if(!$token){
         throw new Exception('获取TOKEN令牌异常');
     }
     if(empty($token)){
         throw new Exception($token['errmsg']);
     }
     $this->saveToCache($token);
     return $token['access_token'];
    }

    private function saveToCache($token){
        $cache=Yii::$app->cache;
        $cache->set(self::TOKEN_CACHED_KEY,$token,self::TOKEN_EXPIRE_IN);
    }

}