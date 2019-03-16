<?php

namespace api\models;

use Yii;


class Goods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
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
        return $this->hasMany(Image::className(),['goods_id'=>'id'])->select('goods_id,url')->orderBy('created ASC');
    }

    public function getMessages(){
        return $this->hasMany(Message::className(),['goods_id'=>'id'])->orderBy('created DESC');
    }
}

