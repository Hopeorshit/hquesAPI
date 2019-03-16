<?php

namespace api\modules\v1\controllers;

use api\models\Goods as GoodsModel;
use api\models\Message as MessageModel;
use api\models\User as UserModel;
use api\modules\v1\service\UserToken as UserTokenService;

class MessageController extends BaseActiveController
{
    public $modelClass = 'api\models\message';

    /**
     * @description 新增留言
     * @method POST
     * @params $goods_id 商品ID
     * @params $content 留言内容
     * @params $content 被回复留言内容的ID，没有的话默认是0
     * @return array 成功;
     */
    public function actionNew()
    {
        $request = \Yii::$app->request->bodyParams;
        $goods_id = $request['goods_id'];
        $content = $request['content'];
        $msg_id = $request['msg_id'];
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $userModel = UserModel::findOne($uid);
        $messageModel = new MessageModel();
        $messageModel->uid = $uid;
        $messageModel->goods_id = $goods_id;
        $messageModel->content = $content;
        $messageModel->msg_id = $msg_id;
        $messageModel->avatarUrl = $userModel['avatarUrl'];
        $messageModel->nickName = $userModel['nickName'];
        $messageModel->save();
        $messageModel = MessageModel::find()->where(['id' => $messageModel['id']])->asArray()->one();
        if ($msg_id != 0) {
            $re_msg = MessageModel::find()->where(['id' => $msg_id])->asArray()->one();
            $messageModel['re_msg'] = $re_msg;
        }
        return self::success($messageModel);
    }


}
