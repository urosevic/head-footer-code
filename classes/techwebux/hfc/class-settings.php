<?php
/**
 * Settings page for Head & Footer Code plugin
 *
 * @package Head_Footer_Code
 */

namespace Techwebux\Hfc;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Techwebux\Hfc\Main;
use Techwebux\Hfc\Common;

class Settings {

	private $settings;
	public $allowed_html;
	public $form_allowed_html;
	public $security_risk_notice;

	public function __construct() {
		$this->settings          = Main::settings();
		$this->allowed_html      = Common::allowed_html();
		$this->form_allowed_html = Common::form_allowed_html();

		// Create menu item for settings page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Initiate settings section and fields.
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		// Add Settings page link to plugin actions cell.
		add_filter( 'plugin_action_links_' . plugin_basename( HFC_FILE ), array( $this, 'plugin_settings_link' ) );

		// Update links in plugin row on Plugins page.
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
	} // END public function __construct

	/**
	 * Add submenu for Head & Footer code to Tools.
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'tools.php',                   // Parent Slug.
			HFC_PLUGIN_NAME,               // Page Title.
			HFC_PLUGIN_NAME,               // Menu Title.
			'manage_options',              // Capability.
			HFC_PLUGIN_SLUG,               // Menu Slug.
			array( $this, 'options_page' ) // Callback.
			// Position.
		);
	} // END public function add_admin_menu

	/**
	 * Register a setting and its sanitization callback
	 * define section and settings fields
	 */
	public function settings_init() {
		/**
		 * Get settings from options table
		 */
		$auhfc_homepage_blog_posts  = 'posts' === get_option( 'show_on_front', false ) ? true : false;
		$wp52note                   = version_compare( get_bloginfo( 'version' ), '5.2', '<' ) ? ' ' . esc_html__( 'Requires WordPress 5.2 or later.', 'head-footer-code' ) : '';
		$head_note                  = $this->head_note();
		$body_note                  = $this->body_note();
		$this->security_risk_notice = Common::security_risk_notice();

		/**
		 * Settings Sections are the groups of settings you see on WordPress settings pages
		 * with a shared heading. In your plugin you can add new sections to existing
		 * settings pages rather than creating a whole new page. This makes your plugin
		 * simpler to maintain and creates less new pages for users to learn.
		 * You just tell them to change your setting on the relevant existing page.
		 */
		add_settings_section(
			'head_footer_code_settings_sitewide',                             // Id.
			__( 'Site-wide head, body and footer code', 'head-footer-code' ), // Title.
			array( $this, 'sitewide_settings_section_description' ),          // Callback.
			HFC_PLUGIN_SLUG                                                   // Page.
		);

		/**
		 * Register a settings field to a settings page and section.
		 * This is part of the Settings API, which lets you automatically generate
		 * wp-admin settings pages by registering your settings and using a few
		 * callbacks to control the output.
		 */
		$this->add_field(
			'head',                                // Id.
			esc_html__( 'HEAD Code', 'head-footer-code' ), // Title.
			'textarea_field_render',               // Callback method name.
			'sitewide',                            // Section.
			array(                                 // Arguments.
				'description' => $head_note . '<p>' . sprintf(
					/* translators: %s will be replaced with preformatted HTML tag </head> */
					esc_html__( 'Code to enqueue in HEAD section (before the %s).', 'head-footer-code' ),
					Common::html2code( '</head>' )
				) . '</p>',
				'field_class' => 'widefat code codeEditor',
				'rows'        => 7,
			)
		);

		$this->add_field(
			'priority_h',
			esc_html__( 'HEAD Priority', 'head-footer-code' ),
			'number_field_render',
			'sitewide',
			array(
				'description' => sprintf(
					/* translators: 1: default HEAD priority, 2: preformatted HTML tag </head> */
					esc_html__( 'Priority for enqueued HEAD code. Default is %1$d. Larger number inject code closer to %2$s.', 'head-footer-code' ),
					10,
					Common::html2code( '</head>' )
				),
				'class'       => 'num',
				'min'         => 1,
				'max'         => 1000,
				'step'        => 1,
			)
		);

		$this->add_field(
			'do_shortcode_h',
			esc_html__( 'Process HEAD Shortcodes', 'head-footer-code' ),
			'select_field_render',
			'sitewide',
			array(
				'items'       => array(
					'y' => __( 'Enable', 'head-footer-code' ),
					'n' => __( 'Disable', 'head-footer-code' ),
				),
				'description' => esc_html__( 'If you wish to process shortcodes in the HEAD section, enable this option. Please note, shortcodes with malformed output in the HEAD section can break the rendering of your website!', 'head-footer-code' ),
				'class'       => 'regular-text',
			)
		);

		$this->add_field(
			'body',
			esc_html__( 'BODY Code', 'head-footer-code' ),
			'textarea_field_render',
			'sitewide',
			array(
				'description' => $body_note . '<p>' . sprintf(
					/* translators: %s will be replaced with preformatted HTML tag <body> */
					esc_html__( 'Code to enqueue in BODY section (after the %s).', 'head-footer-code' ),
					Common::html2code( '<body>' )
				) . ' ' . $wp52note . '</p>',
				'field_class' => 'widefat code codeEditor',
				'rows'        => 7,
			)
		);

		$this->add_field(
			'priority_b',
			esc_html__( 'BODY Priority', 'head-footer-code' ),
			'number_field_render',
			'sitewide',
			array(
				'description' => sprintf(
					/* translators: 1: default BODY priority, 2: preformatted HTML tag <body> */
					esc_html__(
						'Priority for enqueued BODY code. Default is %1$d. Smaller number inject code closer to %2$s.',
						'head-footer-code'
					),
					10,
					Common::html2code( '<body>' )
				)
				. $wp52note,
				'class'       => 'num',
				'min'         => 1,
				'max'         => 1000,
				'step'        => 1,
			)
		);

		$this->add_field(
			'do_shortcode_b',
			esc_html__( 'Process BODY Shortcodes', 'head-footer-code' ),
			'select_field_render',
			'sitewide',
			array(
				'items'       => array(
					'y' => __( 'Enable', 'head-footer-code' ),
					'n' => __( 'Disable', 'head-footer-code' ),
				),
				'description' => esc_html__( 'If you wish to process shortcodes in the BODY section, enable this option.', 'head-footer-code' ),
				'class'       => 'regular-text',
			)
		);

		$this->add_field(
			'footer',
			esc_html__( 'FOOTER Code', 'head-footer-code' ),
			'textarea_field_render',
			'sitewide',
			array(
				'description' => '<p>' . sprintf(
					/* translators: %s will be replaced with preformatted HTML tag </body> */
					esc_html__( 'Code to enqueue in footer section (before the %s).', 'head-footer-code' ),
					Common::html2code( '</body>' )
				) . '</p>',
				'field_class' => 'widefat code codeEditor',
				'rows'        => 7,
			)
		);

		$this->add_field(
			'priority_f',
			esc_html__( 'FOOTER Priority', 'head-footer-code' ),
			'number_field_render',
			'sitewide',
			array(
				'description' => sprintf(
					/* translators: 1: default FOOTER priority, 2: preformatted HTML tag </body> */
					esc_html__( 'Priority for enqueued FOOTER code. Default is %1$d. Larger number inject code closer to %2$s.', 'head-footer-code' ),
					10,
					Common::html2code( '</body>' )
				),
				'class'       => 'num',
				'min'         => 1,
				'max'         => 1000,
				'step'        => 1,
			)
		);

		$this->add_field(
			'do_shortcode_f',
			esc_html__( 'Process FOOTER Shortcodes', 'head-footer-code' ),
			'select_field_render',
			'sitewide',
			array(
				'items'       => array(
					'y' => __( 'Enable', 'head-footer-code' ),
					'n' => __( 'Disable', 'head-footer-code' ),
				),
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
			'auhfc_settings_sitewide',   // Option name.
			array(
				'sanitize_callback' => array( $this, 'sanitize_sitewide' ),
			)
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
				array( $this, 'homepage_settings_section_description' ),                                       // Callback.
				HFC_PLUGIN_SLUG                                                                                // Page.
			);

			/**
			 * Register a settings field to a settings page and section.
			 * This is part of the Settings API, which lets you automatically generate
			 * wp-admin settings pages by registering your settings and using a few
			 * callbacks to control the output.
			 */
			$this->add_field(
				'head',                             // Id.
				esc_html__( 'Homepage HEAD Code', 'head-footer-code' ), // Title.
				'textarea_field_render',                                // Callback name.
				'homepage',                   // Section.
				array(                                                  // Arguments.
					'label'       => __( 'Homepage HEAD Code', 'head-footer-code' ),
					'description' => $head_note . '<p>' . sprintf(
						/* translators: %s will be replaced with preformatted HTML tag </head> */
						esc_html__( 'Code to enqueue in HEAD section (before the %s) on Homepage.', 'head-footer-code' ),
						Common::html2code( '</head>' )
					) . '</p>',
					'field_class' => 'widefat code codeEditor',
					'rows'        => 5,
				)
			);

			$this->add_field(
				'body',
				esc_html__( 'Homepage BODY Code', 'head-footer-code' ),
				'textarea_field_render',
				'homepage',
				array(
					'label'       => __( 'Homepage BODY Code', 'head-footer-code' ),
					'description' => $body_note . '<p>' . sprintf(
						/* translators: %s: preformatted HTML tag <body> */
						esc_html__( 'Code to enqueue in BODY section (after the %s) on Homepage.', 'head-footer-code' ),
						Common::html2code( '<body>' )
					) . '</p>'
					. $wp52note,
					'field_class' => 'widefat code codeEditor',
					'rows'        => 5,
				)
			);

			$this->add_field(
				'footer',
				esc_html__( 'Homepage FOOTER Code', 'head-footer-code' ),
				'textarea_field_render',
				'homepage',
				array(
					'label'       => __( 'Homepage FOOTER Code', 'head-footer-code' ),
					'description' => '<p>' . sprintf(
						/* translators: %s will be replaced with preformatted HTML tag </body> */
						esc_html__( 'Code to enqueue in footer section (before the %s) on Homepage.', 'head-footer-code' ),
						Common::html2code( '</body>' )
					) . '</p>',
					'field_class' => 'widefat code codeEditor',
					'rows'        => 5,
				)
			);

			$this->add_field(
				'behavior',
				esc_html__( 'Behavior', 'head-footer-code' ),
				'select_field_render',
				'homepage',
				array(
					'items'       => array(
						'append'  => __( 'Append to the site-wide code', 'head-footer-code' ),
						'replace' => __( 'Replace the site-wide code', 'head-footer-code' ),
					),
					'description' => esc_html__( 'Chose how the Homepage specific code will be enqueued in relation to site-wide code.', 'head-footer-code' ),
					'class'       => 'regular-text',
				)
			);

			$this->add_field(
				'paged',
				esc_html__( 'On paged homepage', 'head-footer-code' ),
				'select_field_render',
				'homepage',
				array(
					'items'       => array(
						'yes' => __( 'Add on paged homepage', 'head-footer-code' ),
						'no'  => __( 'Do not add on paged homepage', 'head-footer-code' ),
					),
					'description' => esc_html__( 'Chose if the Homepage specific code will be enqueued on paged pages 2, 3, and so on.', 'head-footer-code' ),
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
				'auhfc_settings_homepage',    // Option name.
				array(
					'sanitize_callback' => array( $this, 'sanitize_homepage' ),
				)
			);
		} // END condition: $auhfc_homepage_blog_posts

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
			array( $this, 'article_settings_section_description' ),        // Callback.
			HFC_PLUGIN_SLUG                                                // Page.
		);

		// Prepare clean list of post types w/o attachment.
		$public_post_types = get_post_types( array( 'public' => true ), 'objects' );
		$clean_post_types  = array();
		foreach ( $public_post_types as $public_post_type => $public_post_object ) {
			if ( 'attachment' === $public_post_type ) {
				continue;
			}
			$clean_post_types[ $public_post_type ] = esc_html( $public_post_object->label ) . ' (' . esc_attr( $public_post_type ) . ')';
		}

		$this->add_field(
			'post_types',                                   // Field key.
			esc_html__( 'Post Types', 'head-footer-code' ), // Title.
			'checkbox_group_field_render',                  // Callback method name.
			'article',                                      // Section.
			array(                                          // Arguments.
				'label_for'   => false,
				'items'       => $clean_post_types,
				'description' => esc_html__( 'Choose the post types that will have an article specific section.', 'head-footer-code' )
								. '<br>'
								. esc_html__( 'Note that if you add head, body, and footer code for individual articles and then disable that post type, the article-specific code will no longer be output and only the site-wide code will be used.', 'head-footer-code' ),
				'class'       => 'checkbox',
			)
		);

		$this->add_field(
			'allowed_roles',                                          // Field key.
			esc_html__( 'Allow for User Roles', 'head-footer-code' ), // Title.
			'checkbox_group_field_render',                            // Callback method name.
			'article',                                                // Section.
			array(                                                    // Arguments.
				'label_for'   => false,
				'items'       => array(
					'editor' => __( 'Editor' ),
					'author' => __( 'Author' ),
				),
				'description' => esc_html__( 'Choose which unprivileged user roles can manage article-specific and category-specific code.', 'head-footer-code' )
								. '<br>'
								. '<span class="warn"><strong>'
								. esc_html__( 'Security Notice', 'head-footer-code' )
								. '</strong><br>'
								. '<i></i>' . esc_html__( 'Granting access to non-administrator roles (e.g., Editors) allows users to inject raw HTML, CSS, and JavaScript into individual posts and pages, and categories!', 'head-footer-code' )
								. '<br><i></i>' . esc_html__( 'This may pose a security risk if those users are not fully trusted!', 'head-footer-code' )
								. '<br><i></i>' . esc_html__( 'Only allow for roles you trust to handle code responsibly!', 'head-footer-code' )
								. '</span>',
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
			'auhfc_settings_article',    // Option name.
			array(
				'sanitize_callback' => array( $this, 'sanitize_article' ),
			)
		);
	} // END public function settings_init

	/**
	 * Wrapper for add_settings_field
	 *
	 * @param string   $key           Field key to identify the field.
	 * @param string   $title         Formatted title of the field. Shown as the label for the field during output.
	 * @param string   $callback_name Name of the function that fills the field with the desired form inputs.
	 * @param string   $section       Key of the section of the settings page in which to show the box.
	 * @param array    $args {
	 *     Optional. Extra arguments that get passed to add_settingd_field $args
	 *
	 *     @type bool $label_for      If `false` the setting title will not be wrapped in a `<label>` element
	 *     @type string $class        CSS Class to be added to the `<tr>` element when the field is output.
	 * }
	 */
	private function add_field(
		$key,
		$title,
		$callback_name,
		$section,
		$args = array()
	) {
		// Compose field name from args
		$full_field_name = "auhfc_settings_{$section}[{$key}]";
		$args['field']   = $full_field_name;

		// Generate clean_id
		$clean_id = $this->generate_clean_id( $full_field_name );

		// Append label_for to args if missing
		if ( ! isset( $args['label_for'] ) ) {
			$args['label_for'] = $clean_id;
		}

		// Extract value from the settings array
		$args['value'] = isset( $this->settings[ $section ][ $key ] ) ? $this->settings[ $section ][ $key ] : '';

		// Call WordPress Core metod
		add_settings_field(
			'auhfc_' . $key,
			$title,
			array( $this, $callback_name ),
			HFC_PLUGIN_SLUG,
			'head_footer_code_settings_' . $section,
			$args
		);
	}

	/**
	 * Generate valid HTML ID from the field name.
	 *
	 * @param string $field  Field name (eg. 'prefix[settings][key]').
	 * @param string $suffix Optional suffix (eg. 'editor' or 'post').
	 *
	 * @return string        Clean ID ready for HTML.
	 */
	public function generate_clean_id( $field, $suffix = '' ) {
		$id = $field . ( $suffix ? '_' . $suffix : '' );
		$id = rtrim( str_replace( array( '[', ']', '__' ), '_', $id ), '_' );

		return sanitize_key( $id );
	}

	/**
	 * This function provides textarea for settings fields
	 *
	 * @param array $args Array of arguments (field, rows, field_class, value, description).
	 */
	public function textarea_field_render( $args ) {
		if ( empty( $args ) ) {
			return;
		}
		// Set defaults and sanitize values.
		$field       = isset( $args['field'] ) ? $args['field'] : '';
		$field_id    = isset( $args['label_for'] ) ? $args['label_for'] : $field;
		$field_class = isset( $args['field_class'] ) ? $args['field_class'] : '';
		$label       = isset( $args['label'] ) ? $args['label'] : '';
		$value       = isset( $args['value'] ) ? $args['value'] : '';
		$rows        = isset( $args['rows'] ) ? $args['rows'] : 7;

		// Compose input HTML.
		$html  = '<div class="description">' . $this->security_risk_notice . '</div>';
		$html .= sprintf(
			'<textarea name="%1$s" id="%2$s" rows="%3$s" class="%4$s" title="%5$s">%6$s</textarea>',
			esc_attr( $field ),       // 1
			esc_attr( $field_id ),    // 2
			intval( $rows ),          // 3
			esc_attr( $field_class ), // 4
			esc_html( $label ),       // 5
			esc_textarea( $value )    // 6
		);

		// Append description if exists.
		if ( ! empty( $args['description'] ) ) {
			$html .= sprintf( '<div class="description">%s</div>', $args['description'] );
		}

		// Remove Jetpack filter that may interfere with wp_kses.
		remove_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ), 100 );

