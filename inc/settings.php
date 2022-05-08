<?php
/**
 * Settings page for Head & Footer Code plugin
 *
 * @package Head_Footer_Code
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Create menu item for settings page.
add_action( 'admin_menu', 'auhfc_add_admin_menu' );

// Initiate settings section and fields.
add_action( 'admin_init', 'auhfc_settings_init' );

// Add Settings page link to plugin actions cell.
add_filter( 'plugin_action_links_head-footer-code/head-footer-code.php', 'auhfc_plugin_settings_link' );

// Update links in plugin row on Plugins page.
add_filter( 'plugin_row_meta', 'auhfc_add_plugin_meta_links', 10, 2 );

/**
 * Add submenu for Head & Footer code to Tools.
 */
function auhfc_add_admin_menu() {

	add_submenu_page(
		'tools.php',          // Parent Slug.
		'Head & Footer Code', // Page Title.
		'Head & Footer Code', // Menu Title.
		'manage_options',     // Capability.
		'head_footer_code',   // Menu Slug.
		'auhfc_options_page'  // Position.
	);

}

/**
 * Register a setting and its sanitization callback
 * define section and settings fields
 */
function auhfc_settings_init() {

	/**
	 * Get settings from options table
	 */
	$auhfc_settings            = auhfc_settings();
	$auhfc_homepage_blog_posts = 'posts' === get_option( 'show_on_front', false ) ? true : false;
	$wp52note                  = version_compare( get_bloginfo( 'version' ), '5.2', '<' ) ? ' ' . esc_html__( 'Requires WordPress 5.2 or later.', 'head-footer-code' ) : '';
	$head_note                 = auhfc_head_note();
	$body_note                 = auhfc_body_note();

	/**
	 * Settings Sections are the groups of settings you see on WordPress settings pages
	 * with a shared heading. In your plugin you can add new sections to existing
	 * settings pages rather than creating a whole new page. This makes your plugin
	 * simpler to maintain and creates less new pages for users to learn.
	 * You just tell them to change your setting on the relevant existing page.
	 */
	add_settings_section(
		'head_footer_code_settings_sitewide',                                     // Id.
		esc_html__( 'Site-wide head, body and footer code', 'head-footer-code' ), // Title.
		'auhfc_sitewide_settings_section_description',                            // Callback.
		'head_footer_code'                                                        // Page.
	);

	/**
	 * Register a settings field to a settings page and section.
	 * This is part of the Settings API, which lets you automatically generate
	 * wp-admin settings pages by registering your settings and using a few
	 * callbacks to control the output.
	 */
	add_settings_field(
		'auhfc_head_code',                     // Id.
		__( 'HEAD Code', 'head-footer-code' ), // Title.
		'auhfc_textarea_field_render',         // Callback.
		'head_footer_code',                    // Page.
		'head_footer_code_settings_sitewide',  // Section.
		array(                                 // Arguments.
			'field'       => 'auhfc_settings_sitewide[head]',
			'value'       => $auhfc_settings['sitewide']['head'],
			'description' => $head_note . sprintf(
				/* translators: %s will be replaced with preformatted HTML tag </head> */
				esc_html__( 'Code to enqueue in HEAD section (before the %s).', 'head-footer-code' ),
				auhfc_html2code( '</head>' )
			),
			'field_class' => 'widefat code codeEditor',
			'rows'        => 7,
		)
	);

	add_settings_field(
		'auhfc_priority_h',
		__( 'HEAD Priority', 'head-footer-code' ),
		'auhfc_number_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[priority_h]',
			'value'       => $auhfc_settings['sitewide']['priority_h'],
			'description' => sprintf(
				/* translators: 1: default HEAD priority, 2: preformatted HTML tag </head> */
				esc_html__( 'Priority for enqueued HEAD code. Default is %1$d. Larger number inject code closer to %2$s.', 'head-footer-code' ),
				10,
				auhfc_html2code( '</head>' )
			),
			'class'       => 'num',
			'min'         => 1,
			'max'         => 1000,
			'step'        => 1,
		)
	);

	add_settings_field(
		'auhfc_do_shortcode_h',
		__( 'Process HEAD Shortcodes', 'head-footer-code' ),
		'auhfc_select_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[do_shortcode_h]',
			'items'       => array(
				'y' => __( 'Enable', 'head-footer-code' ),
				'n' => __( 'Disable', 'head-footer-code' ),
			),
			'value'       => $auhfc_settings['sitewide']['do_shortcode_h'],
			'description' => esc_html__( 'If you wish to process shortcodes in the HEAD section, enable this option. Please note, shortcodes with malformed output in the HEAD section can break the rendering of your website!', 'head-footer-code' ),
			'class'       => 'regular-text',
		)
	);

	add_settings_field(
		'auhfc_body_code',
		__( 'BODY Code', 'head-footer-code' ),
		'auhfc_textarea_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[body]',
			'value'       => $auhfc_settings['sitewide']['body'],
			'description' => $body_note . sprintf(
				/* translators: %s will be replaced with preformatted HTML tag <body> */
				esc_html__( 'Code to enqueue in BODY section (after the %s).', 'head-footer-code' ),
				auhfc_html2code( '<body>' )
			) . $wp52note,
			'field_class' => 'widefat code codeEditor',
			'rows'        => 7,
		)
	);

	add_settings_field(
		'auhfc_priority_b',
		__( 'BODY Priority', 'head-footer-code' ),
		'auhfc_number_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[priority_b]',
			'value'       => $auhfc_settings['sitewide']['priority_b'],
			'description' => sprintf(
				/* translators: 1: default BODY priority, 2: preformatted HTML tag <body> */
				esc_html__(
					'Priority for enqueued BODY code. Default is %1$d. Smaller number inject code closer to %2$s.',
					'head-footer-code'
				) . $wp52note,
				10,
				auhfc_html2code( '<body>' ),
				$wp52note
			),
			'class'       => 'num',
			'min'         => 1,
			'max'         => 1000,
			'step'        => 1,
		)
	);

	add_settings_field(
		'auhfc_do_shortcode_b',
		__( 'Process BODY Shortcodes', 'head-footer-code' ),
		'auhfc_select_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[do_shortcode_b]',
			'items'       => array(
				'y' => __( 'Enable', 'head-footer-code' ),
				'n' => __( 'Disable', 'head-footer-code' ),
			),
			'value'       => $auhfc_settings['sitewide']['do_shortcode_b'],
			'description' => esc_html__( 'If you wish to process shortcodes in the BODY section, enable this option.', 'head-footer-code' ),
			'class'       => 'regular-text',
		)
	);

	add_settings_field(
		'auhfc_footer_code',
		__( 'FOOTER Code', 'head-footer-code' ),
		'auhfc_textarea_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[footer]',
			'value'       => $auhfc_settings['sitewide']['footer'],
			'description' => sprintf(
				/* translators: %s will be replaced with preformatted HTML tag </body> */
				esc_html__( 'Code to enqueue in footer section (before the %s).', 'head-footer-code' ),
				auhfc_html2code( '</body>' )
			),
			'field_class' => 'widefat code codeEditor',
			'rows'        => 7,
		)
	);

	add_settings_field(
		'auhfc_priority_f',
		__( 'FOOTER Priority', 'head-footer-code' ),
		'auhfc_number_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[priority_f]',
			'value'       => $auhfc_settings['sitewide']['priority_f'],
			'description' => sprintf(
				/* translators: 1: default FOOTER priority, 2: preformatted HTML tag </body> */
				esc_html__( 'Priority for enqueued FOOTER code. Default is %1$d. Larger number inject code closer to %2$s.', 'head-footer-code' ),
				10,
				auhfc_html2code( '</body>' )
			),
			'class'       => 'num',
			'min'         => 1,
			'max'         => 1000,
			'step'        => 1,
		)
	);

	add_settings_field(
		'auhfc_do_shortcode_f',
		__( 'Process FOOTER Shortcodes', 'head-footer-code' ),
		'auhfc_select_field_render',
		'head_footer_code',
		'head_footer_code_settings_sitewide',
		array(
			'field'       => 'auhfc_settings_sitewide[do_shortcode_f]',
			'items'       => array(
				'y' => __( 'Enable', 'head-footer-code' ),
				'n' => __( 'Disable', 'head-footer-code' ),
			),
			'value'       => $auhfc_settings['sitewide']['do_shortcode_f'],
			'description' => esc_html__( 'If you wish to process shortcodes in the FOOTER section, enable this option.', 'head-footer-code' ),
			'class'       => 'regular-text',
		)
	);

	/**
	 * Register a setting and its sanitization callback.
	 * This is part of the Settings API, which lets you automatically generate
	 * wp-admin settings pages by registering your settings and using a few
	 * callbacks to control the output.
	 */
	register_setting(
		'head_footer_code_settings', // Option group.
		'auhfc_settings_sitewide'    // Option name.
	);

	/**
	 * Add section for Homepage if show_on_front is set to Blog Posts
	 */
	if ( $auhfc_homepage_blog_posts ) {

		/**
		 * Settings Sections are the groups of settings you see on WordPress settings pages
		 * with a shared heading. In your plugin you can add new sections to existing
		 * settings pages rather than creating a whole new page. This makes your plugin
		 * simpler to maintain and creates less new pages for users to learn.
		 * You just tell them to change your setting on the relevant existing page.
		 */
		add_settings_section(
			'head_footer_code_settings_homepage',                                                          // Id.
			esc_html__( 'Head, body and footer code on Homepage in Blog Posts mode', 'head-footer-code' ), // Title.
			'auhfc_homepage_settings_section_description',                                                 // Callback.
			'head_footer_code'                                                                             // Page.
		);

		/**
		 * Register a settings field to a settings page and section.
		 * This is part of the Settings API, which lets you automatically generate
		 * wp-admin settings pages by registering your settings and using a few
		 * callbacks to control the output.
		 */
		add_settings_field(
			'auhfc_homepage_head_code',                     // Id.
			__( 'Homepage HEAD Code', 'head-footer-code' ), // Title.
			'auhfc_textarea_field_render',                  // Callback.
			'head_footer_code',                             // Page.
			'head_footer_code_settings_homepage',           // Section.
			array(                                          // Arguments.
				'field'       => 'auhfc_settings_homepage[head]',
				'value'       => $auhfc_settings['homepage']['head'],
				'description' => $head_note . sprintf(
					/* translators: %s will be replaced with preformatted HTML tag </head> */
					esc_html__( 'Code to enqueue in HEAD section (before the %s) on Homepage.', 'head-footer-code' ),
					auhfc_html2code( '</head>' )
				),
				'field_class' => 'widefat code codeEditor',
				'rows'        => 5,
			)
		);

		add_settings_field(
			'auhfc_homepage_body_code',
			__( 'Homepage BODY Code', 'head-footer-code' ),
			'auhfc_textarea_field_render',
			'head_footer_code',
			'head_footer_code_settings_homepage',
			array(
				'field'       => 'auhfc_settings_homepage[body]',
				'value'       => $auhfc_settings['homepage']['body'],
				'description' => $body_note . sprintf(
					/* translators: %s: preformatted HTML tag <body> */
					esc_html__( 'Code to enqueue in BODY section (after the %s) on Homepage.', 'head-footer-code' ),
					auhfc_html2code( '<body>' )
				) . $wp52note,
				'field_class' => 'widefat code codeEditor',
				'rows'        => 5,
			)
		);

		add_settings_field(
			'auhfc_homepage_footer_code',
			__( 'Homepage FOOTER Code', 'head-footer-code' ),
			'auhfc_textarea_field_render',
			'head_footer_code',
			'head_footer_code_settings_homepage',
			array(
				'field'       => 'auhfc_settings_homepage[footer]',
				'value'       => $auhfc_settings['homepage']['footer'],
				'description' => sprintf(
					/* translators: %s will be replaced with preformatted HTML tag </body> */
					esc_html__( 'Code to enqueue in footer section (before the %s) on Homepage.', 'head-footer-code' ),
					auhfc_html2code( '</body>' )
				),
				'field_class' => 'widefat code codeEditor',
				'rows'        => 5,
			)
		);

		add_settings_field(
			'auhfc_homepage_behavior',
			__( 'Behavior', 'head-footer-code' ),
			'auhfc_select_field_render',
			'head_footer_code',
			'head_footer_code_settings_homepage',
			array(
				'field'       => 'auhfc_settings_homepage[behavior]',
				'items'       => array(
					'append'  => esc_html__( 'Append to the site-wide code', 'head-footer-code' ),
					'replace' => esc_html__( 'Replace the site-wide code', 'head-footer-code' ),
				),
				'value'       => $auhfc_settings['homepage']['behavior'],
				'description' => esc_html__( 'Chose how the Homepage specific code will be enqueued in relation to site-wide code.', 'head-footer-code' ),
				'class'       => 'regular-text',
			)
		);

		/**
		 * Register a setting and its sanitization callback.
		 * This is part of the Settings API, which lets you automatically generate
		 * wp-admin settings pages by registering your settings and using a few
		 * callbacks to control the output.
		 */
		register_setting(
			'head_footer_code_settings', // Option group.
			'auhfc_settings_homepage'    // Option name.
		);

	} // END if ( $auhfc_homepage_blog_posts )

	/**
	 * Settings Sections are the groups of settings you see on WordPress settings pages
	 * with a shared heading. In your plugin you can add new sections to existing
	 * settings pages rather than creating a whole new page. This makes your plugin
	 * simpler to maintain and creates less new pages for users to learn.
	 * You just tell them to change your setting on the relevant existing page.
	 */
	add_settings_section(
		'head_footer_code_settings_article',                           // Id.
		esc_html__( 'Article specific settings', 'head-footer-code' ), // Title.
		'auhfc_article_settings_section_description',                  // Callback.
		'head_footer_code'                                             // Page.
	);

	// Prepare clean list of post types w/o attachment.
	$public_post_types = get_post_types( array( 'public' => true ), 'objects' );
	$clean_post_types  = array();
	foreach ( $public_post_types as $public_post_type => $public_post_object ) {
		if ( 'attachment' === $public_post_type ) {
			continue;
		}
		$clean_post_types[ $public_post_type ] = "{$public_post_object->label} ({$public_post_type})";
	}

	add_settings_field(
		'auhfc_post_types',                     // Id.
		__( 'Post Types', 'head-footer-code' ), // Title.
		'auhfc_checkbox_group_field_render',    // Vallback.
		'head_footer_code',                     // Page.
		'head_footer_code_settings_article',    // Section.
		array(                                  // Arguments.
			'field'       => 'auhfc_settings_article[post_types]',
			'items'       => $clean_post_types,
			'value'       => $auhfc_settings['article']['post_types'],
			'description' => esc_html__( 'Select which post types will have Article specific section. Please note, even if you have Head/Footer Code set per article and then you disable that post type, article specific code will not be printed but only site-wide code.', 'head-footer-code' ),
			'class'       => 'checkbox',
		)
	);

	/**
	 * Register a setting and its sanitization callback.
	 * This is part of the Settings API, which lets you automatically generate
	 * wp-admin settings pages by registering your settings and using a few
	 * callbacks to control the output.
	 */
	register_setting(
		'head_footer_code_settings', // Option group.
		'auhfc_settings_article'     // Option name.
	);

} // END function auhfc_settings_init()

