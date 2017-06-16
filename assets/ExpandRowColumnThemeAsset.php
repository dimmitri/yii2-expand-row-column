<?php

namespace dimmitri\grid\assets;

use yii\web\AssetBundle;

class ExpandRowColumnThemeAsset extends AssetBundle
{
    public $sourcePath = '@dimmitri/grid/assets';

    public $css = [
        'css/expand-row-column.css',
    ];

    public $js = [
        'js/expand-row-column.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
