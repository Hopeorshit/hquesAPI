<?php

?>
<html style="font-size: 37.5px;">
<head>
    <title>我的</title>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <script src="../../js/mui.min.js"></script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
    <script>
        wx.config({
            debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: '<?=Yii::$app->params['weixin']['app_id'];?>', // 必填，公众号的唯一标识
            timestamp: '<?=$wxConfig['timestamp'];?>', // 必填，生成签名的时间戳
            nonceStr: '<?=$wxConfig['noncestr'];?>', // 必填，生成签名的随机串
            signature: '<?=$wxConfig['signature'];?>',// 必填，签名
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'chooseImage',
                'uploadImage'
            ] // 必填，需要使用的JS接口列表
        });

        wx.ready(function () {
            wx.onMenuShareTimeline({
                title: '测试', // 分享标题
                link: 'https://57418857.wechatzp.com/weixin/msg/test', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'www.baidu.com/img/bd_logo1.png', // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
        });

        wx.error(function (res) {
            // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
        });
    </script>
    <link href="../../css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/home/index.css">
</head>
<body >
<!--底部导航栏-->
<div class="home-nav">
    <div id="index">
        <div class="home-ico">
            <img src="../../images/shouye_0.png"/>
        </div>
        <!--<span class="">首页</span>-->
    </div>
    <div>
        <div class="home-ico">
            <img src="../../images/mine_1.png"/>
        </div>
        <!--<span class="">首页</span>-->
    </div>
</div>

<div class="mui-content">
    <!--    我的页面开始-->
    <div style="background-color: #efeff4;">
        <!--页面主内容区开始-->
        <div class="mui-page-content">
            <div class="m-user-info">
                <div class="user-photo">
                    <img class="user-photo-img" src="<?php echo $user['avatarUrl']; ?>">
                </div>
                <div class="user-name">
                    <?php echo $user['nickName']; ?>
                </div>
                <div class="user-nick">
                    <?php echo $user['title']; ?>
                </div>
            </div>
            <!--我的页面下半部分-->
            <div class="mui-scroll-wrapper1">
                <div style="background-color: #efeff4; margin-bottom: 60px">
                    <ul id="zhuYe"  class="mui-table-view mui-table-view-chevron">
                        <li class="mui-table-view-cell">
                            <div >
                                <img class="mui-media-object mui-pull-left head-img" id="head-img"
                                     src="../../images/mine-zhuye.png"> 小程序主页
                                <img style="margin-right: -40px;" class="mui-media-object mui-pull-right head-img" src="../../images/erweima.png">
                            </div >
                        </li>
                    </ul>

                    <ul  class="mui-table-view mui-table-view-chevron">
                        <li id="orderSetting" class="mui-table-view-cell">
                            <div  class="mui-navigate-right">
                                <img class="mui-media-object mui-pull-left head-img" id="head-img" src="../../images/mine-yuyue.png">
                                预约设置
                            </div>
                        </li>
                        <li id="order"  class="mui-table-view-cell">
                            <div class="mui-navigate-right"><img class="mui-media-object mui-pull-left head-img" id="head-img" src="../../images/mine-yuyuewo.png">
                                预约我的
                            </div>
                        </li>
                    </ul>

                    <ul id="editInfo" class="mui-table-view mui-table-view-chevron">
                        <li class="mui-table-view-cell">
                            <div  class="mui-navigate-right">
                                <img class="mui-media-object mui-pull-left head-img" id="head-img"
                                     src="../../images/mine-shezhi.png"> 设置个人信息
                            </div>
                        </li>
                    </ul>

                    <ul id="about" class="mui-table-view mui-table-view-chevron">
                        <li class="mui-table-view-cell">
                            <div  class="mui-navigate-right"> <img
                                        class="mui-media-object mui-pull-left head-img" id="head-img"
                                        src="../../images/mine-zanshangwo.png">关于微作 <i
                                        class="mui-pull-right update">V1.1.0</i></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../js/vue.min.js"></script>
<!--<script src="../../js/index/home.js"></script>-->
<script>
//    //TODO 暂时只做一个数据绑定,因为可能会影响到其它按钮的监听事件
//   var vm=new Vue({
//    el:"#switch",
//    data:{
//        isOn:<?php //echo $user['ison'];?>//, //TODO 一个用来控制初始显示，一个用来和服务器做交互
//        isOnF:<?php //echo $user['ison'];?>
//    }
//   });
//
//   function switchTap () {
//       vm.isOnF=!vm.isOnF;
//       if(vm.isOn){
//           vm.isOn=0;
//       }else {
//           vm.isOn=1;
//       }
//       mui.ajax('https://57418857.wechatzp.com/weixin/mine/yuyue_switch',{
//           dataType:'json',//服务器返回json格式数据
//           type:'post',//HTTP请求类型
//           timeout:10000,//超时时间设置为10秒；
//           headers:{'Content-Type':'application/json'},
//           success:function(data){
//               mui.alert('修改成功', '提示', function() {
//               })
//           },
//           error:function(xhr,type,errorThrown){
//               alert("发生错误"+xhr.responseText);
//           }
//       });
//   }
    mui('#orderSetting')[0].addEventListener('tap',function () {
        window.location.href="https://57418857.wechatzp.com/weixin/yuyue/setting";
    });

    mui('#index')[0].addEventListener('tap', function () {
        window.location.href="https://57418857.wechatzp.com/weixin/home/test";
    });

    mui('#editInfo')[0].addEventListener('tap', function () {
        window.location.href='https://57418857.wechatzp.com/weixin/userinfo/edit'
    });

    mui('#order')[0].addEventListener('tap', function () {
        window.location.href='https://57418857.wechatzp.com/weixin/yuyue/order'
    });


    function getCode() {
        mui.getJSON('https://57418857.wechatzp.com/weixin/mine/get_code', {
        }, function (resp) {
         return resp.erCode;
        });
        return false;
    }

    mui('#zhuYe')[0].addEventListener('tap', function () {
        var current='<?=$user['code'];?>';//如果没有
        if(!current){
          current=getCode();
        }
        var urls=[];
        urls.push(current);
        wx.previewImage({
            current: current,
            urls: urls // 需要预览的图片http链接列表
        });
    });

    mui('#about')[0].addEventListener('tap', function () {
        var current='https://57418857.wechatzp.com/images/ercode.jpg';//
        var urls=[];
        urls.push(current);
        wx.previewImage({
            current: current,
            urls: urls // 需要预览的图片http链接列表
        });
    });

</script>
</body>

</html>
