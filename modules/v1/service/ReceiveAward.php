<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/27
 * Time: 14:27
 */

namespace api\modules\v1\service;

use api\models\User as UserModel;
use yii\base\Exception;

class ReceiveAward extends WxMessage
{
    const templateID='sL36LS3zIAXMHzv7dHyQ_soetow_tbZ5wWrSrQUClhs';

     public function sendReceiveAward($order,$tplJumpPage=''){
         if(!$order){
            throw new  Exception("订单不存在");
         }
         $this->tplID=self::templateID;
         $this->formID=$order->prepay_id;
         $seller=$this->getSeller($order);
         $this->prepareMessageData($seller,$order);
         return parent::sendMessage($seller['openid']);
     }

     private function prepareMessageData($seller,$order){
         $data=[
             'keyword1'=>[//赞赏人
                 'value'=>$seller['user_name']
             ],
             'keyword2'=>[//赞赏金额
                 'value'=>$order->amount
             ],
             'keyword3'=>[//赞赏时间
                 'value'=>$order->created
             ],
             'keyword4'=>[//赞赏详情
                 'value'=>$seller['user_name'].'喜欢你的作品，对你表达了支持哦'
             ],
             'keyword5'=>[//赞赏留言
                 'value'=>$order->msg
             ],
         ];
         $this->data=$data;
     }

     private function getSeller($order){
         $seller=UserModel::findOne($order['seller']);
//         $openID=$user['openid'];
         return $seller;
     }

}