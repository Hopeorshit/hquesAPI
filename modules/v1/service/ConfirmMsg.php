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

class ConfirmMsg extends WxMessage
{
    const templateID='BGeB3Szv60FnRxMio6fgMxA5hhorasBtkw18H2gSTYA';

    public function sendMsgConfirmed($formId,$yorder,$tplJumpPage){
        if(!$yorder){
            throw new  Exception("订单不存在");
        }
        $this->tplID=self::templateID;
        $this->formID=$formId;
        $this->page=$tplJumpPage;
        $user=UserModel::findOne($yorder['buyer']);
        $this->prepareMessageData($yorder,$user);
        return parent::sendMessage($user['openid']);
    }

    private function prepareMessageData($yorder,$user){
        $data=[
            'keyword1'=>[//项目
                'value'=>$yorder->xiangmu
            ],
            'keyword2'=>[//预约人
                'value'=>$user['user_name']
            ],
            'keyword3'=>[//详情
                'value'=>"对方已确认您的预约"
            ],
            'keyword4'=>[
                'value'=>$yorder->yorder_no.'(可凭此订单号联系客服)'
            ]
        ];
        $this->data=$data;
    }

}