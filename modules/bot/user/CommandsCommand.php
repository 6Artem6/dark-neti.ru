<?php

namespace app\modules\bot\user;

use Yii;
use Longman\TelegramBot\Entities\ServerResponse;

use app\modules\bot\BaseUserCommand;


class CommandsCommand extends BaseUserCommand
{

	protected $name = 'commands';
	protected $description = 'Список команд';
	protected $usage = '/commands';

	public const COMMAND_TEXT = 'Список команд';
	public const COMMAND_NAME = 'commands';


	public function execute(): ServerResponse
	{
		$message = $this->getMessage();
		$command = trim($message->getText(true));

		$commands = array_filter($this->telegram->getCommandsList(), function ($command) {
			return !$command->isSystemCommand() && $command->isEnabled();
		});

		if (($command === '') or ($command === static::COMMAND_TEXT)) {
			$text = $this->telegram->getBotUsername() . "\n\n";
			$text = 'Список команд:' . "\n";
			foreach ($commands as $command) {
				$text .= '/' . $command->getName() . ' - ' . $command->getDescription() . "\n";
			}
			$text .= "\n" . 'Для помощи по команде напишите: /commands <command>';
		} else {
			$command = str_replace('/', '', $command);
			if (isset($commands[$command])) {
				$command = $commands[$command];
				$text = 'Команда: ' . $command->getName() . "\n";
				$text .= 'Описание: ' . $command->getDescription() . "\n";
			} else {
				$text = 'Нет подсказок: Команда /' . $command . ' не найдена';
			}
		}
		return $this->replyToUser($text);
	}
}
