<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */
namespace api\modules\v1\service;
use yii;
use api\modules\CommonFunc;

class Code
{
  private $sendUrl="https://api.weixin.qq.com/wxa/getwxacode?access_token=%s";
  private  $uid;
  private  $page;
  private  $m1;
  private  $m2;
  private  $m3;
  private  $m4;
  private  $m5;
  private  $m6;
  private  $isOn;

 function __construct($uid,$page,$m1,$m2,$m3,$m4,$m5,$m6,$isOn)
 {
     $this->uid=$uid;
     $this->page=$page;
     $this->m1=$m1;
     $this->m2=$m2;
     $this->m3=$m3;
     $this->m4=$m4;
     $this->m5=$m5;
     $this->m6=$m6;
     $this->isOn=$isOn;

     $accessToken=new AccessToken();
     $token=$accessToken->get();
     $this->sendUrl=sprintf($this->sendUrl,$token);
 }

 public function getCode($userModel){
     $data=[
       'path'=>$this->page.'?uid='.$this->uid.'&m1='.$this->m1.'&m2='.$this->m2.'&m3='.$this->m3.'&m4='.$this->m4.'&m5='.$this->m5.'&m6='.$this->m6.'&isOn='.$this->isOn,
     ];
     $result=CommonFunc::curl_post($this->sendUrl,$data);
     file_put_contents("image/{$userModel->openid}/code.jpg", $result);
     $domain=YII::$app->params['domain'];
     $code=$domain."image/{$userModel->openid}/code.jpg";
     $userModel->code=$code;
     $userModel->update();
     return [
      'code'=>$code
     ];
//     $result=json_decode($result,true);

//     if($result['errcode']==0){
//         return true;
//     }else{
//      throw new Exception('模板消息发送失败. '.$result['errmsg']);
//     }
 }
}