<?php

	$directory_items = $this->get_items();
	_d($directory_items);
?>
<div class="wrap">
	<?php settings_errors($page['group']); ?>

	<h1>WPRD Folders Media</h1>

	<form id="wrfm-form" method="post" action="options.php">
		<?php settings_fields($page['group']); ?>
		<?php do_settings_sections($page['name']); ?>
		<?php submit_button(); ?>
	</form>

</div>

<?php //<!-- wpbody-content -->
// global $wp_settings_sections, $wp_settings_fields;
//
// _d($wp_settings_sections);
// _d($wp_settings_fields);

_d($params);
// _d($files);
// _d($post_types);
// _d($upload_dirname);

?>
<script id="wrfm-scripts">

var WRFM = WRFM || {};

WRFM.param = {
	plugin_name	: <?=wrfm_js_encode($page['name'])?>,
	post_types	: <?=wrfm_js_encode($post_types)?>,
	base_dir	: <?=wrfm_js_encode($upload_dirname)?>
};

</script>
