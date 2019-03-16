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

class YuyueMsg extends WxMessage
{
    const templateID='RxMym13WnVqwrNarJWrbdL7si36GO_RQIyx4dx58wRY';

     public function sendYuyueMsg($yorder,$tplJumpPage){
         if(!$yorder){
            throw new  Exception("订单不存在");
         }
         $this->tplID=self::templateID;
         $this->formID=$yorder->formId;
         $this->page=$tplJumpPage;
         $this->prepareMessageData($yorder);
         return parent::sendMessage($this->getUserOpenID($yorder));
     }

     private function prepareMessageData($yrder){
         $data=[
             'keyword1'=>[//预约人
                 'value'=>$yrder->name
             ],
             'keyword2'=>[//联系方式
                 'value'=>$yrder->phone
             ],
             'keyword3'=>[//项目
                 'value'=>$yrder->xiangmu
             ],
         ];
         $this->data=$data;
     }

     private function getUserOpenID($yorder){
         $user=UserModel::findOne($yorder['seller']);

         $openID=$user['openid'];
         return $openID;
     }

}