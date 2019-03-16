<?php

namespace api\modules\v1\controllers;

use api\models\Goods as GoodsModel;
use api\models\Image as ImageModel;
use api\models\Image;
use api\models\Message as MessageModel;
use Yii;
use api\modules\v1\service\UserToken as UserTokenService;
use api\models\Want as WantModel;

class GoodsController extends BaseActiveController
{
    public $modelClass = 'api\models\Goods';

    /**
     * 创建商品信息 默认是无效的 等传好图片之后再设置为有效
     * @param string $description 商品名称和信息
     * @param string $phone 用户联系方式
     * @param int $categoryID 目录ID
     * @param number $price 商品价格
     * @return  int goods_id 商品id
     * @return  int uid 用户uid
     */
    public function actionNew()
    {
        $request = Yii::$app->request->bodyParams;
        $description = $request['description'];
        $price = $request['price'];
        $categoryID = $request['categoryID'];
        $phone = $request['phone'];

        $uid = UserTokenService::getCurrentTokenVar('uid');
        if (!is_dir("image/{$uid}")) {
            mkdir("image/{$uid}");//根据用户的uid命名文件夹，username可能文件夹命名不支持
            chmod("image/{$uid}", 0777);//Linux 系统要这样写
        }
        $goodsModel = new goodsModel();
        $goodsModel->uid = $uid;
        $goodsModel->description = $description;
        $goodsModel->category_id = $categoryID;
        $goodsModel->price = $price;
        $goodsModel->phone = $phone;
        $goodsModel->save();

        $result = [
            'goods_id' => $goodsModel['id'],
            'uid' => $goodsModel['uid']
        ];
        return self::success($result);
    }

    public function actionImage_save(){
        $request = Yii::$app->request->bodyParams;
        $goods_id = $request['goods_id'];
        $qiniuImage = $request['qiniuImage'];
        $goodsModel = GoodsModel::findOne($goods_id);
        $goodsModel->head_url=$qiniuImage[0];
        $goodsModel->status = 1;
        $batchArray=[];
        foreach ($qiniuImage as $item){
            $arrayItem=[];
            array_push($arrayItem,$goods_id);
            array_push($arrayItem,$item);
            array_push($batchArray,$arrayItem);
        }
        Yii::$app->db->createCommand()->batchInsert(Image::tableName(),['goods_id','url'],$batchArray)->execute();
        $goodsModel->save();
        return self::success();

    }


    /**
     * @param int $ishead 表示是否是封面
     * @param files $image 商品图片
     * @param int $goods_id 商品ID
     * @param int $uid 用户ID
     * @return array
     * @throws \Exception
     */
    public function actionImage_upload()
    {
        $request = Yii::$app->request->bodyParams;
        $goods_id = $request['goods_id'];
        $uid = $request['uid'];
        $ishead = $request['ishead'];

        $goodsModel = GoodsModel::findOne($goods_id);
        if (!is_dir("image/{$uid}")) {
            throw new \Exception('用户个人目录未创建成功');
        }
        $imageModel = new ImageModel();
        $imageModel->goods_id = $goods_id;
        $domain = YII::$app->params['domain'];
        $imageUrlLocal = "image/{$uid}/";
        $file = $_FILES["image"];
        if ($file) {//如果有上传的文件
            $fp = $imageUrlLocal . microtime();
            if (move_uploaded_file($file['tmp_name'], $fp)) {//保存文件
                $imageUrlLocal = $domain . $fp;
                $imageModel->url = $imageUrlLocal;
                $imageModel->save();
                if ($ishead) {
                    $goodsModel->head_url = $imageUrlLocal;
                    $goodsModel->status = 1;
                    $goodsModel->update();
                }
            }
        }
        return self::success('');
    }

    /**
     * @method GET 获取免费的产品
     */
    public function actionFree($page)
    {
        $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        $goods = GoodsModel::find()->where(['price' => 0, 'status' => 1])->
        with('user')->orderBy('created DESC')->asArray()->offset($offset)->limit($pageSize)->all();
        return self::success($goods);
    }

    /**
     * @description 用户操作已经上传的二手物品
     * @param int $goods_id 商品id
     * @param int $handle_type 标记操作状态，2表示以解决，3表示删除
     */
    public function actionHandle()
    {
        $request = Yii::$app->request->bodyParams;
        $goods_id = $request['goods_id'];
        $handle_type = $request['handle_type'];
        $goodsModel = GoodsModel::findOne($goods_id);
        if ($handle_type == 2) {
            $goodsModel->status = 2;
            $goodsModel->update();
        }
        if ($handle_type == 3) {
            $goodsModel->status = 3;
            $goodsModel->update();
        }
        return self::success();
    }

    /**
     * @description 获取到商品的详情
     * @method GET
     * @param int $goods_id
     * @return array $goodsModel 返回user表的商品模型关联
     */
    public function actionDetail($goods_id)
    {
        //goods_id 查看之后 浏览量加1
        $goods=GoodsModel::findOne($goods_id);
        $goods->views=$goods['views']+1;
        $goods->update();

        $uid = UserTokenService::getCurrentTokenVar('uid');
        $wangModel = WantModel::find()->where(['uid' => $uid, 'goods_id' => $goods_id])->one();
        $status = 0;
        if ($wangModel) {
            $status = $wangModel['status'];
        }
        $goodsModel = GoodsModel::find()->where(['id' => $goods_id])->with('user', 'images','messages')->asArray()->one();
        $messageModel=$goodsModel['messages'];
        foreach ($messageModel as &$item){
            if($item['msg_id']!=0){
                $item['re_msg']=MessageModel::findOne($item['msg_id']);
            }
        }
        $goodsModel['messages']=$messageModel;
        $goodsModel['wantStatus']=$status;
        return self::success($goodsModel);
    }

    /**
     * @description search搜索
     * @param string $text
     * @return array $goodsModel 返回user表的商品模型关联
     */
    public function actionSearch($text, $page)
    {
        $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        $goodsModel = GoodsModel::find()->where(['like', 'description', $text])->
        orderBy('created DESC')->with('user')->offset($offset)->limit($pageSize)->asArray()->all();
        return self::success($goodsModel);
    }
}