/**
 * This function provides textarea for settings fields
 *
 * @param array $args Array of arguments (field, rows, field_class, value, description).
 */
function auhfc_textarea_field_render( $args ) {
	if ( empty( $args['rows'] ) ) {
		$rows = 7;
	}
	printf(
		'<textarea name="%1$s" id="%6$s" rows="%2$s" class="%3$s">%4$s</textarea><p class="description">%5$s</p>',
		$args['field'],
		$args['rows'],
		$args['field_class'],
		$args['value'],
		$args['description'],
		str_replace( ']', '', str_replace( '[', '_', $args['field'] ) )
	);
} // END function auhfc_textarea_field_render( $args )

/**
 * This function provides number input for settings fields
 *
 * @param array $args Array of arguments (field, value, min, max, step, rows, class, description).
 */
function auhfc_number_field_render( $args ) {
	printf(
		'<input type="number" name="%1$s" id="%1$s" value="%2$s" class="%3$s" min="%4$s" max="%5$s" step="%6$s" /><p class="description">%7$s</p>',
		$args['field'], // name/id.
		$args['value'], // value.
		$args['class'], // class.
		$args['min'], // min.
		$args['max'], // max.
		$args['step'], // step.
		$args['description'] // description.
	);
} // END function auhfc_number_field_render( $args )

