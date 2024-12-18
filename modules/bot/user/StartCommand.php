<?php

namespace app\modules\bot\user;

use Yii;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\{
	Keyboard, KeyboardButton, InlineKeyboard, InlineKeyboardButton
};

use app\modules\bot\BaseUserCommand;


class StartCommand extends BaseUserCommand
{

	protected $name = 'start';
	protected $description = 'Начальная команда';
	protected $usage = '/start';

	public const COMMAND_TEXT = 'Начать сначала';
	public const COMMAND_NAME = 'start';


	public function execute(): ServerResponse
	{
		if (!$this->checkCommandAccess()) {
			return Request::emptyResponse();
		}

		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$message_id = $message->getMessageId();

		$this->getConversation($chat_id, true)->stop();
		$this->getConversation($chat_id);

		$text = self::getGeneralMessage();
		$keyboard = new Keyboard([]);

		$keyboard->addRow(
			['text' => CommandsCommand::COMMAND_TEXT]
		);
		$keyboard->addRow(
			['text' => HelpCommand::COMMAND_TEXT]
		);
		$keyboard->addRow(
			['text' => static::COMMAND_TEXT]
		);
		$keyboard = $keyboard->setResizeKeyboard(true)->setOneTimeKeyboard(true)->setSelective(true);
		return $this->replyToUser($text, ['reply_markup' => $keyboard]);
	}

	public function getGeneralMessage(): string
	{
		return "/start - чтобы начать сначала\n".
			"/help - если Вам нужна помощь\n".
			"/commands - посмотреть список комманд";
	}

	/*public function execute(): ServerResponse
	{
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		$message_id = $message->getMessageId();
		$command = trim($message->getText(true));

		$commands = array_filter($this->telegram->getCommandsList(), function ($command) {
			return !$command->isSystemCommand() && $command->isEnabled();
		});

		if ($command === '') {
			$text = $this->telegram->getBotName() . ' v. ' . $this->telegram->getVersion() . "\n\n";
			$text .= 'Commands List:' . "\n";
			foreach ($commands as $command) {
				$text .= '/' . $command->getName() . ' - ' . $command->getDescription() . "\n";
			}
			$text .= "\n" . 'For exact command help type: /help <command>';
		} else {
			$command = str_replace('/', '', $command);
			if (isset($commands[$command])) {
				$command = $commands[$command];
				$text = 'Command: ' . $command->getName() . ' v' . $command->getVersion() . "\n";
				$text .= 'Description: ' . $command->getDescription() . "\n";
				$text .= 'Usage: ' . $command->getUsage();
			} else {
				$text = 'No help available: Command /' . $command . ' not found';
			}
		}
		$data = ['chat_id' => $chat_id, 'reply_to_message_id' => $message_id, 'text' => $text];
		return Request::sendMessage($data);

		// return Request::emptyResponse();
	}*/

}
