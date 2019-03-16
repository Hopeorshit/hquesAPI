<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */

namespace api\modules\weixin\service;
use api\modules\CommonFunc;


class WxMessage
{
  private $sendUrl="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s";

  protected $tplID;
  protected $miniprogram;
  protected  $data;

 function __construct()
 {
     $accessToken=new AccessToken();
     $token=$accessToken->get();
     $this->sendUrl=sprintf($this->sendUrl,$token);
 }

 protected function sendMessage($openID){
     $data=[
         'touser'=>$openID,
         'template_id'=>$this->tplID,
         'miniprogram'=>$this->miniprogram,
         'data'=>$this->data,
     ];
     $result=CommonFunc::curl_post($this->sendUrl,$data);
     $result=json_decode($result,true);
     if($result['errcode']==0){
         return true;
     }else{
         return [
           'msg'=>  $result['errmsg']
         ];
     }
 }

}