<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/29
 * Time: 0:23
 */

namespace api\modules\weixin\service;

use api\models\User;
use  yii;
use api\modules\CommonFunc;

class Msg
{
    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echoStr = $_GET["echostr"];
        $token = Yii::$app->params['weixin']['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $temStr = implode($tmpArr);
        $temStr = sha1($temStr);
        return $signature == $temStr ? $echoStr : 'false';
    }

    public function responseMsg()//接收事件推送做出相应的反应
    {
        $xmldata = file_get_contents("php://input");//接收PHP数据包
        $msgArray = CommonFunc::xml2array($xmldata);
        if ($msgArray['Event'] == 'subscribe') {
            return $this->subscribe($msgArray);
        } else {
            return "success";
        }
    }

    private function subscribe($msgArray)
    {
        //如果用户订阅了就存储他的openid和unionId
        $openidf = $msgArray['FromUserName'];
        $fromUserName = $msgArray['ToUserName'];
        $user = (new User())->getUserByOpenfID($openidf);
        if (!$user) {
            $this->saveUser($openidf);
        }
        //  return $this->sendWelcomeText($openidf,$fromUserName);
        return $this->sendWelcomeNews($openidf, $fromUserName);
    }

    private function saveUser($openidf)
    {
        $unionID = $this->getUnionID($openidf);
        $userWithUnionID = (new User())->getUserByUnionID($unionID);
        if ($userWithUnionID) {//如果已经有unionID了
            $userWithUnionID->openidf = $openidf;
            $userWithUnionID->update();
        } else {
            $userModel = new User();
            $userModel->openidf = $openidf;
            $userModel->unionid = $unionID;
            $userModel->save();
        }
    }

//参数	是否必须	描述
//ToUserName	是	接收方帐号（收到的OpenID）
//FromUserName	是	开发者微信号
//CreateTime	是	消息创建时间 （整型）
//MsgType	是	text
//Content	是	回复的消息内容（换行：在content中能够换行，微信客户端就支持换行显示）
    private function sendWelcomeText($openidf, $fromUserName)
    {
        $sendArray = [
            'ToUserName' => $openidf,
            'FromUserName' => $fromUserName,
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => "欢迎关注微作，创建作品集，免费生成专属小程序主页"
        ];
        $result = CommonFunc::array2xml($sendArray);
//        $str="<xml> <ToUserName>< ![CDATA[%s] ]></ToUserName> <FromUserName>< ![CDATA[%s] ]></FromUserName> <CreateTime>%s</CreateTime> <MsgType>< ![CDATA[text] ]></MsgType> <Content>< ![CDATA[%s] ]></Content> </xml>";
//        $result=sprintf($str,$openidf,$fromUserName,time(),"欢迎关注微作");
//      <xml><ToUserName><![CDATA[oImyD058AIJKtW32dnqeC3mCDOhU]]></ToUserName><FromUserName><![CDATA[wxe77bced0d5ad9715]]></FromUserName><CreateTime>1522386441</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[欢迎关注微作，让作品为自己代言]]></Content></xml>
        return $result;
    }

