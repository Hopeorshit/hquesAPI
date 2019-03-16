<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */
namespace api\modules\v3\service;
use yii;
use api\modules\CommonFunc;

class Code
{
//  private $sendUrl="https://api.weixin.qq.com/wxa/getwxacode?access_token=%s";
  private  $sendUrl="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=%s";
  private  $uid;
  private  $page;

 function __construct($uid,$page)
 {
     $this->uid=$uid;
     $this->page=$page;

     $accessToken=new AccessToken();
     $token=$accessToken->get();
     $this->sendUrl=sprintf($this->sendUrl,$token);
 }

 public function getCode($userModel){
     $data=[
       'path'=>$this->page.'?uid='.$this->uid,
     ];
     $result=CommonFunc::curl_post($this->sendUrl,$data);
     file_put_contents("image/{$userModel->id}/code.jpg", $result);
     $domain=YII::$app->params['domain'];
     $code=$domain."image/{$userModel->id}/code.jpg";
     $userModel->code=$code;
     $userModel->update();
     return [
      'user'=>$userModel
     ];
//     $result=json_decode($result,true);

//     if($result['errcode']==0){
//         return true;
//     }else{
//      throw new Exception('模板消息发送失败. '.$result['errmsg']);
//     }
 }
}