<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:52
 * 请求和管理AccessToken
 */

namespace api\modules\weixin\service;
use yii\base\Exception;
use api\models\User as UserModel;

class YuyueMsg extends WxMessage
{
    const templateID='RO_dXTpocxJW57-HXi_vt2VXzE--watYA_VUujZaruY';

    public function sendYuyueMsg($yorder,$tplJumpPage){
        if(!$yorder){
            throw new  Exception("订单不存在");
        }
        $this->tplID=self::templateID;
        $this->prepareMessageData($yorder,$tplJumpPage);
        return parent::sendMessage($this->getUserOpenID($yorder));
    }

    private function prepareMessageData($yrder,$tplJumpPage){
        $miniProgram=[
            'appid'=>\Yii::$app->params['wxLogin']['app_id'],
            'pagepath'=>$tplJumpPage
        ];
        $this->miniprogram=$miniProgram;
        $data=[
            'first'=>[
                "value"=>"您有一条来自'".$yrder['name']."'的预约",
                "color"=>"#173177"
            ],
            'keyword1'=>[//预约时间
                'value'=>date("Y/m/d"),
                "color"=>"#173177"
            ],
            'keyword2'=>[//预约项目
                'value'=>$yrder->xiangmu,
                "color"=>"#173177"
            ],
            'remark'=>[//项目
                'value'=>"请尽快去处理哦",
                "color"=>"#173177"
            ],
        ];
        $this->data=$data;
    }

    private function getUserOpenID($yorder){
        $user=UserModel::findOne($yorder['seller']);
        $openID=$user['openidf'];
        return $openID;
    }

}