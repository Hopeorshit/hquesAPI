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
use api\modules\v2\service\Code as CodeService;
class User
{
   public function addUser($userInfo)
   {
       $user = (new UserModel())->getUserByUnionID($userInfo['unionid']);//TODO 统一 登陆哪个应用都记录unionID
       if ($user) {
           $user->openidf=$userInfo['openid'];
           $user->update();
           $userModel=$this->saveUserInfo($user,$userInfo);
           return $userModel;
       } else {
           $newUser = new UserModel();
           $newUser->openidf = $userInfo['openid'];
           $newUser->unionid = $userInfo['unionid'];
           $newUser->save();
           $userModel=$this->saveUserInfo($newUser,$userInfo);
           return $userModel;
       }
   }

   private function saveUserInfo($userModel,$userInfo){
       if($userModel['save_status']==1){
         return $userModel;
       }
       $userModel->save_status=1;
       $userModel->gender = $userInfo['sex'];
       $userModel->nickName =$userInfo['nickname'];
       $userModel->city =$userInfo['city'];
       $userModel->province=$userInfo['province'];
       $userModel->country=$userInfo['country'];
       $uid=$userModel->id;
       $userAvatar= $userInfo['headimgurl'];
       if (!is_dir("image/{$uid}")) {
           mkdir("image/{$uid}");//根据用户的OpenID命名文件夹，username可能文件夹命名不支持
           chmod("image/{$uid}",0777);//Linux 系统要这样写
       }
       $time=time();
       file_put_contents("image/{$uid}/avatar{$time}.jpg", file_get_contents($userAvatar));
       $domain=YII::$app->params['domain'];
       $userModel->avatarUrl=$domain."image/{$uid}/avatar{$time}.jpg";
       $userModel->update();
       return $userModel;
   }

   public function getCodeUser(){
       $userModel=UserCheck::grandCooike();
       if($userModel['code']){
           return $userModel;
       }else {
           $page ='pages/zhuye/zhuye';
           $code = new CodeService($userModel['id'],$page);
           $userJson=$code->getCode($userModel);
           return $userJson['user'];
       }
   }

}