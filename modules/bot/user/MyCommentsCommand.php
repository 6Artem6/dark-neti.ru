<?php

namespace app\modules\bot\user;

use Yii;
use yii\helpers\Html;

use Longman\TelegramBot\{Request};
use Longman\TelegramBot\Entities\{
	InlineKeyboard, ServerResponse
};

use app\modules\bot\BaseUserCommand;

use app\models\question\Comment;
use app\models\notification\UserChat;


class MyCommentsCommand extends BaseUserCommand
{

	protected $name = 'mycomments';
	protected $description = 'Список моих комментариев';
	protected $usage = '/mycomments';

	public const COMMAND_TEXT = 'Мои комментарии';
	public const COMMAND_NAME = 'mycomments';

	public const ACTION_ALL = 'all';
	public const ACTION_TODAY = 'today';
	public const ACTION_RECORD = 'record';


	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		$chat_id = 0;
		$message = $this->getMessage();
		if (!empty($message)) {
			$chat_id = $message->getChat()->getId();
		}
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
					$question_id = $query_data->question ?? 0;
					$answer_id = $query_data->answer ?? 0;
					return $this->sendList($chat_id, $page, $question_id, $answer_id);
				} elseif ($action == static::ACTION_TODAY) {
					$page = $query_data->page;
					return $this->sendList($chat_id, $page);
				} elseif ($action == static::ACTION_RECORD) {
					$id = $query_data->id;
					return $this->sendRecord($chat_id, $id);
				}
			}
		}
		return $this->sendMain($chat_id);
	}

	protected function sendMain(int $chat_id = 0)
	{
		$model = new Comment;
		$chat = UserChat::findByChat($chat_id);

		$message_text = [];
		$message_text[] = [
			'text' => 'Всего комментариев',
			'count' => $model->getAllUserCount($chat->user_id)
		];
		$today_count = $model->getTodayUserCount($chat->user_id);
		$message_text[] = [
			'text' => 'Комментариев за сегодня',
			'count' => $today_count
		];

		$text = '';
		foreach ($message_text as $t) {
			$text .= $t['text'] . ': ' . ($t['count'] ? $t['count'] : Yii::t('app', 'нет')) . "\n";
		}
		$keyboard = new InlineKeyboard([]);
		$keyboard->addRow([
			'text' => Yii::t('app', 'Показать все комментарии'),
			'callback_data' => json_encode([
				'command' => static::COMMAND_NAME,
				'action' => static::ACTION_ALL,
				'page' => 0
			])
		]);
		if ($today_count) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать комментарии за сегодня'),
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

	protected function sendList(int $chat_id, int $page = 0, int $question_id = 0, int $answer_id = 0)
	{
		$limit = 5;
		$offset = $page * $limit;
		$model = new Comment;
		$chat = UserChat::findByChat($chat_id);
		$count = $model->getAllUserCount($chat->user_id);
		$list = $model->getAllUserList($chat->user_id, $limit, $offset, $question_id, $answer_id);
		foreach ($list as $record) {
			$message_text = [];
			$message_text[] = $record->shortText;
			if ($record->is_deleted) {
				$message_text[] = Html::tag('i', Yii::t('app', 'Комментарий удалён'));
			} elseif ($record->is_hidden) {
				$message_text[] = Html::tag('i', Yii::t('app', 'Комментарий скрыт'));
			}
			$message_text[] = Yii::t('app', 'Время публикации: {datetime}', [
				'datetime' => $record->timeFull
			]);
			if ($record->isEdited()) {
				$message_text[] = Yii::t('app', 'Время редактирования: {datetime}', [
					'datetime' => $record->editedTimeFull
				]);
			}

			$text = implode("\n", $message_text);
			$keyboard = new InlineKeyboard([]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать комментарий подробнее'),
				'callback_data' => json_encode([
					'command' => static::COMMAND_NAME,
					'action' => static::ACTION_RECORD,
					'id' => $record->id
				])
			]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Перейти к комментарию'),
				'url' => $record->getRecordLink(true)
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
		$text = Yii::t('app', 'Комментарии: {begin}-{end} из {count}', [
			'begin' => $begin, 'end' => $end, 'count' => $count
		]);
		$keyboard = new InlineKeyboard([]);
		if ($offset != 0) {
			$button_data = [
				'command' => static::COMMAND_NAME,
				'action' => static::ACTION_ALL,
				'page' => ($page - 1)
			];
			if ($question_id) {
				$button_data['question'] = $question_id;
			} elseif ($answer_id) {
				$button_data['answer'] = $answer_id;
			}
			$keyboard->addRow([
				'text' => Yii::t('app', 'Предыдущие комментарии'),
				'callback_data' => json_encode($button_data)
			]);
		}
		if (($limit + $offset) < $count) {
			$button_data = [
				'command' => static::COMMAND_NAME,
				'action' => static::ACTION_ALL,
				'page' => ($page + 1)
			];
			if ($question_id) {
				$button_data['question'] = $question_id;
			} elseif ($answer_id) {
				$button_data['answer'] = $answer_id;
			}
			$keyboard->addRow([
				'text' => Yii::t('app', 'Следующие комментарии'),
				'callback_data' => json_encode($button_data)
			]);
		}
		if ($question_id) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Назад к вопросу'),
				'callback_data' => json_encode([
					'command' => MyQuestionsCommand::COMMAND_NAME,
					'action' => MyQuestionsCommand::ACTION_RECORD,
					'id' => $question_id
				])
			]);
		} elseif ($answer_id) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Назад к ответу'),
				'callback_data' => json_encode([
					'command' => MyAnswersCommand::COMMAND_NAME,
					'action' => MyAnswersCommand::ACTION_RECORD,
					'id' => $answer_id
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
		$text = Yii::t('app', 'Комментарий не был найден.');
		$keyboard = new InlineKeyboard([]);
		$record = Comment::findOne($id);
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Комментарий:'));
			if ($record->is_deleted) {
				$message_text[] = Yii::t('app', 'Статус: удалён');
			} elseif ($record->is_hidden) {
				$message_text[] = Yii::t('app', 'Статус: скрыт');
			}
			$message_text[] = $record->text;
			$message_text[] = "----------";
			$message_text[] = Yii::t('app', 'Время публикации: {datetime}', [
				'datetime' => $record->timeFull
			]);
			if ($record->isEdited()) {
				$message_text[] = Yii::t('app', 'Время редактирования: {datetime}', [
					'datetime' => $record->editedTimeFull
				]);
			}

			$text = implode("\n", $message_text);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Перейти к комментарию'),
				'url' => $record->getRecordLink(true)
			]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать вопрос'),
				'callback_data' => json_encode([
					'command' => MyQuestionsCommand::COMMAND_NAME,
					'action' => MyQuestionsCommand::ACTION_RECORD,
					'id' => $record->question_id
				])
			]);
			if ($record->isForAnswer) {
				$keyboard->addRow([
					'text' => Yii::t('app', 'Показать ответ'),
					'callback_data' => json_encode([
						'command' => MyAnswersCommand::COMMAND_NAME,
						'action' => MyAnswersCommand::ACTION_RECORD,
						'id' => $record->answer_id
					])
				]);
			}
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
