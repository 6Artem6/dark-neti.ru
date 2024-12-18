<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\helpers\Url;
use yii\bootstrap5\Html;

use app\models\search\QuestionSearch;


class ModelHelper extends Model
{

	public const TYPE_ALL = 'all';
	public const TYPE_QUESTION = 'question';
	public const TYPE_ANSWER = 'answer';
	public const TYPE_COMMENT = 'comment';
	public const TYPE_DISCIPLINE = 'discipline';
	public const TYPE_TEACHER = 'teacher';
	public const TYPE_TAG = 'tag';

	public const TYPE_RECORD = 'record';
	public const TYPE_HISTORY = 'history';


	public static function getSelect2ClearButton(Model $model, string $attribute)
	{
		return [
			'prepend' => [
				'content' => Html::tag('span', null, [
					'class' => ['bi', 'bi-arrow-counterclockwise', 'text-gray'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Очистить поле'),
				]),
				'options' => [
					'class' => ['input-group-text', 'field-clear-select'],
				]
			],
		];
	}

	public static function getTextClearButton(Model $model, string $attribute)
	{
		return [
			'prepend' => [
				'content' => Html::tag('span', null, [
					'class' => ['bi', 'bi-arrow-counterclockwise', 'text-gray'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Очистить поле'),
				]),
				'options' => [
					'class' => ['input-group-text', 'field-clear-input'],
				]
			],
		];
	}

	public static function attributeJsonToArray(Model $model, string $attribute)
	{
		$field = $model->{$attribute};
		if (!is_array($field)) {
			$field = json_decode($field);
			$field = (array)$field;
		}
		foreach ($field as $key => $value) {
			if (empty($value)) {
				unset($field[$key]);
			}
		}
		$model->{$attribute} = $field;
	}

	public static function attributeArrayToJson(Model $model, string $attribute)
	{
		$field = $model->{$attribute};
		if (is_array($field)) {
			foreach ($field as $key => $value) {
				if (empty($value)) {
					unset($field[$key]);
				}
			}
			$field = json_encode($field);
		}
		$model->{$attribute} = $field;
	}

	public static function attributeListClear(Model $model, $attribute, int $max = 1)
	{
		if ($max < 1) {
			$max = 1;
		}
		$field = $model->{$attribute};
		if (is_array($field)) {
			foreach ($field as $k => $v) {
				if (empty($v)) {
					unset($field[$k]);
				}
			}
			$field = array_values($field);
			if (count($field) > $max) {
				$field = array_slice($field, 0, $max);
			}
			$model->{$attribute} = $field;
		}
	}

	public static function getSearchParamLink(string $param, $value)
	{
		$formName = static::getSearchFormName();
		return Url::to(['/question', $formName => [$param => $value]]);
	}

	public static function getSearchParamName(string $param)
	{
		return Html::getInputName((new QuestionSearch), $param);
	}

	public static function getSearchFormName()
	{
		return (new QuestionSearch)->formName();
	}

}
