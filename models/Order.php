<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "order".
 *
 * @property integer $id
 * @property string $order_no
 * @property string $prepay_id
 * @property integer $status
 * @property integer $buyer
 * @property integer $seller
 * @property string $amount
 * @property string $created
 * @property string $updated
 */
class Order extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'buyer', 'seller'], 'integer'],
            [['amount'], 'number'],
            [['created', 'updated'], 'safe'],
            [['order_no', 'prepay_id'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'prepay_id' => 'Prepay ID',
            'status' => 'Status',
            'buyer' => 'Buyer',
            'seller' => 'Seller',
            'amount' => 'Amount',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    public function getZanwo(){
        return $this->hasOne(User::className(),['id'=>'seller']);
    }

    public function getWozan(){
        return $this->hasOne(User::className(),['id'=>'buyer']);
    }
}
