<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;


class ReportType extends ActiveRecord
{

	public static function tableName()
	{
		return 'report_type';
	}

	public function rules()
	{
		return [
			[['!type_id'], 'unique'],
			[['type_id'], 'integer'],
			[['type_name'], 'string'],
			[['is_other'], 'boolean'],
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
			'type_id'     => Yii::t('app','Тип обращения'),
			'record_type'  => Yii::t('app','Тип записи'),
			'type_name'  => Yii::t('app','Описание обращения'),
		];
	}

	public const TYPE_QUESTION = 1;
	public const TYPE_ANSWER = 2;
	public const TYPE_COMMENT = 3;

	public static function getQuestionList(bool $is_other = true)
	{
		$list = self::find()
			->select(['type_name'])
			->where(['record_type' => static::TYPE_QUESTION]);
		if (!$is_other) {
			$list = $list->andWhere(['is_other' => false]);
		}
		return $list->indexBy('type_id')->column();
	}

	public static function getAnswerList(bool $is_other = true)
	{
		$list = self::find()
			->select(['type_name'])
			->where(['record_type' => static::TYPE_ANSWER]);
		if (!$is_other) {
			$list = $list->andWhere(['is_other' => false]);
		}
		return $list->indexBy('type_id')->column();
	}

	public static function getCommentList(bool $is_other = true)
	{
		$list = self::find()
			->select(['type_name'])
			->where(['record_type' => static::TYPE_COMMENT]);
		if (!$is_other) {
			$list = $list->andWhere(['is_other' => false]);
		}
		return $list->indexBy('type_id')->column();
	}

	public static function getListsByType(bool $is_other = true)
	{
		$list = self::find();
		if (!$is_other) {
			$list = $list->where(['is_other' => false]);
		}
		$list = $list->indexBy('type_id')->asArray()->all();
		$result = [];
		foreach ($list as $type_id => $record) {
			$result[ $record['record_type'] ][ $type_id ] = $record['type_name'];
		}
		return $result;
	}

}
