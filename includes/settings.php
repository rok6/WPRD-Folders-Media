<?php

?>
<div class="wrap">

	<h1>WPRD Folders Media</h1>

	<form method="post" action="options.php">
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
	post_types	: <?=wrfm_js_encode($post_types)?>
};

(function(window, $) {

	'use strict';

	var $filters = $('#wrfm-filters');
	var $new_button = $('#wrfm-new_filter');

	$new_button.on('click', function() {
		add_new_dirs();
	});

	function add_new_dirs() {
		var props = '<li>';
			props += build_select(WRFM.param.post_types);
			props += '<span class="show_directory"></span>';
			props += '<span class="remove">×</span>';
			props += '</li>';

		$filters.append(props);

		$filters.children.off('change', 'select', function() {

		})

		$(props).on('click', 'select', function(e) {
			console.log(this);
			$(this).attr('name', WRFM.param.plugin_name +'[dirs][]');
		});
	}

	function build_select($data) {

		var html = '';

		html = '<select name="'+ WRFM.param.plugin_name +'[dirs][]">';
		$.each($data, function(k, v) {
			html += '<option value="'+ k +'">'+ v +'</option>';
		});
		html += '</select>';

		return html;
	}

})(this, this.jQuery);

</script>
