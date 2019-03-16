<?php
namespace api\modules\weixin\controllers;
use yii\web\Controller;

class CodeController extends Controller
{
    public $enableCsrfValidation=false;//这个要加上否则访问不了

    public function actionShow(){
        $this->layout=false;
        return $this->render('show');
    }

}
