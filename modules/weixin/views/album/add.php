<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/13
 * Time: 10:53
 */
?>

<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
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
    <link href="../../css/mui.min.css" rel="stylesheet"/>
    <link href="../../css/add/add.css" rel="stylesheet"/>
</head>
<style>
    body{
        background-color: #fff;
    }
</style>
<body>
<header class="mui-bar mui-bar-nav">
    <a style="color: #fe5454;" class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <H1 CLASS="MUI-TITLE">创建作品集</H1>
</header>

<div style="background-color: #fff;" class="mui-content">
    <div >
        <div id="model">
            <div class='zpname'>
                <img src='../../images/pen1.png' class='image_pen'></img>
                <input style="-webkit-user-select: auto" class='input_name'  name="zpname" placeholder='作品集名称' maxlength='10'
                       v-model="title" > </input>
            </div>

            <div class='zpjianjie'>
                <img src='../../images/pen1.png' class='image_pen'></img>
                <textarea class='text_jianjie' name="zpjj" placeholder="作品简介（100字以内）" maxlength="100"
                          style="padding-top: 10px;" v-model="description"></textarea>
            </div>
        </div>
        <div   class="photo_list_container">
            <span id="album">
              <div class='photo_list' v-for="(item,index) in localIds">
                <img v-bind:src='item' class='tianjia-shangchuan'/>
                <img src='../../images/delete.png' class='delete' @tap="deletePic(index)"/>
              </div>
            </span>

            <img  id="chooseImagePic" class='tianjia-shangchuan' src='../../images/addPic.png'/>
        </div>
        <div class='xia-button'>
            <button class='save' id="create" data-loading-text = "提交中"> 创建</button>
            <div class='tianjia-jiange'></div>
            <button class='cancel' id='cancel'>取消</button>
        </div>
    </div>
</div>

<script src="../../js/vue.min.js"></script>

<script>
    //步骤 1选好图片添加到addList 里面
    //2 每选一张图片 要添加一个新的div
    var vm = new Vue({
        el: "#album",
        data: {
            localIds: [],
            localIdsTemp:[]
        }
    });
    //TODO 由于MUI listener和 Vue 会起冲突，所以要分开写
    var vmModel=new Vue({
        el: "#model",
        data: {
            title: null,
            description: null
        },
        methods:{

        }
    });
    //TODO 而且Vm中的tap事件不能写在methods里面
    function deletePic(index){
        vm.localIds.splice(index,1);
        vm.localIdsTemp.splice(index,1);
    }

    mui("#chooseImagePic")[0].addEventListener('tap', function () {
        wx.chooseImage({
            count: 9, // 默认9
            sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                vm.localIds=vm.localIds.concat(res.localIds);
                console.log(vm.localIds);
                vm.localIdsTemp=vm.localIdsTemp.concat(res.localIds);//js 浅拷贝的缘故 ,这是上传的数据
            }
        });
    });

    //取消
    mui('#cancel')[0].addEventListener('tap', function () {
        goBackToHome()
    });

    //创建
    mui('#create')[0].addEventListener('tap', function () {
        if(!vmModel.title){
            mui.alert('作品名不能为空', '提示', function() {
            });
            return false;
        }
        if(!vmModel.description){
            mui.alert('简介不能为空', '提示', function() {
            });
            return false;
        }
        if(!vm.localIds.length){
            mui.alert('作品不能为空', '提示', function() {
            });
            return false;
        }
        mui('#create').button('loading');
        syncUpload();
    });

    var serverIdList=[];
    //上传图片到微信服务器
    function syncUpload() {
        if (vm.localIdsTemp.length) {
            var localId = vm.localIdsTemp.pop();
            wx.uploadImage({
                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 0, // 默认为1，显示进度提示
                success: function (res) {
                    serverIdList.push(res.serverId); // 返回图片的服务器端ID
                    syncUpload();//成功的话再次上传
                }
            });
        } else { //最后一张图片
            requestCreateAlbum();
        }
    }
    //    发送请求
    function requestCreateAlbum() {
        mui.ajax('https://57418857.wechatzp.com/weixin/album/create', {
            data: {
                title: vmModel.title,
                description: vmModel.description,
                albumName: Date.parse(new Date()) / 1000
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
//            headers: {'Content-Type': 'application/json'},
            success: function (data) {
                requestUpload(data.album_id)
            },
            error: function (xhr, type, errorThrown) {
                //异常处理；
                alert(xhr.responseText);
                mui('#create').button("reset");
            }
        });
    }
    function requestUpload(album_id) {
//        $.ajax({
//            type: "POST",
//            url: "https://57418857.wechatzp.com/weixin/home/album_upload",
//            dataType: "json",
//            data: {
//                album_id:album_id,
//                mediaIDs:serverIdList
//            },
//            success: function (data) {
//                alert('上传成功');
//                $(window).attr('location','https://57418857.wechatzp.com/weixin/home/test');
//            },
//            error: function (jqXHR) {
//                alert("发生错误：" + jqXHR.responseText);
//            }
//        })
//        console.log();
        mui.ajax('https://57418857.wechatzp.com/weixin/album/upload', {
            data: {
                album_id: album_id,
                mediaIDs: serverIdList
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
//           headers: {'Content-Type': 'application/json'},
            success: function (data) {
                mui('#create').button("reset");
//                goToCode();
              goBackToHome();
            },
            error: function (xhr, type, errorThrown) {
                //异常处理；
                alert(xhr.responseText);
                mui('#create').button("reset");
            }
        });
    }

    function goBackToHome() {
        mui.openWindow({
            url: 'https://57418857.wechatzp.com/weixin/home/test'
        })
    }

    function goToCode() {
        mui.openWindow({
            url: 'https://57418857.wechatzp.com/weixin/code/show'
        })
    }
</script>
</body>

</html>