<?php
/**
 * Created by Byron
 * User: Administrator
 * Date: 2017/9/26
 * Time: 17:41
 * 用来处理复杂的Token逻辑
 */

namespace api\modules\app\service;

use api\models\User;
use api\modules\CommonFunc;
use Yii;
use yii\base\ErrorException;

class UserToken
{

    public function grantToken($userModel){//进行授权
        $cachedValue['unionid']=$userModel['unionid'];
        $cachedValue['openid']=$userModel['openid'];
        $cachedValue['uid']=$userModel['id'];
        $token=$this->saveToCache($cachedValue);
        return $token;
    }

    private function saveToCache($cachedValue){
        $randChar=CommonFunc::getRandChar(32);
        $timeStamp=(string)$_SERVER['REQUEST_TIME_FLOAT'];
        $salt=Yii::$app->params['salt'];
        $key=md5($randChar.$timeStamp.$salt);//通过Md5加密算法生成令牌
        $value=json_encode($cachedValue); //字符串缓存，因此要转换成json格式
        $expire_in=31*24*60*60;
        $cache=Yii::$app->cache;
        $result=$cache->set($key,$value,$expire_in); //可以在配置文件中配置填写对应的缓存形式 如Redis等
        if(!$result){
            throw new ErrorException('缓存异常','10005');
        }
        return $key; //写入缓存了然后返回前端
    }


    public static function getCurrentTokenVar($key){
        $token= $_SERVER['HTTP_TOKEN']; //获取到TOKEN令牌
        $cache=Yii::$app->cache;//缓存
        $vars=$cache->get($token);//因为之前存储的时候 name就是token
        if(!$vars){
            throw new ErrorException('缓存已过期或者不存在TOKEN','401');
        }else{
            if(!is_array($vars)){//由于缓存的形式多种多样，可能不是以数组的形式存储
                $vars=json_decode($vars,true);
            }
            if (!array_key_exists($key,$vars)){//看是否存在要取出来的数据
               return false;//TODO 如果不存在用户Uid
            }
            else{
               return $vars[$key];
            }
        }
    }
    //验证Token是否存在
    public static function  verifyToken(){
        $token= $_SERVER['HTTP_TOKEN']; //获取到TOKEN令牌
        $cache=Yii::$app->cache;
        $vars=$cache->get($token);//因为之前存储的时候 name就是token
        if(!$vars){
            return 0;
        }
        else{
            return 1;
        }
    }
}