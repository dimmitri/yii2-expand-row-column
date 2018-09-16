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
    public $column_id;

    /**
     * @var string the route to call via AJAX to get the data from
     */
    public $url;

    /**
     * @var array|Closure the HTML attributes for the expandable element
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
     * @var string|Closure the text before the expandable element.
     */
    public $beforeValue;

    /**
     * @var bool whether to use the default theme
     */
    public $useDefaultTheme = true;

    /**
     * @var string
     */
    public $hideEffect = 'slideUp';

    /**
     * @var string
     */
    public $showEffect = 'slideDown';

    /**
     * @inheritdoc
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

        $this->registerClientScript();
    }

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        $options = $this->getArrayOfOptions($this->contentOptions, $model, $key, $index);

        return Html::beginTag('td', $options)
            . $this->getInnerContent($model, $key, $index)
            . Html::endTag('td');
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     *
     * @return string
     */
    protected function getInnerContent($model, $key, $index)
    {
        $expandableText = $this->renderDataCellContent($model, $key, $index);
        if (empty($expandableText) || $expandableText === $this->grid->formatter->nullDisplay) {
            return $expandableText;
        }

        return $this->getContentAroundExpandableElement($this->beforeValue, $model, $key, $index)
            . Html::beginTag('span', $this->getExpandableOptions($model, $key, $index))
            . $expandableText
            . Html::endTag('span')
            . $this->getContentAroundExpandableElement($this->afterValue, $model, $key, $index);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     *
     * @return array
     */
    protected function getExpandableOptions($model, $key, $index)
    {
        $expandableOptions = $this->getArrayOfOptions($this->expandableOptions, $model, $key, $index);
        $expandableOptions['data-row_id'] = $this->normalizeRowID($key);
        $expandableOptions['data-col_id'] = $this->getColumnID();
        $expandableOptions['class'] = $this->getExpandableElementClass()
            . (isset($expandableOptions['class']) ? " {$expandableOptions['class']}" : '');

        if ($this->submitData instanceof Closure) {
            $info = call_user_func($this->submitData, $model, $key, $index);
            $expandableOptions['data-info'] = (array)$info;
        } else {
            $expandableOptions['data-info'] = is_array($key) ? $key : ['id' => $key];
        }

        return $expandableOptions;
    }

    /**
     * @param $rowID
     *
     * @return string
     */
    protected function normalizeRowID($rowID)
    {
        if (is_array($rowID)) {
            $rowID = implode('', $rowID);
        }

        return trim(preg_replace("|[^\d\w]+|iu", '', $rowID));
    }

    /**
     * Registers the needed JavaScript
     */
    protected function registerClientScript()
    {
        if (Yii::$app->getRequest()->getIsAjax()) {
            return;
        }

        $clientOptions = Json::encode([
            'ajaxUrl' => $this->url,
            'ajaxMethod' => $this->ajaxMethod,
            'ajaxErrorMessage' => $this->ajaxErrorMessage,
            'countColumns' => count($this->grid->columns),
            'enableCache' => (bool)$this->enableCache,
            'loadingIcon' => $this->loadingIcon,
            'hideEffect' => $this->hideEffect,
            'showEffect' => $this->showEffect,
        ]);

        $js = <<<JS
jQuery(document).on('click', '#{$this->grid->getId()} .{$this->getExpandableElementClass()}', function() {
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
        if (empty($this->column_id)) {
            $this->column_id = md5(VarDumper::dumpAsString(get_object_vars($this), 5));
        }

        return $this->column_id;
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
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     *
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
     * @param string|Closure $value
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     *
     * @return string|null
     */
    protected function getContentAroundExpandableElement($value, $model, $key, $index)
    {
        if ($value !== null) {
            if (is_string($value)) {
                $value = ArrayHelper::getValue($model, $value);
            } else {
                $value = call_user_func($value, $model, $key, $index, $this);
            }
            if (!empty($value)) {
                return $this->grid->formatter->format($value, $this->format);
            }
        }

        return null;
    }
}
