<?php

namespace dimmitri\grid\assets;

use yii\web\AssetBundle;

class ExpandRowColumnAsset extends AssetBundle
{
    public $sourcePath = '@dimmitri/grid/assets';

    public $js = [
        YII_DEBUG ? 'js/expand-row-column.js' : 'js/expand-row-column.min.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
