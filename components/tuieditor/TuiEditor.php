<?php

namespace app\components\tuieditor;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;


class TuiEditor extends InputWidget
{

	public array $pluginOptions = [];
	public ?string $triggerSelector = null;

	protected $varName = null;
	protected $divName = null;
	protected $val = null;

	public function run()
	{
		$output = '';
		$this->options = ArrayHelper::merge($this->options, ['style' => ['display' => 'none']]);
		if ($this->hasModel()) {
			$output .= Html::activeTextarea($this->model, $this->attribute, $this->options);
		} else {
			$output .= Html::textarea($this->name, $this->value, $this->options);
		}
		$output .= Html::tag('div', null, [
			'id' => $this->getDivName()
		]);
		$this->registerAssets();
		return $output;
	}

	protected function getVarName()
	{
		if (is_null($this->varName)) {
			$this->varName = Inflector::variablize('editor_' . $this->id);
		}
		return $this->varName;
	}

	protected function getDivName()
	{
		if (is_null($this->divName)) {
			$this->divName = 'editor_' . $this->id;
		}
		return $this->divName;
	}

	protected function getVal()
	{
		if (is_null($this->val)) {
			$this->val = $this->hasModel()
				? $this->model->{$this->attribute}
				: $this->value;
		}
		return $this->val;
	}

	protected function registerAssets()
	{
		$view = $this->getView();
		TuiEditorAsset::register($view);
		$var = $this->getVarName();
		$options = $this->getPluginOptions();
		$script = new JsExpression("var {$var} = new toastui.Editor({$options});");
		if ($this->triggerSelector) {
			$selector = $this->triggerSelector;
			$script = new JsExpression(<<<JAVASCRIPT
				jQuery('body').on('editor_init', '{$selector}', function() {
					{$script}
				});
				jQuery('body').on('click', '{$selector}', function() {
					$(this).trigger('editor_init').off('editor_init');
				});
			JAVASCRIPT);
			$script = preg_replace('/\s+/', ' ', $script);
		}
		$view->registerJs($script);
	}

	protected function getPluginOptions()
	{
		$editor_id = $this->getDivName();
		$id = $this->options['id'];
		$var = $this->getVarName();
		$this->pluginOptions['el'] = new JsExpression("document.getElementById('{$editor_id}')");
		$this->pluginOptions['events']['change'] = new JsExpression("function() { jQuery('#{$id}').val({$var}.getMarkdown()) }");
		if ($val = $this->getVal()) {
			$this->pluginOptions['initialValue'] = $val;
		}
		$defaultOptions = [
			// 'plugins' => new JsExpression("[[Editor.plugin.codeSyntaxHighlight, { highlighter: Prism }]];")
			'language' => 'ru-RU',
			'height' => '450px',
			'initialEditType' => 'wysiwyg',
			'previewStyle' => 'vertical',
			'autofocus' => false,
			'viewer' => true,
			'theme' => 'light', // 'dark'
			'toolbarItems' => [
				['heading', 'bold', 'italic', 'strike'],
				['hr', 'quote'],
				['ul', 'ol', /*'task',*/ 'indent', 'outdent'],
				['table', /*'image',*/ 'link'],
				['code', 'codeblock'],
				// ['scrollSync'],
			]
		];
		$this->pluginOptions = array_replace_recursive($defaultOptions, $this->pluginOptions);
		return Json::encode($this->pluginOptions);
	}
}
