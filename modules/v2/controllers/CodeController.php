<?php

namespace api\modules\v2\controllers;
use Yii;
use api\modules\v2\service\UserToken as UserTokenService;
use api\models\User as UserModel;
use api\modules\v2\service\Code as CodeService;
class CodeController extends BaseActiveController
{
    public $modelClass='api\models\User';

    public function actionGet()
    {
        $request = Yii::$app->request->bodyParams;//获取到参数
        $uid=$request['uid'];
        $userModel=UserModel::findOne($uid);
        if($userModel['code']){
            return [
                'user'=>$userModel
            ];
        }else {
            $page = $request['path'];;
            $code = new CodeService($uid,$page);
            return $code->getCode($userModel);
       }
    }
}