/**
 * This function provides checkbox group for settings fields
 *
 * @param array $args Array of arguments (items, value, field, class, description).
 */
function auhfc_checkbox_group_field_render( $args ) {

	// Checkbox items.
	$out = '<fieldset>';

	foreach ( $args['items'] as $key => $label ) {

		$checked = '';
		if ( ! empty( $args['value'] ) ) {
			$checked = ( in_array( $key, $args['value'], true ) ) ? 'checked="checked"' : '';
		}

		$out .= sprintf(
			'<label for="%1$s_%2$s"><input type="checkbox" name="%1$s[]" id="%1$s_%2$s" value="%2$s" class="%3$s" %4$s />%5$s</label><br>',
			$args['field'],
			$key,
			$args['class'],
			$checked,
			$label
		);
	}

	if ( ! empty( $args['description'] ) ) {
		$out .= sprintf(
			'<p class="description">%s</p>',
			trim( $args['description'] )
		);
	}

	$out .= '</fieldset>';

	echo $out;

} // END function auhfc_checkbox_group_field_render( $args )

/**
 * This function provides select for settings fields
 *
 * @param  array $args Array of field arguments (class, field, items, value, description).
 */
function auhfc_select_field_render( $args ) {
	if ( empty( $args['class'] ) ) {
		$args['class'] = 'regular-text';
	}
	printf(
		'<select id="%1$s" name="%1$s" class="%2$s">',
		esc_attr( $args['field'] ),
		sanitize_html_class( $args['class'] )
	);
	foreach ( $args['items'] as $key => $val ) {
		$selected = ( $args['value'] === $key ) ? 'selected=selected' : '';
		printf(
			'<option %1$s value="%2$s">%3$s</option>',
			esc_attr( $selected ),      // 1
			sanitize_key( $key ),       // 2
			sanitize_text_field( $val ) // 3
		);
	}
	printf(
		'</select><p class="description">%s</p>',
		wp_kses(
			$args['description'],
			array(
				'a' => array(
					'href'   => array(),
					'target' => array( '_blank' ),
				),
				'strong',
				'em',
				'pre',
				'code',
			)
		)
	);
} // END function auhfc_select_field_render( $args )

