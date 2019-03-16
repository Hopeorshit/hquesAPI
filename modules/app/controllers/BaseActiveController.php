<?php

/*
 * 对原来的ActiveController进行进行再次继承，提取公共函数
 * @Byron
 */
namespace api\modules\app\controllers;
use yii\rest\ActiveController;

class BaseActiveController extends ActiveController
{

    public function behaviors()//重写父类方法，返回Json
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats'] = ['application/json' => \yii\web\Response::FORMAT_JSON];
        return $behaviors;
    }

}
