<?php

namespace api\modules\v4\controllers;
use api\models\Album as AlbumModel;
use api\models\Follow as FollowModel;
use api\models\User;
use api\modules\v4\service\UserToken as UserTokenService;
use yii;
use api\modules\v4\service\Follow as FollowService;

class FollowController extends BaseActiveController
{
    public $modelClass='api\models\Follow';

   //新增关注,关注改变等
   public function actionChange(){
       $request=Yii::$app->request->bodyParams;
       $authorID=$request['authorID'];
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $followModel=FollowModel::find()->where(['follow'=>$uid,"followed"=>$authorID])->one();
       $followService=new FollowService();
       if(!$followModel){
           $followService->add($uid,$authorID);
       }else {
           $followService->changeStatus($followModel,$authorID);
       }
       return [
           'msg'=>"success",
           'cdoe'=>"201"
       ];
   }

   //返回发现页关注列表
   public function actionList_limit(){
       $uid=UserTokenService::getCurrentTokenVar('uid');
       $followModels=FollowModel::find()->where(['follow'=>$uid,"status"=>1])->orderBy('created DESC')->select('followed')->with('followed')->asArray()->limit(7)->all();
       if(!$followModels){
        $userModel=User::findOne($uid);
        array_push($followModels,['followed'=>$userModel]);
       }
       return[
         'followModel'=>$followModels
       ];
   }

   //返回我的关注
   public function  actionList($pageSize,$page)
    {
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $query=FollowModel::find()->where(['follow'=>$uid,'status'=>1])->orderBy('created DESC')->select('followed')->with('followed')->asArray();
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $followModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'followModels' => $followModels
        ];
    }

    //返回作者粉丝
    public function  actionFans($pageSize,$page,$authorID)
    {
        $query=FollowModel::find()->where(['followed'=>$authorID,'status'=>1])->orderBy('created DESC')->select('follow')->with('follow')->asArray();
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $followModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'followModels' => $followModels
        ];
    }

    //测试查询关联
    public function  actionTfans()
    {
        $follow=FollowModel::find()->where(['followed'=>27])->with('tfollow')->asArray()->one();//使用with
        //  $follow=FollowModel::find()->where(['followed'=>27])->one()
//        $result=$follow->hasOne(User::className(),['id'=>'follow'])->asArray()->one();//不使用用with
//        $result=$follow->getTfollow();//不适用with
//        return $result;
        return $follow;
    }

}
