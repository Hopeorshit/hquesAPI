<?php
namespace api\modules\weixin\controllers;
use api\modules\weixin\service\Home;
use api\modules\weixin\service\JsonMsg;
use api\modules\weixin\service\User;
use api\modules\weixin\service\UserCheck;
use yii\web\Controller;

class MineController extends Controller
{
    public $enableCsrfValidation=false;//这个要加上否则访问不了
    //渲染我的页面
    public function actionMine(){
        $userModel=(new User())->getCodeUser();
        $wxConfigData=(new Home())->wxConfigData();
        $this->layout=false;
        return $this->render('mine',['user'=>$userModel,'wxConfig'=>$wxConfigData]);
    }

    public function actionSwitch_status(){
        $userModel=UserCheck::grandCooike();
        return JsonMsg::success($userModel['ison']);
    }

    public function actionYuyue_switch(){
        $userModel=UserCheck::grandCooike();
        if($userModel['ison']==1){
            $userModel->ison=0;
        }else{
            $userModel->ison=1;
        }
        $userModel->update();
    }

    public function actionGet_code()
    {
        $userModel=(new User())->getCodeUser();
        $result=[
            'erCode'=>$userModel['code']
        ];
        return json_encode($result);
    }
}
