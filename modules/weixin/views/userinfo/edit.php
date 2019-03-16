<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20
 * Time: 23:42
 */
?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <script src="https://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
    <script type="text/javascript">
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
                'previewImage',
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
    <script src="../../js/mui.min.js"></script>
    <link href="../../css/mui.min.css" rel="stylesheet" />
    <link href="../../css/editinfo/editinfo.css" rel="stylesheet" />
</head>
<style type="text/css">
    body {
        background-color: #fff;
    }
</style>

<body>
<header class="mui-bar mui-bar-nav">
    <a style="color: #fe5454;" class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <h1 class="mui-title">设置</h1>
</header>

<div style="margin-top: 44px;" id="model">
    <div class='zppic'>
        <label class='home_text'>头像</label>
        <img v-bind:src="avatar"  @tap="wxChooseImage()"/>
    </div>
    <div class='zpname'>
        <label class='home_text'>昵称</label>
        <input style="-webkit-user-select: auto" class='input_name' maxlength='10' placeholder='请输入昵称（10字以内)'  v-model="nickName"> </input>
    </div>
    <div class='zpname'>
        <label class='home_text'>简介</label>
        <input style="-webkit-user-select: auto" placeholder='请输入简介（15字以内）' maxlength='15' class='input_name' v-model="title"> </input>
    </div>
    <div class="btn_container">
        <div>
            <button class='btn_save' @tap="save()" data-loading-text = "提交中">保存</button>
        </div>
        <div>
            <button class='btn_cancel' @tap="cancel()">取消</button>
        </div>
    </div>
</div>


<script src="../../js/vue.min.js"></script>
<script>
    var vm = new Vue({
        el: "#model",
        data: {
            avatar:"<?=$userModel['avatarUrl']?>",
            nickName:"<?=$userModel['nickName'] ?>",
            title:"<?=$userModel['title']?>",
            avatarTemp:null//这个用来上传，chooseImage之后才会调用
        }
    });

    function wxChooseImage() {
        wx.chooseImage({
            count: 1, // 默认9
            sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                vm.avatar=res.localIds[0];
                vm.avatarTemp=res.localIds[0]
            }
        });
    }

    function save() {
        if (!vm.title) {//TODO 简介可以为空
            mui.alert('简介不能为空', '提示', function() {
            });
            return false;
        }
        if (!vm.nickName) {
            mui.alert('昵称不能为空', '提示', function() {
            });
            return false;
        }
        if (!vm.avatarTemp ) {
            //TODO 只修改昵称
            mui('.btn_save').button('loading');
            editUserInfo();
        }else {
            mui('.btn_save').button('loading');
            editUserPhotoAndInfo();
        }
    }

   function editUserPhotoAndInfo() {
       wx.uploadImage({
           localId: vm.avatarTemp, // 需要上传的图片的本地ID，由chooseImage接口获得
           isShowProgressTips: 0, // 默认为1，显示进度提示
           success: function (res) {
               requestUpload(res.serverId); // 返回图片的服务器端ID
           }
       });
   }

   function editUserInfo() {
       mui.ajax('https://57418857.wechatzp.com/weixin/userinfo/edit_info_nt_post', {
           data: {
               nickName:vm.nickName,
               title:vm.title
           },
           dataType: 'json',//服务器返回json格式数据
           type: 'post',//HTTP请求类型
           timeout: 10000,//超时时间设置为10秒；
           success: function (data) {
               mui('.btn_save').button('reset');
               goBackToMine();
           },
           error: function (xhr, type, errorThrown) {
               //异常处理；
               alert(xhr.responseText);
           }
       });
   }

    //将微信临时素材上传更新到服务器
    function requestUpload(serverId) {
        mui.ajax('https://57418857.wechatzp.com/weixin/userinfo/edit_info_post', {
            data: {
                nickName:vm.nickName,
                title:vm.title,
                mediaID:serverId
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
            success: function (data) {
                mui('.btn_save').button('reset');
                goBackToMine();
            },
            error: function (xhr, type, errorThrown) {
                //异常处理；
                alert(xhr.responseText);
            }
        });
    }

    function cancel() {
      goBackToMine();
    }
    function goBackToMine() {
        mui.openWindow({
            url: 'https://57418857.wechatzp.com/weixin/mine/mine'
        })
    }


</script>


</body>

</html>