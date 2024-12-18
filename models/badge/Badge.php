<?php

namespace app\models\badge;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\Url;


class Badge extends ActiveRecord
{

	public static function tableName()
	{
		return 'badge';
	}
 
	public function rules()
	{
		return [
			[['badge_id'], 'unique'],
			[['badge_id', 'badge_level', 'type_id'], 'integer'],
			[['file_name'], 'string', 'max' => 50],
			[['user_condition'], 'string', 'max' => 256],
			[['badge_level'], 'in', 'range' => self::getLevelTypeList()],
		];
	}

	public static function primaryKey()
	{
		return [
			'badge_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'badge_id' => Yii::t('app', 'Достижение'),
		];
	}

	public const NAME_ALL = 'badge';
	public const NAME_PLATINUM = 'badge-platinum';
	public const NAME_GOLD = 'badge-gold';
	public const NAME_SILVER = 'badge-silver';
	public const NAME_BRONZE = 'badge-bronze';

	public const LEVEL_PLATINUM = 0;
	public const LEVEL_GOLD = 1;
	public const LEVEL_SILVER = 2;
	public const LEVEL_BRONZE = 3;

	public const HAS_ALL_PLATINUM = 1;
	public const IS_REGISTERED_GOLD = 2;
	public const TOP_YEAR_GOLD = 3;

	public const ANSWER_GOLD = 4;
	public const COMMENT_GOLD = 5;
	public const QUESTION_GOLD = 6;
	public const LIKE_GOLD = 7;
	public const TOP_MONTH_GOLD = 8;
	public const REPORT_GOLD = 9;
	public const SUBSCRIPTION_GOLD = 10;

	public const ANSWER_SILVER = 11;
	public const COMMENT_SILVER = 12;
	public const QUESTION_SILVER = 13;
	public const LIKE_SILVER = 14;
	public const TOP_MONTH_SILVER = 15;
	public const REPORT_SILVER = 16;
	public const SUBSCRIPTION_SILVER = 17;

	public const ANSWER_BRONZE = 18;
	public const COMMENT_BRONZE = 19;
	public const QUESTION_BRONZE = 20;
	public const LIKE_BRONZE = 21;
	public const TOP_MONTH_BRONZE = 22;
	public const REPORT_BRONZE = 23;
	public const SUBSCRIPTION_BRONZE = 24;


	public function getType()
	{
		return $this->hasOne(BadgeType::class, ['type_id' => 'type_id']);
	}

	public function getUserBadge()
	{
		return $this->hasMany(UserBadge::class, ['badge_id' => 'badge_id']);
	}

	public static function getList()
	{
		return self::find()->all();
	}

	public static function getListByLevel()
	{
		$result = [];
		$list = self::find()
			->joinWith(['type'])
			->all();
		foreach ($list as $record) {
			$result[ $record->badge_level ][] = $record;
		}
		return $result;
	}

	public static function getListByType()
	{
		$result = [];
		$list = self::find()
			->joinWith(['type'])
			->all();
		foreach ($list as $record) {
			$result[ $record->type_id ][ $record->badge_level ] = $record;
		}
		return $result;
	}

	public static function getListForPlatinum()
	{
		return self::find()
			->select('badge_id')
			->where(['!=', 'badge_id', static::HAS_ALL_PLATINUM])
			->column();
	}

	public static function getLevelNames()
	{
		return [
			static::LEVEL_PLATINUM => Yii::t('app', 'Платина'),
			static::LEVEL_GOLD => Yii::t('app', 'Золото'),
			static::LEVEL_SILVER => Yii::t('app', 'Серебро'),
			static::LEVEL_BRONZE => Yii::t('app', 'Бронза'),
		];
	}

	public static function getRecordByType(int $type)
	{
		return self::findOne(['type_id' => $type]);
	}

	public static function getRecordByTypeAndLevel(int $type, int $level)
	{
		return self::findOne(['type_id' => $type, 'badge_level' => $level]);
	}

	public function getBadgePath()
	{
		return Yii::getAlias("@web/badges/" . $this->file_name);
	}

	public function getBadgeUrl()
	{
		return Url::to($this->getBadgePath());
	}

	public function isPlatinum()
	{
		return ($this->badge_level == static::LEVEL_PLATINUM);
	}

