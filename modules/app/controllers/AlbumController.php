<?php

namespace api\modules\app\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\modules\app\service\UserToken as UserTokenService;
use yii\base\Exception;
use api\models\Zan as ZanModel;

class AlbumController extends BaseActiveController
{
    public $modelClass='api\models\Album';

    //TODO 检测当前用户有没有点赞
    public function actionDetail($albumID,$pageSize,$page)
    {
        $query = ImageModel::find()->where(['album_id' => $albumID,'status'=>1]);
        $countQuery = clone $query;//获取到链式结构
        $count = $countQuery->count();//查询到总共有多少
        $offset = ($page - 1) * $pageSize;
        $imageModels = $query->offset($offset)->limit($pageSize)->orderBy('id ASC')->all();
        $albumModel=AlbumModel::findOne($albumID);

        $uid=UserTokenService::getCurrentTokenVar('uid');
        $zanModel=ZanModel::find()->where(['user_id'=>$uid,'album_id'=>$albumID])->one();
        $zanStatus=0;
        if($zanModel){
            $zanStatus=$zanModel['status'];
        }
        return [
            'hasMore' => $offset >= $count ? false : true,
            'images' => $imageModels,
            'albumModel'=>$albumModel,
            'zanStatus'=>$zanStatus
        ];
    }

    public function actionZan_status($albumID){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $zanModel=ZanModel::find()->where(['user_id'=>$uid,'album_id'=>$albumID])->one();
        if($zanModel){
           $zanStatus=$zanModel['status'];
        }else{
            $zanStatus=0;
        }
        return[
            'zanStatus'=>$zanStatus
        ];
    }

    public function actionZan(){
        //TODO 登陆用户才能点赞哦,目前是游客能够点赞
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $request=\Yii::$app->request->bodyParams;
        $albumID=$request['albumID'];//TODO 要用这种方法才能获取到albumID $_request不行 前端是json 而不是xxx-form
        $zanModel=ZanModel::find()->where(['user_id'=>$uid,'album_id'=>$albumID])->one();
        if($zanModel){
            $zanModel->status=!$zanModel->status;
            $albumModel=AlbumModel::findOne($albumID);
            $zanModel->update();
            if ($zanModel['status']==1){
            $albumModel->zan=$albumModel['zan']+1;
            }else{
            $albumModel->zan=$albumModel['zan']-1;
            }
            $albumModel->update();
        }else {
            $zan = new ZanModel();
            $zan->user_id = $uid;
            $zan->album_id =$albumID;
            $zan->save();
            //TODO albumModel 的点赞个数加减
            $albumModel=AlbumModel::findOne($albumID);
            $albumModel->zan=$albumModel['zan']+1;
            $albumModel->update();
        }
        return [
            'msg' => "success",
            'code' => 200,
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
