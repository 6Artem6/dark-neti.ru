<?php

namespace app\models\log;

use Yii;
use yii\db\ActiveRecord;
use yii\log\Logger;
use yii\helpers\VarDumper;
use yii\web\HttpException;

use app\models\user\User;
use app\models\service\Bot;


class ErrorLogs extends ActiveRecord
{

	public static function tableName()
	{
		return 'error_logs';
	}

	public function rules()
	{
		return [
			[['!log_id'], 'unique'],
			[['log_id', 'level', 'user_id', 'error_count'], 'integer'],
			[['category'], 'string', 'max' => 256],
			[['prefix'], 'string', 'max' => 100],
			[['request_uri'], 'string', 'max' => 1024],
			[['message'], 'string', 'max' => pow(2, 16)],
			[['log_time', 'last_log_time'], 'date', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'log_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'log_id'	=> Yii::t('app', 'Номер ошибки'),
			'category'	=> Yii::t('app', 'Тип ошибки'),
			'message'	=> Yii::t('app', 'Описание ошибки'),
		];
	}

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);
		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'sendMessage']);

		parent::init();
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->isNewRecord) {
			$this->log_time = date('Y-m-d H:i:s');
			$this->last_log_time = date('Y-m-d H:i:s');
			$this->request_uri = Yii::$app->request->url;
			if (!Yii::$app->user->isGuest) {
				$this->user_id = Yii::$app->user->identity->id;
			} else {
				$this->user_id = 0;
			}
			$this->error_count = 1;
		}
		return true;
	}

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public function getId()
	{
		return $this->log_id;
	}

	public static function getAllCount()
	{
		return self::find()
			->where(['level' => Logger::LEVEL_ERROR])
			->count();
	}

	public static function getTodayCount()
	{
		return self::find()
			->where(['level' => Logger::LEVEL_ERROR])
			->andWhere(['>=', 'last_log_time', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public function getAllList(int $limit = 0, int $offset = 0)
	{
		$list = self::find()
			->where(['level' => Logger::LEVEL_ERROR])
			->orderBy(['last_log_time' => SORT_DESC])
			->indexBy('log_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		return $list->all();
	}

	public function create()
	{
		if ($this->needToLog()) {
			$this->validate();
			if ($record = $this->findSameRecord()) {
				$record->error_count++;
				$record->last_log_time = date('Y-m-d H:i:s');
				$record->save();
			} else {
				$this->save();
			}
		}
	}

	public function createFromException(\Exception $e)
	{
		$text = $e;
		if (!is_string($text)) {
			if (($text instanceof \Exception) or ($text instanceof \Throwable)) {
				$text = (string)$text;
			} else {
				$text = VarDumper::export($text);
			}
		}
		$this->level = Logger::LEVEL_ERROR;
		$this->category = get_class($e);
		$this->prefix = '-';
		$this->message = $text;
		$this->create();
	}

	protected function needToLog()
	{
		$need = true;
		$e_name = HttpException::class . ':404';
		if ($this->category == $e_name) {
			$need = false;
		}
		if ($this->level == Logger::LEVEL_INFO) {
			$need = false;
		}
		return $need;
	}

	protected function findSameRecord()
	{
		$record = self::find()
			->andWhere(['category' => $this->category])
			->andWhere(['request_uri' => $this->request_uri]);
		if ($this->level == Logger::LEVEL_ERROR) {
			$record = $record->andWhere(['message' => $this->message]);
		}
		return $record->one();
	}

	public function isDuplicated()
	{
		return ($this->error_count > 1);
	}

	public function sendMessage($event)
	{
		$bot = new Bot;
		$bot->messageError($this->id);
	}

}
