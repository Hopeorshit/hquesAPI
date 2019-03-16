<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */
namespace api\modules\v4\service;
use api\models\Follow as FollowModel;
use api\models\User;
class Follow
{
    public function add($uid,$authorID){
        $followModel=new FollowModel();
        $followModel->follow=$uid;
        $followModel->followed=$authorID;
        $followModel->save();
        $userModel=User::findOne($uid);
        $userModel->fans=$userModel['fans']+1;
        $userModel->update();
    }

    public function changeStatus($followModel,$uid){
        $status=$followModel->status==1?0:1;
        $followModel->status = $status;
        $followModel->update();
        $userModel = User::findOne($uid);
        $userModel->fans = $status?$userModel['fans'] + 1:$userModel['fans']-1;
        $userModel->update();
    }

}