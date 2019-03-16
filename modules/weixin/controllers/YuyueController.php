<?php
namespace api\modules\weixin\controllers;
use api\models\Yorder as YorderModel;
use api\modules\weixin\service\UserCheck;
use yii;
use yii\web\Controller;

class YuyueController extends Controller
{
    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染预约页面
    public function actionOrder(){
        $this->layout=false;
        return $this->render('order');
    }

    //渲染设置页面
    public function actionSetting(){
        $userModel=UserCheck::grandCooike();
        $this->layout=false;
        return $this->render('setting',['status'=>$userModel['ison']]);
    }

    //预约列表
    public function actionOrder_list($pageSize,$page){
        $userModel=UserCheck::grandCooike();
        $sellerID=$userModel['id'];
        $query=YorderModel::find()->where(['seller'=>$sellerID])->with('yuewo')
            ->orderBy('created DESC')->asArray();
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $yorderModels = $query->offset($offset)->limit($pageSize)->all();
        $result=[];
        foreach ($yorderModels as $item ) {
            $item['avatar']=$item['yuewo']['avatarUrl'];
            $item['user_name']=$item['yuewo']['nickName'];
            unset($item['yuewo']);
            array_push($result,$item);
        }
        $result=[
            'hasMore' => $offset >= $count ? false : true,
            'orders'=>$result,
        ];
        return json_encode($result);
    }

    //确认预约 TODO 消息确认
    public function actionConfirm(){
        $request=Yii::$app->request->bodyParams;
        $yorder_no=$request['yorder_no'];
        $yorderModel=YorderModel::find()->where(['yorder_no'=>$yorder_no])->one();
        $yorderModel->status=1;
        $yorderModel->update();
        $resutl=[
            'msg'=>"确认成功",
            'code'=>'200'
        ];
        return json_encode($resutl);
    }
}
