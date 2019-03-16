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
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
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

<body>

<style>
    body{
        background-color: #fff;
    }
</style>

<header class="mui-bar mui-bar-nav">
    <a style="color: #fe5454;" class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <H1 CLASS="MUI-TITLE">编辑</H1>
</header>


<div style="background-color: #fff;"  class="mui-content">
    <div>
        <div id="model">
            <div class='zpname'>
                <img src='../../images/pen1.png' class='image_pen'></img>
                <input style="-webkit-user-select: auto" class='input_name'   name="zpname" placeholder='作品集名称' maxlength='10'
                       v-model="title"> </input>
            </div>

            <div class='zpjianjie'>
                <img src='../../images/pen1.png' class='image_pen'></img>
                <textarea class='text_jianjie' name="zpjj" placeholder="作品简介（100字以内）" maxlength="100"
                          style="padding-top: 10px;" v-model="description"></textarea>
            </div>
        </div>
        <div class="photo_list_container">
            <span id="album">
                <div class='photo_list' v-for="(item,index) in albumImages">
                 <img v-bind:src='item.url' class='tianjia-shangchuan'/>
                 <img src='../../images/delete.png' class='delete' @tap="deletePic(index)"/>
                </div>
                <div class='photo_list' v-for="(item,index) in addList">
                 <img v-bind:src='item' class='tianjia-shangchuan'/>
                 <img src='../../images/delete.png' class='delete' @tap="deleteAddPic(index)"/>
               </div>
            </span>

            <img id="chooseImagePic" class='tianjia-shangchuan' src='../../images/addPic.png'/>
        </div>
        <div class='xia-button'>
            <button class='save' id="create" data-loading-text="保存中"> 保存</button>
            <div class='tianjia-jiange'></div>
            <button class='cancel' id='cancel'>取消</button>
        </div>
    </div>
</div>

<script src="../../js/vue.min.js"></script>

<script>
    //TODO 网页中MUI的传参的方式无效 找不到MUI PLUS
    var vm = new Vue({
        el: "#album",
        data: {
            albumImages: [],//原有的图片
            deleteList: [],//要删除的图片
            addList: [],//要添加的图片
            addListTemp: [],
            albumID:<?php echo $albumID?>
        }
    });
    //TODO 由于MUI listener和 Vue 会起冲突，所以要分开写
    var vmModel = new Vue({
        el: "#model",
        data: {
            title: null,
            description: null
        }
    });

    //初始化的相册信息绑定
    mui.getJSON('https://57418857.wechatzp.com/weixin/album/detail', {
        albumID:vm.albumID
    }, function (resp) {
        vmModel.title = resp.albumDetail.title;
        vmModel.description = resp.albumDetail.description;
        vm.albumImages=vm.albumImages.concat(resp.albumDetail.images);

    });

    //TODO 而且Vm中的tap事件不能写在methods里面
    function deletePic(index) {
        vm.deleteList.push({ 'id': vm.albumImages[index].id });//先添加，然后再删除呀！！
        vm.albumImages.splice(index, 1);
    }

    //TODO 删除添加的图片
    function deleteAddPic(index) {
        vm.addList.splice(index,1);
        vm.addListTemp.splice(index, 1);
    }

    //选择图片
    mui("#chooseImagePic")[0].addEventListener('tap', function () {
        wx.chooseImage({
            count: 9, // 默认9
            sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success: function (res) {
                vm.addListTemp = vm.addListTemp.concat(res.localIds);//js 浅拷贝的缘故 ,这是上传的数据
                vm.addList = vm.addList.concat(res.localIds);
            }
        });
    });

    //取消
    mui('#cancel')[0].addEventListener('tap', function () {
        goBackToHome()
    });

    //TODO 名称没有变化，此处是保存修改
    mui('#create')[0].addEventListener('tap', function () {
        if (!vmModel.title) {
            mui.alert('作品名不能为空', '提示', function() {
            });
            return false;
        }
        if (!vmModel.description) {
            mui.alert('作品简介不能为空', '提示', function() {
            });
            return false;
        }
        if (!vm.albumImages.length && !vm.addList.length ) {
            mui.alert('作品图片不能为空', '提示', function() {
            });
            return false;
        }
        mui(this).button('loading');//在回调函数中this 将发生变化
        albumDelete();
    });

    //发送服务器删除请求，统一删除图片ID
    function albumDelete() {
        mui.ajax('https://57418857.wechatzp.com/weixin/album/delete', {
            data: {
                title: vmModel.title,
                description: vmModel.description,
                deleteList:vm.deleteList,
                albumID:vm.albumID
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
            success: function (data) {
//                alert("删除成功");
                //TODO 删除成功之后再上传新的
                syncUpload();
            },
            error: function (xhr, type, errorThrown) {
                //异常处理；
                mui('#create').button("reset");
                alert(xhr.type);
            }
        });
    }

    var serverIdList = [];
    //上传图片到微信服务器
    function syncUpload() {
        if (vm.addListTemp.length) {
            var localId = vm.addListTemp.pop();
            wx.uploadImage({
                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 0, // 默认为1，显示进度提示
                success: function (res) {
                    serverIdList.push(res.serverId); // 返回图片的服务器端ID
                    syncUpload();//成功的话再次上传
                }
            });
        } else { //最后一张图片
            console.log(serverIdList);
            if(!serverIdList.length){//如果没有新的图片上传，直接返回
                mui('#create').button("reset");
                goBackToHome();
            }else {
                requestUpload()
            }
        }
    }
    //    发送请求
    function requestUpload() {
        mui.ajax('https://57418857.wechatzp.com/weixin/album/upload', {
            data: {
                album_id: vm.albumID,
                mediaIDs: serverIdList
            },
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
            success: function (data) {
                mui('#create').button("reset");
                goBackToHome();
            },
            error: function (xhr, type, errorThrown) {
                //异常处理；
                mui('#create').button("reset");
                alert(xhr.type);
            }
        });
    }

    function goBackToHome() {
        mui.openWindow({
            url: 'https://57418857.wechatzp.com/weixin/home/test'
        })
    }
</script>
</body>

</html>