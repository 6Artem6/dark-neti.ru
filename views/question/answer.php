<?php

/**
 * @var app\models\question\Question $model
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;

use app\widgets\question\{
	AnswersAndCommentsRecord,
	QuestionRecord,
	NewQuestionRecord
};
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'Обсуждение вопроса');
?>

<div class="col-sm-12 col-md-8 mb-2 px-0">
	<div class="vstack gap-2">
		<?= QuestionRecord::widget([
			'model' => $question,
			'report_types' => $report_types,
		]) ?>

		<?= AnswersAndCommentsRecord::widget([
			'model' => $question,
			'unseen_list' => $unseen_list,
			'report_types' => $report_types,
		]) ?>

		<div class="card question-body">
			<?php if ($new_list): ?>
				<div class="card-header">
					<h5>
						<?= Yii::t('app', 'Новые вопросы по предмету') ?>
						<?= Html::a($question->discipline->name,
							$question->discipline->getRecordLink(),
							[ 'class' => [ 'badge', 'rounded-pill', 'bg-info', 'text-white'] ]
						) ?>
					</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<?php foreach ($new_list as $k => $record): ?>
							<?php if ($k): ?>
								<hr class="my-2">
							<?php endif ?>
							<?= NewQuestionRecord::widget([
								'model' => $record
							]) ?>
						<?php endforeach ?>
					</div>
				</div>
			<?php endif ?>
			<div class="card-footer p-2">
				<div class="d-flex justify-content-between">
					<div class="p-2">
						<?= Html::a(
							Html::tag('span', null, ['class' => ['bi', 'bi-arrow-left-short']]) . Yii::t('app', 'Предыдущий'),
							['/question/answer', 'id' => $question->prevId],
							['class' => ['btn', 'btn-light']]
						) ?>
					</div>
					<div class="p-2">
						<?= Html::a(
							Yii::t('app', 'Следующий') . Html::tag('span', null, ['class' => ['bi', 'bi-arrow-right-short', 'ms-1']]),
							['/question/answer', 'id' => $question->nextId],
							['class' => ['btn', 'btn-light']]
						) ?>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>

<?= RightBar::widget([
	'show_popular_questions' => true,
	'show_discipline_best' => true,
	'show_similar_disciplines' => true,
	'question_id' => $question->question_id
]) ?>
