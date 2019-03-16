<?php

namespace api\models;

use Yii;


class Needs extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'needs';
    }

    public function  fields()
    {
        $fields = parent::fields();
        // 去掉一些包含敏感信息的字段
        unset($fields['updated'], $fields['created']);
        return $fields;
    }

    public function getUser(){
        return $this->hasOne(User::className(),['id'=>'uid'])->select('id,avatarUrl,nickName');
    }

    public function getImages(){
        return $this->hasMany(Image::className(),['needs_id'=>'id'])->select('needs_id,url')->orderBy('created ASC');
    }
}

