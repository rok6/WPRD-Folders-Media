
(function(window, $) {

	'use strict';

	var $filters = $('.wrfm-filters');
	var $new_button = $('.wrfm-new_filter');
	var filter_count = 0;

	set_event_handler();

	$new_button.on('click', function() {
		add_new_dirs();
	});

	function add_new_dirs() {
		var _id = 'wrfm-add_filter_' + (++filter_count);

		var html = '<li id="'+ _id +'" class="wrfm-panel">';
			html += build_header(WRFM.param.post_types);
			html += build_input();
			html += build_hidden(_id);
			html += '</li>';

		$filters.append(html);

		set_event($filters.children( '#' + _id ));
	}

	function build_header($data) {

		var html = '';

		var select = '<select name="">';
		$.each($data, function(k, v) {
			select += '<option value="'+ k +'">'+ v +'</option>';
		});
		select += '</select>';

		html += '<div class="wrfm-filter-header">';
		html += select;
		html += '<span class="remove button-primary">この項目を削除</span>';
		html += '</div>';

		return html;
	}

	function build_input() {

		var html = '';

		html += '<div class="wrfm-filter-input">';
		html += '<label><span class="show_directory"></span><input name="" type="text" data-name="filter" value="%post_type%" class="regular-text" /></label>';
		html += '</div>';

		return html;
	}

	function build_hidden( id ) {

		var html = '';

		html += '<div class="wrfm-filter-hidden">';
		html += '<input name="" type="hidden" data-name="id" value="'+ id +'" />';
		html += '</div>';

		return html;
	}

	function set_event( $post_filter ) {

		var $show = $post_filter.find('.show_directory');
		var $remove = $post_filter.find('.remove');
		var $input = $post_filter.find('input');

		$post_filter.on('change', 'select', function(e) {
			var $this = $(this);
			var _type = $this.val();
			var _name = WRFM.param.plugin_name +'[dirs]['+ _type +']';

			$input.each(function() {
				var $this = $(this);
				$this.attr('name', _name + '['+ $this.attr('data-name') +']');
			});

			$show.html('<code>'+ WRFM.param.base_dir +'/</code>');
		});
		$post_filter.find('select').trigger('change');


		$remove.on('click', function() {
			$post_filter.off('change');
			$post_filter.remove();
		});

	}

	function set_event_handler() {
		var _count = 0;
		$filters.children('li').each(function() {
			var $this = $(this);
			var _id_num = $this.attr('id').match(/[0-9]+\.?[0-9]*/g);
			_count = _id_num > _count ? _id_num : _count;
			set_event($this);
		});
		filter_count = _count;
	}

})(this, this.jQuery);
