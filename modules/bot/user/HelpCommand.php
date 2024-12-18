<?php

namespace app\modules\bot\user;

use Yii;
use Longman\TelegramBot\{Request};
use Longman\TelegramBot\Entities\{
	InlineKeyboard, ServerResponse
};

use app\modules\bot\BaseUserCommand;


class HelpCommand extends BaseUserCommand
{

	protected $name = 'help';
	protected $description = 'Подсказка в трудных ситуациях';
	protected $usage = '/help';

	public const COMMAND_TEXT = 'Помощь';
	public const COMMAND_NAME = 'help';


	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		$text = "Вот что я умею:"."\n".
				"/start - начать"."\n".
				"/myquestions - управление вопросами"."\n".
				"/myanswers - управление ответами"."\n".
				"/mycomments - управление комментариями"."\n".
				// "/all_records - управление всеми записями сразу"."\n".
				"/help - если вам потребуется помощь";

		$keyboard = new InlineKeyboard([]);
		$keyboard->addRow([
			'text' => $this->telegram->getCommandObject(MyQuestionsCommand::COMMAND_NAME)->getDescription(),
			'callback_data' => json_encode([
				'command' => MyQuestionsCommand::COMMAND_NAME
			])
		]);
		$keyboard->addRow([
			'text' => $this->telegram->getCommandObject(MyAnswersCommand::COMMAND_NAME)->getDescription(),
			'callback_data' => json_encode([
				'command' => MyAnswersCommand::COMMAND_NAME
			])
		]);
		$keyboard->addRow([
			'text' => $this->telegram->getCommandObject(MyCommentsCommand::COMMAND_NAME)->getDescription(),
			'callback_data' => json_encode([
				'command' => MyCommentsCommand::COMMAND_NAME
			])
		]);
		/*$keyboard->addRow([
			'text' => $this->telegram->getCommandObject(AllRecordsCommand::COMMAND_NAME)->getDescription(),
			'callback_data' => json_encode([
				'command' => AllRecordsCommand::COMMAND_NAME
			])
		]);*/
		return $this->replyToUser($text, ['reply_markup' => $keyboard]);
	}
}


