<?php

use kartik\form\ActiveForm;
use yii\bootstrap5\Html;

use app\models\helpers\HtmlHelper;

$this->title = Yii::t('app', 'Обращения пользователей');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-sm-8 col-md-6 vstack gap-3">
	<div class="card">
		<div class="card-header">
			<div class="card-title h3">
				<?= Html::encode($this->title) ?>
			</div>
		</div>
		<div class="card-body">
			<?php if ($support_list or $report_list['questions'] or $report_list['answers'] or $report_list['comments']): ?>
				<div class="accordion" id="reports">
					<?php if ($support_list): ?>
						<div class="accordion-item">
							<h2 class="accordion-header" id="support-list-heading">
								<button
									class="accordion-button"
									type="button"
									data-bs-toggle="collapse"
									data-bs-target="#support-list-collapse"
									aria-expanded="true"
									aria-controls="support-list-collapse">
									<?= Yii::t('app', 'Обращения в поддержку') ?>
								</button>
							</h2>
							<div id="support-list-collapse" class="accordion-collapse collapse show" aria-labelledby="support-list-heading">
								<div class="accordion-body">
									<?php foreach ($support_list as $record): ?>
										<?= $record->support_text ?>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['moderator/support-search'],
										['class' => ['btn', 'btn-sm', 'btn-info', 'rounded-pill']]
									) ?>
								</div>
							</div>
						</div>
					<?php endif ?>

					<?php if ($report_list['questions']): ?>
						<div class="accordion-item">
							<h2 class="accordion-header" id="questions-list-heading">
								<button
									class="accordion-button collapsed"
									type="button"
									data-bs-toggle="collapse"
									data-bs-target="#questions-list-collapse"
									aria-expanded="false"
									aria-controls="questions-list-collapse">
									<?= Yii::t('app', 'Обращения на вопросы') ?>
								</button>
							</h2>
							<div id="questions-list-collapse" class="accordion-collapse collapse" aria-labelledby="questions-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['questions'] as $record): ?>
										<?php $id = 'question-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Автор:') ?>
											<span class="fst-italic">
												<?= Html::a($record->author->name,
													$record->author->getPageLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Yii::t('app', 'Вопрос:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => ['btn', 'btn-light', 'btn-sm', 'rounded-pill'],
													'data-bs-toggle' => "collapse",
													'role' => "button",
													'aria-expanded' => "false",
													"data-bs-target" => '#' . $id,
													'aria-controls' => $id
												]) ?>
											<?= Html::beginTag('div', ['id' => $id, 'class' => 'collapse']) ?>
												<div class="card card-body mt-2">
													<?php foreach ($record->reportsByType as $reports): ?>
														<?php $type = current($reports)->type ?>
														<div>
															<?= $type->type_name ?> - <?= count($reports) ?>
															<?= HtmlHelper::actionModeratorButton(Yii::t('app', 'Не согласиться'),
																'report-close',
																$reports[0]->report_id, [
																	'class' => ['btn', 'btn-sm', 'btn-danger', 'rounded-pill'],
																]) ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<div class="my-2">
											<?= Html::a(Yii::t('app', 'Исправить'),
												$record->getEditLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'me-2']]
											) ?>
											<?= Html::a(Yii::t('app', 'Зафиксировать нарушение'),
												$record->getLimitLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-danger-soft', 'rounded-pill']]
											) ?>
										</div>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['moderator/question-search'],
										['class' => ['btn', 'btn-sm', 'btn-info', 'rounded-pill']]
									) ?>
								</div>
							</div>
						</div>
					<?php endif ?>

					<?php if ($report_list['answers']): ?>
						<div class="accordion-item">
							<h2 class="accordion-header" id="answers-list-heading">
								<button
									class="accordion-button collapsed"
									type="button"
									data-bs-toggle="collapse"
									data-bs-target="#answers-list-collapse"
									aria-expanded="false"
									aria-controls="answers-list-collapse">
									<?= Yii::t('app', 'Обращения на ответы') ?>
								</button>
							</h2>
							<div id="answers-list-collapse" class="accordion-collapse collapse" aria-labelledby="answers-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['answers'] as $record): ?>
										<?php $id = 'answer-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Автор:') ?>
											<span class="fst-italic">
												<?= Html::a($record->author->name,
													$record->author->getPageLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Yii::t('app', 'Ответ:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => ['btn', 'btn-light', 'btn-sm', 'rounded-pill'],
													'data-bs-toggle' => "collapse",
													'role' => "button",
													'aria-expanded' => "false",
													"data-bs-target" => '#' . $id,
													'aria-controls' => $id
												]) ?>
											<?= Html::beginTag('div', ['id' => $id, 'class' => 'collapse']) ?>
												<div class="card card-body mt-2">
													<?php foreach ($record->reportsByType as $reports): ?>
														<?php $type = current($reports)->type ?>
														<div>
															<?= $type->type_name ?> - <?= count($reports) ?>
															<?= Html::button(Yii::t('app', 'Не согласиться'), [
																'class' => ['btn', 'btn-sm', 'btn-danger', 'rounded-pill'],
																'data-button' => 'action',
																'data-action' => 'report-reject',
																'data-id' => $reports[0]->report_id
															]) ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<div class="my-2">
											<?= Html::a(Yii::t('app', 'Исправить'),
												$record->getEditLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'me-2']]
											) ?>
											<?= Html::a(Yii::t('app', 'Зафиксировать нарушение'),
												$record->getLimitLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-danger-soft', 'rounded-pill']]
											) ?>
										</div>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['moderator/answer-search'],
										['class' => ['btn', 'btn-sm', 'btn-info', 'rounded-pill']]
									) ?>
								</div>
							</div>
						</div>
					<?php endif ?>

					<?php if ($report_list['comments']): ?>
						<div class="accordion-item">
							<h2 class="accordion-header" id="comments-list-heading">
								<button
									class="accordion-button collapsed"
									type="button"
									data-bs-toggle="collapse"
									data-bs-target="#comments-list-collapse"
									aria-expanded="false"
									aria-controls="comments-list-collapse">
									<?= Yii::t('app', 'Обращения на комментарии') ?>
								</button>
							</h2>
							<div id="comments-list-collapse" class="accordion-collapse collapse" aria-labelledby="comments-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['comments'] as $record): ?>
										<?php $id = 'comment-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Автор:') ?>
											<span class="fst-italic">
												<?= Html::a($record->author->name,
													$record->author->getPageLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Yii::t('app', 'Комментарий:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-secondary', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => ['btn', 'btn-light', 'btn-sm', 'rounded-pill'],
													'data-bs-toggle' => "collapse",
													'role' => "button",
													'aria-expanded' => "false",
													"data-bs-target" => '#' . $id,
													'aria-controls' => $id
												]) ?>
											<?= Html::beginTag('div', ['id' => $id, 'class' => 'collapse']) ?>
												<div class="card card-body mt-2">
													<?php foreach ($record->reportsByType as $reports): ?>
														<?php $type = current($reports)->type ?>
														<div>
															<?= $type->type_name ?> - <?= count($reports) ?>
															<?= Html::button(Yii::t('app', 'Не согласиться'), [
																'class' => ['btn', 'btn-sm', 'btn-danger', 'rounded-pill'],
																'data-button' => 'action',
																'data-action' => 'report-reject',
																'data-id' => $reports[0]->report_id
															]) ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<div class="my-2">
											<?= Html::a(Yii::t('app', 'Исправить'),
												$record->getEditLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill', 'me-2']]
											) ?>
											<?= Html::a(Yii::t('app', 'Зафиксировать нарушение'),
												$record->getLimitLink(),
												['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-danger-soft', 'rounded-pill']]
											) ?>
										</div>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['moderator/comment-search'],
										['class' => ['btn', 'btn-sm', 'btn-info', 'rounded-pill']]
									) ?>
								</div>
							</div>
						</div>
					<?php endif ?>
				</div>
			<?php else: ?>
				<div class="col-12 mt-3">
					<?= Yii::t('app', 'Здесь будут отображены все обращения') ?>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