		// Filter allowed HTML tags and attributes.
		echo wp_kses( $html, $this->allowed_html );

		// Re-add the Jetpack filter if it exists.
		// This is to ensure compatibility with Jetpack and other plugins that may use this filter.
		if ( is_callable( array( 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ) ) ) {
			add_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ), 100 );
		}
	} // END public function textarea_field_render

	/**
	 * This function provides number input for settings fields
	 *
	 * @param array $args Array of arguments (field, value, min, max, step, rows, class, description).
	 */
	public function number_field_render( $args ) {
		if ( empty( $args ) ) {
			return;
		}

		// Set defaults and sanitize values.
		$field       = isset( $args['field'] ) ? $args['field'] : '';
		$field_id    = isset( $args['label_for'] ) ? $args['label_for'] : $field;
		$field_class = isset( $args['class'] ) ? $args['class'] : '';
		$value       = isset( $args['value'] ) ? $args['value'] : 0;
		$min         = isset( $args['min'] ) ? $args['min'] : 0;
		$max         = isset( $args['max'] ) ? $args['max'] : 100;
		$step        = isset( $args['step'] ) ? $args['step'] : 1;

		// Compose input HTML.
		$html = sprintf(
			'<input type="number" name="%1$s" id="%2$s" value="%3$s" class="%4$s" min="%5$s" max="%6$s" step="%7$s" />',
			esc_attr( $field ),       // 1
			esc_attr( $field_id ),    // 2
			intval( $value ),         // 3
			esc_attr( $field_class ), // 4
			intval( $min ),           // 5
			intval( $max ),           // 6
			intval( $step )           // 7
		);

		// Append description if exists.
		if ( ! empty( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		// Filter allowed HTML tags and attributes.
		echo wp_kses( $html, $this->form_allowed_html );
	} // END public function number_field_render

	/**
	 * This function provides checkbox group for settings fields
	 *
	 * @param array $args Array of arguments (items, value, field, class, description).
	 */
	public function checkbox_group_field_render( $args ) {
		if ( empty( $args ) || ! isset( $args['items'] ) ) {
			return;
		}

		// Set defaults and sanitize values.
		$field       = isset( $args['field'] ) ? $args['field'] : '';
		$field_class = isset( $args['class'] ) ? $args['class'] : '';
		$values      = isset( $args['value'] ) ? (array) $args['value'] : array();

		// Checkbox items.
		$html = '<fieldset>';

		foreach ( $args['items'] as $key => $label ) {
			$item_id = $this->generate_clean_id( $field, $key );
			$checked = checked( in_array( $key, $values, true ), true, false );

			// Output filtered checkbox field.
			$html .= sprintf(
				'<label for="%1$s"><input type="checkbox" name="%2$s[]" id="%1$s" value="%3$s" class="%4$s" %5$s />%6$s</label><br>',
				esc_attr( $item_id ),     // 1
				esc_attr( $field ),       // 2
				esc_attr( $key ),         // 3
				esc_attr( $field_class ), // 4
				$checked,                 // 5
				esc_html( $label )        // 6
			);
		}

		// Append description if exists.
		if ( ! empty( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		$html .= '</fieldset>';

		// Filter allowed HTML tags and attributes.
		echo wp_kses( $html, $this->form_allowed_html );
	} // END public function checkbox_group_field_render

	/**
	 * This function provides select for settings fields
	 *
	 * @param  array $args Array of field arguments (class, field, items, value, description).
	 */
	public function select_field_render( $args ) {
		if ( empty( $args ) ) {
			return;
		}

		// Set defaults and sanitize values.
		$field       = isset( $args['field'] ) ? esc_attr( $args['field'] ) : '';
		$field_id    = isset( $args['label_for'] ) ? esc_attr( $args['label_for'] ) : $field;
		$field_class = isset( $args['class'] ) ? esc_attr( $args['class'] ) : 'regular-text';
		$current_val = isset( $args['value'] ) ? $args['value'] : '';

		// Open SELECT tag.
		$html = sprintf(
			'<select id="%1$s" name="%2$s" class="%3$s">',
			$field_id,   // 1
			$field,      // 2
			$field_class // 3
		);

		// Append OPTIONS.
		foreach ( $args['items'] as $key => $val ) {
			// Determine if this dropdown option is selected or not.
			$selected = selected( $current_val, $key, false );

			$html .= sprintf(
				'<option %1$s value="%2$s">%3$s</option>',
				$selected,        // 1
				esc_attr( $key ), // 2
				esc_html( $val )  // 3
			);
		}

		// Close SELECT tag.
		$html .= '</select>';

		// Append description if exists.
		if ( ! empty( $args['description'] ) ) {
			$html .= sprintf( '<p class="description">%s</p>', $args['description'] );
		}

		// Filter allowed HTML tags and attributes.
		echo wp_kses( $html, $this->form_allowed_html );
	} // END public function select_field_render

	/**
	 * Print description for site-wide section
	 */
	public function sitewide_settings_section_description() {
		echo '<p>' . esc_html__( 'Define site-wide code and behavior. You can Add custom content like JavaScript, CSS, HTML meta and link tags, Google Analytics, site verification, etc.', 'head-footer-code' ) . '</p>';
	} // END public function sitewide_settings_section_description

	/**
	 * Print description for homepage section
	 */
	public function homepage_settings_section_description() {
		echo '<p>' . esc_html__( 'Define code and behavior for the Homepage in Blog Posts mode.', 'head-footer-code' ) . '</p>';
	} // END public function homepage_settings_section_description

	/**
	 * Print description for article section
	 */
	public function article_settings_section_description() {
		echo '<p>' . esc_html__( 'Define what post types will support article specific features, and which non-priviledged user roles will have access to it.', 'head-footer-code' ) . '</p>';
	} // END public function article_settings_section_description

	/**
	 * Print settings page from template
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'head-footer-code' ) );
		}
		// Render the settings template.
		include HFC_DIR . '/templates/settings.php';
	} // END public function options_page

	/**
	 * Append Settings link for Plugins page
	 *
	 * @param  array $links Array of default plugin links
	 *
	 * @return array        Array of plugin links with appended link for Settings page
	 */
	public function plugin_settings_link( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'tools.php?page=' . HFC_PLUGIN_SLUG ) ) . '">' . esc_html__( 'Settings', 'head-footer-code' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links; // Return updated array of links
	} // END public function plugin_settings_link

	/**
	 * Add link to plugin community support
	 *
	 * @param array $links Array of default plugin meta links
	 * @param string $file Current hook file path
	 *
	 * @return array       Array of default plugin meta links with appended link for Support community forum
	 */
	public function add_plugin_meta_links( $links, $file ) {
		if ( plugin_basename( HFC_FILE ) === $file ) {
			$links[] = '<a href="https://wordpress.org/support/plugin/head-footer-code/" target="_blank">' . esc_html__( 'Support', 'head-footer-code' ) . '</a>';
		}

		// Return updated array of links
		return $links;
	} // END public function add_plugin_meta_links

	/**
	 * Function to print note for head section
	 */
	public function head_note() {
		return '<p class="notice">' . sprintf(
			/* translators: 1: italicized 'unseen elements', 2: <script>, 3: <style>, 4: italicized sentence 'could break layouts or lead to unexpected situations' */
			esc_html__( 'Usage of this field should be reserved for output of %1$s like %2$s and %3$s tags or additional metadata. It should not be used to add arbitrary HTML content to a page that %4$s.', 'head-footer-code' ),
			'<em>' . esc_html__( 'unseen elements', 'head-footer-code' ) . '</em>',
			Common::html2code( '<script>' ),
			Common::html2code( '<style>' ),
			'<em>' . esc_html__( 'could break layouts or lead to unexpected situations', 'head-footer-code' ) . '</em>'
		) . '</p>';
	} // END public function head_note

	/**
	 * Function to print note for body section
	 */
	public function body_note() {
		return '<p class="notice">' . sprintf(
			/* translators: %s will be replaced with a link to wp_body_open page on WordPress.org */
			esc_html__( 'Make sure that your active theme support %s hook.', 'head-footer-code' ),
			'<a href="https://developer.wordpress.org/reference/hooks/wp_body_open/" target="_hook">wp_body_open</a>'
		) . '</p>';
	} // END public function body_note

	/**
	 * Sanitize SiteWide settings
	 *
	 * @param array $options
	 * @return array $sanitized
	 */
	public function sanitize_sitewide( $options ) {
		// Sanitize SiteWide HEAD/BODY/FOOTER code and behavior
		$sanitized = Common::sanitize_hfc_data( $options );

		// Sanitize SiteWide Priority
		$sanitized['priority_h'] = isset( $options['priority_h'] ) ? absint( $options['priority_h'] ) : 10;
		$sanitized['priority_b'] = isset( $options['priority_b'] ) ? absint( $options['priority_b'] ) : 10;
		$sanitized['priority_f'] = isset( $options['priority_f'] ) ? absint( $options['priority_f'] ) : 10;

		// Sanitize SiteWide shortcodes
		$allowed_do_shortcode        = array( 'y', 'n' );
		$sanitized['do_shortcode_h'] = ( isset( $options['do_shortcode_h'] ) && in_array( $options['do_shortcode_h'], $allowed_do_shortcode, true ) ) ? $options['do_shortcode_h'] : 'n';
		$sanitized['do_shortcode_b'] = ( isset( $options['do_shortcode_b'] ) && in_array( $options['do_shortcode_b'], $allowed_do_shortcode, true ) ) ? $options['do_shortcode_b'] : 'n';
		$sanitized['do_shortcode_f'] = ( isset( $options['do_shortcode_f'] ) && in_array( $options['do_shortcode_f'], $allowed_do_shortcode, true ) ) ? $options['do_shortcode_f'] : 'n';

		return $sanitized;
	}

	/**
	 * Sanitize Homepage settings
	 *
	 * @param array $options
	 * @return array $sanitized
	 */
	public function sanitize_homepage( $options ) {
		// Sanitize SiteWide HFC code
		$sanitized = Common::sanitize_hfc_data( $options );
		// Sanitize Homepage Paged
		$allowed_paged      = array( 'yes', 'no' );
		$sanitized['paged'] = ( isset( $options['paged'] ) && in_array( $options['paged'], $allowed_paged, true ) ) ? $options['paged'] : 'yes';

		return $sanitized;
	}

	/**
	 * Sanitize Article settings
	 *
	 * @param array $options
	 * @return array $sanitized
	 */
	public function sanitize_article( $options ) {
		$sanitized = array(
			'post_types'    => array(),
			'allowed_roles' => array(),
		);

		// Sanitize Article Post Types (allow only registered public post types)
		if ( ! empty( $options['post_types'] ) && is_array( $options['post_types'] ) ) {
			$registered_post_types = get_post_types( array( 'public' => true ) );

			foreach ( $options['post_types'] as $post_type ) {
				$post_type = sanitize_key( $post_type );
				if ( isset( $registered_post_types[ $post_type ] ) ) {
					$sanitized['post_types'][] = $post_type;
				}
			}
		}

		// Sanitize Article Allowed Roles (allow only existing WP roles on this site)
		if ( ! empty( $options['allowed_roles'] ) && is_array( $options['allowed_roles'] ) ) {
			$wp_roles = wp_roles()->get_names();

			foreach ( $options['allowed_roles'] as $role ) {
				$role = sanitize_key( $role );
				if ( isset( $wp_roles[ $role ] ) ) {
					$sanitized['allowed_roles'][] = $role;
				}
			}
		}

		return $sanitized;
	}
} // END class Settings
