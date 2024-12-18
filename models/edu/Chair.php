<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

use app\models\helpers\ModelHelper;


class Chair extends ActiveRecord
{

	public static function tableName()
	{
		return 'chair';
	}

	public function rules()
	{
		return [
			[['chair_id'], 'unique'],
			[['chair_id', 'faculty_id'], 'integer'],
			[['chair_fullname', 'chair_shortname'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'chair_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'chair_id'     => Yii::t('app', 'Кафедра'),
			'chair_fullname'  => Yii::t('app', 'Кафедра'),
			'chair_shortname'  => Yii::t('app', 'Кафедра'),
		];
	}

	public function getFaculty()
	{
		return $this->hasMany(Faculty::class, ['faculty_id' => 'faculty_id']);
	}

	public static function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['chair_shortname'])
			->orderBy('chair_shortname')
			->indexBy('chair_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все Кафедры');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

}
