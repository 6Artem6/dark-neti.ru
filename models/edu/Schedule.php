<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;

use app\models\helpers\UserHelper;

class Schedule extends ActiveRecord
{

	public static function tableName()
	{
		return 'schedule';
	}

	public function rules()
	{
		return [
			[['record_id'], 'unique'],
			[['record_id', 'group_id', 'discipline_id', 'day',
				'week_start', 'week_end', 'week_offset', 'semestr', 'year'], 'integer'],
			[['group_id', 'discipline_id', 'day', 'week_start', 'week_end', 'week_offset', 'semestr', 'year'], 'unique',
				'targetAttribute' => ['group_id', 'discipline_id', 'day', 'week_start', 'week_end', 'week_offset', 'semestr', 'year']],
		];
	}

	public static function primaryKey()
	{
		return [
			'record_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'record_id' => Yii::t('app', 'Запись'),
		];
	}

	public function getDiscipline()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id']);
	}

	public static function getGroupCurrentWeekSchedule(int $group_id)
	{
		$result = [];
		$year = UserHelper::getYear();
		$semestr = UserHelper::getSemestr();
		$week = UserHelper::getWeek();
		$day = date('w');
		$schedule = self::find()
			->innerJoinWith('discipline.chair')
			->where(['group_id' => $group_id])
			->andWhere(['year' => $year])
			->andWhere(['semestr' => $semestr])
			->orderBy(['day' => SORT_ASC])
			->asArray()
			->all();
		if (!empty($schedule)) {
			foreach ($schedule as $item) {
				$w = floor($week / $item['week_offset']);
			    $item_week = $item['week_start'] - 1 + $w * $item['week_offset'];
			    $item['week'] = $item_week;
			    if (($item_week == $week) and ($day >= $item['day'])) {
			    	$result[] = $item;
			    } elseif (($item_week == $week + 1) and ($day < $item['day'])) {
			    	$result[] = $item;
			    }
			}
		}
		return $result;
	}
}
