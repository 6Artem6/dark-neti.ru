<?php

namespace app\models\file;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

use app\models\service\OcrAccount;
use app\models\helpers\DocumentHelper;
use app\models\question\{Question, Answer};


class File extends ActiveRecord
{

	use FileTrait;

	public static function tableName()
	{
		return 'file';
	}

	public function rules()
	{
		return [
			[['file_id', 'file_system_name'], 'unique'],
			[['file_id', 'size', 'user_id', 'question_id', 'answer_id', 'bucket_link_version'], 'integer'],
			[['file_system_name', 'file_user_name', 'file_folder_name'], 'string', 'max' => 100],
			[['ext'], 'string', 'max' => 10],
			[['file_text'], 'string', 'max' => pow(2, 16) - 1],
			[['bucket_link'], 'string', 'max' => 1024],
			[['expires'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['file_system_name', 'file_user_name', 'ext', 'user_id'], 'required'],
			[['question_id'], 'required', 'on' => [self::SCENARIO_CREATE_QUESTION, self::SCENARIO_CREATE_ANSWER]],
			[['answer_id'], 'required', 'on' => [self::SCENARIO_CREATE_ANSWER]],

			[['file_user_name'], 'filterName'],
		];
	}

	public static function primaryKey()
	{
		return [
			'file_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'file_id'	=> Yii::t('app','Код'),
		];
	}

	public const SCENARIO_CREATE_QUESTION = 'create-question';
	public const SCENARIO_CREATE_ANSWER = 'create-answer';


	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_CREATE_QUESTION => [
				'!file_system_name', '!file_user_name', '!file_folder_name', '!file_text', '!ext', '!user_id', '!question_id'
			],
			self::SCENARIO_CREATE_ANSWER => [
				'!file_system_name', '!file_user_name', '!file_folder_name', '!file_text', '!ext', '!user_id', '!question_id', '!answer_id'
			],
		]);
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->isNewRecord and
			in_array($this->scenario, [static::SCENARIO_CREATE_QUESTION, static::SCENARIO_CREATE_ANSWER])) {
			if (mb_strlen($this->file_user_name) > 100) {
				$name = explode('.', $this->file_user_name)[0];
				$length = 100 - (mb_strlen($this->ext) + 1);
				$name = mb_substr($name, 0, $length);
				$name = $name . '.' . $this->ext;
				$this->file_user_name = $name;
			}
			if ($this->scenario == static::SCENARIO_CREATE_QUESTION) {
				$folder_name = 'question_'.$this->user_id.'_'.$this->question_id;
			}
			if ($this->scenario == static::SCENARIO_CREATE_ANSWER) {
				$folder_name = 'answer_'.$this->user_id.'_'.$this->question_id.'_'.$this->answer_id;
			}
			$this->file_folder_name = $folder_name;
			$name = Yii::$app->security->generateRandomString(32).'.'.$this->ext;
			$this->file_system_name = $name;
		}
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['question_id' => 'question_id', 'answer_id' => 'answer_id']);
	}

	public function saveFromQuestion(Question $question, UploadedFile $file)
	{
		$this->scenario = static::SCENARIO_CREATE_QUESTION;
		$this->question_id = $question->question_id;
		$this->user_id = $question->user_id;
		$this->file_user_name = $file->name;
		$this->ext = $file->extension;
		$this->size = $file->size;
		$this->bucket_link_version = 1;
		if ($this->save()) {
			$path = $this->getLocalFilePath();
			$file->saveAs($path);
			$this->putToBucket();
			$this->saveFileText();
			$this->removeFromLocal();
		}
	}

	public function saveFromAnswer(Answer $answer, UploadedFile $file)
	{
		$this->scenario = static::SCENARIO_CREATE_ANSWER;
		$this->question_id = $answer->question_id;
		$this->answer_id = $answer->answer_id;
		$this->user_id = $answer->user_id;
		$this->file_user_name = $file->name;
		$this->ext = $file->extension;
		$this->size = $file->size;
		$this->bucket_link_version = 1;
		if ($this->save()) {
			$path = $this->getLocalFilePath();
			$file->saveAs($path);
			$this->putToBucket();
			$this->saveFileText();
			$this->removeFromLocal();
		}
	}

	public function createQuestionHistoryRecord(Question $question, bool $need_to_delete = false)
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new FileHistory;
			$history->load($data, '');
			$history->history_question_id = $question->id;
			$result = $history->save();
			if ($need_to_delete) {
				$this->delete();
			}
		}
		return $result;
	}

	public function createAnswerHistoryRecord(Answer $answer, bool $need_to_delete = false)
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new FileHistory;
			$history->load($data, '');
			$history->history_question_id = $answer->question_id;
			$history->history_answer_id = $answer->id;
			$result = $history->save();
			if ($need_to_delete) {
				$this->delete();
			}
		}
		return $result;
	}

	public function saveFileText()
	{
		if ($this->isImg) {
			$url = $this->getLinkFromBucket();
			$file_text = OcrAccount::getTextFromImage($url);
		} else {
			$path = $this->getLocalFilePath();
			$file_text = DocumentHelper::getFileText($path);
		}
		$this->file_text = $file_text;
		$this->save();
	}

}
