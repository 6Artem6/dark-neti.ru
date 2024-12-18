<?php

namespace app\models\helpers;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use yii\swiftmailer\Mailer;
use yii\web\HttpException;

use app\models\service\Bot;


class RequestHelper extends Model
{

	private string $baseUrl;
	private string $url;
	private array $data;
	private string $method;

	public function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function setBaseUrl(string $baseUrl)
	{
		return $this->baseUrl = $baseUrl;
	}

	public function setUrl(string $url)
	{
		return $this->url = $url;
	}

	public function setData(array $data)
	{
		return $this->data = $data;
	}

	public function setMethod(string $method)
	{
		return $this->method = $method;
	}

	public function init()
	{
		$this->baseUrl = Yii::$app->params['idUrl'];;
		$this->url = '/';
		$this->data = [];
		$this->method = 'GET';
		return parent::init();
	}

	public function createRequest()
	{
		$client = new Client();
		$client->baseUrl = $this->baseUrl;
		$request = $client->createRequest();
		$request->method = $this->method;
		$request->url = $this->url;
		$request->data = $this->data;
		$response = $request->send();
		return $response;
	}

	public function getContent()
	{
		$response = $this->createRequest();
		return ($response->isOk ? $response->content : null);
	}

	public static function composeMail($to, $subject, $body)
	{

		if(empty($to)){
			Yii::$app->session->setFlash('info', Yii::t('app','Не укзан отправитель.'));
			return false;
		}

		$mailer = new Mailer([
			'transport' => [
				'class' => 'Swift_SmtpTransport',
				'username' => Yii::$app->params['senderEmail'],
				'password' => Yii::$app->params['senderPassword'],
				'encryption' => 'ssl',
				'host' => Yii::$app->params['senderHost'],
				'port' => '465',
				// 'streamOptions' => [
				// 	'ssl' => [
				// 		'allow_self_signed' => true,
				// 		'verify_peer' => false,
				// 		'verify_peer_name' => false,
				// 	]
				// ]
			]
		]);

		$compose = $mailer->compose();
		$compose->from = [Yii::$app->params['senderEmail'] => Yii::$app->params['senderName']];
		$compose->to = $to;
		$compose->subject = $subject;
		$compose->htmlBody = $body;

		try {
			$r = $compose->send();
		} catch( \Swift_TransportException $e) {
			if ($e->getCode() == 550) {
				$text = Yii::t('app', 'Почтовый адрес не был найден.');
				throw new HttpException(403, $text);
			}
			static::sendMessage();
			$text = Yii::t('app', 'Не удалось отправить письмо. Текст Вашего письма:') . '<br>' . $body;
			// print_r($e->getCode());die;
			throw new HttpException(403, $text);
		}

		return true;
	}

	public static function sendMessage()
	{
		$bot = new Bot;
		$bot->messageMailError();
	}

}
