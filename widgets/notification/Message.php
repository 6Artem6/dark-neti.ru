<?php
namespace app\widgets\notification;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\notification\Notification;
use app\models\helpers\{HtmlHelper, UserHelper};


class Message extends Widget
{

	public Notification $model;
	public bool $is_last_list = false;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->model->isNewRecord) {
			return false;
		}
		return true;
	}

	public function run()
	{
		$name = Html::a($this->model->author->name, $this->model->author->getPageLink());
		list($text, $discipline, $message, $link) = $this->getMessageParams();
		$div_id = 'notification' . '-' . ($this->is_last_list ? 'last' : 'all') . '-' . $this->model->notification_id;
		if ($this->is_last_list) {
			$class = [
				'block' => 'message-block-last',
				'message' => 'message-text-last',
				'time' => 'message-time-last',
			];
		} else {
			$class = [
				'block' => 'message-block',
				'message' => 'message-text',
				'time' => 'message-time',
			];
		}

		$output = "";
		$output .= Html::beginTag('div', [
			'id' => $div_id,
			'class' => ['rounded', 'bg-light', 'd-sm-flex', 'position-relative', 'border-0', $class['block']]
		]);
		$output .= Html::beginTag("div", ["class" => 'avatar avatar-sm']);
		if ($this->model->type_id !== Notification::REPORT) {
			$output .= $this->model->author->getAvatar(UserHelper::SIZE_SM, false);
		} else {
			$output .= Html::tag('span', null, ['bi', 'bi-exclamation-circle-fill', 'bi_icon']);
		}
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['mx-sm-2', 'my-2', 'my-sm-0']]);
		$output .= Html::beginTag('p', ['class' => ['small', 'text-secondary', $class['message']]]);
		$output .= Yii::t('app', $message, ['name' => $name, 'text' => $text, 'discipline' => $discipline]);
		$output .= Html::endTag('p');
		$output .= Html::beginTag('div', ['class' => ['d-flex']]);
		$output .= Html::a(Yii::t('app', 'Просмотреть'), $link, [
			'target' => '_blank',
			'class' => ['btn', 'btn-sm', 'btn-primary', 'py-1', 'me-2']
		]);

		$output .= HtmlHelper::actionButton(Yii::t('app', 'Удалить'),
			'remove-notification',
			$this->model->notification_id, [
			'class' => ['btn', 'btn-sm', 'btn-delete', 'btn-danger', 'py-1'],
			'data-div-id' => $div_id,
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'ms-auto']]);
		$output .= Html::beginTag('p', ['class' => ['small', 'text-secondary', $class['time']]]);
		$output .= Html::tag('span',
			$this->model->timeElapsed, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Уведомление получено: {datetime}', ['datetime' => $this->model->timeFull])
			]);
		$output .= Html::endTag('p');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getMessageParams(): array
	{
		$text = null;
		$discipline = null;
		$message = null;
		$link = null;
		if ($this->model->type_id == Notification::MY_QUESTION_ANSWER) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} дал ответ на Ваш вопрос \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_ANSWER) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} дал ответ на вопрос, на который Вы подписаны, \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_USER_ANSWER) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, дал ответ на вопрос \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::MY_QUESTION_COMMENT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} оставил комментарий к Вашему вопросу \"{text}\".";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_COMMENT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} оставил комментарий к вопросу \"{text}\", на который Вы подписаны.";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_COMMENT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} оставил комментарий к вопросу \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $this->model->comment?->getRecordLink();
			$discipline = $this->model->question?->discipline?->name;
		} elseif ($this->model->type_id == Notification::MY_QUESTION_ANSWER_COMMENT) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на Ваш вопрос.";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_ANSWER_COMMENT) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} оставил комментарий к ответу на вопрос \"{text}\", на который Вы подписаны.";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_MY_ANSWER_COMMENT) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на вопрос, на который Вы подписаны.";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} оставил комментарий к Вашему ответу \"{text}\" на вопрос по предмету {discipline}, на который Вы подписаны.";
			$link = $this->model->comment?->getRecordLink();
			$discipline = $this->model->answer?->question?->discipline?->name;
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_ANSWERED) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\", на который Вы подписаны.";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_USER_QUESTION_ANSWERED) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\" пользователя, на которого Вы подписаны.";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} решил вопрос \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $this->model->answer?->getRecordLink();
			$discipline = $this->model->question?->discipline?->name;
		} elseif ($this->model->type_id == Notification::FOLLOWED_USER_QUESTION_CREATE) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, задал новый вопрос \"{text}\".";
			$link = $this->model->question?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_DISCIPLINE_QUESTION_CREATE) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name}, задал новый вопрос \"{text}\" по предмету {discipline}, на который Вы подписаны.";
			$link = $this->model->question?->getRecordLink();
			$discipline = $this->model->question?->discipline?->name;
		} elseif ($this->model->type_id == Notification::MENTION) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} отметил вас при комментарии к ответу \"{text}\".";
			$link = $this->model->comment?->getRecordLink();
		} elseif ($this->model->type_id == Notification::INVITE_AS_EXPERT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} пригласил Вас в качестве эксперта на вопрос \"{text}\".";
			$link = $this->model->question?->getRecordLink();
		} elseif ($this->model->type_id == Notification::MY_ANSWER_LIKE) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} отметил Ваш ответ \"{text}\" полезным.";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::ANSWER_EDIT) {
			$text = $this->model->answer?->shortText;
			$message = "Пользователь {name} отредактировал свой ответ \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_QUESTION_EDIT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name} отредактировал вопрос, на который Вы подписаны, \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::FOLLOWED_USER_QUESTION_EDIT) {
			$text = $this->model->question?->question_title;
			$message = "Пользователь {name}, на которого Вы подписаны, отредактировал вопрос \"{text}\".";
			$link = $this->model->answer?->getRecordLink();
		} elseif ($this->model->type_id == Notification::REPORT) {
			$text = $this->model->report?->shortText;
			if ($this->model->report?->type->is_other) {
				$type_name = mb_strtolower($this->model->report?->type->type_name);
				$message = "Ваша запись \"{text}\" была отмечена как имеющая нарушение.";
			} else {
				$type_name = mb_strtolower($this->model->report?->type->type_name);
				$message = "Было отмечено, что \"{$type_name}\" на Вашу запись \"{text}\".";
			}
			$link = $this->model->question?->getRecordLink();
		}
		return [$text, $discipline, $message, $link];
	}

}
