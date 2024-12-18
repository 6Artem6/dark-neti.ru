<?php

namespace app\models\edu;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\{ArrayHelper, StringHelper, Url};

use app\models\user\{User};
use app\models\question\{Answer, Question, Tag};
use app\models\follow\FollowDiscipline;
use app\models\helpers\{ModelHelper, TextHelper};


class Discipline extends ActiveRecord
{

	public static function tableName()
	{
		return 'discipline';
	}

	public function rules()
	{
		return [
			[['discipline_id', 'discipline_name'], 'unique'],
			[['discipline_id', '!followers', '!question_count', '!question_helped_count', '!answer_count', '!icon_id'], 'integer'],
			[['discipline_name'], 'string', 'min' => 2, 'max' => 256],
			[['!is_checked'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'discipline_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'discipline_id' => Yii::t('app', 'Дисциплина'),
			'discipline_name' => Yii::t('app', 'Дисциплина'),
		];
	}

	private int $_filter_question_count = 0;
	private int $_filter_question_helped_count = 0;
	private int $_filter_answer_count = 0;
	private ?bool $_is_followed = null;

	public function getChair()
	{
		return $this->hasOne(Chair::class, ['chair_id' => 'chair_id'])
			->via('toChair');
	}

	public function getTeachers()
	{
		return $this->hasMany(Teacher::class, ['teacher_id' => 'teacher_id'])
			->via('toTeacher');
	}

	public function getGroups()
	{
		return $this->hasMany(StudentGroup::class, ['group_id' => 'group_id'])
			->via('toGroup');
	}

	public function getQuestions()
	{
		return $this->hasMany(Question::class, ['discipline_id' => 'discipline_id']);
	}

	public function getTags()
	{
		return $this->hasMany(Tag::class, ['discipline_id' => 'discipline_id']);
	}

	public function getFollow()
	{
		return $this->hasMany(FollowDiscipline::class, ['discipline_id' => 'discipline_id']);
	}

	public function getFollowUser()
	{
		return $this->hasMany(FollowDiscipline::class, ['discipline_id' => 'discipline_id'])
			->onCondition(['follower_id' => Yii::$app->user->identity->id]);
	}

	public function getToGroup()
	{
		return $this->hasMany(GroupToDiscipline::class, ['discipline_id' => 'discipline_id']);
	}

	public function getToChair()
	{
		return $this->hasOne(DisciplineToChair::class, ['discipline_id' => 'discipline_id']);
	}

	public function getToTeacher()
	{
		return $this->hasMany(TeacherToDiscipline::class, ['discipline_id' => 'discipline_id']);
	}

	public function getIcon()
	{
		return $this->hasOne(DisciplineIcon::class, ['icon_id' => 'icon_id']);
	}

	public function findIsFollowed()
	{
		$id = Yii::$app->user->identity->id;
		$list = ArrayHelper::index($this->follow, 'follower_id');
		$this->_is_followed = !empty($list[$id]);
	}

	public function getId()
	{
		return $this->discipline_id;
	}

	public function getName()
	{
		return $this->discipline_name;
	}

	public function getFilter_question_count()
	{
		return $this->_filter_question_count;
	}

	public function getFilter_question_helped_count()
	{
		return $this->_filter_question_helped_count;
	}

	public function getFilter_answer_count()
	{
		return $this->_filter_answer_count;
	}

	public function setFilter_question_count(int $value)
	{
		$this->_filter_question_count = $value;
	}

	public function setFilter_question_helped_count(int $value)
	{
		$this->_filter_question_helped_count = $value;
	}

	public function setFilter_answer_count(int $value)
	{
		$this->_filter_answer_count = $value;
	}

	public function getShortname()
	{
		$text = strip_tags($this->name);
		return StringHelper::truncate($text, 30);
	}

	public function getImgLink()
	{
		if (($this->icon_id != 1) and !empty($this->icon)) {
			$link = $this->icon->getImgLink();
		} else {
			$link = DisciplineIcon::getStandartLink();
		}
		return $link;
	}

	public function getRecordLink()
	{
		return ModelHelper::getSearchParamLink('discipline_name', $this->name);
	}

	public function getPageLink(bool $scheme = false, string $tab = 'about')
	{
		$link = Url::to(['/discipline/view'], $scheme);
		$link .= '/' . $this->name;
		if ($tab) {
			$link .= '/' . $tab;
		}
		return $link;
	}

	public function getRecordByName(string $discipline_name)
	{
		return self::findOne(['discipline_name' => $discipline_name]);
	}

	public function getRecordChecked(int $discipline_id)
	{
		return self::findOne(['discipline_id' => $discipline_id, 'is_checked' => true]);
	}

	public static function findByName(string $name)
	{
		return self::find()->where(['discipline_name' => $name])->one();
	}

	public function getIsFollowed(): bool
	{
		if (is_null($this->_is_followed)) {
			$this->findIsFollowed();
		}
		return $this->_is_followed;
	}

	public function getList(bool $add_all = false)
	{
		$result = [];
		$list = self::find()
			->select(['discipline_name'])
			->orderBy('discipline_name')
			->indexBy('discipline_id')
			->column();
		if ($add_all) {
			$result[null] = Yii::t('app', 'Все дисциплины');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}
		return $result;
	}

	public function getListIndexByName()
	{
		return self::find()
			->select(['discipline_id', 'discipline_name'])
			->indexBy('discipline_name')
			->column();
	}

	public static function getFollowerList(int $discipline_id)
	{
		return FollowDiscipline::find()
			->from(['follow' => FollowDiscipline::tableName()])
			->joinWith('follower')
			->where(['follow.discipline_id' => $discipline_id])
			->all();
	}

	public function checkRecord(string $discipline_name)
	{
		$discipline_name = TextHelper::remove_multiple_whitespaces($discipline_name);
		$model = null;
		if ($discipline_name) {
			$model = self::getRecordByName($discipline_name);
		}
		if (!$model) {
			$model = new self;
			$model->discipline_name = $discipline_name;
			$model->save();
		}
		return $model;
	}

	public function removeRecord(int $id)
	{
		$model = self::findOne($id);
		if ($model) {
			$model->delete();
		}
	}

	public function updateQuestionCount()
	{
		$this->question_count = Question::find()
			->from(['question' => Question::tableName()])
			->where(['discipline_id' => $this->discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->count();
		$this->save();
	}

	public function updateQuestionHelpedCount()
	{
		$this->question_helped_count = Question::find()
			->select('question.question_id')
			->distinct()
			->from(['question' => Question::tableName()])
			->joinWith('answersHelped')
			->where(['discipline_id' => $this->discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->count();
		$this->save();
	}

	public function updateAnswerCount()
	{
		$this->answer_count = Answer::find()
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['discipline.discipline_id' => $this->discipline_id])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->andWhere(['q_question.is_deleted' => false])
			->andWhere(['q_question.is_hidden' => false])
			->count();
		$this->save();
	}

	public function updateFollowers()
	{
		$this->followers = FollowDiscipline::getFollowersCount($this->discipline_id);
		$this->save();
	}

}
