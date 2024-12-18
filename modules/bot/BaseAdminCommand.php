<?php

namespace app\modules\bot;

use Longman\TelegramBot\Commands\AdminCommand;


abstract class BaseAdminCommand extends AdminCommand
{
	use BotCommandTrait;
}
