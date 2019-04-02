<?php

namespace api\modules\v1\controllers;

use api\models\Needs as NeedsModel;
use api\models\User as UserModel;
use api\models\Goods as GoodsModel;
use api\models\Want as WantModel;
use Yii;
use api\modules\v1\service\UserToken as UserTokenService;


require_once Yii::getAlias("@common/lib/encrypt/wxBizDataCrypt.php");

class UserController extends BaseActiveController
{
    public $modelClass = 'api\models\User';

    /**
     * @description 破解用户信息和用户登录
     * @param  encryptedData
     * @param  iv
     * @return array
     */
    public function actionEncrypt_user_info()
    {
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $userModel = UserModel::findOne($uid);
        $userModel = UserTokenService::saveUserInfo($userModel);
        return self::success($userModel);
    }

    /**
     * @description 破解用户手机号
     * @param  encryptedData
     * @param  iv
     * @return array
     */
    public function actionEncrypt()
    {
        $session_key = UserTokenService::getCurrentTokenVar('session_key');
        $wxLogin = Yii::$app->params['wxLogin'];
        $wxAppID = $wxLogin['app_id'];//从配置文件中读取
        $request = Yii::$app->request->bodyParams;//获取到参数
        $encryptedData = $request['encryptedData'];
        $iv = $request['iv'];
        $wxBiz = new \WXBizDataCrypt($wxAppID, $session_key);
        $data = '';
        $code = $wxBiz->decryptData($encryptedData, $iv, $data);
        if ($code == 0) {
            $result = json_decode($data);
            return self::success($result);
        } else {
            return self::success('', 202, '破解失败');
        }
    }

    /**
     * @description  获取用户发布的需求信息
     * @return array
     */
    public function actionNeeds($page)
    {
        $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $needs = NeedsModel::find()->where(['uid' => $uid, 'status' => [0, 1]])->
        orderBy('created DESC')->offset($offset)->limit($pageSize)->all();
        return self::success(["list"=>$needs]);
    }

    /**
     * @description  获取用户发布的二手信息
     * @return array
     */
    public function actionGoods($page)
    {
        $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $goods = GoodsModel::find()->where(['uid' => $uid, 'status' => 1])->
        orderBy('created DESC')->offset($offset)->limit($pageSize)->all();
        return self::success(["list"=>$goods]);
    }

    /**
     * @description  获取用户发布的二手信息
     * @return array
     */
    public function actionWant($page)
    {   $pageSize = 10;
        $offset = $pageSize * ($page - 1);
        $uid = UserTokenService::getCurrentTokenVar('uid');
        $want = WantModel::find()->where(['uid' => $uid, 'status' => 1])->with('goods')
            ->orderBy('created DESC')->asArray()->offset($offset)->limit($pageSize)->all();
        return self::success(["list"=>$want]);
    }


}
