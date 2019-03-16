<?php
namespace api\modules\weixin\controllers;
use api\modules\weixin\service\Home;
use api\modules\weixin\service\UserCheck;
use yii\web\Controller;

class UserinfoController extends Controller
{
    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染修改个人信息页面
    public function actionEdit(){
        $userModel=UserCheck::grandCooike();
        $wxConfigData=(new Home())->wxConfigData();
        $this->layout=false;
        return $this->render('edit',["userModel"=>$userModel,'wxConfig'=>$wxConfigData]);
    }

    //修改用户信息
    public function actionEdit_info_post(){
        (new Home())->userInfoEdit();
        $result=[
            'msg'=>"success",
            "code"=>200
        ];
        return json_encode($result);
    }

    //只修改用户的头像和昵称
    public function actionEdit_info_nt_post(){
        (new Home())->editNameAndTitle();
        $result=[
            'msg'=>"success",
            "code"=>200
        ];
        return json_encode($result);
    }
}
