<?php
/**
 * Created by Byron
 * User: Administrator
 * Date: 2017/9/26
 * Time: 17:41
 * 用来处理复杂的Token逻辑
 */

namespace api\modules\v1\service;

class Yorder
{
    public function sendYuyueMsg($yorder,$jumpPage)
    {
     $msg=new YuyueMsg();
     return $msg->sendYuyueMsg($yorder,$jumpPage);
    }

    public function sendMsgconfirmed($formId,$yorderModel,$jumpPage=''){
    $msg=new ConfirmMsg();
    Return $msg->sendMsgConfirmed($formId,$yorderModel,$jumpPage);
    }

    public function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn = 'Y'.$yCode[intval(date('Y')) - 2018] . strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        //年，月，日，时间戳，微妙数，随机数
        return $orderSn;
    }
}