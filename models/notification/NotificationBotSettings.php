<?php

namespace app\models\notification;

use Yii;
use yii\db\ActiveRecord;


class NotificationBotSettings extends ActiveRecord
{

	public static function tableName()
	{
		return 'notification_bot_settings';
	}

	public function rules()
	{
		return [
			[['!user_id'], 'unique'],
			[['user_id'], 'integer'],
			[[
				'my_question_answer', 'my_question_comment', 'my_question_answer_comment',

				'followed_question_edit', 'followed_question_answer', 'followed_question_comment', 'followed_question_answer_comment',
				'followed_question_my_answer_comment', 'followed_question_answered',

				'followed_user_question_create', 'followed_user_question_edit', 'followed_user_answer',
				'followed_user_comment', 'followed_user_question_answered',

				'followed_discipline_question_create', 'followed_discipline_question_edit', 'followed_discipline_question_answer',
				'followed_discipline_question_comment', 'followed_discipline_question_answer_comment', 'followed_discipline_question_answered',

				'mention', 'invite_as_expert', 'my_answer_like', 'answer_edit'
			], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'user_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app', 'Пользователь'),

			'my_question_answer' => Yii::t('app', 'Ответы на мои вопросы'),
			'my_question_comment' => Yii::t('app', 'Комментарии к моим вопросам'),
			'my_question_answer_comment' => Yii::t('app', 'Комментарии к ответам на мои вопросы'),

			'followed_question_edit' => Yii::t('app', 'Вопрос изменён'),
			'followed_question_answer' => Yii::t('app', 'Ответы на вопросы'),
			'followed_question_comment' => Yii::t('app', 'Комментарии к вопросам'),
			'followed_question_answer_comment' => Yii::t('app', 'Комментарии к ответам на вопросы'),
			'followed_question_my_answer_comment' => Yii::t('app', 'Комментарии к моим ответам на вопросы'),
			'followed_question_answered' => Yii::t('app', 'Вопрос получил решение'),

			'followed_user_question_create' => Yii::t('app', 'Вопрос создан'),
			'followed_user_question_edit' => Yii::t('app', 'Вопрос изменён'),
			'followed_user_answer' => Yii::t('app', 'Дал ответ'),
			'followed_user_comment' => Yii::t('app', 'Оставил комментарий'),
			'followed_user_question_answered' => Yii::t('app', 'Вопрос получил решение'),

			'followed_discipline_question_create' => Yii::t('app', 'Вопрос создан'),
			'followed_discipline_question_edit' => Yii::t('app', 'Вопрос изменён'),
			'followed_discipline_question_answer' => Yii::t('app', 'Ответы на вопросы'),
			'followed_discipline_question_comment' => Yii::t('app', 'Комментарии к вопросам'),
			'followed_discipline_question_answer_comment' => Yii::t('app', 'Комментарии к ответам вопросы'),
			'followed_discipline_question_answered' => Yii::t('app', 'Вопрос получил решение'),

			'mention' => Yii::t('app', 'Упоминание меня'),
			'invite_as_expert' => Yii::t('app', 'Приглашение меня в качестве эксперта'),
			'my_answer_like' => Yii::t('app', 'Мои ответы отметили полезными'),
			'answer_edit' => Yii::t('app', 'Ответ изменён'),
		];
	}

	public function returnToDefault()
	{
		$this->my_question_answer = true;
		$this->my_question_comment = true;
		$this->my_question_answer_comment = true;
		$this->followed_question_edit = true;
		$this->followed_question_answer = true;
		$this->followed_question_comment = false;
		$this->followed_question_answer_comment = false;
		$this->followed_question_my_answer_comment = true;
		$this->followed_question_answered = true;
		$this->followed_user_question_create = true;
		$this->followed_user_question_edit = false;
		$this->followed_user_answer = false;
		$this->followed_user_comment = false;
		$this->followed_user_question_answered = true;
		$this->followed_discipline_question_create = true;
		$this->followed_discipline_question_edit = false;
		$this->followed_discipline_question_answer = false;
		$this->followed_discipline_question_comment = false;
		$this->followed_discipline_question_answer_comment = false;
		$this->followed_discipline_question_answered = true;
		$this->mention = true;
		$this->invite_as_expert = true;
		$this->my_answer_like = true;
		$this->answer_edit = false;
		$this->save();
	}

	public function isDefault()
	{
		return (
			($this->my_question_answer == true) and
			($this->my_question_comment == true) and
			($this->my_question_answer_comment == true) and
			($this->followed_question_edit == true) and
			($this->followed_question_answer == true) and
			($this->followed_question_comment == false) and
			($this->followed_question_answer_comment == false) and
			($this->followed_question_my_answer_comment == true) and
			($this->followed_question_answered == true) and
			($this->followed_user_question_create == true) and
			($this->followed_user_question_edit == false) and
			($this->followed_user_answer == false) and
			($this->followed_user_comment == false) and
			($this->followed_user_question_answered == true) and
			($this->followed_discipline_question_create == true) and
			($this->followed_discipline_question_edit == false) and
			($this->followed_discipline_question_answer == false) and
			($this->followed_discipline_question_comment == false) and
			($this->followed_discipline_question_answer_comment == false) and
			($this->followed_discipline_question_answered == true) and
			($this->mention == true) and
			($this->invite_as_expert == true) and
			($this->my_answer_like == true) and
			($this->answer_edit == false)
		);
	}

}
