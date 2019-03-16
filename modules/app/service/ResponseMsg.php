<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/28
 * Time: 16:14
 */
namespace api\modules\app\service;


class ResponseMsg
{
   public function success($data){
       $result=[
         "code"=>200,
         "data"=>$data,
         "msg"=>"success"
       ];
       return $result;
   }

}