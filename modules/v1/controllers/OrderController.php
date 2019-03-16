<?php

namespace api\modules\v1\controllers;

use api\models\Order;
use Yii;
use api\modules\v1\service\UserToken;
use api\modules\v1\service\Order as OrderService;
use api\modules\v1\service\Pay as PayService;
use api\models\Order as OrderModel;
use api\modules\v1\service\WxNotify;

class OrderController extends BaseActiveController
{
    public $modelClass='api\models\Order';

    /*
     * 支付详细流程
     * 1前端 提交赞赏金额 和 seller（被赞赏的人） 用户ID
     * 2API接收到消息之后，把订单写入数据库，告诉前端可以发起支付了
     *3 前端发起支付
     *
     *
     */
    public function actionPlace()
    {
     $request=Yii::$app->request->bodyParams;
     $buyerID=UserToken::getCurrentTokenVar('uid');
     $money=$request['money'];
     $sellerID=$request['sellerID'];
     $order=new OrderService();
     $order=$order->place($buyerID,$sellerID,$money);
     $order=$order->toArray();
     return [//下单成功，向前端返回下单单号
       'order_id'=>$order['id'],
       'order_no'=>$order['order_no']
     ];
    }

    public function actionGetpreorder(){
      $request=YII::$app->request->bodyParams;
      $orderID=$request['orderID'];//获取到订单ID
      $pay=new PayService($orderID);
      $uid=UserToken::getCurrentTokenVar('uid');
      return $pay->pay($uid);
    }

    public function actionNotify(){//支付完成之后要进行的动作
        $notify=new WxNotify();
        $notify->Handle();
    }

    public function actionMsg(){
        $request=YII::$app->request->bodyParams;
        $orderID=$request['orderID'];//获取到订单ID
        $msg=$request['msg'];
        $orderModel=OrderModel::findOne($orderID);
        $orderModel->msg=$msg;
        $orderModel->save();
        $order = new OrderService();
        $order->sendAwardMsg($orderModel);
        return [
          'msg'=>'success',
          'code'=>'200'
        ];
    }

    public function actionYue(){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $order=new OrderService();
        return[
         'amount'=>$order->countMoney($sellerID)
        ];
    }

    public function actionZanwo(){
        $sellerID=UserToken::getCurrentTokenVar('uid');
        $orderModels=OrderModel::find()->where(['and','seller'=>$sellerID,['in','status',[1,2]]])->with('zanwo')->select(['id','buyer','seller','created','msg','amount'])
            ->orderBy('created DESC')->asArray()->all();//加上asArray才能用模型关联
        $result=[];
        foreach ($orderModels as $item ) {
            $item['avatar']=$item['zanwo']['avatar'];
            $item['user_name']=$item['zanwo']['user_name'];
            unset($item['zanwo']);
            array_push($result,$item);
        }
        $amount=OrderModel::find()->where(['and','seller'=>$sellerID,['in','status',[1,2]]])->sum('amount');
        return[
          'orders'=>$result,
          'amount'=>$amount
        ];
    }

    public function actionWozan(){
        $buyerID=UserToken::getCurrentTokenVar('uid');
        $orderModels=OrderModel::find()->where(['and','buyer'=>$buyerID,['in','status',[1,2]]])->with('wozan')->select(['id','seller','buyer','created','msg','amount'])
            ->orderBy('created DESC')->asArray()->all();//加上asArray才能用模型关联
        $result=[];
        foreach ($orderModels as $item ) {
            $item['avatar']=$item['wozan']['avatar'];
            $item['user_name']=$item['wozan']['user_name'];
            unset($item['wozan']);
            array_push($result,$item);
        }
        $amount=OrderModel::find()->where(['and','buyer'=>$buyerID,['in','status',[1,2]]])->sum('amount');
        return[
            'orders'=>$result,
            'amount'=>$amount
        ];
    }
}
