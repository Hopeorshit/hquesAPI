<?php

namespace api\modules\v2\controllers;

use api\models\User;
use api\models\Yorder as YorderModel;
use api\modules\v2\service\UserToken;
use api\modules\v2\service\Yorder as YorderService;
use api\modules\weixin\service\Msg as WeiXinMsg;
use api\modules\weixin\service\YuyueMsg as WeiXinYuYueMsg;
use Yii;

class YorderController extends BaseActiveController
{
    public $modelClass='api\models\Yorder';

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
     $weixinMsg=new WeiXinMsg();
     $user=User::findOne($request['sellerID']);
//     $weixinMsg->sendKeFuMsg($user['openidf'],$yorder);//TODO 没有关注微信公众号的应该给提醒
     $success=(new WeiXinYuYueMsg())->sendYuyueMsg($yorder,$jumpPage);
         return[
                'msg'=>"发送收到赞赏模板消息成功",
                'code'=>200
            ];
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

    public function actionYuewo($pageSize,$page){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $query=YorderModel::find()->where(['seller'=>$sellerID])->with('yuewo')
            ->orderBy('created DESC')->asArray();
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $yorderModels = $query->offset($offset)->limit($pageSize)->all();
        $result=[];
        foreach ($yorderModels as $item ) {
            $item['avatar']=$item['yuewo']['avatarUrl'];
            $item['user_name']=$item['yuewo']['nickName'];
            unset($item['yuewo']);
            array_push($result,$item);
        }
        return[
            'hasMore' => $offset >= $count ? false : true,
            'orders'=>$result,
        ];
    }

    public function actionJyuyue(){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $request=Yii::$app->request->bodyParams;
        $yorder_no=$request['yorder_no'];
        $yorderModel=YorderModel::find()->where(['yorder_no'=>$yorder_no])->with('yuewo')
            ->asArray()->one();//加上asArray才能用模型关联
        $yorderModel['avatar']= $yorderModel['yuewo']['avatarUrl'];
        $yorderModel['user_name']= $yorderModel['yuewo']['nickName'];
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
