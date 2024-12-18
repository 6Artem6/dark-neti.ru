<?php
namespace app\widgets\notification;

use Yii;
use yii\bootstrap5\{Accordion, Html, Widget};
use yii\helpers\Url;

use app\models\notification\Notification;
use app\models\helpers\HtmlHelper;


class MessageList extends Widget
{

	public array $list;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if (empty($this->list)) {
			return null;
		}
		return true;
	}

	public function run()
	{
		$output = '';
		$items = [];
		$params_list = $this->getMessageLabelList();
		foreach ($params_list as $type_id => $label) {
			$expand = true;
			if (!empty($this->list[$type_id])) {
				$list = $this->list[$type_id];
				$label = Html::tag('span',
					HtmlHelper::getIconText($label, true) .
					HtmlHelper::getCountText(count($list), true)
				);
				$content = '';
				foreach ($list as $record) {
					$content .= Message::widget([
						'model' => $record
					]);
				}
				$items[] = [
					'label' => $label,
					'content' => $content,
					'expand' => $expand
				];
				$expand = false;
			}
		}
		return Accordion::widget([
			'items' => $items,
			'encodeLabels' => false,
			'options' => ['id' => 'messages-all']
		]);
	}

	protected function getMessageLabelList(): array
	{
		return [
			Notification::MY_QUESTION_ANSWER => Yii::t('app', 'Ответы на Ваши вопросы'),
			Notification::MY_QUESTION_COMMENT => Yii::t('app', 'Комментарии на вопросы'),
			Notification::MY_QUESTION_ANSWER_COMMENT => Yii::t('app', 'Комментарии на ответы к вопросам'),
			Notification::FOLLOWED_QUESTION_ANSWERED => Yii::t('app', 'Решённые вопросы'),
			Notification::FOLLOWED_USER_QUESTION_CREATE => Yii::t('app', 'Новые вопросы'),
			Notification::MENTION => Yii::t('app', 'Упоминания Вас'),
			Notification::INVITE_AS_EXPERT => Yii::t('app', 'Приглашения Вас в качестве эксперта'),
			Notification::MY_ANSWER_LIKE => Yii::t('app', 'Посчитали Ваши ответы полезными'),
			Notification::ANSWER_EDIT => Yii::t('app', 'Изменения вопросов и ответов, на которые Вы подписаны'),
			Notification::REPORT => Yii::t('app', 'Жалобы'),
		];
	}

}
