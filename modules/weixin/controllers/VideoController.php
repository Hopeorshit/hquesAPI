<?php

namespace api\modules\weixin\controllers;


use api\models\Album as AlbumModel;
use api\modules\weixin\service\Home;
use api\modules\weixin\service\UserCheck;
use yii;
use yii\web\Controller;
use api\modules\weixin\service\User ;


class VideoController extends Controller
{

    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染首页页面
    public function actionIndex(){

    }
}
