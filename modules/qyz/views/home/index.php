<?php

?>
<html style="font-size: 37.5px;">
<head>
    <title>微作</title>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
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
    <script src="../../js/mui.min.js"></script>
    <link href="../../css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/home/index.css">
</head>
<body style="background-color: #FFFFFF">

<!--<div id="code" style="width: 100%; display: flex;justify-content: center" class="index-zhuye-container">-->
<!--<div class="index-zhuye">-->
<!--        <img class="index-zhuye-img" src="../../images/erweima.png">-->
<!--    <p class="index-zhuye-txt"> 查看作品主页</p>-->
<!--</div>-->
<!--</div>-->

<!--底部导航栏-->
<!--<div class="home-nav">-->
<!--    <div>-->
<!--        <div class="home-ico">-->
<!--            <img src="../../images/shouye_1.png"/>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div id="mine">-->
<!--        <div class="home-ico">-->
<!--            <img src="../../images/mine_0.png"/>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

<div>
    <div style="height: 94px; width: 100%"></div>
    <div class="index-title">
        生成专属小程序主页
    </div>
    <div class="index-subtitle">
        网页端创建和编辑，小程序端展示和分享
    </div>
    <a href="<?= Yii::$app->params['domain'] . 'weixin/album/add'; ?>">
        <div class='v-top-create'>
            <button id="create" class="index-btn">
                +创建我的作品集
            </button>
        </div>
    </a>
</div>


<div id="pullrefresh" style="margin-bottom: 60px">
    <div class="mui-card" v-for="(item,index) in albumList">
        <div class="mui-card-content">
            <img v-bind:src="item.head_url" width="100%" @tap="editAlbum(item.id)"/>
            <img class="card-content-delete" src="../../images/delete-works1.png"
                 @tap="deleteAlbum(item.id,index)"/>
        </div>
        <div class="mui-card-header mui-card-media">
            <div style="margin-left: 0px" class="mui-media-body">
                <h5 style="font-weight: bold; color: #333333;padding-bottom: 4px">{{item.title}}</h5>
                <p style="padding-bottom: 4px">{{item.description}}</p>
            </div>
        </div>
    </div>
    <div v-if="!hasMore" style=" text-align:center;font-size: 0.3rem; color: #8f8f94">
        没有更多数据了
    </div>
</div>



<script src="../../js/vue.min.js"></script>
<script>
    var pageSize = 2;
    var page = 1;
    var vm = new Vue({
        el: "#pullrefresh",
        data: {
            albumList: [],
            hasMore:true
        },
        mounted: function () {
            pullupRefresh();//初始化加载
        },
        filters: {},
        methods: {}
    });


    window.addEventListener('scroll',function () {
        var wScrollY = window.scrollY; // 当前滚动条位置
        var wInnerH = window.innerHeight; // 设备窗口的高度（不会变）
        var bScrollH = document.body.scrollHeight; // 滚动条总高度
        if(wScrollY + wInnerH >= bScrollH-20 && vm.hasMore){
           pullupRefresh();
        }
    });

    function pullupRefresh() {
        mui.getJSON('https://57418857.wechatzp.com/weixin/home/list', {
            pageSize: pageSize,
            page: page++
        }, function (resp) {
            vm.albumList = vm.albumList.concat(resp.albumModels);
            vm.hasMore=resp.hasMore;
        });
    }
    //MUI 提示框 通过@绑定
    function deleteAlbum(id, index) {
        var btnArray = ['否', '是'];
        mui.confirm('是否确认删除', '', btnArray, function (e) {
            if (e.index == 1) {
                mui.ajax('https://57418857.wechatzp.com/weixin/album/delete_all', {
                    data: {
                        albumID: id
                    },
                    dataType: 'json',//服务器返回json格式数据
                    type: 'post',//HTTP请求类型
                    timeout: 10000,//超时时间设置为10秒；
                    headers: {'Content-Type': 'application/json'},
                    success: function (data) {
                        vm.albumList.splice(index, 1)
                    },
                    error: function (xhr, type, errorThrown) {
                        //异常处理；
                        alert(xhr.responseText);
                    }
                });
            } else {

            }
        })
    }
    function editAlbum(id) {
        mui.openWindow({
            url: 'https://57418857.wechatzp.com/weixin/album/edit?albumID=' + id,
            id: 'albumEdit',
            extras: {
                albumID: id
            }
        });
    }
    mui('#mine')[0].addEventListener('tap', function () {
        window.location.href = "https://57418857.wechatzp.com/weixin/mine/mine";
    });

    function getCode() {
        mui.getJSON('https://57418857.wechatzp.com/weixin/mine/get_code', {
        }, function (resp) {
            return resp.erCode;
        });
        return false;
    }

    mui('#code')[0].addEventListener('tap', function () {
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
</script>
</body>

</html>
