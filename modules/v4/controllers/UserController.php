<?php

namespace api\modules\v4\controllers;
use api\models\Album as AlbumModel;
use api\models\Album;
use api\models\Follow;
use api\models\Image as ImageModel;
use api\models\User as UserModel;
use api\models\User;
use api\modules\v4\service\UserToken as UserTokenService;
use Yii;
use yii\base\Exception;
use  api\models\Yorder as YorderModel;
use api\modules\v4\service\User as UserService;

require_once Yii::getAlias("@common/lib/encrypt/wxBizDataCrypt.php");
class UserController extends BaseActiveController
{
    public $modelClass='api\models\User';

    public function actionInfo_save()
    {
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        if($userModel['save_status']==1){
            return[
              "msg"=>"已经存储过用户信息",
              "user"=>$userModel,
              "code"=>201
            ];
        }
        $request=Yii::$app->request->bodyParams;//获取到参数
        $userInfo=$request['userInfo'];
        $userModel->save_status=1;
        $userModel->gender = $userInfo['gender'];
        $userModel->nickName =$userInfo['nickName'];
        $userModel->city =$userInfo['city'];
        $userModel->province=$userInfo['province'];
        $userModel->country=$userInfo['country'];
        $userAvatar= $userInfo['avatarUrl'];
        if (!is_dir("image/{$uid}")) {
            mkdir("image/{$uid}");//根据用户的OpenID命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}",0777);//Linux 系统要这样写
        }
        $time=time();
        file_put_contents("image/{$uid}/avatar{$time}.jpg", file_get_contents($userAvatar));
        $domain=YII::$app->params['domain'];
        $userModel->avatarUrl=$domain."image/{$uid}/avatar{$time}.jpg";
        $userModel->update();
        return[
         'msg'=>'保存成功',
         'code'=>200
       ];
    }

    public function actionInfo_edit(){
        if( $_FILES["avatar"]){//如果有上传的文件
            $file =$_FILES["avatar"];
            $uid=$_REQUEST['uid'];
            if(!is_dir("image/{$uid}")) {
                mkdir("image/{$uid}");//根据用户的OpenID命名文件夹，username可能文件夹命名不支持
                chmod("image/{$uid}",0777);//Linux 系统要这样写
            }
            $userModel=UserModel::findOne($uid);
            $userModel->nickName=$_REQUEST['nickName'];
            $userModel->title=$_REQUEST['title'];
            $domain=YII::$app->params['domain'];
            $imageUrlLocal="image/{$uid}/";
            $time=time();
            $fp =$imageUrlLocal."avatar{$time}.jpg";
            if (move_uploaded_file ($file['tmp_name'], $fp )) {//保存文件
                $imageUrlLocal = $domain . $fp;
                $userModel->avatarUrl = $imageUrlLocal;
            }
            $userModel->update();
        }
        return[
          'msg'=>"success",
          'code'=>'201'
        ];
    }

    public function actionInfo_edit_nt(){
        $request=Yii::$app->request->bodyParams;
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        $userModel->nickName=$request['nickName'];
        $userModel->title=$request['title'];
        $userModel->update();
        return[
            'msg'=>"success",
            'code'=>'201'
        ];
    }

    public function actionInfo(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=User::findOne($uid);
        return [
            "uid"=>$uid,
            "userModel"=>$userModel
        ];
    }

    public function actionEncrypt(){
        $session_key=UserTokenService::getCurrentTokenVar('session_key');
        $wxLogin=Yii::$app->params['wxLogin'];
        $wxAppID=$wxLogin['app_id'];//从配置文件中读取
        $request=Yii::$app->request->bodyParams;//获取到参数
        $encryptedData=$request['encryptedData'];
        $iv=$request['iv'];
        $wxBiz=new \WXBizDataCrypt($wxAppID,$session_key);
        $data='';
        $code=$wxBiz->decryptData($encryptedData,$iv,$data);
        if($code==0) {
            $result = json_decode($data);
            $uid = UserTokenService::getCurrentTokenVar('uid');
            $userModel = UserModel::findOne($uid);
            $userModel->number = $result->phoneNumber;//
            $userModel->update();
            return $result;
        }
        else{
            return [
              'code'=>$code,
              'msg'=>"获取手机号失败"
            ];
        }
    }

