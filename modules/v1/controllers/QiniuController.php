<?php

namespace api\modules\v1\controllers;

use api\models\Goods as GoodsModel;
use api\models\Message as MessageModel;
use api\models\User as UserModel;
use api\modules\qyz\service\JsonMsg;
use api\modules\v1\service\UserToken as UserTokenService;

require_once \Yii::getAlias("@vendor/qiniu/php-sdk/autoload.php");
use \Qiniu\Auth;
use \Qiniu\Processing\PersistentFop;


class QiniuController extends BaseActiveController
{
    public $modelClass = 'api\models\message';

    /**
     * @description 七牛云SDK
     * @method GET
     * @params
     * @return string token;
     */
    public function actionToken(){
        $accessKey = 'PW8zqPrTGcGvxsaWA5vpy5Llj0NCniDPih-kc0uS';
        $secretKey = 'sP3-DiGhPBWYMoPLu5oiiM-F2TaIQnL71YYZ2szD';
        $auth = new Auth($accessKey, $secretKey);
        $bucket = 'qiniu';
// 生成上传Token
        $token = $auth->uploadToken($bucket);
        return self::success(['token'=>$token]);
    }




}
