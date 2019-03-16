<?php

namespace api\modules\qyz\controllers;


use api\models\Album as AlbumModel;
use api\modules\qyz\service\Home;
use api\modules\qyz\service\UserCheck;
use yii;
use yii\web\Controller;
use api\modules\qyz\service\User ;


class VideoController extends Controller
{

    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染首页页面
    public function actionIndex(){

    }
}
