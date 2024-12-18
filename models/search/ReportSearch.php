<?php

namespace app\models\search;

use Yii;
use yii\data\{ArrayDataProvider, Pagination};

use app\models\question\{Question, Answer, Comment};
use app\models\request\{Report, Support};


class ReportSearch extends Report
{

	public $text;
	public $sort;

	private $_pages;
	private $_type;
	private $_is_moderator;

	public function rules()
	{
		return [
			[['text', 'sort'], 'safe'],
			[['text'], 'string'],
			[['text'], 'trim'],
			[['sort'], 'in', 'range' => array_keys($this->getSortList())],
			[['type'], 'in', 'range' => array_keys($this->getTypeList())],

			[['text'], 'filter', 'filter' => 'strip_tags'],
		];
	}

	public function attributeLabels()
	{
		return [
			'text' => Yii::t('app', 'Текст'),
			'sort'  => Yii::t('app','Сортировка'),
		];
	}

	public const TYPE_SUPPORT = 'support';
	public const TYPE_QUESTION = 'questions';
	public const TYPE_ANSWER = 'answers';
	public const TYPE_COMMENT = 'comments';

	private const DATETIME = 'datetime';
	private const TEXT = 'text';

	public function search($params, string $type)
	{
		$this->_type = $type;
		$this->_is_moderator = Yii::$app->user->identity->isModerator();
		$this->load($params);
		if (!$this->validate()) {
			$this->clearFields();
		}
		return $this->getProvider();
	}

	public function getPages() {
		return $this->_pages;
	}

	public function getType() {
		return $this->_type;
	}

	protected function getIsModerator() {
		return $this->_is_moderator;
	}

	public function getTypeName() {
		return $this->getTypeList()[$this->_type] ?? null;
	}

	public function getSortList() {
		return [
			static::TEXT => Yii::t('app', 'В алфавитном порядке'),
			'-' . static::DATETIME => Yii::t('app', 'По дате получения'),
		];
	}

	public function getProvider()
	{
		$list = $this->getList();
		$this->_pages = new Pagination([
			'totalCount' => count($list),
			'defaultPageSize' => 10,
		]);

		return new ArrayDataProvider([
			'allModels' => $list,
			'sort' => [
				'attributes' => [
					static::TEXT,
					static::DATETIME
				],
				'defaultOrder' => $this->getOrder(),
			],
			'pagination' => [
				'pageSize' => $this->_pages->limit,
			],
		]);
	}

	public function getList()
	{
		if ($this->isSupport) {
			$list = $this->getSupportList();
		} elseif ($this->isQuestion) {
			$list = $this->getQuestionList();
		} elseif ($this->isAnswer) {
			$list = $this->getAnswerList();
		} elseif ($this->isComment) {
			$list = $this->getCommentList();
		}
		return $list;
	}

	public function getSupportList()
	{
		$list = Support::find()
			->joinWith('author as author');
		if ($this->text) {
			$list = $list->where(['OR',
				['LIKE', 'support_text', $this->text],
				['LIKE', 'response_text', $this->text]
			]);
		}
		if (!$this->isModerator) {
			$list = $list->andWhere(['author.user_id' => Yii::$app->user->identity->id]);
		}
		return $list->orderBy(['response_datetime' => SORT_DESC, 'support_datetime' => SORT_DESC])->all();
	}

	public function getQuestionList()
	{
		$list = Question::find()
			->innerJoinWith('reports.type');
		if ($this->text) {
			$list = $list->where(['LIKE', 'question.question_text', $this->text]);
		}
		if (!$this->isModerator) {
			$list = $list->andWhere(['report.user_id' => Yii::$app->user->identity->id]);
		}
		return $list->orderBy(['report.report_datetime' => SORT_DESC])->all();
	}

	public function getAnswerList()
	{
		$list = Answer::find()
			->innerJoinWith('reports.type');
		if ($this->text) {
			$list = $list->where(['LIKE', 'answer.answer_text', $this->text]);
		}
		if (!$this->isModerator) {
			$list = $list->andWhere(['report.user_id' => Yii::$app->user->identity->id]);
		}
		return $list->orderBy(['report.report_datetime' => SORT_DESC])->all();
	}

	public function getCommentList()
	{
		$list = Comment::find()
			->innerJoinWith('reports.type');
		if ($this->text) {
			$list = $list->where(['LIKE', 'comment.comment_text', $this->text]);;
		}
		if (!$this->isModerator) {
			$list = $list->andWhere(['report.user_id' => Yii::$app->user->identity->id]);
		}
		return $list->orderBy(['report.report_datetime' => SORT_DESC])->all();
	}

	public function clearFields()
	{
		if ($this->errors) {
			foreach ($this->errors as $attribute => $value) {
				$this->{$attribute} = null;
				$this->clearErrors($attribute);
			}
		}
	}

	public function getOrder()
	{
		$sort = [
			static::DATETIME => SORT_DESC,
			static::TEXT => SORT_ASC,
		];
		if ($this->sort) {
			if (mb_substr($this->sort, 0, 1) == '-') {
				$field = mb_substr($this->sort, 1);
				$order = SORT_DESC;
			} else {
				$field = $this->sort;
				$order = SORT_ASC;
			}
			$sort = [$field => $order];
		}
		return $sort;
	}

	public function getSortField()
	{
		if (mb_substr($this->sort, 0, 1) == '-') {
			return mb_substr($this->sort, 1);
		}
		return $this->sort;
	}

	public function getTypeList()
	{
		return [
			static::TYPE_SUPPORT => Yii::t('app', 'Обращения в поддержку'),
			static::TYPE_QUESTION => Yii::t('app', 'Обращения на мои вопросы'),
			static::TYPE_ANSWER => Yii::t('app', 'Обращения на мои ответы'),
			static::TYPE_COMMENT => Yii::t('app', 'Обращения на мои комментарии'),
		];
	}

	public function getIsSupport()
	{
		return ($this->type == static::TYPE_SUPPORT);
	}

	public function getIsQuestion()
	{
		return ($this->type == static::TYPE_QUESTION);
	}

	public function getIsAnswer()
	{
		return ($this->type == static::TYPE_ANSWER);
	}

	public function getIsComment()
	{
		return ($this->type == static::TYPE_COMMENT);
	}


}
