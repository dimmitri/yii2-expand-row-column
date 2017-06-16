
```php

use dimmitri\grid\ExpandRowColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            // simple example
            [
                'class' => ExpandRowColumn::className(),
                'attribute' => 'name',
                'url' => Url::to(['info']),
            ],
            // advanced example
            [
                'class' => ExpandRowColumn::className(),
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
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>
```

Actions:
```php
public function actionIndex()
    {
        $searchModel = new ModelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
//      The key (or keyField) must be filled, if the key is not equal to 'id'.        
        $dataProvider->key = 'uuid';// for ActiveDataProvider 
//        $dataProvider->keyField = 'uuid';// for ArrayDataProvider 

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
        ]);
    }
```

view/detail.php:
```php
use yii\grid\GridView;
use yii\widgets\Pjax;

<?php Pjax::begin(); ?>

<?= GridView::widget([
    // ....
]) ?>

<?php Pjax::end(); ?>
```

