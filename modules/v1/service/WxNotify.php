<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/19
 * Time: 15:31
 */
namespace api\modules\v1\service;
use api\models\Order as OrderModel;
use api\modules\v1\service\Order as OrderService;
use yii;
require_once Yii::getAlias("@common/lib/WxPay/WxPay.Api.php");
class WxNotify extends \WxPayNotify
{
   //用于接收微信的支付回调
  public function NotifyProcess($data, &$msg)
  {
//      <xml>
//  <appid><![CDATA[wx2421b1c4370ec43b]]></appid>
//  <attach><![CDATA[支付测试]]></attach>
//  <bank_type><![CDATA[CFT]]></bank_type>
//  <fee_type><![CDATA[CNY]]></fee_type>
//  <is_subscribe><![CDATA[Y]]></is_subscribe>
//  <mch_id><![CDATA[10000100]]></mch_id>
//  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>
//  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>
//  <out_trade_no><![CDATA[1409811653]]></out_trade_no>
//  <result_code><![CDATA[SUCCESS]]></result_code>
//  <return_code><![CDATA[SUCCESS]]></return_code>
//  <sign><![CDATA[B552ED6B279343CB493C5DD0D78AB241]]></sign>
//  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>
//  <time_end><![CDATA[20140903131540]]></time_end>
//  <total_fee>1</total_fee>
//  <trade_type><![CDATA[JSAPI]]></trade_type>
//  <transaction_id><![CDATA[1004400740201409030005092168]]></transaction_id>
//</xml>
      if ($data['result_code'] == 'SUCCESS') {
              $orderNo = $data['out_trade_no'];
              $orderModel = OrderModel::find()->where(['order_no' => $orderNo])->one();
              if($orderModel->status==0) {
//                  $order = new OrderService();
//                  $order->sendAwardMsg($orderModel);
                  $orderModel->status = 1;
                  $orderModel->update();
              }
              return true;
      } else {
          return true; //true 或者 false 决定微信是否需要继续回调
      }
  }

}