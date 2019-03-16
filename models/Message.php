<?php

namespace api\models;

use Yii;


class Message extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'message';
    }

//    public function  fields()
//    {
//        $fields = parent::fields();
//        // 去掉一些包含敏感信息的字段
//        unset($fields['updated'], $fields['created']);
//        return $fields;
//    }

    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'uid'])->select('id,avatarUrl,nickName');
    }

}

