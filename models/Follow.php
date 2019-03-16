<?php

namespace api\models;

use Yii;


class Follow extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'follow';
    }

    /**
     * @inheritdoc
     */
    public function getFollowed(){
        return $this->hasOne(User::className(),['id'=>'followed']);
    }

    public function getFollow(){
        return $this->hasOne(User::className(),['id'=>'follow']);
    }

    public function getTfollow(){
//        return $this->hasOne(User::className(),['id'=>'follow'])->select('nickName')->asArray()->one();//    //这种关联查询，可以不用返回，父亲
        return $this->hasOne(User::className(),['id'=>'follow'])->select('nickName');//带with的关联查询
    }
}
