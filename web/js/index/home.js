
var pageSize = 2;
var page = 1;

var vm=new Vue({
    el:"#pullrefresh",
    data:{
        albumList:[]
    },
    mounted:function () {

    },
    filters:{

    },
    methods:{

    }
})

mui.init({
    pullRefresh: {
        container: '#pullrefresh',
        up: {
            auto: true,
            offset:'100px',
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
    mui.getJSON('https://57418857.wechatzp.com/weixin/home/list', {
        pageSize:pageSize,
        page:page++
    }, function(resp) {
        vm.albumList = vm.albumList.concat(resp.albumModels);
        mui('#pullrefresh').pullRefresh().endPullupToRefresh(!resp.hasMore); //参数为true代表没有更多数据了。
        if(!resp.hasMore){
            mui('#pullrefresh').pullRefresh().scrollToBottom();
        }
    });
}

//TODO MUI 提示框
function deleteAlbum(id) {
    var btnArray = ['否', '是'];
    mui.confirm('是否确认删除', '', btnArray, function (e) {
        if (e.index == 1) {
//                    alert("确认")
//                    var className='.'+id;
//                    $.ajax({
//                        type: "POST",
//                        url: "https://57418857.wechatzp.com/weixin/home/delete_all",
//                        dataType: "json",
//                        data: {
//                            albumID:id
//                        },
//                        success: function (data) {
////                            alert(data.code);
////                            $(className).remove();//通过类名删除
//                        },
//                        error: function (jqXHR) {
////                            alert("发生错误：" + jqXHR.responseText);
//                        }
//                    })
        } else {
//                    $(window).attr('location','https://57418857.wechatzp.com/weixin/album/edit?albumID='+id);
        }
    })
}
