
	<?php $name = 'default_dir'; $value = get_option($name); ?>

	<span class="show_directory"><code><?=self::$upload_dirname?>/</code></span>
	<input name="<?=$this->input_name($name)?>" type="text" id="<?=$this->selecter($name)?>" value="<?=esc_attr($value)?>" class="regular-text" />
	<p class="description">
		下記、投稿タイプごとの指定がない場合のフォルダ作成の基本設定です。<br />
		空欄の場合はフォルダを作成しません。「/」で階層を分けることができ、空白があった場合は自動的に削除され詰められます。
	</p>;

	<?php $name = 'dirs'; $value = $this->get_param($name); ?>

	<div class="<?=$this->selecter('container')?>">
		<ul id="<?=$this->selecter('filters')?>">

		<?php if( !empty($this->options['dirs']) ) : ?>

		<?php endif; ?>

		</ul>
		<div id="<?=$this->selecter('new_filter')?>">
			<p class="<?=$this->selecter('new_filter-info')?>"><?=__('投稿タイプの毎のフォルダ条件を追加', self::$plugin_name)?></p>
		</div>
	</div>
