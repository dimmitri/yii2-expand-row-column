<?php

namespace dimmitri\grid\assets;

use yii\web\AssetBundle;

class ExpandRowColumnAsset extends AssetBundle
{
    public $sourcePath = '@dimmitri/grid/assets';

    public $js = [
        'js/expand-row-column.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
