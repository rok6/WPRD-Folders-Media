<?php foreach( $dirs as $post_type => $dir ) :

$dir_id	= $dir['id'];
$dir_filter = $dir['filter'];

?>
	<li id="<?=esc_attr($dir_id)?>" class="wrfm-panel">
		<div class="<?=$this->selecter('filter-header')?>">
			<select>
			<?php foreach( self::$allowed_post_types as $allow_type => $label ) : ?>
				<option value="<?=esc_attr($allow_type)?>" <?php selected($allow_type, $post_type); ?>><?=esc_html($label)?></option>
			<?php endforeach; ?>
			</select>
			<span class="remove button-primary"><?=__('この項目を削除', self::$plugin_name)?></span>
		</div>
		<div class="<?=$this->selecter('filter-input')?>">
			<label><span class="show_directory"></span><input name="" type="text" data-name="filter" value="<?=esc_attr($dir_filter)?>" class="regular-text" /></label>
		</div>
		<div class="<?=$this->selecter('filter-hidden')?>">
			<input name="" type="hidden" data-name="id" value="<?=esc_attr($dir_id)?>" />
		</div>
	</li>

<?php endforeach;
