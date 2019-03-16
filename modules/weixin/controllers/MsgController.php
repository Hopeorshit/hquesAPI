<?php

namespace api\modules\weixin\controllers;

use api\modules\weixin\service\Msg;
use api\modules\weixin\service\Msg as MsgService;
use yii\rest\ActiveController;


class MsgController extends ActiveController
{
    public $modelClass='';

    public function actionResponse()
    {
//      if(isset($_GET['echostr'])){//微信服务器首次验证
//        $msg=new MsgService();
//        echo $msg->checkSignature(); //TODO 这个时候用的是echo
//        die();
//      }
//      else{
          $msg=new MsgService();
          $result=$msg->responseMsg();
          return $result;
//      }
    }

    public function actionMenu(){
        $msg=new MsgService();
        $result=$msg->setMenu();
        return $result;
    }


}
