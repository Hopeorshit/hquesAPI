<?php

namespace api\modules\v1\controllers;

use api\modules\CommonFunc;
use Yii;
use api\modules\v1\service\UserToken;
use api\modules\v1\service\Yorder as YorderService;
use api\models\Yorder as YorderModel;

class YorderController extends BaseActiveController
{
    public $modelClass='api\models\Yorder';

    /*
     * 支付详细流程
     * 1前端 提交赞赏金额 和 seller（被赞赏的人） 用户ID
     * 2API接收到消息之后，把订单写入数据库，告诉前端可以发起支付了
     *3 前端发起支付
     */
    public function actionPlace()
    {
     $request=Yii::$app->request->bodyParams;
     $buyerID=UserToken::getCurrentTokenVar('uid');
     $yorder=new YorderModel();
     $yorder->buyer=$buyerID;
     $yorder->name=$request['name'];
     $yorder->phone=$request['phone'];
     $yorder->xiangmu=$request['xiangmu'];
     $yorder->seller=$request['sellerID'];
     $yorder->formId=$request['formId'];
     $yorderService=new YorderService();
     $yorder_no=$yorderService->makeOrderNo();
     $yorder->yorder_no=$yorder_no;
     $jumpPage='pages/jyuyue/jyuyue?yorder_no='.$yorder_no;
     $yorder->save();
     $success=$yorderService->sendYuyueMsg($yorder,$jumpPage);
     if($success){
         return[
                'msg'=>"发送收到赞赏模板消息成功",
                'code'=>200
            ];
     }
     return $success;
    }

    public function actionMsgconfirmed(){
        $request=Yii::$app->request->bodyParams;
        $yorder_no=$request['yorder_no'];
        $formId=$request['formId'];
        $yorder_model=YorderModel::find()->where(['yorder_no'=>$yorder_no])->one();
        $yorderService=new YorderService();
        $success=$yorderService->sendMsgconfirmed($formId,$yorder_model,$jumpPage='');
        return $success;
    }

    public function actionYuewo(){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $yorderModels=YorderModel::find()->where(['seller'=>$sellerID])->with('yuewo')
            ->orderBy('created DESC')->asArray()->all();//加上asArray才能用模型关联
        $result=[];
        foreach ($yorderModels as $item ) {
            $item['avatar']=$item['yuewo']['avatar'];
            $item['user_name']=$item['yuewo']['user_name'];
            unset($item['yuewo']);
            array_push($result,$item);
        }
        return[
            'orders'=>$result,
        ];
    }

    public function actionJyuyue(){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $request=Yii::$app->request->bodyParams;
        $yorder_no=$request['yorder_no'];
        $yorderModel=YorderModel::find()->where(['yorder_no'=>$yorder_no])->with('yuewo')
            ->asArray()->one();//加上asArray才能用模型关联
        $yorderModel['avatar']= $yorderModel['yuewo']['avatar'];
        $yorderModel['user_name']= $yorderModel['yuewo']['user_name'];
        unset($yorderModel['yuewo']);
        return[
            'yorder'=>$yorderModel,
        ];
    }

    public function actionConfirm(){
        $request=Yii::$app->request->bodyParams;
        $yorder_no=$request['yorder_no'];
        $yorderModel=YorderModel::find()->where(['yorder_no'=>$yorder_no])->one();
        $yorderModel->status=1;
        $yorderModel->update();
        return[
          'msg'=>"确认成功",
          'code'=>'200'
        ];
    }


    public function actionWoyue(){
        $buyID=UserToken::getCurrentTokenVar('uid');
        $yorderModels=YorderModel::find()->where(['seller'=>$buyID])->with('woyue')
            ->orderBy('created DESC')->asArray()->all();//加上asArray才能用模型关联
        $result=[];
        foreach ($yorderModels as $item ) {
            $item['avatar']=$item['woyue']['avatar'];
            $item['user_name']=$item['woyue']['user_name'];
            unset($item['woyue']);
            array_push($result,$item);
        }
        return[
            'orders'=>$result,
        ];
    }

}
