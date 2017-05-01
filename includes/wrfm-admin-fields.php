<?php

trait WRFM_Admin_Fields
{
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
		$this->add_settings_field( 'settings', '基本設定' );

	}


	/**
	 *
	 */
	private function add_settings_field( $id, $title, array $args = [] )
	{
		$field_id = ($id !== 'uploads_use_yearmonth_folders') ? self::$plugin_name : $id;

		add_settings_field( $field_id, __( $title ),
			[$this, 'build_fields'],
			self::$plugin_name,
			'default',
			$args += ['id' => $id, 'title' => $title, 'label_for' => $id]
		);

		register_setting(self::$setting_group, $field_id);

			_d('add_settings_field');
	}


	/**
	 *
	 */
	public function build_fields( $args )
	{
		$id = $args['id'];

		switch( $id )
		{
			case 'settings':
				include('parts/struct.php');
				break;
			case 'uploads_use_yearmonth_folders':
				echo '<input name="'.$id.'" type="hidden" value="0" />',
			 		 '<label for="'.$id.'"><input name="'.$id.'" type="checkbox" id="'.$id.'" value="1" '. checked(1, get_option($id), false) .' /></label>';
				break;
		}
	}
}
