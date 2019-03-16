<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "album".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $created
 * @property string $updated
 * @property integer $user_id
 */
class Album extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'album';
    }

    /**
     * @inheritdoc
     */


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'created' => 'Created',
            'updated' => 'Updated',
            'user_id' => 'User ID',
        ];
    }

    public function getImages(){
        return $this->hasMany(Image::className(),['album_id'=>'id']);
    }
    public function getOwner(){
        return $this->hasOne(User::className(),['id'=>'user_id']);
    }

}

