<?php

namespace app\models\register;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\Url;

use app\models\user\User;
use app\models\helpers\{RequestHelper, TextHelper};
use app\models\service\Bot;

use \DOMDocument;
use \DOMXpath;


class Register extends ActiveRecord
{

	public static function tableName()
	{
		return 'register';
	}

	public function rules()
	{
		return [
			[['!id', /*'email',*/ 'student_email'], 'unique'],
			[['!id', '!status', '!reject_reason', '!attempt_count'], 'integer'],
			[['fio', 'group_name', /*'email',*/ 'student_email'], 'string', 'max' => 50],
			[['!verify_code'], 'string', 'max' => 100],
			[[/*'email',*/ 'student_email'], 'email'],
			[['birth_date'], 'date', 'format' => 'php:d.m.Y'],
			[['!register_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['fio', 'group_name'], 'filter', 'filter' => [TextHelper::class, 'remove_multiple_whitespaces']],

			['agreement', 'required', 'requiredValue' => 1, 'on' => [static::SCENARIO_CREATE],
				'message' => Yii::t('app', 'Необходимо согласиться с условиями и правилами')],
			[['fio', 'group_name', /*'email',*/ 'student_email', 'birth_date'], 'required',
				'message' => Yii::t('app', 'Необходимо заполнить данное поле')],
			// ['captcha', 'captcha'],
			// ['captcha', 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'id'
		];
	}

	public function attributeLabels()
	{
		return [
			'id'	=> Yii::t('app', 'Код'),
			'fio'	=> Yii::t('app', 'ФИО'),
			'group_name'	=> Yii::t('app', 'Группа'),
			'email'	=> Yii::t('app', 'Личная почта'),
			'student_email'	=> Yii::t('app', 'Корпоративная студенческая почта'),
			'birth_date'	=> Yii::t('app', 'Дата рождения'),
			'agreement'	=> Yii::t('app', 'Принимаю условия и правила сайта'),
			'captcha'	=> Yii::t('app', 'Я не робот'),
		];
	}

	public $agreement = 0;
	public $captcha;

	public const SCENARIO_CREATE = 'create';

	public const STATUS_WRONG_DATA = 0;
	public const STATUS_CREATED = 1;
	public const STATUS_VERIFIED = 2;
	public const STATUS_BLOCKED = -1;

	public const MAX_ATTEMPT_COUNT = 3;
	public const MAIL_DOMAIN = 'stud.nstu.ru';

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkBeforeInsert']);
		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkBeforeInsert']);

		return parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_CREATE => [
				'fio', 'group_name', /*'email',*/ 'student_email', 'birth_date',
				'!attempt_count', 'agreement', //'captcha'
			],
		]);
	}

	protected function checkBeforeValidate($event)
	{
		$this->birth_date = date("d.m.Y", strtotime($this->birth_date));
	}

	protected function checkBeforeInsert($event)
	{
		if (!$this->attempt_count) {
			$this->attempt_count = 1;
		}
		if (!$this->register_datetime) {
			$this->register_datetime = date("Y-m-d H:i:s");
		}
		$this->birth_date = date("Y-m-d", strtotime($this->birth_date));
	}

	public function getRejectReason()
	{
		return RegisterRejectReason::findOne($this->reject_reason);
	}

	public function findExists()
	{
		return self::find()
			// ->where(['fio' => $this->fio])
			// ->andWhere(['group_name' => $this->group_name])
			->andWhere(['student_email' => $this->student_email])
			->andWhere(['birth_date' => date("Y-m-d", strtotime($this->birth_date))])
			->one();
	}

	public function isRegistered()
	{
		return !empty(User::findByRegister($this->id));
	}

	public function hasAttempts()
	{
		return ($this->attempt_count <= static::MAX_ATTEMPT_COUNT);
	}

	public function hasRepeatMail()
	{
		return CronRegisterMail::find()
			->where(['register_id' => $this->id])
			->exists();
	}

	public function createCronMail()
	{
		$model = new CronRegisterMail;
		$model->register_id = $this->id;
		$model->repeat_datetime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
		$model->save();
	}

	public function checkUser(): bool
	{
		$student_email = explode('@', $this->student_email);
		if ($student_email[1] != static::MAIL_DOMAIN) {
			$this->setReasonWrongStudentMail();
			return false;
		}

		$helper = new RequestHelper;
		$helper->baseUrl = Yii::$app->params['idUrl'];
		$helper->url = "/user_lookup";
		$helper->method = "POST";
		$helper->data = [
			'find_user' => '',
			'fio' => $this->fio,
			'dob' => date("d.m.Y", strtotime($this->birth_date))
		];

		$content = $helper->getContent();
		$response_data = json_decode($content);

		if (!$response_data->user_found) {
			$this->setReasonNotFound();
			return false;
		}
		if ($response_data->user_group != 'student') {
			$this->setReasonNotStudent();
			return false;
		}
		if (mb_strtolower($response_data->fullname) != mb_strtolower($this->fio)) {
			$this->setReasonWrongFio();
			return false;
		}
		$this->fio = $response_data->fullname;
		$group = TextHelper::get_string_between($response_data->fullname_and_info, '(', ')');
		$group = mb_strtolower($group);
		$groups = explode(', ', $group);
		if (!in_array('гр. ' . mb_strtolower($this->group_name), $groups)) {
			$this->setReasonWrongGroup();
			return false;
		}

		// if (!$this->checkEmail($response_data->verification_html)) {
		// 	return false;
		// }

		return true;
	}

	protected function checkEmail($verification_html)
	{
		$document = new DOMDocument;
		$document->loadHTML($verification_html);
		$xpath = new DOMXpath($document);

		$email_string = $xpath->query("*/ul[@class='skinless-list']/li[2]/label")->item(0)->textContent;
		$string_parts = explode(' ', $email_string);
		$id_email = $string_parts[count($string_parts) - 1];
		$id_email = mb_strtolower($id_email);

		$email = explode('@', $this->email);
		for ($i = 3; $i < mb_strlen($email[0]); $i++) {
			$email[0][$i] = '*';
		}
		$email = implode('@', $email);

		if ($id_email !== $email) {
			$this->setReasonWrongMail();
			return false;
		}
		return true;
	}

	public function registerUser(): bool
	{
		$this->student_email = mb_strtolower($this->student_email);
		// $this->email = mb_strtolower($this->email);

		if (!$this->validate()) {
			return false;
		}
		if (!$this->checkUser()) {
			return false;
		}
		$this->createVerifyCode();
		$this->setStatusCreated();
		$this->save();
		$this->sendMailStudentMail();
		$this->sendRegisterCreated();
		return true;
	}

	public function newAttempt(): bool
	{
		$this->student_email = mb_strtolower($this->student_email);
		// $this->email = mb_strtolower($this->email);

		if (!$this->validate()) {
			return false;
		}
		if (!$this->checkUser()) {
			return false;
		}
		$this->createVerifyCode();
		$this->setNewAttempt();
		$this->save();
		// $this->sendMailStudentMail();
		$this->createCronMail();
		$this->sendRegisterNewAttempt();
		return true;
	}

	public function createUser(): bool
	{
		$user = new User;
		$check_user = $user->findByRegister($this->id);
		if (!empty($check_user)) {
			if (!$check_user->isActive()) {
				return false;
			}
			$user = $check_user;
		}
		$user->createUserData($this->id);
		$password = $user->password;
		$user->password = $user->encrypt_password($user->password);
		$user->save();
		$user->createDataRecords();
		$this->setStatusVerified();
		$this->save();
		$this->sendMailCredentials($user->username, $password);
		$this->sendRegisterVerified();
		return true;
	}

	protected function createRejectedRecord()
	{
		$record = new RegisterRejected;
		$record->load($this->attributes, '');
		$record->save();
	}

	protected function createVerifyCode()
	{
		$this->verify_code = Yii::$app->security->generateRandomString(100);
	}

	public function isWrongData(): bool
	{
		return ($this->status == static::STATUS_WRONG_DATA);
	}

	public function isCreated(): bool
	{
		return ($this->status == static::STATUS_CREATED);
	}

	public function isVerified(): bool
	{
		return ($this->status == static::STATUS_VERIFIED);
	}

	public function isBlocked(): bool
	{
		return ($this->status == static::STATUS_BLOCKED);
	}

	protected function setStatusWrongData()
	{
		$this->status = static::STATUS_WRONG_DATA;
	}

	protected function setStatusCreated()
	{
		$this->status = static::STATUS_CREATED;
	}

	protected function setStatusVerified()
	{
		$this->status = static::STATUS_VERIFIED;
	}

	protected function setStatusBlocked()
	{
		$this->status = static::STATUS_BLOCKED;
	}

	protected function setReasonNotFound()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_NOT_FOUND;
		$this->setStatusWrongData();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setReasonNotStudent()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_NOT_STUDENT;
		$this->setStatusBlocked();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setReasonWrongFio()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_WRONG_FIO;
		$this->setStatusWrongData();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setReasonWrongGroup()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_WRONG_GROUP;
		$this->setStatusWrongData();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setReasonWrongStudentMail()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_WRONG_EMAIL;
		$this->setStatusBlocked();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setReasonWrongMail()
	{
		$this->reject_reason = RegisterRejectReason::STATUS_WRONG_EMAIL;
		$this->setStatusWrongData();
		// $this->save();
		$this->createRejectedRecord();
	}

	protected function setNewAttempt()
	{
		$this->attempt_count++;
	}

	public function sendMailStudentMail()
	{
		$url = Url::to(['/site/verify', 'id' => $this->id, 'code' => $this->verify_code], true);
		$subject = Yii::t('app', 'Подтверждение регистрации в {name}', ['name' => Yii::$app->name]);
		$body = [];
		$body[] = Yii::t('app', 'Уважаемый(-ая) {fio}!', ['fio' => $this->fio]);
		$body[] = Yii::t('app', 'Для подтверждения, что Вы являетесь студентом, перейдите по следующей ссылке:');
		$body[] = Html::a(Yii::t('app', 'Ссылка'), $url) . '.';
		$body[] = Yii::t('app', 'Логин и пароль от аккаунта придут на эту же почту после подтверждения.');
		$body[] = $this->getMailFooter();
		$body = implode('<br>', $body);
		RequestHelper::composeMail($this->student_email, $subject, $body);
	}

	public function sendMailCredentials(string $username, string $password)
	{
		$url = Url::base(true);
		$link = Html::a($url, $url);
		$subject = Yii::t('app', 'Данные для входа на сайте {name}', ['name' => Yii::$app->name]);
		$body = [];
		$body[] = Yii::t('app', 'Уважаемый(-ая) {fio}!', ['fio' => $this->fio]);
		$body[] = Yii::t('app', 'Поздравляем! Подтверждение прошло успешно.');
		$body[] = Yii::t('app', ' Данные Вашей учётной записи для входа в {link}:', ['link' => $link]);
		$body[] = Yii::t('app', 'Логин: {username}', ['username' => $username]);
		$body[] = Yii::t('app', 'Пароль: {password}', ['password' => $password]);
		$body[] = $this->getMailFooter();
		$body = implode('<br>', $body);
		RequestHelper::composeMail($this->student_email, $subject, $body);
	}

	public function sendContinueRegistrationMail()
	{
		$url = Yii::$app->params['idUrl'];
		$subject = Yii::t('app', 'Подтверждение регистрации на сайте {name}', ['name' => Yii::$app->name]);
		$body = [];
		$body[] = Yii::t('app', 'Уважаемый(-ая) {fio}!', ['fio' => $this->fio]);
		$body[] = Yii::t('app', 'Для подтверждения, что Вы являетесь студентом, перейдите в студенческий почтовый ящик по следующей ссылке:');
		$body[] = Html::a(Yii::t('app', 'Ссылка'), $url) . '.';
		$body[] = Yii::t('app', 'Дальнейшие инструкции указаны в присланном письме.');
		$body[] = Yii::t('app', 'Приносим свои извинения за возникшие неудобства при регистрации.');
		$body[] = $this->getMailFooter();
		$body = implode('<br>', $body);
		RequestHelper::composeMail($this->student_email, $subject, $body);
	}

	protected function getMailFooter()
	{
		$link = Html::a(Yii::t('app', 'Поддержка'), Url::to(['/site/support'], true));
		$footer = [];
		$footer[] = Yii::t('app', 'Если у Вас возникли вопросы по работе в DARK-NETi или Вы ошиблись при вводе личных данных на этапе регистрации, обратитесь к нам в разделе «{link}»', ['link' => $link]);
		$footer[] = Yii::t('app', 'Благодарим за работу.');
		$footer[] = '————————————————————————————————————————————';
		$footer[] = Yii::t('app', 'Это сообщение отправлено в целях уведомления. Ответы на него не отслеживаются и не обрабатываются.');
		return implode('<br>', $footer);
	}

	protected function sendRegisterCreated()
	{
		$bot = new Bot;
		$bot->messageRegisterCreated($this->id);
	}

	protected function sendRegisterVerified()
	{
		$bot = new Bot;
		$bot->messageRegisterVerified($this->id);
	}

	protected function sendRegisterNewAttempt()
	{
		$bot = new Bot;
		$bot->messageRegisterNewAttempt($this->id);
	}

}
