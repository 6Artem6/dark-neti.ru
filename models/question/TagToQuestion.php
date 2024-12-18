<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;

use app\models\helpers\ModelHelper;


class TagToQuestion extends ActiveRecord
{

	public static function tableName()
	{
		return 'tag_to_question';
	}

	public function rules()
	{
		return [
			[['tag_id', 'question_id'], 'integer'],
			[['tag_id', 'question_id'], 'unique', 'targetAttribute' => ['tag_id', 'question_id']],
		];
	}

	public static function primaryKey()
	{
		return [
			'tag_id', 'question_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'tag_id' => Yii::t('app', 'Тег'),
			'question_id' => Yii::t('app', 'Вопрос'),
		];
	}

	public function getTag()
	{
		return $this->hasOne(Tag::class, ['tag_id' => 'tag_id']);
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getListByQuestion(int $question_id)
	{
		return self::find()
			->innerJoinWith('tag as t')
			->innerJoinWith('question as q')
			->where(['question_id' => $question_id])
			->all();
	}

	public static function createRecord(int $tag_id, int $question_id)
	{
		$record = self::findOne(['tag_id' => $tag_id, 'question_id' => $question_id]);
		if (!$record) {
			$record = new self(['tag_id' => $tag_id, 'question_id' => $question_id]);
			$record->save();
		}
	}

	public static function removeRecord(int $tag_id, int $question_id)
	{
		$record = self::findOne(['tag_id' => $tag_id, 'question_id' => $question_id]);
		if ($record) {
			$record->delete();
		}
	}

}