    private function sendWelcomeNews($openidf, $fromUserName)
    {
//        $sendArray=[
//            'ToUserName'=>$openidf,
//            'FromUserName'=>$fromUserName,
//            'CreateTime'=>time(),
//            'MsgType'=>'news',
//            'ArticleCount'=>'1',
//            'Articles'=>[
//              [
//                 'Title'=>"使用指南",
//                 'Description'=>"用前必读",
//                  "PicUrl"=>"https://mmbiz.qpic.cn/mmbiz_jpg/O4kPKjibqSXktVgXRdxkwfdX5b4mbx9UawNLIvkm7Lq1trBiaXSuHgN5h9e3o6RZCQUCp8SjYN9htBlDFcUNVvbw/640?wx_fmt=jpeg&tp=webp&wxfrom=5&wx_lazy=1",
//                  "Url"=>"https://mp.weixin.qq.com/s?__biz=MzU0ODYwNTQ3NA==&mid=2247483679&idx=1&sn=abfc6478cb03b0f4e3d4f7a0f65c7fec&chksm=fbbdda52ccca5344b00f370a7ab15c71e364ba5c2d89f7e24660d08f6721b6e991ce6df73518#rd"
//              ]
//            ]
//        ];

//        $tempNews="<xml><ToUserName>< ![CDATA[%s] ]></ToUserName>
//                       <FromUserName>< ![CDATA[%s] ]></FromUserName>
//                       <CreateTime>%s</CreateTime>
//                       <MsgType>< ![CDATA[%s] ]></MsgType>
//                       <ArticleCount>%s</ArticleCount>
//                       <Articles>
//                                  <item>
//                                    <Title>< ![CDATA[%s] ]></Title>
//                                     <Description>< ![CDATA[%s] ]></Description>
//                                     <PicUrl>< ![CDATA[%s] ]></PicUrl>
//                                     <Url>< ![CDATA[%s] ]></Url>
//                                   </item>
//                        </Articles>
//                        <FuncFlag>0</FuncFlag>
//                  </xml>";
        $tempNews = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><ArticleCount>%s</ArticleCount><Articles><item><Title><![CDATA[%s]]></Title> <Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item></Articles><FuncFlag>0</FuncFlag></xml>";
        //$result=CommonFunc::array2xml($sendArray);//TODO 复杂数组不具备这个功能
        $title = "使用必读 | 欢迎关注微作";
        $description = "点击公众号下栏“创建作品集”，对作品主页进行编辑和设置";
        $picUrl = "https://57418857.wechatzp.com/images/zhinan.png";
        $url = "https://mp.weixin.qq.com/s?__biz=MzU0ODYwNTQ3NA==&mid=2247483725&idx=2&sn=340fe85432a47028991235da52655c46&chksm=fbbdda00ccca531665ee15aea51bf49c309e6a9bc4d89d13625eada6cf15d1a519a767d6f0e7#rd";
        $sendNews = sprintf($tempNews, $openidf, $fromUserName, time(), 'news', 1, $title, $description, $picUrl, $url);
        return $sendNews;
    }

    //     {
//    "subscribe": 1,
//    "openid": "o6_bmjrPTlm6_2sgVt7hMZOPfL2M",
//    "nickname": "Band",
//    "sex": 1,
//    "language": "zh_CN",
//    "city": "广州",
//    "province": "广东",
//    "country": "中国",
//    "headimgurl":"http://thirdwx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
//    "subscribe_time": 1382694957,
//    "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
//    "remark": "",
//    "groupid": 0,
//    "tagid_list":[128,2],
//    "subscribe_scene": "ADD_SCENE_QR_CODE",
//    "qr_scene": 98765,
//    "qr_scene_str": ""
//}
    private function getUnionID($openidf)
    {//获取到用户的UnionID
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $sendUrl = sprintf(Yii::$app->params['weixin']['get_union_id'], $token, $openidf);
        $result = CommonFunc::curl_get($sendUrl);
        $result = json_decode($result, true);
        return $result['unionid'];
    }

    public function sendKeFuMsg($openidf, $yorder)
    {
//        $appid=Yii::$app->params['wxLogin']['app_id'];
        $postArray = [
            "touser" => $openidf,
            "msgtype" => "text",
            "text" => [
                "content" => "<a href=\"http://www.qq.com\" data-miniprogram-appid='wx7b2519aa0e131474' data-miniprogram-path='pages/jyuyue/jyuyue?yorder_no=$yorder->yorder_no'>有新的 $yorder->xiangmu 预约,点击查看详情</a>"
            ]
        ];
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $sendUrl = sprintf(Yii::$app->params['weixin']['custom'], $token);
        $data = CommonFunc::curl_post($sendUrl, $postArray);
    }

    public function setMenu()
    {
        $menuArry = [
            "button" => [
                [
                    'type' => "view",
                    'name' => "编辑作品主页",
                    'url' => 'https://57418857.wechatzp.com/weixin/home/userinfo'
                ],
                [
                    "type" => "miniprogram",
                    "name" => "小程序",
                    "url" => "https://57418857.wechatzp.com/weixin/home/userinfo",//TODO 给一个不支持小程序的网页
                    "appid" => Yii::$app->params['wxLogin']['app_id'],
                    "pagepath" => "pages/home/home"
                ],
                [
                    'type' => "view",
                    'name' => "使用场景",
                    'url' => 'https://mp.weixin.qq.com/s?__biz=MzU0ODYwNTQ3NA==&mid=2247483725&idx=1&sn=8d7c070c0aab0b818e5c5a24879b9389&chksm=fbbdda00ccca5316855be307b75de1f72133eee0df4b47d89d2e3ca4ef240bd5db927bf277c9#rd'
                ]
            ]
        ];
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $sendUrl = sprintf(Yii::$app->params['weixin']['menu'], $token);
        $result = CommonFunc::curl_post($sendUrl, $menuArry);
        return $result;
    }
}