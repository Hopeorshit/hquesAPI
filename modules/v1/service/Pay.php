<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/18
 * Time: 14:57
 */

namespace api\modules\v1\service;

use api\models\Order;
use api\modules\v1\service\UserToken as UserTokenService;
use yii\base\ErrorException;
use yii;

class Pay
{
    private $orderID;
    private $order;
    private $orderNo;

   function __construct($orderID){
       $this->orderID=$orderID;
       $order=Order::findOne($orderID);
       $this->order=$order;
       $this->orderNo=$order['order_no'];
   }

  public function pay($uid){
      $order=$this->order;
      //TODO 向前端返回JSON 格式
      if($order['status']==1){
          throw new ErrorException('已经支付过');
      }
      if($order['buyer']!=$uid){
          throw new ErrorException('操作非法,不是自己的订单');
       }
      return $this->makeWxPreOrder($order['amount']);
  }

  private function  makeWxPreOrder($price){
      $openid=UserTokenService::getCurrentTokenVar('openid'); //获取到openID
      require_once Yii::getAlias("@common/lib/WxPay/WxPay.Api.php");//没有命名空间无法,无法Use 采用这种方法注入
      $wxOrderData=new \WxPayUnifiedOrder();
      $wxOrderData->SetOut_trade_no($this->orderNo);//
      $wxOrderData->SetTrade_type('JSAPI');//
      $wxOrderData->SetTotal_fee($price*100);
      $wxOrderData->SetBody('微作');
      $wxOrderData->SetOpenid($openid);
      $wxOrderData->SetNotify_url(Yii::$app->params['pay_back_url']);
      return $this->getPaySignature($wxOrderData);
  }

  private function getPaySignature($wxOrderData){//在这个方法内部调用预订单接口
      require_once Yii::getAlias("@common/lib/WxPay/WxPay.Api.php");
      $wxOrder=\WxPayApi::unifiedOrder($wxOrderData);

      if ($wxOrder['return_code']!='SUCCESS' ||$wxOrder['result_code']!='SUCCESS'){
        throw new ErrorException('获取预支付订单失败');
      }
      //将prepay_id写入数据库中
      $order=$this->order;
      $order->prepay_id=$wxOrder['prepay_id'];
      $order->update();

      $signature=$this->sign($wxOrder);
      return $signature;
  }

  private function sign($wxOrder)
  {
      require_once Yii::getAlias("@common/lib/WxPay/WxPay.Api.php");
      $jsApiPayData = new \WxPayJsApiPay();

      $wxLogin = Yii::$app->params['wxLogin'];
      $wxAppID = $wxLogin['app_id'];//从配置文件中读取

      $jsApiPayData->SetAppid($wxAppID);
      $jsApiPayData->SetTimeStamp((string)time());
      $rand = md5(time() . mt_rand(0, 1000));
      $jsApiPayData->SetNonceStr($rand);
      $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
      $jsApiPayData->SetSignType('md5');

      $sign = $jsApiPayData->MakeSign();

      $rowValues =$jsApiPayData->GetValues();//获取原生的数组形式
      $rowValues['paySign']=$sign;

      unset($rowValues['appId']);//客户端不需要

      return $rowValues;
    }

}