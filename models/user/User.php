<?php   

namespace app\models\user;
   
use Yii;   
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
use yii\helpers\Url;

use app\models\register\Register;
use app\models\badge\UserBadgeData;
use app\models\edu\StudentGroup;
use app\models\name\{FirstName, LastName};
use app\models\notification\{
	NotificationSiteSettings, NotificationBotSettings
};
use app\models\helpers\{TextHelper, UserHelper};


class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{

	public static function tableName()
	{
		return 'user';
	}

	public function rules()
	{
		return [
			[['!user_id', '!register_id'], 'unique'],
			[['!user_id', '!status', '!register_id', '!role_id'], 'integer'],
			[['!authKey', '!accessToken'], 'string', 'max' => 256],
			[['!username', '!password', '!first_name', '!last_name'], 'string', 'max' => 50],
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
			'user_id'	=> Yii::t('app', 'Код'),
			'password'	=> Yii::t('app', 'Пароль'),
			'username'	=> Yii::t('app', 'Никнейм'),
			'status'	=> Yii::t('app', 'Статус'),
			'first_name'	=> Yii::t('app', 'Имя'),
			'last_name'	=> Yii::t('app', 'Фамилия'),
		];
	}


	public const GUEST_ID = 0;

	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = -1;

	public const ROLE_STUDENT = 1;
	public const ROLE_TEACHER = 2;
	public const ROLE_MODERATOR = 10;

	public static function findIdentity($id) {
		$user = self::findOne($id);
		if (empty($user))
			return null;
		return new static($user);
	}

	public static function findIdentityByAccessToken($token, $userType = null) {
		$user = self::find()->where(["accessToken" => $token])->one();
		if (empty($user))
			return null;
		return new static($user);
	}

	public static function findByUsername($username) {
		$user = self::find()->where(["username" => $username])->one();
		if (empty($user))
			return null;
		return new static($user);
	}

	public function findIdentityById($id) {
		$user = self::findOne($id);
		if (empty($user))
			return null;
		return new static($user);
	}

	public static function findByStudentEmail($student_email) {
		$user = self::find()
			->joinWith('register')
			->where(["student_email" => $student_email])
			->one();
		if (empty($user))
			return null;
		return new static($user);
	}

	public static function findByAtUsername(string $at_username) {
		if (!TextHelper::hasAt($at_username))
			return null;
		$username = mb_substr($at_username, 1);
		$user = self::find()->where(["username" => $username])->one();
		if (empty($user))
			return null;
		return $user;
	}

	public static function findForView($id) {
		$user = self::find()
			->joinWith('data.follow')
			->joinWith('register')
			->where(['user_id' => $id])
			->one();
		if (empty($user))
			return null;
		return $user;
	}

	public static function findByRegister($id) {
		$user = self::find()->where(["register_id" => $id])->one();
		if (empty($user))
			return null;
		return $user;
	}

	public function getRegister()
	{
		return $this->hasOne(Register::class, ['id' => 'register_id']);
	}

	public function getData()
	{
		return $this->hasOne(UserData::class, ['user_id' => 'user_id']);
	}

	public function getLimit()
	{
		return $this->hasOne(UserLimit::class, ['user_id' => 'user_id']);
	}

	public function getId() {
		return $this->user_id;
	}

	public function getAuthKey() {
		return $this->authKey;
	}

	public function getName() {
		return $this->first_name . ' ' . $this->last_name;
	}

	public function getShortname() {
		return $this->first_name[0] . $this->last_name;
	}

	public function validateAuthKey($authKey) {
		return $this->authKey === $authKey;
	}

	public function validatePassword($password) {
		return $this->password === $this->encrypt_password($password);
	}

	public function encrypt_password($password) {
		return sha1(sha1($password));
	}

	public function createUserData(int $register_id)
	{
		if (empty($this->findByRegister($register_id))) {
			$this->register_id = $register_id;
			$this->status = static::STATUS_ACTIVE;
			$this->password = $this->createPassword();
			$this->username = $this->createLogin();
		} else {
			if ($this->register->isCreated()) {
				$this->password = $this->createPassword();
			}
		}
	}

