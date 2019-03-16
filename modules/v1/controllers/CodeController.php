<?php

namespace api\modules\v1\controllers;
use Yii;
use api\modules\v1\service\UserToken as UserTokenService;
use api\models\User as UserModel;
use api\modules\v1\service\Code as CodeService;
class CodeController extends BaseActiveController
{
    public $modelClass='api\models\User';

    public function actionGet()
    {
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        $request=Yii::$app->request->bodyParams;//获取到参数
        $page=$request['path'];
        $m1=$request['m1'];
        $m2=$request['m2'];
        $m3=$request['m3'];
        $m4=$request['m4'];
        $m5=$request['m5'];
        $m6=$request['m6'];
        $isOn=$request['isOn'];
        $code=new CodeService($uid,$page,$m1,$m2,$m3,$m4,$m5,$m6,$isOn);
        return  $code->getCode($userModel);
    }
}
