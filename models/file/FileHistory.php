<?php

namespace app\models\file;

use Yii;
use yii\db\ActiveRecord;


class FileHistory extends ActiveRecord
{

	use FileTrait;

	public static function tableName()
	{
		return 'file_history';
	}

	public function rules()
	{
		return [
			[['history_file_id'], 'unique'],
			[['history_file_id', 'file_id', 'size', 'user_id', 'history_question_id', 'history_answer_id'], 'integer'],
			[['file_system_name', 'file_user_name'], 'string', 'max' => 100],
			// [['file_text'], 'string', 'max' => pow(2, 16) - 1],
			[['ext'], 'string', 'max' => 10],
		];
	}

	public static function primaryKey()
	{
		return [
			'history_file_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'history_file_id'	=> Yii::t('app','Код'),
		];
	}

	public function getQuestion()
	{
		return $this->hasOne(QuestionHistory::class, ['history_question_id' => 'history_question_id']);
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['history_question_id' => 'history_question_id', 'history_answer_id' => 'history_answer_id']);
	}

}
