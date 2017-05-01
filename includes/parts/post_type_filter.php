foreach( $this->options['dirs'] as $post_type => $dir_set ) {

	$_namebase = self::$plugin_name . '[dirs]['.$post_type.']';

	$_type	= !empty($dir_set['post_type']) ? $dir_set['post_type'] : '';
	$_id	= !empty($dir_set['id']) ? $dir_set['id'] : '';
	$_filter = !empty($dir_set['filter']) ? $dir_set['filter'] : '';

	echo '<li id="'. esc_attr($_id) .'">',
			'<div class="wrfm-filter-header">',
				'<select name="'. $_namebase .'">';
				foreach( $this->post_types as $type => $label ) {
					echo '<option value="'. esc_attr($type) .'" '. selected($type, $_type, false) .'>'. esc_html($label) .'</option>';
				}
	echo		'</select>',
				'<span class="remove">この項目を削除</span>',
			'</div>',
			'<div class="wrfm-filter-input">',
				'<label><span class="show_directory"></span><input name="" type="text" data-name="filter" value="'. esc_attr($_filter) .'" /></label>',
			'</div>',
			'<div class="wrfm-filter-hidden">',
				'<input name="" type="hidden" data-name="id" value="'. esc_attr($_id) .'" />',
			'</div>',
		 '</li>';
		 ;
	}
