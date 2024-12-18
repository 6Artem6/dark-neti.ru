<?php

namespace app\models\service;

use Yii;
use yii\base\Model;
use yii\helpers\Url;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Exception\TelegramException;

use app\models\log\ErrorLogs;
use app\models\notification\UserChat;

use app\modules\bot\system\NotificationsCommand;

use \ReflectionClass;


class Bot extends Model
{

	public $telegram;
	protected $params;
	protected $user_list;

	public function init()
	{
		$this->params = Yii::$app->params['telegram'];
		$this->user_list = UserChat::getUserIds();

		$bot_api_key	= $this->params['bot']['API_KEY'];
		$bot_username	= $this->params['bot']['USERNAME'];
		$this->telegram = new Telegram($bot_api_key, $bot_username);

		$this->telegram->setCommandsPaths([
			Yii::getAlias("@app/modules/bot/admin"),
			Yii::getAlias("@app/modules/bot/system"),
			Yii::getAlias("@app/modules/bot/user"),
		]);
		$this->telegram->enableMySql(
			$this->params['mysql'],
			$this->params['bot_prefix']
		);
		$this->telegram->enableAdmins( UserChat::getAdminIds() );
		// $this->telegram->setDownloadPath(Yii::getAlias("@webroot/tests/"));

		parent::init();
	}

	public function set()
	{
		$message = null;
		$hook_url = Url::to(['/api/bot/hook', 'secret_key' => $this->getSecret()], true);
		try {
			$result = $this->telegram->setWebhook($hook_url);
			if ($result->isOk()) {
				$message = $result->getDescription();
			}
		} catch (TelegramException $e) {
			$message = $e->getMessage();
		}
		return $message;
	}

	public function unset()
	{
		$message = null;
		try {
			$result = $this->telegram->deleteWebhook();
			if ($result->isOk()) {
				$message = $result->getDescription();
			}
		} catch (TelegramException $e) {
			$message = $e->getMessage();
		}
		return $message;
	}

	public function hook()
	{
		$message = null;
		try {
			$this->telegram->handle();
		} catch (TelegramException $e) {
			$message = $e->getMessage();
			$m = new ErrorLogs;
			$m->createFromException($e);
		}
		return $message;
	}

	public function checkSecret($secret = null)
	{
		return ($secret == $this->getSecret());
	}

	protected function getSecret()
	{
		return sha1(sha1($this->params['SECRET_KEY']));
	}

	protected function getNotificationCommand()
	{
		$rc = new ReflectionClass(NotificationsCommand::class);
		$filename = $rc->getFileName();
		return $this->telegram->getCommandObject(NotificationsCommand::COMMAND_NAME, $filename);
	}

	public function messageNotification(int $chat_id, string $text, ?string $link = null)
	{
		$command = $this->getNotificationCommand();
		$command->sendNotification($chat_id, $text, $link);
	}

	public function messageQuestion(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendQuestion($chat_id, $id);
		}
	}

	public function messageAnswer(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendAnswer($chat_id, $id);
		}
	}

	public function messageComment(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendComment($chat_id, $id);
		}
	}

	public function messageSupport(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendSupport($chat_id, $id);
		}
	}

	public function messageError(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendError($chat_id, $id);
		}
	}

	public function messageMailError()
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendMailError($chat_id);
		}
	}

	public function messageOcrError()
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendOcrError($chat_id);
		}
	}

	public function messageRegisterCreated(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendRegisterCreated($chat_id, $id);
		}
	}

	public function messageRegisterNewAttempt(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendRegisterNewAttempt($chat_id, $id);
		}
	}

	public function messageRegisterVerified(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendRegisterVerified($chat_id, $id);
		}
	}

	public function messageLoggedin(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendLoggedin($chat_id, $id);
		}
	}

	public function messageRegisterRejected(int $id)
	{
		$command = $this->getNotificationCommand();
		foreach ($this->telegram->getAdminList() as $chat_id) {
			$command->sendRegisterRejected($chat_id, $id);
		}
	}
}
