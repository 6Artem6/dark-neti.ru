<?php

/**
 * @var app\models\Answer $answer
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;

use app\widgets\question\AnswerText;
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'История ответа');

$history_count = count($answer->historyRecords);
?>

<div class="col-sm-12 col-md-8 gap-3">
	<div class="vstack gap-2">

		<div class="card card-body">
			<h4>
				<?= Html::a(Html::encode($this->title), $answer->getRecordLink()) ?>
				<?= Yii::t('app', 'к вопросу') ?> "<?= Html::a($answer->question->question_title, $answer->question->getRecordLink()) ?>"
			</h4>
			<h6 class="mt-0">
				<?= Yii::t('app', 'Автор:') ?>
				<?= Html::a(Html::tag('span', $answer->author->name,
						['class' => [ 'badge', 'rounded', 'bg-info', 'text-dark' ] ]),
					$answer->author->getPageLink()) ?>
			</h6>
		</div>

		<div class="card card-body">
			<h4 class="mt-0">
				<?php if ($history_count): ?>
					<?= Yii::t('app', 'Последнее изменение ответа') ?>
				<?php else: ?>
					<?= Yii::t('app', 'Ответ дан') ?>
				<?php endif ?>
			</h4>
			<?= AnswerText::widget([
				'model' => $answer,
				'is_short' => true,
				'is_feed' => false,
				'is_first' => !$history_count,
			]) ?>

			<?php if ($history_count): ?>
				<?php foreach ($answer->historyRecords as $k => $history): ?>
					<hr class="my-1">
					<h4 class="mt-0">
						<?php if ($k < $history_count - 1): ?>
							<?= Yii::t('app', 'Ответ изменён') ?>
						<?php else: ?>
							<?= Yii::t('app', 'Ответ дан') ?>
						<?php endif ?>
					</h4>
					<?= AnswerText::widget([
						'model' => $history,
						'is_short' => true,
						'is_feed' => false,
						'is_first' => ($k >= $history_count - 1),
					]) ?>
				<?php endforeach ?>
			<?php endif ?>
		</div>
	</div>
</div>

<?= RightBar::widget([
	'show_popular_questions' => true,
]) ?>
