<?php

namespace api\modules\weixin\controllers;

use yii\web\Controller;
use api\models\User as UserModel;
use yii;

class BaseController extends Controller
{

    public static function reLogin(){
        self::redirect(Yii::$app->params['domain'].'weixin/home/userinfo');
    }

//    public function beforeAction($action)
//    {
//        if($this->checkLoginStatus()){
//            return true;
//        }else{//重新获取cookie
//
//        }
//
//    }
//
//    private function checkLoginStatus(){
//       if(isset($_COOKIE['zp'])) {
//           $authCookie = $_COOKIE['zp'];
//           list($authToken,$uid)=explode('#',$authCookie);
//           $user=UserModel::findOne($uid);
//           $authTokenMd5=md5($user['openidf'].$user['unionid'].\Yii::$app->params['salt']);
//           if($authToken!=$authTokenMd5){
//               return false;
//           }
//           return true;
//       }
//       else{
//           return false;
//       }
//    }

}
