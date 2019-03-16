<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "image".
 */
class Image extends BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    public function  fields()
    {
        $fields = parent::fields();
        // 去掉一些包含敏感信息的字段
        unset($fields['updated'], $fields['created']);
        return $fields;
    }

}
