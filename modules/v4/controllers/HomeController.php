<?php

namespace api\modules\v4\controllers;
use api\models\Album as AlbumModel;

class HomeController extends BaseActiveController
{
    public $modelClass='api\models\Album';

    public function  actionList($pageSize,$page)
    {
        $query=AlbumModel::find()->where(['status'=>1])->orderBy('zan DESC')->with('owner')->asArray();
        $countQuery=clone $query;
        $count=$countQuery->count();
        $offset = ($page - 1) * $pageSize;
        $albumModels = $query->offset($offset)->limit($pageSize)->all();
        return [
            'hasMore' => $offset >= $count ? false : true,
            'albumModels' => $albumModels
        ];
    }


}
