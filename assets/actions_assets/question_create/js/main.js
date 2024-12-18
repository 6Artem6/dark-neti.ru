
$(document).ready(function($) {
	const editor = new toastui.Editor({
		el: document.querySelector('#question_text'),
		height: '500px',
		initialEditType: 'markdown',
		previewStyle: 'vertical'
	});

	editor.getMarkdown();
});
