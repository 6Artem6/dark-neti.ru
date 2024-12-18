<?php

namespace app\models\badge;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\User;


class UserBadge extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_badge';
	}
 
	public function rules()
	{
		return [
			[['user_id', 'badge_id'], 'integer'],
			[['user_id', 'badge_id'], 'unique', 'targetAttribute' => ['user_id', 'badge_id']],
			[['is_unseen'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'user_id', 'badge_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'badge_id' => Yii::t('app', 'Достижение'),
			'user_id' => Yii::t('app', 'Пользователь'),
		];
	}


	public function getBadge()
	{
		return $this->hasOne(Badge::class, ['badge_id' => 'badge_id']);
	}

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public static function getUserBadges(int $user_id)
	{
		return self::find()
			->joinWith('badge')
			->where(['user_id' => $user_id])
			->indexBy('badge_id')
			->all();
	}

	public static function getUserBadgesUnseen(int $user_id)
	{
		$list = self::find()
			->joinWith('badge')
			->where(['user_id' => $user_id])
			->andWhere(['is_unseen' => true])
			->all();
		foreach ($list as $record) {
			$record->is_unseen = false;
			$record->save();
		}
		return $list;
	}

	public static function getUserBadgesByType(int $user_id, int $type_id)
	{
		return self::find()
			->joinWith('badge as badge')
			->where(['user_id' => $user_id])
			->andWhere(['badge.type_id' => $type_id])
			->orderBy(['badge.badge_level' => SORT_DESC])
			->all();
	}

	public static function getUserBadgeLevel(int $user_id, int $type_id)
	{
		$level = null;
		$list = static::getUserBadgesByType($user_id, $type_id);
		if (!empty($list)) {
			$level = $list[0]->badge->badge_level;
		}
		return $level;
	}

	public static function getUserBadgesImportant(int $user_id)
	{
		return self::find()
			->joinWith('badge as badge')
			->where(['user_id' => $user_id])
			->orderBy(['badge.badge_level' => SORT_ASC])
			->limit(6)
			->indexBy('badge_id')
			->all();
	}

	public static function getUserBadgeIds(int $user_id)
	{
		return self::find()
			->select('badge_id')
			->where(['user_id' => $user_id])
			->column();
	}

	public static function removeByUserTypeLevel(int $user_id, int $type_id, int $level)
	{
		$record = self::find()
			->joinWith('badge as badge')
			->where(['user_id' => $user_id])
			->andWhere(['badge.type_id' => $type_id])
			->andWhere(['badge.badge_level' => $level])
			->one();
		if ($record) {
			$record->delete();
		}
	}

}
