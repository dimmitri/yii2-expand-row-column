<?php

namespace dimmitri\grid\assets;

class ExpandRowColumnThemeAsset extends ExpandRowColumnAsset
{
    public $css = [
        YII_DEBUG ? 'css/expand-row-column.css' : 'css/expand-row-column.min.css',
    ];
}
