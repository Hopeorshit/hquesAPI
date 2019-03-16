<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/21
 * Time: 10:42
 */ ?>

<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="viewport"
          content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no"/>
    <script src="../../js/mui.min.js"></script>
    <link href="../../css/mui.min.css" rel="stylesheet"/>
    <link href="../../css/order/order.css" rel="stylesheet"/>
</head>

<body>
<header class="mui-bar mui-bar-nav">
    <a style="color: #fe5454;" class="mui-action-back mui-icon mui-icon-left-nav mui-pull-left"></a>
    <h1 class="mui-title">预约列表</h1>
</header>
<div id="vmpullrefresh" class="mui-content">
    <div v-if="has==true" id="pullrefresh" class="mui-content mui-scroll-wrapper" style="padding-top: 44px; padding-bottom: 40px">
        <div class="mui-scroll" >
            <div>
                <div v-for="(item,index) in orders" class='querenyue-items'>
                    <!-- 上半部分头像及名字 -->
                    <div class='querenyue-info'>
                        <div class='info-img-name'>
                            <img class='info-img' v-bind:src="item.avatar"/>
                            <text class='info-name'>李大锤</text>
                        </div>
                        <div  class='info-button'>
                            <button  v-bind:data-clipboard-text="item.phone" class='copy-num'>复制号码</button>
                        </div>
                    </div>
                    <div class='querenyue-detail'>
                        <div class="querenyue-detail-items">
                            <label class='detail-more-name'>姓名:</label>
                            <label class='detail-your-name1'>{{item.name}}</label>
                        </div>
                        <div class="querenyue-detail-items">
                            <label class='detail-more-name'>预约项目:</label>
                            <label class='detail-your-name'>{{item.xiangmu}}</label>
                        </div>
                        <div class="querenyue-detail-items">
                            <label class='detail-more-name'>联系方式:</label>
                            <label class='detail-your-name'>{{item.phone}}</label>
                        </div>
                    </div>
                    <div>
                        <div class="querenyue-sure-container">
                            <button v-if="item.status==0" @tap="confirm(item.yorder_no,index)" class='querenyue-sure'>确认预约</button>
                            <button v-if="item.status==1" class='querenyue-had'>已确认</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="has==false" style="color: #b2b2b2;padding: 10px">暂未收到预约 </div>
</div>

<script src="../../js/vue.min.js"></script>
<script src="../../js/clipboard.min.js"></script>
<script>
    var pageSize = 5;
    var page = 1;
    var vm = new Vue({
        el: "#vmpullrefresh",
        data: {
            orders: [],
//            hasLoad:[],
            has:true
        }
    });

    function confirm(yorder_no,index) {
        mui.ajax('https://57418857.wechatzp.com/weixin/yuyue/confirm',{
            data:{
                yorder_no:yorder_no
            },
            dataType:'json',//服务器返回json格式数据
            type:'post',//HTTP请求类型
            timeout:10000,//超时时间设置为10秒；
            success:function(data){
                vm.orders[index].status=1;
                alert("已确认");
            },
            error:function(xhr,type,errorThrown){
                //异常处理；
                alert("发生错误"+xhr.responseText);
            }
        });
    }

    var clipboard = new ClipboardJS('.copy-num');//实例化
    //复制成功执行的回调，可选
    clipboard.on('success', function(e) {
        alert('复制成功,快去微信或电话联系吧')
    });


    mui.init({
        pullRefresh: {
            container: '#pullrefresh',
            up: {
                auto: true,
                contentrefresh: '正在加载...',
                callback: pullupRefresh
            }
        }
    });
    mui('.mui-scroll-wrapper').scroll({
        indicators: false,
        deceleration: 0.0005 //flick 减速系数，系数越大，滚动速度越慢，滚动距离越小，默认值0.0006
    });

    function pullupRefresh() {
        mui.getJSON('https://57418857.wechatzp.com/weixin/yuyue/order_list', {
            pageSize: pageSize,
            page: page++
        }, function (resp) {
            console.log(!resp.orders.length);
            console.log(!vm.has && !resp.orders.length);
            if(vm.has && !resp.orders.length && (page==2)){
                vm.has=false
            }
            vm.orders = vm.orders.concat(resp.orders);
            mui('#pullrefresh').pullRefresh().endPullupToRefresh(!resp.hasMore); //参数为true代表没有更多数据了。
        });
    }

    window.addEventListener('popstate', function () {
      alert('stop');
    });

</script>

</body>


</html>
