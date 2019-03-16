<?php

namespace api\modules\v2\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\modules\v2\service\User as UserService;
use api\modules\v2\service\UserToken as UserTokenService;
use yii\base\Exception;
use api\models\Zan as ZanModel;
use api\models\User as UserModel;

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
        $zanModels=ZanModel::find()->where(['album_id'=>$albumID])->orderBy('created DESC')->select('avatarUrl')->limit(7)->all();
        if($zanModel){
            $zanStatus=$zanModel['status'];
        }
        return [
            'hasMore' => $offset >= $count ? false : true,
            'images' => $imageModels,
            'albumModel'=>$albumModel,
            'zanStatus'=>$zanStatus,
            'zanModels'=>$zanModels,
        ];
    }

    public function actionAllzan($albumID){
        $zanModels=ZanModel::find()->where(['album_id'=>$albumID])->select('avatarUrl')->all();
        return [
          "zanModels"=> $zanModels
        ];
    }

    public function actionZan_status($albumID){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $zanModel=ZanModel::find()->where(['user_id'=>$uid,'album_id'=>$albumID])->one();
        if($zanModel){
           $zanStatus=1;//点赞不能取消
        }else{
            $zanStatus=0;
        }
        return[
            'zanStatus'=>$zanStatus
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

    public function actionZan(){
        //TODO 登陆用户才能点赞哦,目前是游客能够点赞
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $request=\Yii::$app->request->bodyParams;
        $albumID=$request['albumID'];//TODO 要用这种方法才能获取到albumID $_request不行 前端是json 而不是xxx-form
        $zanModel=ZanModel::find()->where(['user_id'=>$uid,'album_id'=>$albumID])->one();
        $userModel=UserModel::findOne($uid);
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
            $zan->avatarUrl=$userModel['avatarUrl'];
            $zan->save();
            //TODO albumModel 的点赞个数加减
            $albumModel=AlbumModel::findOne($albumID);
            $albumModel->zan=$albumModel['zan']+1;
            $albumModel->update();
            $authorModel=UserModel::findOne($albumModel->user_id);
            $authorModel->redu=$authorModel['redu']+1;
            $authorModel->update();
            return[
              'msg'=>"success",
              'code'=>201,
              'avatarUrl'=>$userModel['avatarUrl']
            ];
        }
        return [
            'msg' => "success",
            'code' => 200,
        ];
    }

    //此接口是为了填充之前的 赞Model 没有图片
    public function actionZanfull(){
        $zanModels=ZanModel::find()->all();
        foreach ($zanModels as $item){
            if(!$item['avatarUrl']){
                $userModel=UserModel::find()->where(['id'=>$item['user_id']])->one();
                $item->avatarUrl=$userModel['avatarUrl'];
                $item->update();
            }
        }
        return "success";
    }

    //此接口是为累加每个Album被点赞的所有个数
    public function actionCountzan(){
       $albumModels=AlbumModel::find()->all();
       foreach ($albumModels as $item){
        $count=ZanModel::find()->where(['album_id'=>$item['id']])->count();
        $item->zan=$count;
        $item->update();
       };
       return "success";
    }

}
