<?php

trait WRFM_Admin_Fields
{
	private static $allowed_post_types;

	/**
	 * 設定フィールドの登録
	 */
	public function register_settings_fields() {

		add_settings_section(
			'default',
			null,
			null,
			self::$plugin_name
		);

		$this->add_settings_field( 'uploads_use_yearmonth_folders', 'フォルダを年月ベースにする' );
		$this->add_settings_field( self::$plugin_name, '基本設定', [$this, 'valid_options'] );

	}


	/**
	 *
	 */
	private function add_settings_field( $id, $title, $callback = null, array $args = [] )
	{
		add_settings_field( $id, __( $title ),
			[$this, 'build_fields'],
			self::$plugin_name,
			'default',
			$args += ['id' => $id, 'label_for' => $id]
		);
		register_setting(self::$setting_group, $id, $callback);
	}


	/**
	 *
	 */
	public function valid_options( $options )
	{
		$new_options = [];
		$new_options['default_dir']	= $this->valid_input($options['default_dir']);
		$new_options['dirs']		= isset($options['dirs']) ? $options['dirs'] : [];

		$errors = [];

		// dirs 内に利用可能な post_type 及び filter があるか。
		foreach( $new_options['dirs'] as $post_type => $prop ) {

			$prop['filter'] = $this->valid_input($prop['filter']);

			if( !array_key_exists($post_type, self::$allowed_post_types)
					||
				empty($prop['filter'])
			) {
				unset($new_options['dirs'][$post_type]);
				add_settings_error( self::$setting_group, 'filter_empty', '投稿タイプ '.$post_type.' は有効な文字列を含んでいないため登録されませんでした。' );
				continue;
			}

			$new_options['dirs'][$post_type]['filter'] = $prop['filter'];

		}

		if( !empty($errors) ) {
			return get_option(self::$plugin_name);
		}

		return $new_options;
	}

	private function valid_input( $input )
	{
		if( empty($input) ) {
			return (string) $input;
		}

		$input = preg_replace('/[\s.]+|[^\w%-\/]+/', '', $input);
		preg_match_all('/%[^%]+%/', $input, $matches);

		foreach( $matches[0] as $result ) {
			if( !in_array($result, array_keys(self::$safe_tags)) ) {
				$input = str_replace($result, '', $input);
				add_settings_error( self::$setting_group, 'unknown_tags', $result . ' は使用できないタグです。' );
			}
		}

		$input = rtrim(preg_replace('/[\/]{2,}/', '/', $input), '/');

		return (string) $input;
	}


	/**
	 *
	 */
	public function build_fields( $args )
	{
		$id = $args['id'];

		switch( $id )
		{
			case self::$plugin_name:
				include('parts/struct.php');
				break;

			case 'uploads_use_yearmonth_folders':
				echo '<input name="'.$id.'" type="hidden" value="0" />',
			 		 '<label for="'.$id.'"><input name="'.$id.'" type="checkbox" id="'.$id.'" value="1" '. checked(1, get_option($id), false) .' /></label>',
			 		 '<span class="description">※チェックが入っている場合、以下は有効になりません。</span>';
				break;
		}
	}
}
