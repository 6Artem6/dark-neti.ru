<?php

namespace app\modules\bot\system;

use Yii;
use yii\helpers\Html;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\{
	InlineKeyboard, ServerResponse
};

use app\modules\bot\BaseSystemCommand;
use app\modules\bot\admin\{
	QuestionsCommand, AnswersCommand, CommentsCommand,
	ErrorsCommand,
};

use app\models\question\{Question, Answer, Comment};
use app\models\user\{User};
use app\models\register\{Register, RegisterRejected};
use app\models\log\ErrorLogs;
use app\models\request\Support;


class NotificationsCommand extends BaseSystemCommand
{

	protected $name = 'notifications';
	protected $description = 'Отправка уведомлений';

	public const COMMAND_NAME = 'notifications';


	public function execute(): ServerResponse
	{
		return Request::emptyResponse();
	}

	public function sendNotification(int $chat_id, string $text, ?string $link = null): ServerResponse
	{
		$keyboard = null;
		if (!is_null($link)) {
			$keyboard = new InlineKeyboard([]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Просмотреть'),
				'url' => $link
			]);
			$keyboard = $keyboard->setResizeKeyboard(true)
				->setOneTimeKeyboard(true)
				->setSelective(true);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'reply_markup'	=> $keyboard
		]);
	}

	public function sendQuestion(int $chat_id, int $id = 0)
	{
		$text = Yii::t('app', 'Вопрос не был найден.');
		$keyboard = new InlineKeyboard([]);
		$record = Question::findOne($id);
		if ($record) {
			$keyboard->addRow([
				'text' => Yii::t('app', 'Перейти к вопросу'),
				'url' => $record->getRecordLink(true)
			]);
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Вопрос:'));
			$message_text[] = Html::tag('b', $record->question_title);
			if ($record->is_deleted) {
				$message_text[] = Yii::t('app', 'Статус: удалён');
			} elseif ($record->is_hidden) {
				$message_text[] = Yii::t('app', 'Статус: скрыт');
			}
			$message_text[] = $record->text;
			$message_text[] = "----------";
			$message_text[] = Yii::t('app', 'Опубликован: {datetime}', [
				'datetime' => $record->timeFull
			]);
			if ($record->isEdited()) {
				$message_text[] = Yii::t('app', 'Отредактирован: {datetime}', [
					'datetime' => $record->editedTimeFull
				]);
			}
			if ($record->isChecked()) {
				$message_text[] = Yii::t('app', 'Проверен: {datetime}', [
					'datetime' => $record->checkedTimeFull
				]);
			}
			if ($count = $record->fileCount) {
				$message_text[] = Yii::t('app', 'Файлов: {count}', [
					'count' => $count
				]);
			}
			if ($count = $record->answerCount) {
				$message_text[] = Yii::t('app', 'Ответов: {count}', [
					'count' => $count
				]);
				$keyboard->addRow([
					'text' => Yii::t('app', 'Показать ответы'),
					'callback_data' => json_encode([
						'command' => AnswersCommand::COMMAND_NAME,
						'action' => AnswersCommand::ACTION_ALL,
						'question' => $record->id,
						'page' => 0
					])
				]);
			}
			if ($count = $record->commentAllCount) {
				$message_text[] = Yii::t('app', 'Комментариев: {count}', [
					'count' => $count
				]);
				$keyboard->addRow([
					'text' => Yii::t('app', 'Показать комментарии'),
					'callback_data' => json_encode([
						'command' => CommentsCommand::COMMAND_NAME,
						'action' => CommentsCommand::ACTION_ALL,
						'question' => $record->id,
						'page' => 0
					])
				]);
			}

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

	public function sendAnswer(int $chat_id, int $id = 0)
	{
		$text = Yii::t('app', 'Ответ не был найден.');
		$keyboard = new InlineKeyboard([]);
		$record = Answer::findOne($id);
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Ответ:'));
			if ($record->is_deleted) {
				$message_text[] = Yii::t('app', 'Статус: удалён');
			} elseif ($record->is_hidden) {
				$message_text[] = Yii::t('app', 'Статус: скрыт');
			}
			$message_text[] = $record->text;
			$message_text[] = "----------";
			$message_text[] = Yii::t('app', 'Опубликован: {datetime}', [
				'datetime' => $record->timeFull
			]);
			if ($record->isEdited()) {
				$message_text[] = Yii::t('app', 'Отредактирован: {datetime}', [
					'datetime' => $record->editedTimeFull
				]);
			}
			if ($record->isChecked()) {
				$message_text[] = Yii::t('app', 'Проверен: {datetime}', [
					'datetime' => $record->checkedTimeFull
				]);
			}
			if ($count = $record->fileCount) {
				$message_text[] = Yii::t('app', 'Файлов: {count}', [
					'count' => $count
				]);
			}
			if ($count = $record->commentCount) {
				$message_text[] = Yii::t('app', 'Комментариев: {count}', [
					'count' => $count
				]);
				$keyboard->addRow([
					'text' => Yii::t('app', 'Показать комментарии'),
					'callback_data' => json_encode([
						'command' => CommentsCommand::COMMAND_NAME,
						'action' => CommentsCommand::ACTION_ALL,
						'answer' => $record->id,
						'page' => 0
					])
				]);
			}

			$text = implode("\n", $message_text);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Перейти к ответу'),
				'url' => $record->getRecordLink(true)
			]);
			$keyboard->addRow([
				'text' => Yii::t('app', 'Показать вопрос'),
				'callback_data' => json_encode([
					'command' => QuestionsCommand::COMMAND_NAME,
					'action' => QuestionsCommand::ACTION_RECORD,
					'id' => $record->question_id
				])
			]);
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

	public function sendComment(int $chat_id, int $id = 0)
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
			if ($record->isChecked()) {
				$message_text[] = Yii::t('app', 'Время проверки: {datetime}', [
					'datetime' => $record->checkedTimeFull
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
					'command' => QuestionsCommand::COMMAND_NAME,
					'action' => QuestionsCommand::ACTION_RECORD,
					'id' => $record->question_id
				])
			]);
			if ($record->isForAnswer) {
				$keyboard->addRow([
					'text' => Yii::t('app', 'Показать ответ'),
					'callback_data' => json_encode([
						'command' => AnswersCommand::COMMAND_NAME,
						'action' => AnswersCommand::ACTION_RECORD,
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

	public function sendSupport(int $chat_id, int $id): ServerResponse
	{
		$record = Support::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Обращение в поддержку'));
			$message_text[] = Yii::t('app', 'Тип обращения:');
			$message_text[] = Html::tag('b', $record->type->type_name);
			$message_text[] = Yii::t('app', 'Текст:');
			$message_text[] = $record->text;
			$message_text[] = Yii::t('app', 'Время обращения: {datetime}', [
				'datetime' => $record->support_datetime
			]);
			$message_text[] = Yii::t('app', 'Пользователь: {name}', [
				'name' => $record->senderName
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendError(int $chat_id, int $id): ServerResponse
	{
		$record = ErrorLogs::findOne($id);

		$text = Yii::t('app', 'Ошибка не была найдена.');
		$keyboard = new InlineKeyboard([]);
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Ошибка:'));
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
					'command' => ErrorsCommand::COMMAND_NAME,
					'action' => ErrorsCommand::ACTION_RECORD,
					'id' => $id
				])
			]);
		}
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

	public function sendMailError(int $chat_id): ServerResponse
	{
		$message_text = [];
		$message_text[] = Html::tag('i', Yii::t('app', 'Ошибка:'));
		$message_text[] = Html::tag('b', Yii::t('app', 'Почта не отправила письмо.'));

		$text = implode("\n", $message_text);
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendOcrError(int $chat_id): ServerResponse
	{
		$message_text = [];
		$message_text[] = Html::tag('i', Yii::t('app', 'Ошибка:'));
		$message_text[] = Html::tag('b', Yii::t('app', 'Закончились попытки распознавания текста.'));

		$text = implode("\n", $message_text);
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendRegisterCreated(int $chat_id, int $id): ServerResponse
	{
		$record = Register::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Пользователь прошёл форму регистрации'));
			$message_text[] = Yii::t('app', 'Время регистрации: {datetime}', [
				'datetime' => $record->register_datetime
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendRegisterNewAttempt(int $chat_id, int $id): ServerResponse
	{
		$record = Register::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Пользователь повторно прошёл форму регистрации'));
			$message_text[] = Yii::t('app', 'Время регистрации: {datetime}', [
				'datetime' => $record->register_datetime
			]);
			$message_text[] = Yii::t('app', 'Время повторной: {datetime}', [
				'datetime' => date('Y-m-d H:i:s')
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendRegisterVerified(int $chat_id, int $id): ServerResponse
	{
		$record = Register::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Пользователь подтвердил почту'));
			$message_text[] = Yii::t('app', 'Время подтверждения: {datetime}', [
				'datetime' => date('Y-m-d H:i:s')
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendLoggedin(int $chat_id, int $id): ServerResponse
	{
		$record = User::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Первый вход нового пользователя'));
			$message_text[] = Yii::t('app', 'Никнейм:');
			$message_text[] = Html::tag('b', $record->username);
			$message_text[] = Yii::t('app', 'Время входа: {datetime}', [
				'datetime' => date('Y-m-d H:i:s')
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}

	public function sendRegisterRejected(int $chat_id, int $id): ServerResponse
	{
		$record = RegisterRejected::findOne($id);

		$text = Yii::t('app', 'Запись не была найдена.');
		if ($record) {
			$message_text = [];
			$message_text[] = Html::tag('i', Yii::t('app', 'Неудачная регистрация'));
			$message_text[] = Yii::t('app', 'Причина:');
			$message_text[] = Html::tag('b', $record->reason_text);
			$message_text[] = Yii::t('app', 'Время регистрации: {datetime}', [
				'datetime' => $record->register_datetime
			]);

			$text = implode("\n", $message_text);
		}
		return Request::sendMessage([
			'chat_id'	=> $chat_id,
			'text'	=> $text,
			'parse_mode' => 'html',
		]);
	}
}
