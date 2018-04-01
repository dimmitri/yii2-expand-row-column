# Expand row column for the Yii 2.0 GridView widget
Displays a clickable column that will make an ajax request and display its resulting data into a new row.

## Installation

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run
```
php composer.phar require --prefer-dist dimmitri/yii2-expand-row-column "*"
```
or add
```
"dimmitri/yii2-expand-row-column": "*"
```
to the require section of your ```composer.json``` file.

## Usage

![Example](./resources/expand-row-column.gif?raw=true)

view/index.php:
```php

<?php
use dimmitri\grid\ExpandRowColumn;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => SerialColumn::class],
        // simple example
        [
            'class' => ExpandRowColumn::class,
            'attribute' => 'name',
            'url' => Url::to(['info']),
        ],
        // advanced example
        [
            'class' => ExpandRowColumn::class,
            'attribute' => 'status_id',
            'ajaxErrorMessage' => 'Oops',
            'ajaxMethod' => 'GET',
            'url' => Url::to(['detail']),
            'submitData' => function ($model, $key, $index) {
                return ['id' => $model->status_id, 'advanced' => true];
            },
            'enableCache' => false,
            'afterValue' => function ($model, $key, $index) {
                return ' ' . Html::a(
                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-download', 'aria-hidden' => 'true']),
                    ['csv', 'ref' => $model->status_id],
                    ['title' => 'Download event history in csv format.']
                );
            },
            'format' => 'raw',
            'expandableOptions' => [
                'title' => 'Click me!',
                'class' => 'my-expand',
            ],
            'contentOptions' => [
                'style' => 'display: flex; justify-content: space-between;',
            ],
        ],
        ['class' => ActionColumn::class],
    ],
]) ?>
```

Actions:
```php
public function actionIndex()
{
    $searchModel = new ModelSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//  The key (or keyField) must be filled, if the key is not equal to primary key.        
    $dataProvider->key = 'uuid';// for ActiveDataProvider 
//  $dataProvider->keyField = 'uuid';// for ArrayDataProvider 

    return $this->render('index', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}
    
public function actionDetail($id, $advanced = false)
{
    $model = $this->findModel($id);

    $dataProvider = new ArrayDataProvider([
        'allModels' => $model->events,
    ]);

    return $this->renderAjax('_detail', [
        'dataProvider' => $dataProvider,
        'advanced' => $advanced,
        'id' => $id,
    ]);
}
```

view/_detail.php:
```php

<?php
use yii\grid\GridView;
use yii\widgets\Pjax;
?>

<?php Pjax::begin(['id' => "pjax-{$id}", 'enablePushState' => false]); ?>

<?= GridView::widget([
    // ....
]) ?>

<?php Pjax::end(); ?>
```
