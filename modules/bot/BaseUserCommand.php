<?php

namespace app\modules\bot;

use Longman\TelegramBot\Commands\UserCommand;


abstract class BaseUserCommand extends UserCommand
{
	use BotCommandTrait;
}
