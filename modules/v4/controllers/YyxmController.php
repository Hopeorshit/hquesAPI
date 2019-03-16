<?php

namespace api\modules\v4\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\modules\v4\service\User as UserService;
use api\modules\v4\service\UserToken as UserTokenService;
use yii\base\Exception;
use api\models\Zan as ZanModel;
use api\models\User as UserModel;
use api\models\Yyxm as YyxmModel;

class YyxmController extends BaseActiveController
{
    public $modelClass = 'api\models\Yyxm';

    public function actionAdd(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $request=\Yii::$app->request->bodyParams;
        $yyxmModel=new YyxmModel();
        $yyxmModel->xiangmu=$request['xiangmu'];
        $yyxmModel->money=$request['money'];
        $yyxmModel->user_id=$uid;
        $yyxmModel->save();
        return [
          'yyxmModel'=>$yyxmModel
        ];
    }
    public function actionEdit(){
//      $uid=UserTokenService::getCurrentTokenVar('uid'); TODO 非法用户检测
        $request=\Yii::$app->request->bodyParams;
        $yyxmModel=YyxmModel::findOne($request['id']);
        $yyxmModel->xiangmu=$request['xiangmu'];
        $yyxmModel->money=$request['money'];
        $yyxmModel->update();
        return [
            'yyxmModel'=>$yyxmModel
        ];
    }
    public function actionList(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $yyxmModels=YyxmModel::find()->where(['user_id'=>$uid,'status'=>1])->select(['xiangmu','money','id'])->all();
        return[
          'yyxmModels'=>$yyxmModels
        ];
    }
    public function actionDeletexm(){
        $request=\Yii::$app->request->bodyParams;
        $yyxmModel=YyxmModel::findOne($request['id']);
        $yyxmModel->status=0;
        $yyxmModel->update();
        return [
            'yyxmModel'=>$yyxmModel
        ];
    }
}