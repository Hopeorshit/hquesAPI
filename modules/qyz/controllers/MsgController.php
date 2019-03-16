<?php

namespace api\modules\qyz\controllers;

use api\modules\qyz\service\Msg;
use api\modules\qyz\service\Msg as MsgService;
use yii\rest\ActiveController;


class MsgController extends ActiveController
{
    public $modelClass = '';

    public function actionResponse()
    {
        $cache = \Yii::$app->cache;
        $cache->set('001', 'haha');
        echo 'meme';
//        if (isset($_GET['echostr'])) {//微信服务器首次验证
//            $cache = \Yii::$app->cache;
//            $cache->set('000', 'haha');
//            $msg = new MsgService();
//            echo $msg->checkSignature(); //TODO 这个时候用的是echo
//            die();
//        } else {
//            $msg = new MsgService();
//            $result = $msg->responseMsg();
//            return $result;
//        }
    }

    public function actionMenu()
    {
        $msg = new MsgService();
        $result = $msg->setMenu();
        return $result;
    }


}
