<?php

// Initiate settings section and fields.
add_action( 'admin_init', 'auhfc_settings_init' );

// Create menu item for settings page.
add_action( 'admin_menu', 'auhfc_add_admin_menu' );

// Add Settings page link to plugin actions cell.
add_filter( 'plugin_action_links_head-footer-code/head-footer-code.php', 'auhfc_plugin_settings_link' );

// Update links in plugin row on Plugins page.
add_filter( 'plugin_row_meta', 'auhfc_add_plugin_meta_links', 10, 2 );

// Load admin styles on plugin settings page
add_action( 'admin_enqueue_scripts', 'auhfc_admin_enqueue_scripts' );

/**
 * Enqueue the admin style
 */
function auhfc_admin_enqueue_scripts($hook) {
	if ( 'tools_page_head_footer_code' == $hook ) {
		wp_enqueue_style(
			'head-footer-code-admin',
			plugin_dir_url( __FILE__ ) . '../assets/css/admin.css',
			array(),
			WPAU_HEAD_FOOTER_CODE_VER
		);
	}
} // END function wpau_enqueue_colour_picker()

function auhfc_add_admin_menu(  ) {

	add_submenu_page(
		'tools.php',
		'Head & Footer Code',
		'Head & Footer Code',
		'manage_options',
		'head_footer_code',
		'auhfc_options_page'
	);

}

/**
 * Register a setting and its sanitization callback
 * define section and settings fields
 */
function auhfc_settings_init(  ) {

	/**
	 * Get settings from options table
	 */
	$auhfc_settings = auhfc_defaults();

	/**
	 * Register a setting and its sanitization callback.
	 * This is part of the Settings API, which lets you automatically generate
	 * wp-admin settings pages by registering your settings and using a few
	 * callbacks to control the output.
	 */
	register_setting( 'head_footer_code_sitewide_settings', 'auhfc_settings' );
	register_setting( 'head_footer_code_article_settings', 'auhfc_settings' );

	/**
	 * Settings Sections are the groups of settings you see on WordPress settings pages
	 * with a shared heading. In your plugin you can add new sections to existing
	 * settings pages rather than creating a whole new page. This makes your plugin
	 * simpler to maintain and creates less new pages for users to learn.
	 * You just tell them to change your setting on the relevant existing page.
	 */
	add_settings_section(
		'head_footer_code_sitewide_settings',
		esc_attr__( 'Site-wide Head and Footer Code', 'head-footer-code' ),
		'auhfc_sitewide_settings_section_description',
		'head_footer_code'
	);

	/**
	 * Register a settings field to a settings page and section.
	 * This is part of the Settings API, which lets you automatically generate
	 * wp-admin settings pages by registering your settings and using a few
	 * callbacks to control the output.
	 */
	add_settings_field(
		'auhfc_head_code',
		__( 'HEAD Code', 'head-footer-code' ),
		'auhfc_textarea_field_render',
		'head_footer_code',
		'head_footer_code_sitewide_settings',
		array(
			'field'       => 'auhfc_settings[head]',
			'value'       => $auhfc_settings['head'],
			'description' => __( 'Code to enqueue in HEAD section', 'head-footer-code' ),
			'field_class' => 'widefat code',
			'rows'        => 7,
		)
	);

	add_settings_field(
		'auhfc_footer_code',
		__( 'FOOTER Code', 'head-footer-code' ),
		'auhfc_textarea_field_render',
		'head_footer_code',
		'head_footer_code_sitewide_settings',
		array(
			'field'       => 'auhfc_settings[footer]',
			'value'       => $auhfc_settings['footer'],
			'description' => esc_html__( 'Code to enqueue in footer section (before the </body>)', 'head-footer-code' ),
			'field_class' => 'widefat code',
			'rows'        => 7,
		)
	);

	add_settings_field(
		'auhfc_priority',
		__( 'Priority', 'head-footer-code' ),
		'auhfc_number_field_render',
		'head_footer_code',
		'head_footer_code_sitewide_settings',
		array(
			'field'       => 'auhfc_settings[priority]',
			'value'       => $auhfc_settings['priority'],
			'description' => esc_html__( 'Priority of inserted head and footer code. Default is 10. Larger number inject code closer to </head> and </body>.', 'head-footer-code' ),
			'class'       => 'num',
			'min'         => 1,
			'max'         => 1000,
			'step'        => 1,
		)
	);

	/**
	 * Settings Sections are the groups of settings you see on WordPress settings pages
	 * with a shared heading. In your plugin you can add new sections to existing
	 * settings pages rather than creating a whole new page. This makes your plugin
	 * simpler to maintain and creates less new pages for users to learn.
	 * You just tell them to change your setting on the relevant existing page.
	 */
	add_settings_section(
		'head_footer_code_article_settings',
		esc_attr__( 'Article specific Head and Footer Code', 'head-footer-code' ),
		'auhfc_article_settings_section_description',
		'head_footer_code'
	);

	// Prepare clean list of post types w/o attachment
	$clean_post_types = get_post_types( array( 'public' => true ) );
	unset( $clean_post_types['attachment'] );

	add_settings_field(
		'auhfc_post_types',
		__( 'Post types', 'head-footer-code' ),
		'auhfc_checkbox_group_field_render',
		'head_footer_code',
		'head_footer_code_article_settings',
		array(
			'field'       => 'auhfc_settings[post_types]',
			'items'       => $clean_post_types,
			'value'       => $auhfc_settings['post_types'],
			'description' => esc_html__( 'Select which post types will have Article specific section. Default is post and page. Please note, even if you have Head/Footer Code set per article and then you disable that post type, article specific code will not be printed but only site-wide code.', 'head-footer-code' ),
			'class'       => 'checkbox',
		)
	);

} // END function auhfc_settings_init(  )

