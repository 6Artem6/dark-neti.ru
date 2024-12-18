<?php

/**
 * @var app\models\Comment $comment
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;

use app\widgets\question\CommentText;
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'История комментария');

$history_count = count($comment->historyRecords);
?>

<div class="col-sm-12 col-md-8 gap-3">
	<div class="vstack gap-2">

		<div class="card card-body">
			<h4>
				<?= Html::a(Html::encode($this->title), $comment->getRecordLink()) ?>
				<?php if ($comment->isForAnswer): ?>
					<?= Yii::t('app', 'к ответу') ?> "<?= Html::a($comment->answer->shortText, $comment->answer->getRecordLink()) ?>"
				<?php else: ?>
					<?= Yii::t('app', 'к вопросу') ?> "<?= Html::a($comment->question->question_title, $comment->question->getRecordLink()) ?>"
				<?php endif ?>
			</h4>
			<h6 class="mt-0">
				<?= Yii::t('app', 'Автор:') ?>
				<?= Html::a(Html::tag('span', $comment->author->name,
						['class' => ['badge', 'rounded-pill', 'bg-info', 'text-dark']]),
					$comment->author->getPageLink()
				) ?>
			</h6>
		</div>

		<div class="card card-body">
			<h4 class="mt-0">
				<?php if ($history_count): ?>
					<?= Yii::t('app', 'Последнее изменение комментария') ?>
				<?php else: ?>
					<?= Yii::t('app', 'Комментарий оставлен') ?>
				<?php endif ?>
			</h4>
			<?= CommentText::widget([
				'model' => $comment,
				'is_short' => true,
				'is_feed' => false,
				'is_first' => !$history_count
			]) ?>

			<?php if ($history_count): ?>
				<?php foreach ($comment->historyRecords as $k => $history): ?>
					<hr class="my-1">
					<h4 class="mt-0">
						<?php if ($k < $history_count - 1): ?>
							<?= Yii::t('app', 'Комментарий изменён') ?>
						<?php else: ?>
							<?= Yii::t('app', 'Комментарий оставлен') ?>
						<?php endif ?>
					</h4>
					<?= CommentText::widget([
						'model' => $history,
						'is_short' => true,
						'is_feed' => false,
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

