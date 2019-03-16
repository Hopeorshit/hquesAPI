<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "yorder".
 *
 * @property integer $id
 * @property integer $buyer
 * @property string $name
 * @property string $phone
 * @property string $xiangmu
 * @property integer $seller
 * @property string $updated
 * @property string $created
 */
class Yorder extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yorder';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'buyer' => 'Buyer',
            'name' => 'Name',
            'phone' => 'Phone',
            'xiangmu' => 'Xiangmu',
            'seller' => 'Seller',
            'updated' => 'Updated',
            'created' => 'Created',
        ];
    }
    public function getYuewo(){
        return $this->hasOne(User::className(),['id'=>'buyer']);
    }
    public function getWoyue(){
        return $this->hasOne(User::className(),['id'=>'seller']);
    }
}
