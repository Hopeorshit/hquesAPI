<?php

namespace api\modules\v1\controllers;
use api\modules\v1\service\QiYePay as QpayService;
use api\modules\v1\service\UserToken as UserTokenService;
use api\models\Order as OrderModel;
use Yii;

class QpayController extends BaseActiveController
{
    public $modelClass='api\models\Qorder';
    /**
     * @param  amount
     * @method Post
     */
    public function actionWithdraw()
    {
        $request=Yii::$app->request->bodyParams;//获取到参数
        $amount=$request['amount'];
        $openid=UserTokenService::getCurrentTokenVar('openid');
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $qPayService=new QpayService();
        $result=$qPayService->pay($openid,$amount);
        if($result['result_code']=='SUCCESS'){
             OrderModel::updateAll(['status'=>2],['seller'=>$uid,'status'=>1]);
        }
        return $result;
    }

}
