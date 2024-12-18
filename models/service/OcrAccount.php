<?php

namespace app\models\service;

use Yii;
use yii\db\ActiveRecord;
use yii\httpclient\Client;


class OcrAccount extends ActiveRecord
{

	public static function tableName()
	{
		return 'ocr_account';
	}

	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['day_attempt', 'month_attempt'], 'integer'],
			[['email', 'api_key'], 'string', 'max' => 256],
			[['day_begin_datetime', 'month_begin_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['is_active'], 'boolean'],
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
			'id' => Yii::t('app', '№ записи'),
		];
	}

	public const DAY_ATTEMPT_MAX = 3;
	public const MONTH_ATTEMPT_MAX = 100;

	public static function getActiveList()
	{
		return self::find()
			->where(['is_active' => true])
			->andWhere(['>', 'day_attempt', 0])
			->andWhere(['>', 'month_attempt', 0])
			->all();
	}

	public static function getActiveAccount()
	{
		return self::find()
			->where(['is_active' => true])
			->andWhere(['>', 'day_attempt', 0])
			->andWhere(['>', 'month_attempt', 0])
			->one();
	}

	public static function checkRequestAttempts()
	{
		$list = self::find()
			->where(['<', 'day_begin_datetime', date('Y-m-d H:i:s', strtotime('-24 hours'))])
			->andWhere(['<', 'day_attempt', static::DAY_ATTEMPT_MAX])
			->all();
		if ($list) {
			foreach ($list as $record) {
				$record->revertDayAttempts();
			}
		}
		$list = self::find()
			->where(['<', 'month_begin_datetime', date('Y-m-d H:i:s', strtotime('-31 days'))])
			->andWhere(['<', 'month_attempt', static::MONTH_ATTEMPT_MAX])
			->all();
		if ($list) {
			foreach ($list as $record) {
				$record->revertMonthAttempts();
			}
		}
	}

	public function setInactive()
	{
		$this->is_active = false;
		$this->save();
	}

	public function decAttempt()
	{
		$this->day_attempt--;
		$this->month_attempt--;
		$this->save();
	}

	public function revertDayAttempts(int $attempts = self::DAY_ATTEMPT_MAX)
	{
		if ($attempts < 0) {
			$attempts = 0;
		}
		$this->day_begin_datetime = date('Y-m-d H:i:s');
		$this->day_attempt = $attempts;
		$this->save();
	}

	public function revertMonthAttempts(int $attempts = self::MONTH_ATTEMPT_MAX)
	{
		if ($attempts < 0) {
			$attempts = 0;
		}
		$this->month_begin_datetime = date('Y-m-d H:i:s');
		$this->month_attempt = $attempts;
		$this->save();
	}

	public static function getTextFromImage(string $image_url)
	{
		$account = self::getActiveAccount();
		if (empty($account)) {
			static::sendMessage();
			return false;
		}
		$key = $account->api_key;

		$client = new Client;
		$client->baseUrl = Yii::$app->params['ocrUrl'];
		$url = '/process';
		$data = [
			'apiKey' => $key,
			'url' => $image_url,
		];
		$headers = [
			'Content-Type' => 'application/json'
		];
		$request = $client->get($url, $data, $headers);
		$response = $request->send();

		$result = null;
		if ($response->isOk) {
			$account->decAttempt();
			$content = json_decode($response->content);
			if (!empty($content->text)) {
				$result = $content->text;
			}
		}
		return $result;
	}

	private static function sendMessage()
	{
		$bot = new Bot;
		$bot->messageOcrError();
	}
}
