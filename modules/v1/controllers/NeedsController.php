<?php

namespace api\modules\v1\controllers;

use api\models\Needs as NeedsModel;
use api\models\Image as ImageModel;
use Yii;
use api\modules\v1\service\UserToken as UserTokenService;

class NeedsController extends BaseActiveController
{
    public $modelClass='api\models\Needs';

    /**
     * 创建需求信息 默认是0无图 等传好图片之后再设置为1
     * @params string $description 商品名称和信息
     * @params string $phone 用户联系方式
     * @params int  $categoryID 目录ID
     * @return  int goods_id 商品id
     * @return  int uid 用户uid
     */
    public function actionNew()
    {  
        $request=Yii::$app->request->bodyParams;
        $description=$request['description'];
        $categoryID=$request['categoryID'];
        $phone=$request['phone'];
        $uid=UserTokenService::getCurrentTokenVar('uid');
        if (!is_dir("image/{$uid}")) {
            mkdir("image/{$uid}");//根据用户的uid命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}",0777);//Linux 系统要这样写
        }
        $needsModel = new needsModel();
        $needsModel->uid = $uid;
        $needsModel->description = $description;
        $needsModel->category_id = $categoryID;
        $needsModel->phone=$phone;
        $needsModel->save();
        $result=[
            'needs_id'=>$needsModel['id'],
            'uid'=>$needsModel['uid']
        ];
        return self::success($result);
    }
    /**
     * @params int $ishead 表示是否是封面
     * @params files $image 需求图片
     * @params int $goods_id 需求ID
     * @params int $uid 用户ID
     * @return array
     * @throws \Exception
     */
    public function actionImage_upload(){
        $request=Yii::$app->request->bodyParams;
        $needs_id=$request['needs_id'];
        $uid=$request['uid'];
        $ishead=$request['ishead'];
        $needsModel=NeedsModel::findOne($needs_id);
        if(!is_dir("image/{$uid}")) {
            mkdir("image/{$uid}");//根据用户的uid命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}",0777);//Linux 系统要这样写
        }
        $imageModel=new ImageModel();
        $imageModel->needs_id=$needs_id;
        $domain=YII::$app->params['domain'];
        $imageUrlLocal="image/{$uid}/";
        $file = $_FILES["image"];
        if($file){//如果有上传的文件
            $fp =$imageUrlLocal.microtime();
            if (move_uploaded_file ($file['tmp_name'], $fp )) {//保存文件
                $imageUrlLocal = $domain . $fp;
                $imageModel->url = $imageUrlLocal;
                $imageModel->save();
                if ($ishead) {
                    $needsModel->head_url = $imageUrlLocal;
                    $needsModel->status=1;
                    $needsModel->update();
                }
            }
        }
        return self::success();
    }

    /**
     * @method GET 需求
     */
    public function actionAll($page)
    {
        $pageSize=10;
        $offset=$pageSize*($page-1);
        $needs = NeedsModel::find()->where(['status'=>[0,1]])->with('user','images')->orderBy('created DESC')->asArray()->offset($offset)->limit($pageSize)->all();
        return self::success($needs);
    }

    /**
     * @description 用户操作已经上传的二手物品
     * @params int needs_id 商品id
     * @params int handle_type 标记操作状态，2表示以解决，3表示删除
     */
    public function actionHandle(){
        $request = Yii::$app->request->bodyParams;
        $needs_id = $request['needs_id'];
        $handle_type = $request['handle_type'];
        $needsModel=NeedsModel::findOne($needs_id);
        if($handle_type==2){
            $needsModel->status=2;
            $needsModel->update();
        }
        if($handle_type==3){
            $needsModel->status=3;
            $needsModel->update();
        }
        return self::success();
    }

    /**
     * @description 获取到商品的详情
     * @method GET
     * @params int $needs_id
     * @return array $needsModel 返回user表的商品模型关联
     */
    public function actionDetail($needs_id){
        $needsModel=NeedsModel::find()->where(['id'=>$needs_id])->with('user','images')->asArray()->one();
        return self::success($needsModel);
    }
}
