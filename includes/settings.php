<?php

?>
<div class="wrap">

	<h1>WPRD Folders Media</h1>

	<form id="wrfm-form" method="post" action="options.php">
		<?php settings_fields($setting_group); ?>
		<?php do_settings_sections($page_name); ?>
		<?php submit_button(); ?>
	</form>

</div>

<?php //<!-- wpbody-content -->
// global $wp_settings_sections, $wp_settings_fields;
//
// _d($wp_settings_sections);
// _d($wp_settings_fields);

_d($params);

// _d($post_types);
?>
<script id="wrfm-scripts">

var WRFM = WRFM || {};

WRFM.param = {
	plugin_name	: <?=wrfm_js_encode($plugin_name)?>,
	post_types	: <?=wrfm_js_encode($post_types)?>,
	base_dir	: <?=wrfm_js_encode($upload_base_dir)?>
};

(function(window, $) {

	'use strict';

	var $filters = $('#wrfm-filters');
	var $new_button = $('#wrfm-new_filter');
	var filter_count = 0;

	set_event_handler();

	$new_button.on('click', function() {
		add_new_dirs();
	});

	function add_new_dirs() {
		var _id = 'wrfm-add_filter_' + (++filter_count);

		var html = '<li id="'+ _id +'">';
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
		html += '<span class="remove">この項目を削除</span>';
		html += '</div>';

		return html;
	}

	function build_input() {

		var html = '';

		html += '<div class="wrfm-filter-input">';
		html += '<label><span class="show_directory"></span><input name="" type="text" data-name="filter" value="" /></label>';
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

			$this.attr('name', _name + '[post_type]');
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

</script>
