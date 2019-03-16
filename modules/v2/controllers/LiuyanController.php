<?php

namespace api\modules\v2\controllers;

use api\models\Liuyan;
use api\modules\v2\service\UserToken as UserTokenService;
use api\models\Liuyan as LiuyanModel;
use api\models\User;

class LiuyanController extends BaseActiveController
{
    public $modelClass='api\models\Liuyan';

   public function actionDetail($albumID)
   {
       $liuyanModels=Liuyan::find()->where(['album_id'=>$albumID])->all();
       return [
         'liuyan'=>$liuyanModels
       ];
   }

   public function actionLiuyan()
   {
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $userModel=User::find()->where(['uid'=>$uid])->all();
       $request=\Yii::$app->request->bodyParams;
       $albumID=$request['albumID'];//TODO 要用这种方法才能获取到albumID
       $liuyan=new LiuyanModel();
       $liuyan->user_id = $uid;
       $liuyan->album_id =$albumID;
       $liuyan->nickName=$userModel['nickName'];
       $liuyan->content=$userModel['content'];
       $liuyan->save();
       return [
           'msg'=>"success",
           "code"=>200
       ];
   }
}
