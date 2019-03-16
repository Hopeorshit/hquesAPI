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
use yii\base\Exception;



class WxMessage
{
  private $sendUrl="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s";

  private  $touser;
  private  $color='black';

  protected $tplID;
  protected $page;
  protected  $formID;
  protected  $data;
  protected $emphasisKeyWord;

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
         'page'=>$this->page,
        'form_id'=>$this->formID,
        'data'=>$this->data,
         'emphasis_keyword'=>$this->emphasisKeyWord
     ];
     $result=CommonFunc::curl_post($this->sendUrl,$data);
     $result=json_decode($result,true);
     if($result['errcode']==0){
         return true;
     }else{
         return [
           'msg'=>  $result['errmsg']
         ];
//      throw new Exception('模板消息发送失败. '.$result['errmsg']);
     }
 }

}