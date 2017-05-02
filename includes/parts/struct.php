
	<?php $name = 'default_dir'; $value = self::$wrfm_options[$name]; ?>

<div id="wrfm-settings-field">

	<span class="show_directory"><code><?=self::$upload_dirname?>/</code></span>
	<input name="<?=$this->input_name($name)?>" type="text" id="<?=$this->selecter($name)?>" value="<?=esc_attr($value)?>" class="regular-text" />
	<p class="description wrfm-panel">
		<?=__('下記、投稿タイプごとの指定がない場合のフォルダ作成の基本設定です。', self::$plugin_name)?><br />
		<?=__('空欄の場合はフォルダを作成しません。「/」で階層を分けることができ、空白があった場合は自動的に削除され詰められます。', self::$plugin_name)?>
	</p>

	<?php $name = 'dirs'; $dirs = self::$wrfm_options[$name]; ?>

	<div class="<?=$this->selecter('container')?>">
		<h2><?=__('投稿タイプごとのフォルダ作成設定', self::$plugin_name)?></h2>
		<ul class="<?=$this->selecter('filters')?>">

		<?php if( !empty($dirs) ) {
			include('post_type_filter.php');
		} ?>

		</ul>
		<div class="<?=$this->selecter('new_filter')?>">
			<p class="<?=$this->selecter('new_filter-info')?>"><?=__('投稿タイプごとのフォルダ作成設定を追加', self::$plugin_name)?></p>
		</div>
	</div>

</div>