/**
 * Print description for site-wide section
 */
function auhfc_sitewide_settings_section_description() {
	printf(
		'<p>%s</p>',
		esc_html__( 'Define site-wide code and behavior. You can Add custom content like JavaScript, CSS, HTML meta and link tags, Google Analytics, site verification, etc.', 'head-footer-code' )
	);
} // END function auhfc_sitewide_settings_section_description()

/**
 * Print description for homepage section
 */
function auhfc_homepage_settings_section_description() {
	printf(
		'<p>%s</p>',
		esc_html__( 'Define code and behavior for the Homepage in Blog Posts mode.', 'head-footer-code' )
	);
} // END function auhfc_homepage_settings_section_description()

/**
 * Print description for article section
 */
function auhfc_article_settings_section_description() {
	printf(
		'<p>%s</p>',
		esc_html__( 'Define what post types will support article specific features.', 'head-footer-code' )
	);
} // END function auhfc_article_settings_section_description()

/**
 * Print settings page from template
 */
function auhfc_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'head-footer-code' ) );
	}
	// Render the settings template.
	include( WPAU_HEAD_FOOTER_CODE_DIR . 'templates/settings.php' );
} // END function auhfc_options_page()

/**
 * Generate Settings link on Plugins page listing
 *
 * @param  array $links Array of existing plugin row links.
 * @return array        Updated array of plugin row links with link to Settings page
 */
function auhfc_plugin_settings_link( $links ) {
	$settings_link = sprintf( '<a href="tools.php?page=head_footer_code">%s</a>', __( 'Settings', 'head-footer-code' ) );
	array_unshift( $links, $settings_link );
	return $links;
} // END function auhfc_plugin_settings_link( $links )

/**
 * Add link to official plugin pages
 *
 * @param array  $links  Array of existing plugin row links.
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
					__( 'Support', 'head-footer-code' )
				),
				sprintf(
					'<a href="https://urosevic.net/wordpress/donate/?donate_for=head-footer-code" target="_blank">%s</a>',
					__( 'Donate', 'head-footer-code' )
				),
			)
		);
	}
	return $links;
} // END function auhfc_add_plugin_meta_links( $links, $file )
