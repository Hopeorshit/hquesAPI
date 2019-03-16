<?php

namespace api\modules\weixin\controllers;


use api\models\Album as AlbumModel;
use api\modules\weixin\service\Home;
use api\modules\weixin\service\UserCheck;
use yii;
use yii\web\Controller;
use api\modules\weixin\service\User ;


class HomeController extends Controller
{

    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染首页页面
    public function actionTest(){
        $userModel=(new User())->getCodeUser();
        $wxConfigData=(new Home())->wxConfigData();
        $this->layout=false;
        return $this->render('index',['wxConfig'=>$wxConfigData,'user'=>$userModel]);
    }

   public function actionUserinfo(){//获取code码
      $redirect_uri=urlencode(Yii::$app->params['domain'].'weixin/home/test');
      $state=1;
      $codeUrl=sprintf(Yii::$app->params['weixin']['code'],Yii::$app->params['weixin']['app_id'],$redirect_uri,'snsapi_userinfo',$state);
      header("Location:".$codeUrl);
      die();
   }
    //渲染编辑页面
    public function actionEdit($albumID){
        $userModel=UserCheck::grandCooike();
        $wxConfigData = (new Home())->wxConfigData();
        $this->layout = false;
        return $this->render('edit', ['wxConfig' => $wxConfigData,'albumID'=>$albumID]);
    }
    //渲染修改个人信息页面
    public function actionEdit_info(){
      $userModel=UserCheck::grandCooike();
      $wxConfigData=(new Home())->wxConfigData();
      $this->layout=false;
      return $this->render('editinfo',["userModel"=>$userModel,'wxConfig'=>$wxConfigData]);
    }
    //下拉刷新模块
    public function  actionList($pageSize,$page)
    {
        $uid=UserCheck::getUid();
        $query=AlbumModel::find()->where(['user_id'=>$uid,'status'=>1])->orderBy('created DESC');
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $albumModels = $query->offset($offset)->limit($pageSize)->asArray()->all();
        $result=[
            'hasMore' => $offset >= $count ? false : true,
            'albumModels' => $albumModels
        ];
        return json_encode($result);
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
}
