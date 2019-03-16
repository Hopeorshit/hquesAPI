<?php
/**
 * Created by Byron
 * User: Administrator
 * Date: 2017/9/26
 * Time: 17:41
 * 用来处理复杂的Token逻辑
 */

namespace api\modules\v2\service;

use api\models\User;
use api\modules\CommonFunc;
use Yii;
use yii\base\ErrorException;

class UserToken
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    public function get($code){
        $this->code=$code;
        $wxLogin=Yii::$app->params['wxLogin'];
        $this->wxAppID=$wxLogin['app_id'];//从配置文件中读取
        $this->wxAppSecret=$wxLogin['app_secret'];
        $this->wxLoginUrl=sprintf($wxLogin['login_url'],$this->wxAppID,$this->wxAppSecret,$this->code);
       $result=CommonFunc::curl_get($this->wxLoginUrl);//获取请求的调用结果
       $wxResult=json_decode($result,true);//加ture表示是替换成数组，否则是对象
       if(empty($wxResult)){
         throw new ErrorException('获取session_key和openID时异常，微信内部错误');
       }
       else{
           $loginFail=array_key_exists('errcode',$wxResult);
           if ($loginFail){
            throw new ErrorException($wxResult['errmsg']);
           }else{
            $token=$this->grantToken($wxResult);
           }
       }
       return $token;
    }
    //TODO wx.login只能获取到用户的 openid(未关注微信公众号)
    //TODO 进行授权但不进行存储用户
    public function grantToken($wxResult){//进行授权
        //1 检测有没有unionid字段
        //2 如果有。检测用户存不存在，存在不去创建用户，然后token也要记录用户id，不存在去创建。
        //3 如果没有，直接缓存
        if(array_key_exists('unionid',$wxResult)){
            $unionID=$wxResult['unionid'];
            $openid=$wxResult['openid'];
            $user=(new User())->getUserByUnionID($unionID);
            if($user){
                if(!$user['openid']){
                    $user->openid=$openid;
                    $user->update();
                }
                $uid=$user->id;
            }
            else{
                $newUser=new User();
                $newUser->openid=$openid;
                $newUser->unionid=$unionID;
                $newUser->save();
                $uid=$newUser->id;
            }
            $wxResult['uid']=$uid;
        }else{
            $wxResult['uid']=1;//1 代表没有unionID
        }
        $token=$this->saveToCache($wxResult);
        //为了让前端更好区分用户有没有登录，返回登录状态
        $loginStatus=$wxResult['uid']==1?false:true;
        $result=[
          'token'=>$token,
          'loginStatus'=>$loginStatus
        ];
        return $result;
    }

    private function saveToCache($cachedValue){
        $randChar=CommonFunc::getRandChar(32);
        $timeStamp=(string)$_SERVER['REQUEST_TIME_FLOAT'];
        $salt=Yii::$app->params['salt'];
        $key=md5($randChar.$timeStamp.$salt);//通过Md5加密算法生成令牌
        $value=json_encode($cachedValue); //字符串缓存，因此要转换成json格式
        $expire_in=Yii::$app->params['token_expire_in'];
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