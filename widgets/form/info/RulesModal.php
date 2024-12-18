<?php
namespace app\widgets\form\info;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};


class RulesModal extends Widget
{

	public function run()
	{
		$id = 'rulesModal';
		$title = Yii::t('app', 'Основные правила Сайта');
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

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', 'Сайт «DARK-NETi» предназначен для вопросов и ответов студентов по учёбе.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', 'Миссия «DARK-NETi» - создание платформы для взаимопомощи студентов в области учёбы, обмена доступной для всех участников сообщества полезной информации и формирование на её основе структурированной базы знаний.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '1. Размещая любую информацию (включая вопрос, ответ, комментарий) на страницах Сайта, пользователь:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '1. 1.1. Гарантирует наличие у него всех необходимых прав на размещение этой информации в публичном доступе в сети Интернет;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '1.2. Изъявляет своё желание нести полную правовую ответственность, в случае выявления несоответствия размещённой им информации нормам действующего законодательства Российской Федерации;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '1.3. Даёт добровольное согласие на редактирование, модификацию и удаление размещённой им информации, модераторами Сайта, в целях соблюдения регламента работы Сайта;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '1.4. Обязуется соблюдать требования модераторов Сайта в части оформления, дополнения и конкретизации представленной им информации.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '2. Перед тем как задать вопрос пользователь Сайта обязан:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '2.1. Убедиться в том, что вопрос, который он собирается задать, соответствует тематике Сайта, т.е. имеет непосредственное отношение к учебной сфере');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '2.2 Убедиться в том, что в сети Интернет, и на страницах Сайта в частности, отсутствуют ответы на данный вопрос');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '2.3 Убедиться в том, что публикация не адресована модераторам Сайта. Помните, что для обращения к администраторам электронных ресурсов следует использовать предусмотренные этими администраторами каналы связи, например, если речь идёт о данном Сайта - форму обратной связи.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '3. В процессе создания вопроса пользователь Сайта обязан:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.1. Указывать только предмет, имеющий непосредственное отношение к вопросу. Данное требование обусловлено тем, что многие пользователи отслеживают поступление новых вопросов по конкретным предметам в своих лентах и подписываются на почтовые уведомления о поступлении новых вопросов. Соответственно, каждый раз, когда какой-либо тег указывается некорректно, это приводит к тому, что все подписанные на этот предмет пользователи получают нерелевантную их интересам информацию и, как следствие, напрасно тратят время на её прочтение. Поэтому очень важно уважать время пользователей, стремящихся поделиться своими знаниями, и указывать только предмет, имеющий непосредственное отношение к вопросу.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.2. Формулировать вопрос максимально информативно и однозначно. Использование общих формулировок приводит к тому, что пользователи оказываются вынуждены тратить время на ознакомление с вопросом, хотя он может быть заведомо им не интересен. Поэтому, вместо общих формулировок (например, «Как решать задачу?»), следует использовать максимально детальные формулировки (например, «Как решить задачу по физике по теме сила Лоренца?»).');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.3. Формулировать вопрос в вежливой форме. Следует избегать употребления панибратских и фамильярных обращений к пользователям Сайта.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.4. Помнить о том, что сайт «DARK-NETi» не является форумом, чатом или социальной сетью. Следует избегать употребления речевых оборотов, характерных для этих типов ресурсов. Вопрос и его описание не должны содержать приветствий и прочих «лирических отступлений».');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.5. Использовать только общепринятую терминологию. Не следует злоупотреблять жаргонизмами, знакомыми лишь узкому кругу специалистов.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.6. Соблюдать нормы русского языка и принципы построения вопросительных предложений. Формулируя текст вопроса не следует злоупотреблять ПРОПИСНЫМИ БУКВАМИ. Также следует понимать, что само по себе добавление вопросительного знака не превращает любое предложение в вопрос, поэтому следует использовать порядок слов, характерный для вопросительных предложений.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.7. Не размещать несколько разнородных вопросов в рамках одного вопроса. Наличие дополнительных вопросов в описании вопроса допускается лишь в том случае, если ответы на эти вопросы непосредственно взаимосвязаны друг с другом и могут рассматриваться как подвопросы одного сложносочинённого вопроса.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '3.8. Использовать для вставки изображений область для загрузки файлов (размещение ссылок на изображения запрещено).');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '4. После размещения вопроса пользователю запрещается осуществлять:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '4.1. Дублирование вопроса, который уже размещался на страницах Сервиса. В том числе и в случае, если вопрос был удалён модератором, или на вопрос не был дан ответ (т.е. категорически запрещается дублирование вопроса с целью повторного привлечения к нему внимания).');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '4.2. Редактирование вопроса с целью его искажения или замены бессвязным текстом. На работу с каждым вопросом тратят время другие пользователи ресурса: модераторы (на его редактирование) и пользователи (на его прочтение и подготовку ответа). Соответственно, при удалении вопроса, потраченное ими время будет обесценено.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '5. Категорически запрещается размещать на страницах Сайта:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.1. Оскорбления. Любые оскорбительные формулировки запрещены. Даже в том случае, если наличествуют неопровержимые доказательства того, что в указанных оскорблениях имеется доля правды.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.2. Экспрессивную лексику. Запрещены любые формы размещения экспрессивных слов и выражений. Это означает, что любые реплики, содержащие экспрессивную лексику, будут удалены, даже если экспрессивные выражения размещены в составе url (для маскировки нецензурных url можно использовать сервис сокращения ссылок goo.gl). Мы намеренно не ограничиваем перечень запрещённых форм употребления экспрессивной лексики путём подробного перечисления. Сами понимаете, народ у нас изобретательный. Кто-то догадается нецензурное слово из смайликов выложить, а потом будет всех уверять, что мы — деспоты, ограничили его доступ к ресурсу за то, что нашими правилами не было запрещено.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.3. Любую информацию, распространение которой запрещено действующим российским законодательством. Напоминаем, что, помимо прочего, под этот запрет подпадают нацистская символика и атрибутика, а также символика и атрибутика экстремистских организаций (Ст. 20.3 КоАП РФ). Соответственно, использование такого рода изображений на страницах Сайт неизбежно повлечёт за собой санкции со стороны администрации Сайта.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.4. Любую информацию, способную спровоцировать нарушение законодательства РФ. Описания нарушений законодательства или способов ухода от наказания за противоправные деяния.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.5. Любую информацию, распространение которой может повлечь за собой деструктивные для общества последствия.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.6. Любые элементы рекламы, включая обзоры, анонсы, спам и реферальные ссылки.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.7. Ссылки на малоизвестные источники. В том числе, запрещается использовать для создания коротких ссылок редиректы на персональном или малоизвестном ресурсе. Ссылки на таких ресурсах могут в любой момент быть перенаправлены на вредоносный ресурс. Поэтому, чтобы обезопасить пользователей, мы удаляем такие ссылки со страниц Сервиса. Рекомендуется использовать для сокращения ссылок специальные сервисы крупных компаний (goo.gl или vk.com/cc).');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.8. Чужие персональные данные и чужую контактную информацию. Под этот запрет подпадает и информация, полученная из открытых источников и призванная изобличить действия мошенников.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.9. Изображения, не имеющие непосредственного отношения к вопросу и поиску ответа на этот вопрос.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.10. Объявления. В отличие от вопроса, ответа или комментария, объявление направлено не на поиск ответа на поставленный вопрос, а на распространение определённой информации. Под данный запрет подпадают любые объявления, в том числе объявления о поиске наставника и объявления о раздаче инвайтов.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.11. Опросы и соцопросы. Ключевая особенность опросов и соцопросов заключается в том, что для их авторов важен не каждый ответ по-отдельности, а статистическая сумма всех ответов одновременно.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.12. Вакансии, портфолио, резюме и предложения услуг.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.13. Флуд. Будут удалены ответы и комментарии, состоящие исключительно из смайликов, картинок, видео- или аудиозаписей, а также, не имеющие непосредственного отношения к вопросу, в рамках обсуждения которого они размещаются. Важно понимать, что, размещая любую информацию, пользователь вынуждает тратить время на её прочтение всех тех, кого интересует непосредственное решение вопроса. Поэтому следует воздерживаться от размещения бессмысленной/бесполезной информации.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.14. Вопросы, провоцирующие высказывание предположений. Например, «Почему Правительство принимает законы, вредящие электронному бизнесу?».');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', 'В качестве другого распространённого примера вопросов, провоцирующих высказывание предположений, можно привести вопросы типа «Можно ли устроиться на работу Х в Z лет?», поскольку, как правило, они:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', ' - бесполезны не только для других пользователей, но и для самого автора, поскольку от ответов на такой вопрос не зависят ни ситуация на рынке, ни личностные качества автора вопроса;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', ' - затрудняют пользователям поиск по Сайту.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.15. Троллинг. Если вопросы, ответы и комментарии пользователя носят преимущественно дискуссионный характер, содержат переходы на личности, и провоцируют пользователей на ведение спора и выяснение отношений, то доступ такого пользователя к Сайта может быть ограничен по усмотрению администрации Сайта.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '5.16. Жалобы, сообщения об ошибках и проблемах в работе электронных ресурсов. В подавляющем большинстве случаев, инициирование публичных обсуждений такого рода влечёт за собой высказывание предположений, слухов и заблуждений. Это вынуждает сотрудников обсуждаемых электронных ресурсов тратить время на отслеживание и комментирование таких дискуссий. Более того, может возникнуть ситуация, при которой представители электронного ресурса знают объективную причину, по которой то или иное решение не может быть реализовано, но не имеют права предоставить объяснение пользователям, поскольку это приведёт к нарушению их обязательств по сохранению коммерческой тайны. Чтобы не провоцировать возникновение таких противоречивых ситуаций, следует использовать для сообщения об ошибках и проблемах в работе электронных ресурсов обращения в службу поддержки.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '6. Категорически запрещается вводить пользователей Сайта в заблуждение, в том числе:');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '6.1. Выдавать себя за модератора, эксперта, известную личность или другого пользователя Сайта;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '6.2. Использовать элементы оформления, которые могут создать у пользователей впечатление того, что владельцу аккаунта доступны какие-то особые привилегии (например, подпись к ответам/комментариям) или особые полномочия;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '6.3. Накручивать показатель «Полезный» у ответов/комментариев и хвалить их, а также накручивать подписчиков вопросам с привлечением других пользователей;');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '6.4. Отправлять жалобы на вопросы, ответы и комментарии не содержащие нарушений правил Сайта.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '7. Контроль за соблюдением регламента работы Сайта');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '7.1. Контроль за соблюдением регламента работы Сайта осуществляют пользователи Сайта, наделённые полномочиями модераторов.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '7.2. В качестве санкций по отношению к аккаунтам пользователей могут применяться: временное ограничение доступа к функциям Сайта (от 1 до 360 суток), бессрочное ограничение доступа к функциям Сайта (с возможностью восстановления полномочий по усмотрению администрации Сайта) и удаление аккаунта (в случае выявления целенаправленных нарушений регламента работы Сайта). Выбор санкций осуществляется в индивидуальном порядке, исходя из частоты и тяжести нарушений пользователем регламента работы Сайта.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '7.3. В случае выявления дополнительных обстоятельств нарушения, или других сопутствующих нарушений, срок ограничения доступа к Сайта может быть увеличен вплоть до бессрочного.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '7.4. В случае выявления попытки обхода суточных ограничений, правил Сайта и санкций за нарушение правил Сайта (например, путём использования аккаунта другого пользователя), аккаунты нарушителей подлежат бессрочной блокировке или полному удалению.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '7.5. В случае выявления противоречий между данным регламентом и пользовательским Соглашением, приоритет имеет данный регламент.');
		echo Html::endTag('p');

		echo Html::beginTag('p', ['class' => ['mb-4', 'mt-2', 'text-secondary']]);
		echo Yii::t('app', '8. Обсуждение санкций');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '8.1. Любые вопросы, связанные с обсуждением санкций за нарушение данного регламента следует адресовать в службу поддержки.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '8.2. Не рекомендуется инициировать обсуждения действий модераторов Сайта. Напоминаем, что Вы всегда можете получить разъяснения действий модераторов и потребовать пересмотра санкций, обратившись в службу поддержки.');
		echo Html::endTag('p');
		echo Html::beginTag('p', ['class' => ['mb-3', 'text-secondary']]);
		echo Yii::t('app', '8.3. Модераторы Сайта оставляют за собой право отказывать в ведении конструктивного диалога пользователям, формулирующим свои обращения в грубой/оскорбительной форме.');
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
