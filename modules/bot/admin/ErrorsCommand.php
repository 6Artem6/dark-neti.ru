<?php

namespace app\modules\bot\admin;

use Yii;
use yii\helpers\Html;
use Longman\TelegramBot\{Request};
use Longman\TelegramBot\Entities\{
	InlineKeyboard, ServerResponse
};

use app\modules\bot\BaseAdminCommand;

use app\models\log\ErrorLogs;


class ErrorsCommand extends BaseAdminCommand
{

	protected $name = 'errors';
	protected $description = 'Управление ошибками';
	protected $usage = '/errors';

	public const COMMAND_TEXT = 'Ошибки';
	public const COMMAND_NAME = 'errors';

	public const ACTION_ALL = 'all';
	public const ACTION_TODAY = 'today';
	public const ACTION_RECORD = 'record';


	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		$chat_id = 0;
		// $this->getConversation($chat_id, true)->stop();
		// $this->getConversation($chat_id);
		$callback_query = $this->getCallbackQuery();
		if (!empty($callback_query)) {
			$chat_id	= $callback_query->getFrom()->getId();
			$query_data	= $callback_query->getData();
			$query_data = json_decode($query_data);
			if (!empty($query_data->action)) {
				$action = $query_data->action;
				if ($action == static::ACTION_ALL) {
					$page = $query_data->page;
					return $this->sendList($chat_id, $page);
				} elseif ($action == static::ACTION_TODAY) {
					$page = $query_data->page;
					return $this->sendList($chat_id, $page);
				} elseif ($action == static::ACTION_RECORD) {
					$id = (int)$query_data->id;
					return $this->sendRecord($chat_id, $id);
				}
			}
		}
		return $this->sendMain($chat_id);
	}

	protected function sendMain(int $chat_id = 0)
	{
		$model = new ErrorLogs;

		$message_text = [];
		$message_text[] = [
			'text' => 'Всего ошибок',
			'count' => $model->getAllCount()
		];
		$today_count = $model->getTodayCount();
		$message_text[] = [
			'text' => 'Ошибок за сегодня',
			'count' => $today_count
		];

		$text = '';
		foreach ($message_text as $t) {
			$text .= $t['text'] . ': ' . ($t['count'] ? $t['count'] : Yii::t('app', 'нет')) . "\n";
		}

		$keyboard = new InlineKeyboard([]);
		$keyboard->addRow([
			'text' => Yii::t('app', 'Показать все ошибки'),
			'callback_data' => json_encode([
				'command' => static::COMMAND_NAME,
				'action' => static::ACTION_ALL,
				'page' => 0
			])
		]);
		if ($today_count) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать ошибки за сегодня'),
				'callback_data' => json_encode([
					'command' => static::COMMAND_NAME,
					'action' => static::ACTION_TODAY,
					'page' => 0
				])
			]);
		}
		$keyboard = $keyboard->setResizeKeyboard(true)
			->setOneTimeKeyboard(true)
			->setSelective(true);

		if (!empty($chat_id)) {
			Request::sendMessage([
				'chat_id'	=> $chat_id,
				'text'	=> $text,
				'reply_markup'	=> $keyboard
			]);
		} else {
			$this->replyToUser($text, ['reply_markup' => $keyboard]);
		}
		return Request::emptyResponse();
	}

	protected function sendList(int $chat_id, int $page = 0)
	{
		$limit = 5;
		$offset = $page * $limit;
		$model = new ErrorLogs;
		$count = $model->getAllCount();
		$list = $model->getAllList($limit, $offset);
		foreach ($list as $record) {
			$message_text = [];
			$message_text[] = Html::tag('b', $record->category);
			$message_text[] = Yii::t('app', 'Время появления: {datetime}', [
				'datetime' => $record->log_time
			]);
			if ($record->isDuplicated()) {
				$message_text[] = Yii::t('app', 'Время последнего повторения: {datetime}', [
					'datetime' => $record->last_log_time
				]);
				$message_text[] = Yii::t('app', 'Количество повторений: {datetime}', [
					'datetime' => $record->error_count
				]);
			}
			$message_text[] = Yii::t('app', 'Маршрут: {uri}', [
				'uri' => $record->request_uri
			]);

			$text = implode("\n", $message_text);
			$keyboard = new InlineKeyboard([]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать ошибку подробнее'),
				'callback_data' => json_encode([
					'command' => static::COMMAND_NAME,
					'action' => static::ACTION_RECORD,
					'id' => $record->id
				])
			]);
			$keyboard = $keyboard->setResizeKeyboard(true)
				->setOneTimeKeyboard(true)
				->setSelective(true);
			Request::sendMessage([
				'chat_id'	=> $chat_id,
				'text'	=> $text,
				'parse_mode' => 'html',
				'reply_markup'	=> $keyboard
			]);
		}

		$begin = ($offset + 1);
		$end = ($offset + $limit);
		if ($end > $count) {
			$end = $count;
		}
		$text = Yii::t('app', 'Ошибки: {begin}-{end} из {count}', [
			'begin' => $begin, 'end' => $end, 'count' => $count
		]);
		$keyboard = new InlineKeyboard([]);
		if ($offset != 0) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Предыдущие ошибки'),
				'callback_data' => json_encode([
					'command' => static::COMMAND_NAME,
					'action' => static::ACTION_ALL,
					'page' => ($page - 1)
				])
			]);
		}
		if (($limit + $offset) < $count) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Следующие ошибки'),
				'callback_data' => json_encode([
					'command' => static::COMMAND_NAME,
					'action' => static::ACTION_ALL,
					'page' => ($page + 1)
				])
			]);
		}
		$keyboard->addRow([
			'text' => Yii::t('app', 'Назад к управлению'),
			'callback_data' => json_encode([
				'command' => static::COMMAND_NAME
			])
		]);
		$keyboard = $keyboard->setResizeKeyboard(true)
			->setOneTimeKeyboard(true)
			->setSelective(true);
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
			'reply_markup'	=> $keyboard
		]);
	}

	public function sendRecord(int $chat_id, int $id = 0)
	{
		$text = Yii::t('app', 'Ошибка не была найдена.');
		$keyboard = new InlineKeyboard([]);
		$record = ErrorLogs::findOne($id);
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Ошибка:'));
			$message_text[] = Html::tag('b', $record->category);

			$message_text[] = $record->message;
			$message_text[] = "----------";
			$message_text[] = Yii::t('app', 'Время появления: {datetime}', [
				'datetime' => $record->log_time
			]);
			if ($record->isDuplicated()) {
				$message_text[] = Yii::t('app', 'Время последнего повторения: {datetime}', [
					'datetime' => $record->last_log_time
				]);
				$message_text[] = Yii::t('app', 'Количество повторений: {datetime}', [
					'datetime' => $record->error_count
				]);
			}
			if ($user = $record->user) {
				$message_text[] = Yii::t('app', 'Пользователь: {username}', [
					'username' => $user->username
				]);
			} else {
				$message_text[] = Yii::t('app', 'Пользователь: неавторизован');
			}
			$message_text[] = Yii::t('app', 'Маршрут: {uri}', [
				'uri' => $record->request_uri
			]);

			$text = implode("\n", $message_text);
			$keyboard = $keyboard->setResizeKeyboard(true)
				->setOneTimeKeyboard(true)
				->setSelective(true);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
			'reply_markup'	=> $keyboard
		]);
	}
}
