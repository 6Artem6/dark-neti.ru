<?php

/**
 * @var app\models\Question $question
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;

use app\widgets\question\QuestionRecord;
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'История вопроса');

$history_count = count($question->historyRecords);
?>

<div class="col-sm-12 col-md-8 gap-3">
	<div class="vstack gap-2">

		<div class="card card-body">
			<h4><?= Html::encode($this->title) ?> "<?= Html::a($question->question_title, $question->getRecordLink()) ?>"</h4>
			<h6 class="mt-0">
				<?= Yii::t('app', 'Автор:') ?>
				<?= Html::a(Html::tag('span', $question->author->name,
						['class' => [ 'badge', 'rounded', 'bg-warning', 'text-dark' ] ]),
					$question->author->getPageLink()) ?>
			</h6>
		</div>

		<div class="card card-body">
			<h4 class="mt-0">
				<?php if ($history_count): ?>
					<?= Yii::t('app', 'Последнее изменение вопроса') ?>
				<?php else: ?>
					<?= Yii::t('app', 'Вопрос задан') ?>
				<?php endif ?>
			</h4>
			<?= QuestionRecord::widget([
				'model' => $question,
				'is_short' => true,
				'is_first' => !$history_count
			]) ?>

			<?php if ($history_count): ?>
				<?php foreach ($question->historyRecords as $k => $history): ?>
					<hr class="my-1">
					<h4 class="mt-0">
						<?php if ($k < $history_count - 1): ?>
							<?= Yii::t('app', 'Вопрос изменён') ?>
						<?php else: ?>
							<?= Yii::t('app', 'Вопрос задан') ?>
						<?php endif ?>
					</h4>
					<?= QuestionRecord::widget([
						'model' => $history,
						'is_short' => true,
						'is_first' => ($k >= $history_count - 1)
					]) ?>
				<?php endforeach ?>
			<?php endif ?>
		</div>
	</div>
</div>

<?= RightBar::widget([
	'show_popular_questions' => true,
]) ?>

