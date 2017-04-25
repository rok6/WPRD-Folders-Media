(function(window, $) {

	'use strict';

	var custom_uploader;

	$('#demo-media').on('click', function(e) {
		e.preventDefault();
		_uploader();
	});

	function _uploader() {

		if( custom_uploader ) {
			custom_uploader.open();
			return;
		}

		custom_uploader = wp.media({
			title	: 'Choose Image',
			library	: { type: 'image' },
			button	: { text: 'Choose Image' },
			multiple: true
		});

		custom_uploader.on('select', function() {
			var images = custom_uploader.state().get('selection');

			images.each(function(file){
				$('#demo-images').append('<img src="' + file.toJSON().url + '" />');
			});
		});

		custom_uploader.open();
	}

})(this, this.jQuery);
