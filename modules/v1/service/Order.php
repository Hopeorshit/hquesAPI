<?php
/**
 * Created by Byron
 * User: Administrator
 * Date: 2017/9/26
 * Time: 17:41
 * 用来处理复杂的Token逻辑
 */

namespace api\modules\v1\service;

use api\models\Order as OrderModel;

class Order
{
    protected $buyerID;
    protected $sellerID;
    protected $money;

    public function place($buyerID, $sellerID, $money)
    {
        $this->buyerID = $buyerID;
        $this->sellerID = $sellerID;
        $this->money = $money;
        return $this->createOrder();
    }

    private function createOrder()
    {
        $order = new OrderModel();
        $order->buyer = $this->buyerID;
        $order->seller = $this->sellerID;
        $order->status = 0;//数据库默认设置的也是填入0
        $order->amount = $this->money;
        $order->order_no = $this->makeOrderNo();
        $order->save();
        return $order;
    }

    private function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = $yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        //年，月，日，时间戳，微妙数，随机数
        return $orderSn;
    }

    public function sendAwardMsg($order,$jumpPage='')
    {
     $msg=new ReceiveAward();
     return $msg->sendReceiveAward($order,$jumpPage);
    }

    public function countMoney($sellerID){
        $result=OrderModel::find()->where(['seller'=>$sellerID,'status'=>1])->sum('amount');
        return $result;
    }
}