    //破解用户信息，颁发新的TOKEN
    public function actionEncrypt_user_info(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        //2 用户信息存储过，就直接返回用户，否则去破解信息，并存储
        if($userModel){
            $TokenContent['session_key']=UserTokenService::getCurrentTokenVar('session_key');
            $TokenContent['openid']=$userModel['openid'];
            $TokenContent['unionid']=$userModel['unionid'];
            $TokenContent['uid']=$userModel['id'];
            $newToken=(new UserTokenService())->grantToken($TokenContent);
            return[
                "msg"=>"已经存储过用户信息",
                "userModel"=>$userModel,
                "code"=>201,
                "token"=>$newToken,
                "loginStatus"=>$newToken['loginStatus']
            ];
        }else{
            $userModel=new UserModel();
            $userService=new UserService();
            $userModel=$userService->saveUserInfo($userModel);
            $TokenContent['session_key']=UserTokenService::getCurrentTokenVar('session_key');
            $TokenContent['openid']=$userModel['openid'];
            $TokenContent['unionid']=$userModel['unionid'];
            $TokenContent['uid']=$userModel['id'];
            $newToken=(new UserTokenService())->grantToken($TokenContent);
            return[
                "msg"=>"成功存储过用户信息",
                "userModel"=>$userModel,
                "code"=>201,
                "token"=>$newToken,
                "loginStatus"=>$newToken['loginStatus']
            ];
        }
    }

    public function actionAlbum_create(){
        $albumName=$_REQUEST['albumName'];
        $title=$_REQUEST['title'];
        $description=$_REQUEST['description'];
        $position=$_REQUEST['position'];
        $uid=UserTokenService::getCurrentTokenVar('uid');
        if (!is_dir("image/{$uid}")) {
            mkdir("image/{$uid}");//根据用户的uid命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}",0777);//Linux 系统要这样写
        }
        if(!is_dir("image/{$uid}/{$albumName}")) {
            mkdir("image/{$uid}/{$albumName}");//根据用户的uid命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}/{$albumName}", 0777);//Linux 系统要这样写
        }
        $albumModel = new AlbumModel();
        $albumModel->user_id = $uid;
        $albumModel->description = $description;
        $albumModel->title = $title;
        $albumModel->position = $position;
        $albumModel->name = $albumName;
        $albumModel->save();
        return [
          'album_id'=>$albumModel['id'],
           'uid'=>$albumModel['user_id']
        ];
    }

    public function actionAlbum_upload(){
        $album_id=$_REQUEST['album_id'];
        $uid=$_REQUEST['uid'];
        $index=$_REQUEST['index'];
        $albumModel=AlbumModel::findOne($album_id);
        $albumName=$albumModel['name'];
        if(!is_dir("image/{$uid}/{$albumName}")) {
          throw new Exception('作品册目录未创建成功');
        }
        $imageModel=new ImageModel();
        $imageModel->album_id=$album_id;

        $domain=YII::$app->params['domain'];
        $imageUrlLocal="image/{$uid}/{$albumName}/";
        $file = $_FILES["zp"];
        if($file){//如果有上传的文件
                $fp =$imageUrlLocal.$file['name'];
                if (move_uploaded_file ($file['tmp_name'], $fp )) {//保存文件
                    $imageUrlLocal = $domain . $fp;
                    $imageModel->album_id = $album_id;
                    $imageModel->url = $imageUrlLocal;
                    $imageModel->save();
                    if ($index == 0) {
                        $albumModel->head_url = $imageUrlLocal;
                        $imageModel->is_head_img = 1;
                        $imageModel->update();
                        $albumModel->update();
                    }
                }
        }
        return[
          "imgUrl"=>$imageModel['url']
        ];
    }


