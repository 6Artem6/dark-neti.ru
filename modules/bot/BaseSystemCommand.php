<?php

namespace app\modules\bot;

use Longman\TelegramBot\Commands\SystemCommand;


abstract class BaseSystemCommand extends SystemCommand
{
	use BotCommandTrait;
}
