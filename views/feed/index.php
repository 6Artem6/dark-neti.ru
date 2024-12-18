<?php

/**
 * @var yii\web\View $this
 */

use yii\bootstrap5\{Html, LinkPager};
use yii\helpers\Url;

use app\assets\actions\FeedIndexAsset;

use app\widgets\discipline\DisciplineFeedSlider;
use app\widgets\user\UserSlider;
use app\widgets\question\{FeedRecord, Loader};
use app\widgets\bar\RightBar;

FeedIndexAsset::register($this);


$this->title = Yii::t('app', 'Моя лента');
?>

<div class='col-sm-12 col-md-8 vstack gap-2'>
	<?= DisciplineFeedSlider::widget([
		'list' => $discipline_followed_list
	]) ?>
	<?php if ($pages->totalCount): ?>
		<div id='feed' class='row grid gap-2 px-3'>
			<?php foreach ($list as $k => $record): ?>
				<?= FeedRecord::widget([
					'model' => $record,
					'report_types' => $report_types,
				]) ?>
				<?php if ($k == 4): ?>
					<div class="card py-3">
						<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
							<?= Yii::t('app', 'Рекомендуемые пользователи')  ?>
						</div>
						<div class="card-body p-0">
							<?= UserSlider::widget([
								'list' => $user_list
							]) ?>
						</div>
					</div>
				<?php elseif ($k == 9): ?>
					<div class="card py-3">
						<div class="card-header border-0 text-secondary h5 pt-0 pb-1">
							<?= Yii::t('app', 'Рекомендуемые предметы') ?>
						</div>
						<div class="card-body p-0">
							<?= DisciplineFeedSlider::widget([
								'list' => $discipline_preferred_list
							]) ?>
						</div>
					</div>
				<?php endif ?>
			<?php endforeach ?>
		</div>
		<?= Loader::widget() ?>
	<?php else: ?>
		<div class='row justify-content-center row-cols-1 gap-2 mx-2'>
			<div class='card py-4'>
				<div class='col'>
					<h3 class='text-center'>
						<?= Yii::t('app', 'Записей не было найдено.') ?>
					</h3>
				</div>
				<div class='col'>
					<h4 class='text-center'>
						<?= Yii::t('app', 'Попробуйте выбрать другой предмет.') ?>
					</h4>
				</div>
				<div class='col'>
					<hr>
					<h5 class='text-center'>
						<?= Html::a(Yii::t('app', 'Показать все предметы'),
							[''],
							['class' => ['btn', 'btn-md', 'btn-outline-primary', 'text-center', 'w-50']]
						) ?>
					</h5>
				</div>
				<div class='col'>
					<hr>
					<h5 class='text-center mt-4'>
						<?= Yii::t('app', 'Или задайте вопрос сами!') ?>
					</h5>
				</div>
				<div class='col'>
					<h5 class='text-center'>
						<?= Html::a(Yii::t('app', 'Задать вопрос'),
							['/question/create'],
							['class' => ['btn', 'btn-md', 'btn-outline-primary', 'text-center', 'w-50']]
						) ?>
					</h5>
				</div>
			</div>
		</div>
	<?php endif ?>
</div>

<?= RightBar::widget([
	'show_active_users' => true,
	'show_popular_questions' => true,
	'show_discipline_best' => true,
	'show_similar_disciplines' => true,
	'discipline_name' => $discipline,
]) ?>

<?php
if ($discipline) {
	$this->registerJs(<<<JAVASCRIPT
		$('#feed').data('discipline', '{$discipline}');
	JAVASCRIPT);
}
if ($has_badge) {
	$this->registerJs(<<<JAVASCRIPT
		$('body').data('badge', true);
	JAVASCRIPT);
}
?>