/**
 * This function provides textarea for settings fields
 */
function auhfc_textarea_field_render( $args ) {
	extract( $args );
	if ( empty( $rows ) ) {
		$rows = 7;
	}
	printf(
		'<textarea name="%1$s" id="%1$s" rows="%2$s" class="%3$s">%4$s</textarea><p class="description">%5$s</p>',
		$field,
		$rows,
		$field_class,
		$value,
		$description
	);
} // END function auhfc_textarea_field_render( $args )


/**
 * This function provides number input for settings fields
 */
function auhfc_number_field_render( $args ) {
	extract( $args );
	printf(
		'<input type="number" name="%1$s" id="%1$s" value="%2$s" class="%3$s" min="%4$s" max="%5$s" step="%6$s" /><p class="description">%7$s</p>',
		$field, // name/id
		$value, // value
		$class, // class
		$min, // min
		$max, // max
		$step, // step
		$description // description
	);
} // END function auhfc_number_field_render($args)

/**
 * This function provides checkbox group for settings fields
 */
function auhfc_checkbox_group_field_render( $args ) {

	extract( $args );

	// Checkbox items.
	$out = '<fieldset>';

	foreach ( $items as $key => $label ) {

		$checked = '';
		if ( ! empty( $value ) ) {
			$checked = ( in_array( $key, $value ) ) ? 'checked="checked"' : '';
		}

		$out .= sprintf(
			'<label for="%1$s_%2$s"><input type="checkbox" name="%1$s[]" id="%1$s_%2$s" value="%2$s" class="%3$s" %4$s />%5$s</label><br>',
			$field,
			$key,
			$class,
			$checked,
			$label
		);
	}

	$out .= '</fieldset>';
	$out .= sprintf( '<p class="description">%s</p>', $description );

	echo $out;

} // eom settings_field_checkbox()

function auhfc_sitewide_settings_section_description(  ) {
?>
<p>Define site-wide code and behavior. You can Add custom content like JavaScript, CSS, HTML meta and link tags, Google Analytics, site verification, etc.</p>
<?php
} // END function auhfc_sitewide_settings_section_description(  )

function auhfc_article_settings_section_description(  ) {
?>
<p>Define article specific behavior.</p>
<?php
} // END function auhfc_article_settings_section_description(  )

function auhfc_options_page(  ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_attr__( 'You do not have sufficient permissions to access this page.' ) );
	}
	// Render the settings template.
	include( sprintf( '%s/../templates/settings.php', dirname( __FILE__ ) ) );
}

/**
 * Generate Settings link on Plugins page listing
 * @param  array $links Array of existing plugin row links.
 * @return array        Updated array of plugin row links with link to Settings page
 */
function auhfc_plugin_settings_link( $links ) {
	$settings_link = '<a href="tools.php?page=head_footer_code">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
} // END public static function auhfc_plugin_settings_link( $links )

/**
 * Add link to official plugin pages
 * @param array $links  Array of existing plugin row links.
 * @param string $file  Path of current plugin file.
 * @return array        Array of updated plugin row links
 */
function auhfc_add_plugin_meta_links( $links, $file ) {
	if ( 'head-footer-code/head-footer-code.php' === $file ) {
		return array_merge(
			$links,
			array(
				sprintf(
					'<a href="https://wordpress.org/support/plugin/head-footer-code" target="_blank">%s</a>',
					__( 'Support' )
				),
				sprintf(
					'<a href="https://urosevic.net/wordpress/donate/?donate_for=head-footer-code" target="_blank">%s</a>',
					__( 'Donate' )
				),
			)
		);
	}
	return $links;
} // END function auhfc_add_plugin_meta_links( $links, $file )
