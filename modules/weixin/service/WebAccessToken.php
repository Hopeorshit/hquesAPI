<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:52
 * 请求和管理AccessToken
 */

namespace api\modules\weixin\service;
use api\modules\CommonFunc;
use Faker\Provider\Base;
use Yii;
use yii\base\Exception;
use api\modules\weixin\controllers\BaseController;

//由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token有效期为30天，当refresh_token失效之后，需要用户重新授权。
//网页授权获取access_token接口无上限，不采用缓存策略
class WebAccessToken
{
    private $tokenUrl;

    function __construct($code)
    {
        $this->tokenUrl=sprintf(YII::$app->params['weixin']['web_access_token'],Yii::$app->params['weixin']['app_id'],Yii::$app->params['weixin']['app_secret'],$code);
    }

    //微信access_token接口获取限制的2000次/天
    public function  get(){
      $token=$this->getFromWxServer();
//        $cache=Yii::$app->cache;
//        $cache->set('zp',$token,7200);
      if($this->checkAccessToken($token)){
          $newToken=$this->getRefreshFromWxServer($token['refresh_token']);
          return $newToken;
      }
      return $token;
    }

    private function getFromWxServer(){//从服务器读取
      $token=CommonFunc::curl_get($this->tokenUrl);
      $token=json_decode($token,true);
        if(isset($token['errmsg'])){//TODO Render Json
         header('Location:'.Yii::$app->params['domain'].'weixin/home/userinfo');
         die();
        }
      return $token;
    }

    private function getRefreshFromWxServer($refreshToken){
        $senUrl=sprintf(Yii::$app->params['weixin']['refresh_token'],Yii::$app->params['weixin']['app_id'],$refreshToken);
        $token=CommonFunc::curl_get($senUrl);
        $token=json_decode($token,true);
        if(isset($token['errmsg'])){
            header('Location:'.Yii::$app->params['domain'].'weixin/home/userinfo');
            die();
        }
        return $token;
    }

    //验证AccessToken是否有效，有效则直接使用，无效则选用Refresh_token
    private function checkAccessToken($token){
      $senUrl=sprintf(Yii::$app->params['weixin']['check_refresh'],$token['access_token'],$token['openid']);
      $result=CommonFunc::curl_get($senUrl);
      $result=json_decode($result,true);
      if($result['errcode']==0){
         return true;
      }else{
         return false;
      }
    }

}