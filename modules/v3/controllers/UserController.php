<?php
namespace api\modules\v3\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\models\User as UserModel;
use api\models\User;
use api\modules\v3\service\UserToken as UserTokenService;
use api\modules\v3\service\Yorder;
use Yii;
use yii\base\Exception;
use  api\models\Yorder as YorderModel;

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
        $user=UserModel::findOne($uid);
        return[
          'user'=>  $user
        ];
    }

    public function actionEncrypt(){
        $session_key=UserTokenService::getCurrentTokenVar('session_key');
        $wxLogin=Yii::$app->params['wxLogin+'];
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

    //作者 和 其他人看到的主页 样式是不同的
    public function actionZhuye($authorID,$pageSize,$page){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $bgCanChange=$uid==$authorID?true:false;//如果不是作者，那么背景将不可改变
        $userModel=UserModel::find()->where(['id'=>$authorID])->one();
        $query=AlbumModel::find()->where(['user_id'=>$authorID,'status'=>1])->orderBy('updated DESC');
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $albumModels = $query->offset($offset)->limit($pageSize)->all();
        $yorderCount=YorderModel::find()->where(['seller'=>$authorID])->count();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'zpCount'=>$count,
            'bgCanChange'=>$bgCanChange,
            'yorderCount'=>$yorderCount,
            'userModel'=>$userModel,
            'albumModels'=>$albumModels
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
            'msg'=>"success"
        ];
    }

    public function actionYuyue_switch($isOn){
        $uid=UserTokenService::getCurrentTokenVar('uid');
        $userModel=UserModel::findOne($uid);
        if($isOn=='true'){
          $userModel->ison=true;
        }
        else{
          $userModel->ison=false;
        }
        $userModel->update();
        return [
            'msg'=>"更改成功",
            'code'=>201
        ];
    }
}
