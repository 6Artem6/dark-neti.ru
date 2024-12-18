<?php

namespace app\modules\bot;

use Yii;
use Longman\TelegramBot\{Conversation, Request};
use Longman\TelegramBot\Entities\{Message, ServerResponse};

use app\models\user\UserData;
use app\models\notification\UserChat;

trait BotCommandTrait {

	public function checkCommandAccess()
	{
		$text = '';
		if (!empty($this->getCallbackQuery())) {
			$callback_query = $this->getCallbackQuery();
			$chat_id = $callback_query->getFrom()->getId();
		} elseif (!empty($this->getMessage())) {
			$message	= $this->getMessage();
			$chat_id	= $message->getChat()->getId();
			$text		= trim($message->getText(true));
		} else {
			return true;
		}
		$chat = UserChat::findByChat($chat_id);
		if (empty($chat)) {
			$data = UserData::findByBotCode($text);
			if (empty($data)) {
				return $this->closeBot($chat_id);
			}
			if (empty($data->chat)) {
				$chat = UserChat::createStudent($chat_id, $data->user_id);
			} elseif ($data->chat->chat_id != $chat_id) {
				return $this->closeBot($chat_id);
			}
		}
		if(!$chat->isActive()){
			return $this->closeBot($chat_id);
		}
		return true;
	}

	public function closeBot(int $chat_id)
	{
		$this->private_only = true;
		$text = Yii::t('app', 'Бот неактивен. Попробуйте позднее.');
		$this->replyToUser($text);
		return false;
	}

	public function getConversation(int $chat_id, bool $new=false): Conversation
	{
		return (new Conversation($chat_id, $chat_id, ($new ? '' : $this->getName())));
	}

	public function deleteMessage(int $chat_id, ?Message $message=null, int $message_id=0): ServerResponse
	{
		$message_id = (!empty($message) ? $message->getMessageId() : $message_id);
		$message_data = ['chat_id' => $chat_id];
		$message_data['message_id'] = $message_id;
		return Request::deleteMessage($message_data);
	}

	public function addMessageToDelete(Conversation $c, Message $message): void
	{
		if(empty($c->notes['messages_to_delete']) or !is_array($c->notes['messages_to_delete'])){
			$c->notes['messages_to_delete'] = [];
		}
		if(!in_array($message->getMessageId(), $c->notes['messages_to_delete'])){
			$c->notes['messages_to_delete'][] = $message->getMessageId();
			$c->update();
		}
	}

	public function deleteMessagesToDelete(int $chat_id, Conversation $c): void
	{
		if(!empty($c->notes['messages_to_delete']) and is_array($c->notes['messages_to_delete'])){
			foreach($c->notes['messages_to_delete'] as $message_id){
				$this->deleteMessage($chat_id, null, $message_id);
			}
			unset($c->notes['messages_to_delete']);
			$c->update();
		}
	}
}
