<?php
namespace app\widgets\form\info;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};


class HonorCodeModal extends Widget
{

	public function run()
	{
		$id = 'honorCodeModal';
		$title = Yii::t('app', 'Кодекс чести');
		$widget = new Modal([
			'options' => ['id' => $id],
			'dialogOptions' => ['class' => ['modal-blur', 'text-justify']],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'bodyOptions' => ['class' => ['mx-2']],
			'footer' => $this->renderFooter(),
			'centerVertical' => true,
			'scrollable' => true,
		]);
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', 'Сайт «DARK-NETi» предназначен для помощи в обучении студентов в любой сфере, касающейся учёбы, путем сотрудничества с другими студентами-участниками сообщества. Мы просим наше сообщество помогать другим, ведь только благодаря Вам мы сможем улучшить качество обучения и воспитать прекрасных специалистов, которые будут востребованы в любой точки нашей планеты.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', 'Взаимосвязь студентов на сайте «DARK-NETi» основана на вопросах и ответах на них. На каждый вопрос студент может дать только один ответ. Студенты должны использовать «DARK-NETi» для улучшения своего образования. Нельзя выдавать чужие слова, мысли или идеи за свои собственные. Это правило включает в себя: копирование контента других пользователей, веб-сайтов или источников и выдачу его за собственную оригинальную работу.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', 'Пожалуйста, будьте вежливы друг с другом, только благодаря этому мы сможем сделать мир лучше. Наши модераторы следят за соблюдением правил из Кодекса чести, мы прилагаем все усилия, чтобы все участники сообщества соблюдали правила и разрешаем ситуацию в зависимости от степени нарушения.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', 'Если Вы считаете, что кто-то мог нарушить Кодекс чести, пожалуйтесь на участника сообщества, чтобы наши модераторы рассмотрели жалобу и приняли соответствующие меры. Более подробно ознакомиться с правилами на сайте «DARK-NETi» вы можете в разделе «Правила».');
		echo Html::endTag('p');

		return $widget->run();
	}

	protected function renderFooter()
	{
		$output = '';
		$output .= Html::a(Yii::t('app', 'Скачать'), '/documents/Кодекс чести.docx', [
			'target' => '_blank',
			'class' => ['btn', 'btn-primary', 'rounded-pill'],
		]);
		$output .= Html::button(Yii::t('app', 'Закрыть'), [
			'class' => ['btn', 'btn-light', 'rounded-pill'],
			'data-bs-dismiss' => 'modal',
		]);
		return $output;
	}

}
