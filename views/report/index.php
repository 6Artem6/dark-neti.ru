<?php

/**
 * @var app\models\data\UserData $model
 * @var yii\web\View $this
 */

use yii\bootstrap5\Html;

use app\models\helpers\HtmlHelper;

$this->title = Yii::t('app', 'Просмотр обращений');
?>

<div class="col-sm-8 col-md-6 vstack gap-3">
	<div class="card">
		<div class="card-header card-title h4">
			<?= Html::encode($this->title) ?>
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
									<?= Yii::t('app', 'Мои обращения в поддержку') ?>
								</button>
							</h2>
							<div id="support-list-collapse" class="accordion-collapse collapse show" aria-labelledby="support-list-heading">
								<div class="accordion-body">
									<?php foreach ($support_list as $record): ?>
										<?= $record->support_text ?>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['report/support'],
										['class' => ['btn', 'btn-sm', 'btn-info']]
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
									<?= Yii::t('app', 'Обращения на мои вопросы') ?>
								</button>
							</h2>
							<div id="questions-list-collapse" class="accordion-collapse collapse" aria-labelledby="questions-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['questions'] as $record): ?>
										<?php $id = 'question-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Вопрос:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-dark', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => 'btn btn-light btn-sm',
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
															<?php if ($reports[0]->isSent): ?>
																<?= HtmlHelper::actionButton(Yii::t('app', 'Не согласиться'),
																	'report-reject',
																	$reports[0]->report_id, [
																		'class' => ['btn', 'btn-sm', 'btn-danger'],
																	]) ?>
															<?php elseif ($reports[0]->isRejected): ?>
																<span class="fw-bold">
																	<?= Yii::t('app', 'Вы не согласны. Вопрос находится на проверке.') ?>
																</span>
															<?php endif ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<?php if (is_null($record->canEdit())): ?>
											<div class="my-2">
												<?= Html::a(Yii::t('app', 'Исправить'),
													$record->getEditLink(),
													['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary']]
												) ?>
											</div>
										<?php endif ?>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['report/questions'],
										['class' => ['btn', 'btn-sm', 'btn-info']]
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
									<?= Yii::t('app', 'Обращения на мои ответы') ?>
								</button>
							</h2>
							<div id="answers-list-collapse" class="accordion-collapse collapse" aria-labelledby="answers-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['answers'] as $record): ?>
										<?php $id = 'answer-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Ответ:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-dark', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => 'btn btn-light btn-sm',
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
															<?php if ($reports[0]->isSent): ?>
																<?= HtmlHelper::actionButton(Yii::t('app', 'Не согласиться'),
																	'report-reject',
																	$reports[0]->report_id, [
																		'class' => ['btn', 'btn-sm', 'btn-danger'],
																	]) ?>
															<?php elseif ($reports[0]->isRejected): ?>
																<span class="fw-bold">
																	<?= Yii::t('app', 'Вы не согласны. Ответ находится на проверке.') ?>
																</span>
															<?php endif ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<?php if (is_null($record->canEdit())): ?>
											<div class="my-2">
												<?= Html::a(Yii::t('app', 'Исправить'),
													$record->getEditLink(),
													['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary']]
												) ?>
											</div>
										<?php endif ?>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['report/answers'],
										['class' => ['btn', 'btn-sm', 'btn-info']]
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
									<?= Yii::t('app', 'Обращения на мои комментарии') ?>
								</button>
							</h2>
							<div id="comments-list-collapse" class="accordion-collapse collapse" aria-labelledby="comments-list-heading">
								<div class="accordion-body">
									<?php foreach ($report_list['comments'] as $record): ?>
										<?php $id = 'comment-'.$record->id ?>
										<div class="my-2">
											<?= Yii::t('app', 'Комментарий:') ?>
											<span class="fst-italic">
												<?= Html::a($record->shortText,
													$record->getRecordLink(),
													['class' => ['bg-light', 'rounded', 'text-dark', 'link-primary', 'p-1']]) ?>
											</span>
										</div>
										<div class="my-2">
											<?= Html::button(Yii::t('app', 'Заявлений: {count}', ['count' => $record->reportCount]), [
													'class' => 'btn btn-light btn-sm',
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
															<?php if ($reports[0]->isSent): ?>
																<?= HtmlHelper::actionButton(Yii::t('app', 'Не согласиться'),
																	'report-reject',
																	$reports[0]->report_id, [
																		'class' => ['btn', 'btn-sm', 'btn-danger'],
																	]) ?>
															<?php elseif ($reports[0]->isRejected): ?>
																<span class="fw-bold">
																	<?= Yii::t('app', 'Вы не согласны. Комментарий находится на проверке.') ?>
																</span>
															<?php endif ?>
														</div>
													<?php endforeach ?>
												</div>
											<?= Html::endTag('div') ?>
										</div>
										<hr class="my-2">
										<?php if (is_null($record->canEdit())): ?>
											<div class="my-2">
												<?= Html::a(Yii::t('app', 'Исправить'),
													$record->getEditLink(),
													['targer' => '_blank', 'class' => ['btn', 'btn-sm', 'btn-primary']]
												) ?>
											</div>
										<?php endif ?>
										<hr class="my-2">
									<?php endforeach ?>
									<?= Html::a(Yii::t('app', 'Просмотреть все'),
										['report/comments'],
										['class' => ['btn', 'btn-sm', 'btn-info']]
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
