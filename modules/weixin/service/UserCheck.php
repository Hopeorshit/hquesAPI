<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/29
 * Time: 0:23
 */

namespace api\modules\weixin\service;

use  yii;
use api\models\User as UserModel;
use api\modules\CommonFunc;
use api\modules\weixin\service\User as UserService;
class UserCheck extends yii\web\Controller
{

//     public static function getUserID(){
//        if(self::checkLoginStatus()){
//            return self::checkLoginStatus();
//        }else{
//           self::redirect(Yii::$app->params['domain'].'weixin/home/userinfo');
//           die();
//        }
//     }

     public static function getUid(){
         $authCookie = $_COOKIE['wz'];
         list($authToken,$uid)=explode('#',$authCookie);
         return $uid;
     }

     private function checkLoginStatus(){
         if(isset($_COOKIE['wz'])) {
            $authCookie = $_COOKIE['wz'];
            list($authToken,$uid)=explode('#',$authCookie);
            $user=UserModel::findOne($uid);
            $authTokenMd5=md5($user['openidf'].$user['unionid'].\Yii::$app->params['salt']);//注意服务号存的openidf
            if($authToken!=$authTokenMd5){//TODO 更好的错误管理机制
                return false;
            }
            return $user;
        }
        else{
            return false;
        }
    }

    public static function grandCooike(){//授权cookie返回用户
        $checkedModel=self::checkLoginStatus();
        if( $checkedModel){
            return $checkedModel;
        }else
        {
            if(isset($_GET['code']))//微信机制，通过转发打开的会屏蔽掉code码，复制微信内部链接也会屏蔽掉后面的code,来实现必须在微信内部打开链接
            {
                $code=$_GET['code'];
                $webAccessToken=new WebAccessToken($code);
                $token=$webAccessToken->get();
                $sendUrl=sprintf(Yii::$app->params['weixin']['user_info'],$token['access_token'],$token['openid']);
                $result=CommonFunc::curl_get($sendUrl);
                $result=json_decode($result,true);
                $user=new UserService();
                $auth_token=md5($result['openid'].$result['unionid'].Yii::$app->params['salt']);//用加密字符串来区分用户
                $userModel=$user->addUser($result);
                setcookie('wz',$auth_token.'#'.$userModel['id'],time()+30*24*60*60,'/');//path指向根目录，否则跨控制器访问cookie会丢失
                return $userModel;
            }
            self::redirect(Yii::$app->params['domain'].'weixin/home/userinfo');
            return false;
//            die();
        }
    }
}