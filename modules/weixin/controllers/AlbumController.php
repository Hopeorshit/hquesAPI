<?php
namespace api\modules\weixin\controllers;
use api\models\Album as AlbumModel;
use api\models\Image as ImageModel;
use api\modules\weixin\service\Home;
use api\modules\weixin\service\JsonMsg;
use api\modules\weixin\service\UserCheck;
use yii;
use yii\base\Exception;
use yii\web\Controller;

class AlbumController extends Controller
{
    public $enableCsrfValidation=false;//这个要加上否则访问不了

    //渲染编辑页面
    public function actionEdit($albumID){
        $userModel=UserCheck::grandCooike();
        $wxConfigData = (new Home())->wxConfigData();
        $this->layout = false;
        return $this->render('edit', ['wxConfig' => $wxConfigData,'albumID'=>$albumID]);
    }

    //渲染添加页面
    public function actionAdd()
    {
        $this->layout = false;
        $wxConfigData = (new Home())->wxConfigData();
        return $this->render('add', ['wxConfig' => $wxConfigData]);
    }

    //先创建作品相册，然后再图片
    public function actionCreate(){
        $album=(new Home())->createAlbum();
        $result=[
            'album_id'=>$album['id'],
        ];
        return json_encode($result);
    }

    public function actionUpload(){
        (new Home())->albumUpload();
        $result=[
            'msg'=>"success",
            "code"=>200
        ];
        return json_encode($result);
    }

    public function actionDetail($albumID){
        $albumDetail=AlbumModel::find()->where(['id'=>$albumID])->with(['images'=>
            function ($query){
                $query->andWhere('status>0');
            }
        ])->asArray()->one();//加上asArray才能用模型关联
        $result=[
            "albumDetail"=>$albumDetail
        ];
        return json_encode($result);
    }

    //删除部分图片 TODO 由于小程序的机制，此处有修改作品titile和description的作用，成功之后再上传图片
    public function actionDelete(){
        $request=Yii::$app->request->bodyParams;
        $albumID=$request['albumID'];
        $albumModel=AlbumModel::findOne($albumID);
        $albumModel->title=$request['title'];
        $albumModel->description=$request['description'];
        $result=[
            'msg'=>"success",
            'code'=>"200"
        ];
        if(!isset($request['deleteList']))//如果没有要删除的图片，直接更新
        {
            $albumModel->update();
            return json_encode($result);
        }
        $imageModels=ImageModel::find()->where(['album_id'=>$albumID])->all();
        $headHasChanged=false;
        //判断哪些要删除掉
        $deleteList=$request['deleteList'];
        foreach ( $deleteList as $itemd){
            foreach ($imageModels as $itemi ){
                if((int)$itemd['id']==$itemi['id']){
                    $itemi->status=0;
                    if($itemi['is_head_img']==1){
                        $itemi->is_head_img=0;
                        //album 头图要发生改变
                        $headHasChanged=true;
                    }
                    $itemi->update();
                }
            }
        }
        if($headHasChanged){
            $imageModel=ImageModel::find()->where(['status'=>1,'album_id'=>$albumID])->one();
            $albumModel->head_url=$imageModel['url'];
            $imageModel->is_head_img=1;
            $imageModel->update();
        }
        $albumModel->update();
        //循环之后找另外一张图片作为头图
        return json_encode($result);
    }


    public function actionDelete_all(){
        $uid=UserCheck::getUid();
        $request=\Yii::$app->request->bodyParams;
        $albumID=$request['albumID'];
        $albumModel=AlbumModel::findOne($albumID);
        if($uid!=$albumModel['user_id']){
            throw new Exception('非法操作','501');
        }
        $albumModel->status=0;
        $albumModel->update();
        return JsonMsg::success(null);
    }
}
