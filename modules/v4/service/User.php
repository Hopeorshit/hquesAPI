<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */
namespace api\modules\v4\service;
use api\modules\v4\service\UserToken as UserTokenService;
use Yii;
class User
{
    public function saveUserInfo($userModel){
        $userInfo=$this->getUserInfo();
        $userModel->save_status=1;
        $userModel->gender = $userInfo['gender'];
        $userModel->nickName =$userInfo['nickName'];
        $userModel->city =$userInfo['city'];
        $userModel->province=$userInfo['province'];
        $userModel->country=$userInfo['country'];
        $userModel->openid=$userInfo['openid'];
        $userModel->unionid=$userInfo['unionId'];
        $uid=$userModel['id'];
        //存照片
        $userAvatar= $userInfo['avatarUrl'];
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

    private function getUserInfo(){
        $session_key=UserTokenService::getCurrentTokenVar('session_key');
        $wxLogin=Yii::$app->params['wxLogin'];
        $wxAppID=$wxLogin['app_id'];//从配置文件中读取
        $request=Yii::$app->request->bodyParams;//获取到参数
        $encryptedData=$request['encryptedData'];
        $iv=$request['iv'];
        $wxBiz=new \WXBizDataCrypt($wxAppID,$session_key);
        $data='';
        $code=$wxBiz->decryptData($encryptedData,$iv,$data);
        $result = json_decode($data,true);
        return $result;
    }

}