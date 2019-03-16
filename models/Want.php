<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "want".
 */
class Want extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'want';
    }

    public function  fields()
    {
        $fields = parent::fields();
        // 去掉一些包含敏感信息的字段
        unset($fields['updated'], $fields['created']);
        return $fields;
    }

    public function getGoods(){
        return $this->hasOne(Goods::className(),['id'=>'goods_id']);
    }

}