    public function actionZhuye_album($authorID,$pageSize,$page){
//          $userModel = UserModel::find()->where(['id' => $authorID])->one();
            $query = AlbumModel::find()->where(['user_id' => $authorID, 'status' => 1])->orderBy('created DESC');
            $countQuery = clone $query;
            $offset = ($page - 1) * $pageSize;
            $count = $countQuery->count();
            $hasMore = $offset >= $count ? false : true;
            $albumModels = $query->offset($offset)->limit($pageSize)->all();
            if ($count==0) {//如果用户的作品为0，或者作者id是1，则显示默认的作品集
                $hasMore = false;
                $albumModels = AlbumModel::find()->where(['id' => 1])->asArray()->all();
            }
            return [
                'albumModels' => $albumModels,
                'hasMore' => $hasMore
            ];
    }
    //主页初次请求
    public function actionZhuye_initial($authorID){
        $authorModel=UserModel::findOne($authorID);
        $count=AlbumModel::find()->where(['user_id' => $authorID, 'status' => 1])->count();
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $followModel=Follow::find()->where(['follow'=>$uid,'followed'=>$authorID])->one();//判断当前用户有没有关注过
        if(!$followModel){
            $followStatus=false;
        }else{
            $followStatus=$followModel['status'];
        }
        return[
            'authorModel'=>$authorModel,
            'zpCount'=>$count,
            'followStatus'=>$followStatus
        ];
    }

    public function actionRedu($authorID){
        $authorModel=UserModel::findOne($authorID);
        return [
            'redu'=>$authorModel['redu']
        ];
    }

    public function  actionAlbum_list($pageSize,$page)
    {
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $query=AlbumModel::find()->where(['user_id'=>$uid,'status'=>1])->orderBy('updated DESC');
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $albumModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'albumModels' => $albumModels
        ];
    }

    //albumID 1 返回Demo作品详情
    public function actionAlbum_detail($albumID){
        $albumDetail=AlbumModel::find()->where(['id'=>$albumID])->with(['images'=>
            function ($query){
                $query->andWhere('status>0');
            }
         ])->asArray()->one();//加上asArray才能用模型关联
        return [
          "albumDetail"=>$albumDetail
        ];
    }

    public function actionAlbum_delete(){
        $request=Yii::$app->request->bodyParams;
        $deleteList=$request['deleteList'];
        $albumID=$request['albumID'];

        $albumModel=AlbumModel::findOne($albumID);
        $albumModel->title=$request['title'];
        $albumModel->description=$request['description'];

        $imageModels=ImageModel::find()->where(['album_id'=>$albumID])->all();
        $headHasChanged=false;
        //判断哪些要删除掉
        foreach ( $deleteList as $itemd){
            foreach ($imageModels as $itemi ){
                if((int)$itemd['id']==$itemi['id']){
                   $itemi->status=0;
                   if($itemi['is_head_img']==1){
                   $itemi->is_head_img=0;
                   //album 头图要发生改变
                   $headHasChanged=true;
                   }
                   $itemi->update();
                }
            }
        }
        if($headHasChanged){
            $imageModel=ImageModel::find()->where(['status'=>1,'album_id'=>$albumID])->one();
            $albumModel->head_url=$imageModel['url'];
            $imageModel->is_head_img=1;
            $imageModel->update();
        }
        $albumModel->update();
        //循环之后找另外一张图片作为头图
        return [
            'msg'=>"success",
            'code'=>"201"
        ];
    }

    public function actionYuyue_status(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        return[
          "ison"=>$userModel['ison']
        ];
    }

    public function actionYuyue_switch(){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        $userModel->ison=!$userModel['ison'];
        $userModel->update();
        return [
            'msg'=>"更改成功",
            'code'=>201
        ];
    }

   //热度统计
    public function actionReducount(){
      $userModels=UserModel::find()->all();
      foreach ($userModels as $item){
          if(!$item['redu']){
             $redu=AlbumModel::find()->where(['user_id'=>$item['id']])->sum('zan');
             $item->redu=$redu;
             $item->update();
          }
      }
      return "success";
    }

    //关注统计
    public function actionFollowcount(){
        $userModels=UserModel::find()->all();
        foreach ($userModels as $item){
           $item->fans=Follow::find()->where(['followed'=>$item['id'],'status'=>1])->count();
           $item->update();
        }
        return "success";
    }

    /*测试*/
//    public function  actionMaikoo() //TODO 没有破解出unionid?
//    {
////        $session_key=UserTokenService::getCurrentTokenVar('session_key');
//        $wxAppID='wx98a3483a36a6f26b';
//        $request=Yii::$app->request->bodyParams;//获取到参数
//        $encryptedData=$request['encrypteddata'];
//        $iv=$request['iv'];
//        $session_key=$request['sessionkey'];
//        $wxBiz=new \WXBizDataCrypt($wxAppID,$session_key);
//        $data='';
//        $code=$wxBiz->decryptData($encryptedData,$iv,$data);
//        $result = json_decode($data,true);
//        return $result;
//    }

}