	public function isGold()
	{
		return ($this->badge_level == static::LEVEL_GOLD);
	}

	public function isSilver()
	{
		return ($this->badge_level == static::LEVEL_SILVER);
	}

	public function isBronze()
	{
		return ($this->badge_level == static::LEVEL_BRONZE);
	}

	public function getBadgeHtml()
	{
		return Html::img($this->badgeUrl, ['class' => ['rounded-circle', 'badge-layer_bottom']]);
	}

	public static function getLevel(int $count, int $type)
	{
		$level = null;
		$levels = static::getLevelList($type);
		$bronze = $levels[static::LEVEL_BRONZE];
		$silver = $levels[static::LEVEL_SILVER];
		$gold = $levels[static::LEVEL_GOLD];
		if (($count >= $bronze) and ($count < $silver)) {
			$level = static::LEVEL_BRONZE;
		} elseif (($count >= $silver) and ($count < $gold)) {
			$level = static::LEVEL_SILVER;
		} elseif ($count >= $gold) {
			$level = static::LEVEL_GOLD;
		}
		return $level;
	}

	public function getLevelCount()
	{
		$levels = static::getLevelList($this->type_id);
		$count = $levels[$this->badge_level];
		return $count;
	}

	public static function getLevelList(int $type)
	{
		return match ($type) {
			BadgeType::TYPE_QUESTION => static::getQuestionLevelList(),
			BadgeType::TYPE_ANSWER => static::getAnswerLevelList(),
			BadgeType::TYPE_COMMENT => static::getCommentLevelList(),
			BadgeType::TYPE_LIKE => static::getLikeLevelList(),
			BadgeType::TYPE_TOP_MONTH => static::getTopMonthLevelList(),
			BadgeType::TYPE_REPORT => static::getReportLevelList(),
			BadgeType::TYPE_SUBSCRIPTION => static::getSubscriptionLevelList(),

			BadgeType::TYPE_HAS_ALL => static::getHasAllLevelList(),
			BadgeType::TYPE_IS_REGISTERED => static::getIsRegisteredLevelList(),
			BadgeType::TYPE_TOP_YEAR => static::getTopYearLevelList(),
			default => null,
		};
	}

	public static function getLevelTypeList()
	{
		return [
			static::LEVEL_PLATINUM,
			static::LEVEL_GOLD,
			static::LEVEL_SILVER,
			static::LEVEL_BRONZE,
		];
	}

	public static function getHasAllLevelList()
	{
		return [
			static::LEVEL_PLATINUM => 1,
		];
	}

	public static function getIsRegisteredLevelList()
	{
		return [
			static::LEVEL_GOLD => 1,
		];
	}

	public static function getTopYearLevelList()
	{
		return [
			static::LEVEL_GOLD => 1,
		];
	}

	public static function getAnswerLevelList()
	{
		return [
			static::LEVEL_BRONZE => 1,
			static::LEVEL_SILVER => 10,
			static::LEVEL_GOLD => 50,
		];
	}

	public static function getCommentLevelList()
	{
		return [
			static::LEVEL_BRONZE => 50,
			static::LEVEL_SILVER => 200,
			static::LEVEL_GOLD => 500,
		];
	}

	public static function getQuestionLevelList()
	{
		return [
			static::LEVEL_BRONZE => 25,
			static::LEVEL_SILVER => 50,
			static::LEVEL_GOLD => 100,
		];
	}

	public static function getLikeLevelList()
	{
		return [
			static::LEVEL_BRONZE => 100,
			static::LEVEL_SILVER => 300,
			static::LEVEL_GOLD => 700,
		];
	}

	public static function getTopMonthLevelList()
	{
		return [
			static::LEVEL_BRONZE => 1,
			static::LEVEL_SILVER => 2,
			static::LEVEL_GOLD => 3,
		];
	}

	public static function getReportLevelList()
	{
		return [
			static::LEVEL_BRONZE => 1,
			static::LEVEL_SILVER => 10,
			static::LEVEL_GOLD => 50,
		];
	}

	public static function getSubscriptionLevelList()
	{
		return [
			static::LEVEL_BRONZE => 1,
			static::LEVEL_SILVER => 3,
			static::LEVEL_GOLD => 12,
		];
	}

}
