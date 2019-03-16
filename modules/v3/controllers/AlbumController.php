<?php

namespace api\modules\v3\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\modules\v3\service\UserToken as UserTokenService;
use yii\base\Exception;

class AlbumController extends BaseActiveController
{
    public $modelClass='api\models\Album';

    public function actionDetail($albumID,$pageSize,$page)
    {
        $query = ImageModel::find()->where(['album_id' => $albumID,'status'=>1]);
        $countQuery = clone $query;//获取到链式结构
        $count = $countQuery->count();//查询到总共有多少
        $offset = ($page - 1) * $pageSize;
        $imageModels = $query->offset($offset)->limit($pageSize)->orderBy('id ASC')->all();
        $albumModel=AlbumModel::findOne($albumID);
        return [
            'hasMore' => $offset >= $count ? false : true,
            'images' => $imageModels,
            'albumModel'=>$albumModel
        ];
    }

    public function actionDelete_all(){
       $request=\Yii::$app->request->bodyParams;
       $albumID=$request['albumID'];
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $albumModel=AlbumModel::findOne($albumID);
       if($uid!=$albumModel['user_id']){
         throw new Exception('非法操作','501');
       }
       $albumModel->status=0;
       $albumModel->update();
       return[
         'msg'=>"success",
          'code'=>"200"
       ];
     }

}
