<?php

namespace api\modules\v2\controllers;
use api\models\Album as AlbumModel;
use api\models\Follow as FollowModel;
use api\models\Image as ImageModel;
use api\models\User;
use api\modules\v2\service\User as UserService;
use api\modules\v2\service\UserToken as UserTokenService;
use yii\base\Exception;
use api\models\Zan as ZanModel;
use api\models\User as UserModel;
use yii;

class FollowController extends BaseActiveController
{
    public $modelClass='api\models\Follow';

   //新增关注
    public function actionAdd(){
       $request=Yii::$app->request->bodyParams;
       $authorID=$request['authorID'];
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $followModel=new FollowModel();
       $followModel->follow=$uid;
       $followModel->followed=$authorID;
       $followModel->save();
       $userModel=User::findOne($uid);
       $userModel->fans=$userModel['fans']+1;
       $userModel->update();
       return [
         'msg'=>"success",
         'cdoe'=>"201"
       ];
   }

   //取消关注
   public function actionCancel($authorID){
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $followModel=FollowModel::find()->where(['follow'=>$uid,"followed"=>$authorID])->one();
       $followModel->status=0;
       $followModel->update();
       $userModel=User::findOne($uid);
       $userModel->fans=$userModel['fans']-1;
       $userModel->update();
       return [
           'msg'=>"success",
           'cdoe'=>"201"
       ];
   }

   //返回发现页关注列表
   public function actionList_limit(){
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $followModels=FollowModel::find()->where(['follow'=>$uid,"status"=>1])->orderBy('created DESC')->select('follow')->with('follower')->limit(7)->all();
       return[
         'followModel'=>$followModels
       ];
   }


    public function  actionList($pageSize,$page)
    {
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $query=FollowModel::find()->where(['follow'=>$uid,'status'=>1])->orderBy('created DESC');
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $followModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'followModels' => $followModels
        ];
    }


   //返回发现页面作品列表
    public function  actionDiscover($pageSize,$page)
    {
//        $uid=UserTokenService::getCurrentTokenVar('uid');
        $query=AlbumModel::find()->where(['status'=>1])->orderBy('zan DESC');
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $albumModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'albumModels' => $albumModels
        ];
    }

   //


}
