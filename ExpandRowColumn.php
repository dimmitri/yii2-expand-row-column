<?php

namespace dimmitri\grid;

use Closure;
use dimmitri\grid\assets\ExpandRowColumnAsset;
use dimmitri\grid\assets\ExpandRowColumnThemeAsset;
use Yii;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\VarDumper;

/**
 * Class ExpandRowColumn
 *
 * Displays a clickable column that will make an ajax request and display its resulting data into a new row.
 */
class ExpandRowColumn extends DataColumn
{
    const EXPANDABLE_BEGINNING_CLASS_NAME = 'expand-row-column';
    const DETAIL_BEGINNING_CLASS_NAME = 'expand-row-column-detail';

    /**
     * @var string unique identifier of column
     */
    private $_columnID;

    /**
     * @var string the route to call via AJAX to get the data from
     */
    public $url;

    /**
     * @var array|\Closure the HTML attributes for the expandable element
     */
    public $expandableOptions = [];

    /**
     * @var bool if set to true, there won't be more than one AJAX request. If set to false, the widget will
     * continuously make AJAX requests. This is useful if the data could vary. If the data does not change then
     * is better to set it to true. Defaults to true.
     */
    public $enableCache = true;

    /**
     * @var string the message that is displayed on the newly created row in case there is an AJAX error.
     */
    public $ajaxErrorMessage = 'Error';

    /**
     * @var string the HTTP method to use for the request
     */
    public $ajaxMethod = 'GET';

    /**
     * @var Closure data into the query string being sent to the server.
     * Default: the row id is sent as 'id'.
     * function ($model, $key, $index) { return ['param' => $model->field];}
     */
    public $submitData;

    /**
     * @var string the HTML tag for the loading icon (glyphicon, font awesome icon or tag img)
     */
    public $loadingIcon;

    /**
     * @var string|Closure the text after the expandable element.
     */
    public $afterValue;

    /**
     * @var bool whether to use the default theme
     */
    public $useDefaultTheme = true;

    /**
     * Initializes the object.
     */
    public function init()
    {
        parent::init();

        if ($this->useDefaultTheme) {
            ExpandRowColumnThemeAsset::register($this->grid->getView());
        } else {
            ExpandRowColumnAsset::register($this->grid->getView());
        }

        if (empty($this->url)) {
            $this->url = Yii::$app->getRequest()->getUrl();
        }

        $this->registerJs();
    }

    /**
     * Renders a data cell.
     * @param mixed $model the data model being rendered
     * @param mixed $key the key associated with the data model
     * @param int $index the zero-based index of the data item among the item array returned by [[GridView::dataProvider]].
     * @return string the rendering result
     */
    public function renderDataCell($model, $key, $index)
    {
        $options = $this->getArrayOfOptions($this->contentOptions, $model, $key, $index);

        $expandableOptions = $this->getArrayOfOptions($this->expandableOptions, $model, $key, $index);
        $expandableOptions['data-row_id'] = $key;
        $expandableOptions['data-col_id'] = $this->grid->id . '-' . $this->getColumnID();
        $expandableOptions['class'] = $this->getExpandableElementClass()
            . (isset($expandableOptions['class']) ? " {$expandableOptions['class']}" : '');

        if ($this->submitData instanceof Closure) {
            $info = call_user_func($this->submitData, $model, $key, $index);
            $expandableOptions['data-info'] = Json::encode((array)$info);
        }

        return Html::beginTag('td', $options)
            . Html::beginTag('span', $expandableOptions)
            . $this->renderDataCellContent($model, $key, $index)
            . Html::endTag('span')
            . $this->getDataCellAfterValue($model, $key, $index)
            . Html::endTag('td');
    }

    public function registerJs()
    {
        if (Yii::$app->request->isAjax) {
            return;
        }

        $clientOptions = Json::encode([
            'ajaxUrl' => $this->url,
            'ajaxMethod' => $this->ajaxMethod,
            'ajaxErrorMessage' => $this->ajaxErrorMessage,
            'countColumns' => count($this->grid->columns),
            'enableCache' => (bool)$this->enableCache,
            'loadingIcon' => $this->loadingIcon,
        ]);

        $js = <<<JS
$(document).on('click', '#{$this->grid->id} .{$this->getExpandableElementClass()}', function() {
    var row = new ExpandRow({$clientOptions});
    row.run($(this));
});
JS;
        $this->grid->getView()->registerJs($js);
    }

    /**
     * Unique identifier of column
     *
     * @return string
     */
    protected function getColumnID()
    {
        if (empty($this->_columnID)) {
            $this->_columnID = md5(VarDumper::dumpAsString(get_object_vars($this), 5));
        }
        return $this->_columnID;
    }

    /**
     * @return string
     */
    protected function getExpandableElementClass()
    {
        return self::EXPANDABLE_BEGINNING_CLASS_NAME . '-' . $this->getColumnID();
    }

    /**
     * @param $options
     * @param $model
     * @param $key
     * @param $index
     * @return array
     */
    protected function getArrayOfOptions($options, $model, $key, $index)
    {
        if ($options instanceof Closure) {
            $options = call_user_func($options, $model, $key, $index, $this);
        }
        return (array)$options;
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return null|string
     */
    public function getDataCellAfterValue($model, $key, $index)
    {
        if ($this->afterValue !== null) {
            if (is_string($this->afterValue)) {
                $value = ArrayHelper::getValue($model, $this->afterValue);
            } else {
                $value = call_user_func($this->afterValue, $model, $key, $index, $this);
            }
            return $this->grid->formatter->format($value, $this->format);
        }
        return null;
    }
}
