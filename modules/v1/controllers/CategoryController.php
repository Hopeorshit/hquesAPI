<?php

namespace api\modules\v1\controllers;

use api\models\Category;
use api\models\Goods as GoodsModel;

class CategoryController extends BaseActiveController
{
    public $modelClass = 'api\models\Category';

    /**
     * @method GET 获取目录
     * @return array
     */
    public function actionAll()
    {
        $categroy = Category::find()->all();
        return self::success($categroy);
    }

    /**
     * @method GET 获取单个目录下面的商品
     */
    public function actionId($categoryID, $page)
    {
        $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        if ($categoryID == 0) {
            $goods = GoodsModel::find()->where(['status' => 1])->with('user', 'images')->
            orderBy('created DESC')->asArray()->offset($offset)->limit($pageSize)->all();
        } else {
            $goods = GoodsModel::find()->where(['category_id' => $categoryID, 'status' => 1])->with('user', 'images')
                ->orderBy('created DESC')->asArray()->offset($offset)->limit($pageSize)->all();
        }
        return self::success(["list"=>$goods]);
    }


}
