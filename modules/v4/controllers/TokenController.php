<?php

namespace api\modules\v4\controllers;

use Yii;
use api\validate\BaseValidate;
use yii\base\ErrorException;
use api\modules\v4\service\UserToken;


class TokenController extends BaseActiveController
{
    public $modelClass='api\models\User';

    public function actionGet_token()//获取令牌
    {
        $request=Yii::$app->request->bodyParams;//获取到参数
     //   file_get_contents('php://input')
        $valid=new BaseValidate([
                'method'=>['isNotEmpty'],
                'message'=>['code码不能为空']
            ]
        );
        $valid->validate($request,$error);
        if($error){
            throw new ErrorException($error);//参数验证结束
        }
        $ut=new UserToken();
        $result =$ut->get($request['code']);//根据code码获取token
        return [
            'token'=>$result['token'],
            'loginStatus'=>$result['loginStatus']
        ];
    }

    public function actionVerify_token(){
        return UserToken::verifyToken();
    }
}
