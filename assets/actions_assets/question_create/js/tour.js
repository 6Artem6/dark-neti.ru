
$(document).ready(function(){

	window.tour = function() {
		var when_show = {
			show() {
				const vElement = this.getElement();
				if (vElement) vElement.focus = () => { };
			}
		};

		const tour = new Shepherd.Tour({
			defaultStepOptions: {
				cancelIcon: {
					enabled: true
				},
				scrollTo: { behavior: 'smooth', block: 'center' }
			},
			modalOverlayOpeningPadding: '10',
			useModalOverlay: true
		});

		tour.addStep({
			title: 'Как задать вопрос?',
			text: `Давай при создании вопроса будет придерживаться нескольких простых правил, чтобы всем было удобно работать.`,
			buttons: [
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-1',
			when: when_show
		});

		tour.addStep({
			title: 'Описание вопроса.',
			text: `Отрази здесь основную суть твоего вопроса, сформулируй его так, чтобы сразу было понятно о чем будет вопрос.<br>` +
				`(Например: «Доклад на тему: Как я провел лето».)`,
			attachTo: {
				element: '.field-question_title',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-2',
			when: when_show
		});

		tour.addStep({
			title: 'Предмет.',
			text: `Выбери предмет, по которому ты задаешь вопрос.`,
			attachTo: {
				element: '.field-discipline_name',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-3',
			when: when_show
		});

		tour.addStep({
			title: 'Тип задания.',
			text: `Выбери тип задания, чтобы людям было проще понять, что конкретно тебе нужно.`,
			attachTo: {
				element: '.field-type_id',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-4',
			when: when_show
		});

		tour.addStep({
			title: 'Преподаватель.',
			text: `При желании, ты можешь указать ФИО своего преподавателя.<br>` +
				`Если дисциплину у тебя ведут несколько преподавателей, то укажи того, у которого возникла проблема.<br>` +
				`(Например: представим ситуацию, что лекции у тебя ведёт Сидоров Д. А., а лабораторные работы Петрова Д. А., и у тебя возникли трудности именно с лабораторными работами.<br>` +
				`Следовательно, тебе нужно указать Петрову Д. А.)`,
			attachTo: {
				element: '.field-teacher_id',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-5',
			when: when_show
		});

		tour.addStep({
			title: 'Срок сдачи.',
			text: `Ты можешь указать срок сдачи задания, чтобы он был виден всем и тебе смогли помочь как можно быстрее.`,
			attachTo: {
				element: '.field-end_datetime',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-6',
			when: when_show
		});

		tour.addStep({
			title: 'Теги.',
			text: `Для чего нужны теги?<br>` +
				`Это очень важная функция, с помощью которой люди смогут быстрее найти твой вопрос.<br>` +
				`Чем больше тегов ты укажешь, тем лучше. Не бойся экспериментировать.<br>` +
				`(Например: 13 вариант, 3 задание, срочно, с объяснением, интегралы и т.д.)`,
			attachTo: {
				element: '.field-tag_list',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-7',
			when: when_show
		});

		tour.addStep({
			title: 'Текст вопроса.',
			text: `Чем подробнее ты опишешь вопрос, тем более точный ответ ты получишь.<br>` +
					`Не стесняйся.`,
			attachTo: {
				element: '.field-question_text',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-8',
			when: when_show
		});

		tour.addStep({
			title: 'Прикрепляемые файлы.',
			text: `Если необходимо добавить файлы с описанием вопроса, то можешь так же включить их.`,
			attachTo: {
				element: '.field-upload_files',
				on: 'bottom'
			},
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Дальше',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-9',
			when: when_show
		});

		tour.addStep({
			title: 'Дополнительные рекомендации.',
			text: `Лучше всего отразить максимум необходимой информации.<br>` +
				`Так можно будет быстрее для остальных найти вопрос, понять его и дать ответ.<br>` +
				`Старайся задавать в форме вопроса.<br>` +
				`Вопрос должен быть задан в рамках правил сайта.`,
			buttons: [
				{
					action() {
						return this.back();
					},
					classes: 'btn btn-sm btn-light rounded-pill',
					text: 'Назад'
				},
				{
					action() {
						return this.next();
					},
					text: 'Завершить',
					classes: 'btn btn-sm btn-primary rounded-pill'
				}
			],
			classes: 'tour-div',
			id: 'step-10',
			when: when_show
		});

		tour.start();
	}

	$('#tour-button').click(function (e) {
		e.preventDefault();
		tour();
	});
});
