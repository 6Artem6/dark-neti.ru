<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;


class DisciplineIcon extends ActiveRecord
{

	public static function tableName()
	{
		return 'discipline_icon';
	}

	public function rules()
	{
		return [
			[['icon_id'], 'unique'],
			[['icon_id'], 'integer'],
			[['icon_name'], 'string'],
		];
	}

	public static function primaryKey()
	{
		return [
			'icon_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'icon_id' => Yii::t('app', 'Номер'),
			'icon_name' => Yii::t('app', 'Имя'),
		];
	}

	public function getImgLink()
	{
		return "/disciplines/" . $this->icon_name;
	}

	public static function getStandartLink()
	{
		return "/disciplines/discipline.png";
	}
}
