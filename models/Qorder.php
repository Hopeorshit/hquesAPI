<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "qorder".
 *
 * @property integer $id
 * @property string $order_no
 * @property integer $status
 * @property integer $user_id
 * @property string $amount
 * @property string $created
 * @property string $updated
 */
class Qorder extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qorder';
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'status' => 'Status',
            'user_id' => 'User ID',
            'amount' => 'Amount',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }
}
