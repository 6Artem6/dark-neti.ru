<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;

use app\models\helpers\ModelHelper;


class QuestionType extends ActiveRecord
{

	public static function tableName()
	{
		return 'question_type';
	}

	public function rules()
	{
		return [
			[['!type_id'], 'unique'],
			[['type_id'], 'integer'],
			[['type_name'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'type_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'type_id' => Yii::t('app', 'Тип вопроса'),
			'type_name' => Yii::t('app', 'Тип вопроса'),
		];
	}

	public function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['type_name'])
			->indexBy('type_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все типы');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getRecordLink()
	{
		return ModelHelper::getSearchParamLink('type_id', $this->type_id);
	}

}
