<?php

namespace app\models\user;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\{ArrayHelper, Url};

use app\models\question\{Question, Answer, Comment};
use app\models\badge\{UserBadge, UserBadgeData};
use app\models\edu\StudentGroup;
use app\models\follow\FollowUser;
use app\models\helpers\{RequestHelper, UserHelper};
use app\models\notification\{
	UserChat,
	NotificationSiteSettings,
	NotificationBotSettings,
};
use \app\models\service\Bot;


class UserData extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_data';
	}

	public function rules()
	{
		return [
			[['user_id'], 'unique'],
			[['user_id', 'group_id', 'followers'], 'integer'],
			[['can_write_comments', 'can_write_answers', 'can_write_questions', 'theme', 'passed_tour'], 'boolean'],
			[['last_online'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['avatar'], 'string'],
			[['bot_code'], 'string', 'max' => 256],
			[['rate_sum'], 'double'],

			[['rate_limit', 'allowance'], 'integer'],
			[['allowance_updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'user_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app','Пользователь'),
		];
	}

	private $_is_followed = null;
	private $_is_self = null;

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public function getGroup()
	{
		return $this->hasOne(StudentGroup::class, ['group_id' => 'group_id']);
	}

	public function getFollow()
	{
		return $this->hasMany(FollowUser::class, ['user_id' => 'user_id']);
	}

	public function getBadgeData()
	{
		return $this->hasOne(UserBadgeData::class, ['user_id' => 'user_id']);
	}

	public function getBadges()
	{
		return $this->hasMany(UserBadge::class, ['user_id' => 'user_id']);
	}

	public function getChat()
	{
		return $this->hasOne(UserChat::class, ['user_id' => 'user_id']);
	}

	public function getSiteSettings()
	{
		return $this->hasOne(NotificationSiteSettings::class, ['user_id' => 'user_id']);
	}

	public function getBotSettings()
	{
		return $this->hasOne(NotificationBotSettings::class, ['user_id' => 'user_id']);
	}

	public function getId()
	{
		return $this->user_id;
	}

	public static function findByBotCode($bot_code)
	{
		$record = null;
		$bot_code = (string)$bot_code;
		if (!empty($bot_code)) {
			$record = self::find()
				->where(['bot_code' => $bot_code])
				->one();
		}
		return $record;
	}

	public function findIsFollowed()
	{
		$id = Yii::$app->user->identity->id;
		$list = ArrayHelper::index($this->follow, 'follower_id');
		$this->_is_followed = !empty($list[$id]);
	}

	public function findIsSelf()
	{
		$id = Yii::$app->user->identity->id;
		$this->_is_self = ($this->user_id == $id);
	}

	public function getIsFollowed(): bool
	{
		if (is_null($this->_is_followed)) {
			$this->findIsFollowed();
		}
		return $this->_is_followed;
	}

	public function getIsSelf(): bool
	{
		if (is_null($this->_is_self)) {
			$this->findIsSelf();
		}
		return $this->_is_self;
	}

	public function getLastTime(): string
	{
		return strtotime(date("Y-m-d H:i:s")) - strtotime($this->last_online);
	}

	public function getIsOnline(): bool
	{
		$online = false;
		if ($this->isSelf) {
			$online = true;
		} else {
			$time = $this->getLastTime();
			$min = 5 * 60;
			$online = ($time < $min);
		}
		return $online;
	}

	public function getOnlineSatus(bool $full = true): string
	{
		if ($this->getIsOnline()) {
			$text = UserHelper::getIsOnlineText($full);
		} else {
			$text = UserHelper::getWasOnlineText($this->last_online, $full);
		}
		return $text;
	}

	public function getAvatarPath(): string
	{
		return Yii::getAlias("@webroot/avatars/" . $this->avatar);
	}

	public function getAvatarLink(): string
	{
		return Url::to(Yii::getAlias("@web/avatars/" . $this->avatar));
	}

	public function createAvatar()
	{
		$name = $this->user->username;
		$format = 'png';
		$avatar = $name . '.' . $format;
		$this->avatar = $avatar;
		$this->save();
		$size = 512;

		$content = UserHelper::getAvatarImagick($name, $format, $size);
		file_put_contents($this->avatarPath, $content);
	}

	public function createBotCode()
	{
		if ($chat = $this->chat) {
			$chat->delete();
		}
		$this->bot_code = Yii::$app->security->generateRandomString(100);
		$this->save();
	}

	public function checkLastOnline()
	{
		$time = $this->getLastTime();
		$min = 60;
		if ($time > $min) {
			$this->last_online = date("Y-m-d H:i:s");
			$this->save();
		}
	}

	public function checkTour()
	{
		if (!$this->passed_tour) {
			$this->passed_tour = true;
			$this->save();
			$this->sendMessage();
		}
	}

	public function switchTheme()
	{
		$this->theme = !$this->theme;
		$this->save();
	}

	public function updateFollowers()
	{
		$this->followers = FollowUser::getFollowersCount($this->user_id);
		$this->save();
	}

	public function updateRateSum()
	{
		$this->rate_sum = UserRate::getRateSum($this->user_id);
		$this->save();
	}

	public function setCanWriteQuestions()
	{
		$this->can_write_questions = true;
		$this->save();
	}

	public function setCanWriteAnswers()
	{
		$this->can_write_answers = true;
		$this->save();
	}

	public function setCanWriteComments()
	{
		$this->can_write_comments = true;
		$this->save();
	}

	public function setCanNotWriteQuestions()
	{
		$this->can_write_questions = false;
		$this->save();
	}

	public function setCanNotWriteAnswers()
	{
		$this->can_write_answers = false;
		$this->save();
	}

	public function setCanNotWriteComments()
	{
		$this->can_write_comments = false;
		$this->save();
	}

	public function saveAllowance($allowance, $timestamp)
	{
		$this->allowance = $allowance;
		$this->allowance_updated_at = date('Y-m-d H:i:s', $timestamp);
		$this->save();
	}

	protected function sendMessage()
	{
		$bot = new Bot;
		$bot->messageLoggedin($this->id);
	}

}
