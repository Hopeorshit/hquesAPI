<?php

namespace api\modules\v1\controllers;

use api\models\Goods as GoodsModel;
use api\models\Want as WantModel;
use api\modules\v1\service\UserToken as UserTokenService;

class WantController extends BaseActiveController
{
    public $modelClass = 'api\models\want';

    /**
     * @description 新建收藏关系，若存在则改变原有收藏关系的状态
     * @method POST
     * @params $goods_id 商品ID
     * @return Array wantModel;
     */
    public function actionHandle()
    {
        $request = \Yii::$app->request->bodyParams;
        $goods_id = $request['goods_id'];
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $wangModel = WantModel::find()->where(['uid' => $uid, 'goods_id' => $goods_id])->one();
        $goodsModel=GoodsModel::findOne($goods_id);
        if (!$wangModel) {
            $wangModel = new WantModel();
            $wangModel->uid = $uid;
            $wangModel->goods_id = $goods_id;
            $goodsModel->like=$goodsModel['like']+1;
            $wangModel->save();
            $goodsModel->update();
        } else {
            $wangModel->status = $wangModel['status'] == 1 ? 0 : 1;
            $goodsModel->like= $wangModel['status']==1?$goodsModel['like']-1:$goodsModel['like']+1;
            $wangModel->update();
            $goodsModel->update();
        }
        return self::success($wangModel);
    }


}
