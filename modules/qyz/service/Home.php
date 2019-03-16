<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/29
 * Time: 0:23
 */

namespace api\modules\qyz\service;

use api\modules\CommonFunc;
use  yii;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use yii\base\Exception;
class Home
{

  const JSAPI_TICKED='jsticket';
  const EXPIRE_TIME=7200;


  public function wxConfigData()
  {
      $wxConfig['timestamp'] = time();
      $wxConfig['noncestr'] = CommonFunc::getRandChar(16);
      $accessToken = new AccessToken();
      $token = $accessToken->get();
      $cache = Yii::$app->cache;
      $jsTicket = $cache->get(self::JSAPI_TICKED);
      if ($jsTicket['ticket']){
          $wxConfig['jsapi_ticket'] = $jsTicket['ticket'];
      }
      else{
        $sendUrl=sprintf(Yii::$app->params['qyz']['jsapi'],$token);
       $resultJson=CommonFunc::curl_get($sendUrl);
       $resultArray=json_decode($resultJson,true);
       $cacheJsAPI = Yii::$app->cache;
       $cacheJsAPI->set(self::JSAPI_TICKED,$resultArray,self::EXPIRE_TIME);
       $wxConfig['jsapi_ticket'] = $resultArray['ticket'];
      }
      $finalArr=$wxConfig;
      $wxConfig['url']='https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];;//获取当前网页的URL
      $tempStr='jsapi_ticket='.$wxConfig['jsapi_ticket'].'&noncestr='.$wxConfig['noncestr'].'&timestamp='.$wxConfig['timestamp'].'&url='.$wxConfig['url'];
      $signature=sha1($tempStr);
      $finalArr['signature']=$signature;
      return $finalArr;
  }

  public function  createAlbum(){
      $uid=UserCheck::getUid();//从cooike中读取uid;
      $albumName=$_REQUEST['albumName'];
      $title=$_REQUEST['title'];
      $description=$_REQUEST['description'];
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
      $albumModel->name = $albumName;
      $albumModel->save();
      return $albumModel;
  }

  public function albumUpload(){
      $album_id=$_REQUEST['album_id'];
      $mediaIDs=$_REQUEST['mediaIDs'];
      $cache=Yii::$app->cache;
      $cache->set("medias",$mediaIDs,7200);
      $albumModel=AlbumModel::findOne($album_id);
      $uid=$albumModel->user_id;
      $albumName=$albumModel['name'];
//      $cache=Yii::$app->cache;
//      $cache->SET('ZH',"HA",7200);
      if(!is_dir("image/{$uid}/{$albumName}")) {
          throw new Exception('作品册目录未创建成功');
      }
      $domain=YII::$app->params['domain'];
      $imageUrlLocal="image/{$uid}/{$albumName}/";
      $accessToken=new AccessToken();
      $token=$accessToken->get();
      foreach ($mediaIDs as $mediaID){
          $imageModel=new ImageModel();
          $imageModel->album_id=$album_id;
          $sendUrl=sprintf(Yii::$app->params['qyz']['media'],$token,$mediaID);
          $result=CommonFunc::curl_get($sendUrl);
          $localName=CommonFunc::getRandChar('32');
          $newImageUrlLocal=$imageUrlLocal."{$localName}.jpg";
          file_put_contents($newImageUrlLocal,$result);//
          $imageModel->url = $domain.$newImageUrlLocal;
          $imageModel->save();
      }
      //TODO 每次上传完图片，重新更新一下头图,选择有效的，最早在数据库有记录的
      $imageModelHead=ImageModel::find()->where(['album_id' => $album_id,'status'=>1])->orderBy('id ASC')->one();
      $imageModelHead->is_head_img=1;
      $imageModelHead->update();
      $albumModel->head_url=$imageModelHead['url'];
      $albumModel->update();
  }


  public function userInfoEdit(){
      $userModel=UserCheck::grandCooike();
      $mediaID=$_REQUEST['mediaID'];
      $title=$_REQUEST['title'];
      $nickName=$_REQUEST['nickName'];
      $accessToken=new AccessToken();
      $token=$accessToken->get();
      $sendUrl=sprintf(Yii::$app->params['qyz']['media'],$token,$mediaID);
      $result=CommonFunc::curl_get($sendUrl);
      $imageUrlLocal="image/{$userModel['id']}/";
      $newAvatar=time();
      $imageUrlLocal=$imageUrlLocal.$newAvatar;
      file_put_contents($imageUrlLocal,$result);//
      $domain=YII::$app->params['domain'];
      $userModel->avatarUrl=$domain.$imageUrlLocal;
      $userModel->title=$title;
      $userModel->nickName=$nickName;
      $userModel->update();
  }

  public function editNameAndTitle(){
      $userModel=UserCheck::grandCooike();
      $title=$_REQUEST['title'];
      $nickName=$_REQUEST['nickName'];
      $userModel->title=$title;
      $userModel->nickName=$nickName;
      $userModel->update();
  }
}