<?php

namespace app\modules\bot\system;

use Yii;
use Longman\TelegramBot\{Request, Telegram};
use Longman\TelegramBot\Entities\ServerResponse;

use app\modules\bot\BaseSystemCommand;
use app\modules\bot\admin\{
	QuestionsCommand, AnswersCommand, CommentsCommand,
	SupportCommand, ErrorsCommand
};
use app\modules\bot\user\{
	StartCommand, CommandsCommand,
	MyQuestionsCommand, MyAnswersCommand, MyCommentsCommand
};

use app\models\user\UserData;


class GenericmessageCommand extends BaseSystemCommand
{

	protected $name = Telegram::GENERIC_MESSAGE_COMMAND;
	protected $description = 'Принимает основные сообщения';
	protected $need_mysql = true;


	public function executeNoDb(): ServerResponse
	{
		if (self::$execute_deprecated && $deprecated_system_command_response = $this->executeDeprecatedSystemCommand()) {
			return $deprecated_system_command_response;
		}

		return Request::emptyResponse();
	}

	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();

		$text = trim($message->getText(true));
		$text = trim($text, '.');

		$command_list = [
			MyQuestionsCommand::COMMAND_NAME => MyQuestionsCommand::COMMAND_TEXT,
			MyAnswersCommand::COMMAND_NAME => MyAnswersCommand::COMMAND_TEXT,
			MyCommentsCommand::COMMAND_NAME => MyCommentsCommand::COMMAND_TEXT,
			QuestionsCommand::COMMAND_NAME => QuestionsCommand::COMMAND_TEXT,
			AnswersCommand::COMMAND_NAME => AnswersCommand::COMMAND_TEXT,
			CommentsCommand::COMMAND_NAME => CommentsCommand::COMMAND_TEXT,
			SupportCommand::COMMAND_NAME => SupportCommand::COMMAND_TEXT,
			CommandsCommand::COMMAND_NAME => CommandsCommand::COMMAND_TEXT,
			ErrorsCommand::COMMAND_NAME => ErrorsCommand::COMMAND_TEXT,
		];
		if (in_array($text, $command_list)) {
			// $c = $this->getConversation($chat_id, true);
			// if(!empty($c->getCommand())){
			// 	$c->stop();
			// }
			return $this->telegram->executeCommand(array_search($text, $command_list));
		}

		$data = UserData::findByBotCode($text);
		if (!empty($data) and ($data->chat->chat_id == $chat_id)) {
			$reply = "Бот активирован. Для просмотра команд наберите '/help'.";
			return $this->replyToUser($reply);
		}

		$reply = "Команда не найдена. Попробуйте воспользоваться командой '/help'.";
		return $this->replyToUser($reply);

		/*
		if(!is_null(StartCommand::getQueryDataByText($text))){
			$c = $this->getConversation($chat_id, true);
			if(!empty($c->notes['messages_to_delete'])){
				$this->deleteMessagesToDelete($chat_id, $c);
			}
			if(!empty($c->getCommand())){
				$c->stop();
			}
			return $this->telegram->executeCommand('callbackquery');
		}
		*/

		// Try to continue any active conversation.
		if ($active_conversation_response = $this->executeActiveConversation()) {
			return $active_conversation_response;
		}

		// Try to execute any deprecated system commands.
		if (self::$execute_deprecated && $deprecated_system_command_response = $this->executeDeprecatedSystemCommand()) {
			return $deprecated_system_command_response;
		}

		$reply = StartCommand::getGeneralMessage();
		return $this->replyToUser($reply);
	}
}