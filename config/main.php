<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [//模块ID和模块启动名称前面的一个要一致
        'v1' => [
            'class' => 'api\modules\v1\module',
        ],
        'v2' => [
            'class' => 'api\modules\v2\module',
        ],
        'v3' => [
            'class' => 'api\modules\v3\module',
        ],
        'v4' => [
            'class' => 'api\modules\v4\module',
        ],
        'weixin' => [
            'class' => 'api\modules\weixin\module',
        ],
        'app' => [
            'class' => 'api\modules\app\module',
        ],
        'qyz' => [
            'class' => 'api\modules\qyz\module',
        ],
    ],
    'components' => [
//        'request' => [
//            'csrfParam' => '_csrf-api',
//        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
            'enableSession'=>false,
            'loginUrl'=>null,
        ],
//        'session' => [
//            // this is the name of the session cookie used for login on the api
//            'name' => 'advanced-api',
//        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
//
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            // 'enableStrictParsing' => true, //TODo 若取消注释则自定义的Action无法访问
            'showScriptName' => false,
            'rules' => [
                [
                    'class'=>'yii\rest\UrlRule',
                    'controller'=>['v1/token'],//注册控制器
                    'pluralize' => false //这样在URl中就不用强制加s
                ],
            ],
        ],
    ],
    'params' => $params,
];
