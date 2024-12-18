<?php

/**
 * @var app\models\question\Question $model
 * @var app\models\User[] $list
 * @var yii\web\View $this
 * @var yii\data\Pagination $pages
 */

use yii\bootstrap5\{Html, LinkPager};

use app\widgets\user\UserRecord;
use app\widgets\question\QuestionRecord;
use app\widgets\bar\RightBar;

$this->title = Yii::t('app', 'Подписчики вопроса');
?>

<div class="col-sm-12 col-md-8 mb-2 px-0">
	<div class="vstack gap-2">
		<?= QuestionRecord::widget([
			'model' => $question,
			'report_types' => $report_types,
		]) ?>

		<div class="card question-body">
			<div class="card-body py-2" id="followers">
				<div class="row px-3 h6">
					<div class="col px-0">
						<?= Html::a(Yii::t('app', 'Назад к обсуждению'), $question->getRecordLink(), ['class' => ['btn', 'btn-sm', 'btn-outline-light', 'text-secondary']]); ?>
					</div>
					<hr class="my-2">
				</div>
				<div class="row px-3 h6">
					<?= Yii::t('app', 'Подписчики вопроса ({count})', ['count' => $pages->totalCount]) ?>
					<hr class="my-2">
				</div>
				<div class="row g-2 row-cols-1 row-cols-sm-3 row-cols-md-2 row-cols-lg-2 row-cols-xl-3">
					<?php foreach ($list as $record): ?>
						<?= UserRecord::widget([
							'model' => $record->follower,
						]) ?>
					<?php endforeach ?>
				</div>
			</div>

			<?php if ($pages->totalCount > $pages->limit): ?>
				<div class="card card-body mt-3">
					<div class="row">
						<?= LinkPager::widget([
							'pagination' => $pages,
							'registerLinkTags' => true,
							'maxButtonCount' => 5,
						]) ?>
					</div>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>

<?= RightBar::widget([
	'show_popular_questions' => true,
	'show_discipline_best' => true,
]) ?>
