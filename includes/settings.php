<?php //update-nag
require_once( ABSPATH . 'wp-admin/admin-header.php' );
?>

<div class="wrap">
	<h1>WPRD Folders Media</h1>

	<ul><?php foreach( $files as $file ) : ?>

		<li id="media_<?=esc_attr($file->ID)?>">
			<div class="post_title"><?=esc_html($file->post_title)?></div>
			<div class="post_url"><?=esc_html($file->guid)?></div>
			<div class="post_date"><?=esc_html($file->post_date)?></div>
			<div class="post_modified"><?=esc_html($file->post_modified)?></div>
		</li>

	<?php endforeach; ?></ul>
</div>

<?php //<!-- wpbody-content -->
//include( ABSPATH . 'wp-admin/admin-footer.php' );

//_d($files);
_d($post_types);
