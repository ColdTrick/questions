define(function() {
	
	var $ = require('jquery');
	var elgg = require('elgg');
	
	$(document).on('click', '#questions-move-to-discussions', function() {
		
		var text = $(this).attr('rel');
		console.log(text);
		if (!confirm(text)) {
			return false;
		}
		
		$(this).closest('form').prop('action', elgg.normalize_url('action/object/question/move_to_discussions')).submit();
		return true;
	});
});