	public function createDataRecords()
	{
		$user_data = new UserData(['user_id' => $this->user_id]);
		$group = StudentGroup::findByName($this->register->group_name);
		if ($group) {
			$user_data->group_id = $group->group_id;
		}
		$user_data->save();
		$user_data->createAvatar();
		$user_limit = new UserLimit(['user_id' => $this->user_id]);
		$user_limit->save();
		$user_badge_data = new UserBadgeData(['user_id' => $this->user_id]);
		$user_badge_data->save();
		$user_badge_data->checkRegister();
		$notification_site_settings = new NotificationSiteSettings(['user_id' => $this->user_id]);
		$notification_site_settings->save();
		$notification_bot_settings = new NotificationBotSettings(['user_id' => $this->user_id]);
		$notification_bot_settings->save();
	}

	public function createLogin()
	{
		$unique_only = true;

		$first_name_ids = FirstName::getFreeIds();
		if (empty($first_name_ids)) {
			$unique_only = false;
			$first_name_ids = FirstName::getAllIds();
		}
		$fn_id = random_int(0, count($first_name_ids) - 1);
		$first_name_id = $first_name_ids[$fn_id];
		$fn_model = FirstName::findOne($first_name_id);
		if ($unique_only) {
			$fn_model->setTaken();
		}
		$this->first_name = $fn_model->first_name;

		if ($unique_only) {
			$last_name_ids = LastName::getFreeIds();
		} else {
			$last_name_ids = LastName::getFreeFromFirstNameIds($this->first_name);
		}
		$ln_id = random_int(0, count($last_name_ids) - 1);
		$last_name_id = $last_name_ids[$ln_id];
		$ln_model = LastName::findOne($last_name_id);
		if ($unique_only) {
			$ln_model->setTaken();
		}
		$this->last_name = $ln_model->last_name;

		$username = $this->first_name . '_' . $this->last_name;
		return mb_strtolower($username);
	}

	public function createPassword()
	{
		$alphabet = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'));
		$alphabet = implode($alphabet);
		$pass = [];
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < 10; $i++) {
			$n = random_int(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		$pass = implode($pass);
		return  $pass;
	}

	public function isGuest(): bool
	{
		return ($this->user_id == static::GUEST_ID);
	}

	public function isActive(): bool
	{
		return ($this->status == static::STATUS_ACTIVE);
	}

	public function isInactive(): bool
	{
		return ($this->status == static::STATUS_INACTIVE);
	}

	public function isStudent(): bool
	{
		return ($this->role_id == static::ROLE_STUDENT);
	}

	public function isTeacher(): bool
	{
		return ($this->role_id == static::ROLE_TEACHER);
	}

	public function isModerator(): bool
	{
		return ($this->role_id == static::ROLE_MODERATOR);
	}

	public function getAtUsername()
	{
		return '@' . $this->username;
	}

	public function getPageLink(bool $scheme = false, string $tab = 'about')
	{
		$link = Url::to(['/user/view/', 'username' => $this->username], $scheme);
		if ($tab) {
			$link .= '/' .$tab;
		}
		return $link;
	}

	public function getAvatar(string $type = UserHelper::SIZE_MD, bool $show_info = true)
	{
		return UserHelper::getAvatar(
			$type,
			$this->data->isOnline,
			$show_info,
			$this->data->getAvatarLink(),
			$this->getPageLink(),
			$this->username
		);
	}

	public function canQuestion()
	{
		$can = false;
		if ($this->data->can_write_questions) {
			$can = true;
		} else {
			$can = $this->limit->checkCanQuestion();
			if ($can) {
				$this->data->setCanWriteQuestions();
			}
		}
		return $can;
	}

	public function canAnswer()
	{
		$can = false;
		if ($this->data->can_write_answers) {
			$can = true;
		} else {
			$can = $this->limit->checkCanAnswer();
			if ($can) {
				$this->data->setCanWriteAnswers();
			}
		}
		return $can;
	}

	public function canComment()
	{
		$can = false;
		if ($this->data->can_write_comments) {
			$can = true;
		} else {
			$can = $this->limit->checkCanComment();
			if ($can) {
				$this->data->setCanWriteComments();
			}
		}
		return $can;
	}

	public function getRateLimit($request, $action)
	{
		$window_seconds = 10;
		return [$this->data->rate_limit, $window_seconds];
	}

	public function loadAllowance($request, $action)
	{
		return [$this->data->allowance, strtotime($this->data->allowance_updated_at)];
	}

	public function saveAllowance($request, $action, $allowance, $timestamp)
	{
		$this->data->saveAllowance($allowance, $timestamp);
	}
}
