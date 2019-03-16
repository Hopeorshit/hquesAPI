<?php

?>
<html style="font-size: 37.5px;">
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <script src="../../js/mui.min.js"></script>
    <link href="../../css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="../../css/home/index.css">
</head>
<body>
<header class="mui-bar mui-bar-nav">
    <a style="color: #fe5454;" class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <H1 class="mui-title">预约设置</H1>
</header>

<div class="mui-content">
    <ul class="mui-table-view mui-table-view-chevron">
        <li id="switch" class="mui-table-view-cell">
            <div class="mui-navigate-right">
                接受技能预约
            </div>
            <div v-if="isOn==1" @tap="switchTap()" class="mui-switch mui-switch-mini mui-active ">
                <div class="mui-switch-handle"></div>
            </div>
            <div v-if="isOn==0" @tap="switchTap()" class="mui-switch mui-switch-mini ">
                <div class="mui-switch-handle"></div>
            </div>
        </li>
    </ul>
</div>
<script src="../../js/vue.min.js"></script>
<!--<script src="../../js/index/home.js"></script>-->
<script>
//    //TODO 暂时只做一个数据绑定,因为可能会影响到其它按钮的监听事件
    var vm = new Vue({
        el: "#switch",
        data: {
            isOn:<?php echo $status;?>, //TODO 一个用来控制初始显示，一个用来和服务器做交互
            isOnF:<?php echo $status;?>
        }
    });

    function switchTap() {
        vm.isOnF = !vm.isOnF;
        if(!vm.isOnF)
        mui.alert('关闭技能预约，小程序作品主页预约按钮将不显示', '提示', function () {
        });
        mui.ajax('https://57418857.wechatzp.com/weixin/mine/yuyue_switch', {
            dataType: 'json',//服务器返回json格式数据
            type: 'post',//HTTP请求类型
            timeout: 10000,//超时时间设置为10秒；
            headers: {'Content-Type': 'application/json'},
            success: function (data) {
            },
            error: function (xhr, type, errorThrown) {
                alert("发生错误" + xhr.responseText);
            }
        });
    }


</script>
</body>

</html>
