<?php

namespace app\modules\bot\system;

use Yii;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ServerResponse;

use app\modules\bot\BaseSystemCommand;


class GenericCommand extends BaseSystemCommand
{

    protected $name = 'generic';
    protected $description = 'Выполняется, когда команда не была найдена';
    protected $version = '1.1.0';


    public function execute(): ServerResponse
    {
        if (!$this->checkCommandAccess()) {
            return Request::emptyResponse();
        }

        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $reply = "Команда не найдена. Попробуйте воспользоваться командой '/help'.";
        return $this->replyToUser($reply);
    }

}
