<?php

namespace app\modules\bot\system;

use Yii;
use Longman\TelegramBot\{Request};
use Longman\TelegramBot\Entities\{ServerResponse};

use app\modules\bot\BaseSystemCommand;
use app\modules\bot\admin\{
	QuestionsCommand, AnswersCommand, CommentsCommand,
	SupportCommand, ErrorsCommand
};
use app\modules\bot\user\{
	MyQuestionsCommand, MyAnswersCommand, MyCommentsCommand
};


class CallbackqueryCommand extends BaseSystemCommand
{

	protected static $callbacks = [];
	protected $name = 'callbackquery';
	protected $description = 'Отвечает на callback-запросы';

	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		// Call all registered callbacks.
		// foreach (self::$callbacks as $callback) {
		// 	$answer = $callback($callback_query);
		// }

		$callback_query = $this->getCallbackQuery();
		$query_data     = $callback_query->getData();
		$query_data = json_decode($query_data);

		if (!empty($query_data->command)) {
			$command_name = $query_data->command;
			$command_list = [
				MyQuestionsCommand::COMMAND_NAME,
				MyAnswersCommand::COMMAND_NAME,
				MyCommentsCommand::COMMAND_NAME,
				QuestionsCommand::COMMAND_NAME,
				AnswersCommand::COMMAND_NAME,
				CommentsCommand::COMMAND_NAME,
				SupportCommand::COMMAND_NAME,
				ErrorsCommand::COMMAND_NAME,
			];
			if (in_array($command_name, $command_list)) {
				return $this->telegram->executeCommand($command_name);
			}
		}
		return Request::emptyResponse();
	}

	public static function addCallbackHandler($callback): void
	{
		self::$callbacks[] = $callback;
	}

}
