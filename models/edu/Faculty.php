<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

use app\models\helpers\ModelHelper;


class Faculty extends ActiveRecord
{

	public static function tableName()
	{
		return 'faculty';
	}

	public function rules()
	{
		return [
			[['faculty_id'], 'unique'],
			[['faculty_id'], 'integer'],
			[['faculty_fullname', 'faculty_shortname'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'faculty_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'faculty_id'     => Yii::t('app', 'Факультет'),
			'faculty_fullname'  => Yii::t('app', 'Факультет'),
			'faculty_shortname'  => Yii::t('app', 'Факультет'),
		];
	}

	public function getGroups()
	{
		return $this->hasMany(StudentGroup::class, ['faculty_id' => 'faculty_id']);
	}

	public function getRecordLink()
	{
		return ModelHelper::getSearchParamLink('faculty_id', $this->faculty_id);
	}

	public static function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['faculty_shortname'])
			->orderBy('faculty_shortname')
			->indexBy('faculty_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все факультеты');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

}
