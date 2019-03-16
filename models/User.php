<?php

namespace api\models;

class User extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }
    /**
     * @inheritdoc
     */

    public function getUserByOpenID($OpenID){
        return self::find()->where(['openid'=>$OpenID])->one();
    }

    public function getUserByUnionID($unionID){
        return self::find()->where(['unionid'=>$unionID])->one();
    }
    /**
     * @inheritdoc
     */
    public function  fields()
    {
        $fields = parent::fields();
        // 去掉一些包含敏感信息的字段
        unset($fields['updated'], $fields['created']);
        return $fields;
    }
}
