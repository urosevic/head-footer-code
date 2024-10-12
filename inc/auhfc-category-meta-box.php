<?php
/**
 * Routine to handle Category metabox for Head & Footer Code
 *
 * @package Head_Footer_Code
 * @since 1.3.0
 */

// Inject Head & Footer Code to Category Edit form.
add_action( 'category_edit_form', 'auhfc_category_form_fields', 10, 1 );

// Save changes when a category is updated/edited.
add_action( 'edit_category', 'auhfc_category_save', 10, 1 );

/**
 * Function to render Category metabox fields for Head & Footer Code
 *
 * @param object $term_object Taxonomy term object.
 */
function auhfc_category_form_fields( $term_object ) {
	// Get existing HFC meta for known Category or use defaults.
	if ( ! empty( $term_object->term_id ) ) {
		$auhfc = get_term_meta( $term_object->term_id, '_auhfc', true );
	} else {
		$auhfc = array(
			'init'     => 'default',
			'behavior' => 'append',
			'head'     => '',
			'body'     => '',
			'footer'   => '',
		);
	}
	?>
	<h2><?php echo esc_html( HFC_PLUGIN_NAME ); ?></h2>
	<p>
	<?php
	printf(
		/* translators: 1: translated 'category specific', 2: </head>, 3: <body>, 4: </body> */
		esc_html__( 'Here you can insert %1$s code for HEAD (before the %2$s), BODY (after the %3$s) and FOOTER (before the %4$s) sections.', 'head-footer-code' ),
		__( 'category specific', 'head-footer-code' ),
		'<code>&lt;/head&gt;</code>',
		'<code>&lt;body&gt;</code>',
		'<code>&lt;/body&gt;</code>'
	);

	echo '<br>';
	// One who can manage options and modify category settings
	if ( ! current_user_can( 'manage_options' ) ) {
		$allowed_managers  = is_multisite() ? __( 'Super Admin', 'head-footer-code' ) . ' ' . __( 'and', 'head-footer-code' ) : '';
		$allowed_managers .= __( 'Administrator', 'head-footer-code' );
		printf(
			/* translators: 1: User role(s) that can manage options (Super Admin and/or Administrator), 2: Path/Name of Plugin Settings page */
			esc_html__( 'They work in exactly the same way as site-wide code, which %1$s can configure under %2$s.', 'head-footer-code' ),
			__( 'Tools', 'head-footer-code' ) . ' > ' . HFC_PLUGIN_NAME,
			$allowed_managers
		);
	} else {
		printf(
			/* translators: Link to Plugin Settings page */
			esc_html__( 'They work in exactly the same way as site-wide code, which you can configure under %s.', 'head-footer-code' ),
			'<a href="tools.php?page=' . HFC_PLUGIN_SLUG . '">' . __( 'Tools', 'head-footer-code' ) . ' > ' . HFC_PLUGIN_NAME . '</a>'
		);
	}

	echo '<br>';
	printf(
		/* translators: 1: translated 'category specific', HTML comment code */
		esc_html__( 'Please note, if you leave empty any of %1$s fields and choose replace behavior, site-wide code will not be removed until you add empty space or empty HTML comment %2$s here.', 'head-footer-code' ),
		__( 'category specific', 'head-footer-code' ),
		'<code>&lt;!-- --&gt;</code>'
	);
	?>
	</p>

	<table class="form-table" role="presentation">
		<tbody>
			<tr class="form-field term-auhfc-behavior">
				<th scope="row">
					<label for="auhfc_behavior"><?php esc_html_e( 'Behavior', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<select name="auhfc[behavior]" id="auhfc_behavior">
						<option value="append" <?php echo ( ! empty( $auhfc['behavior'] ) && 'append' === $auhfc['behavior'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Append to the site-wide code', 'head-footer-code' ); ?></option>
						<option value="replace" <?php echo ( ! empty( $auhfc['behavior'] ) && 'replace' === $auhfc['behavior'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Replace the site-wide code', 'head-footer-code' ); ?></option>
					</select>
				</td>
			</tr>

			<tr class="form-field term-auhfc-head">
				<th scope="row">
					<label for="auhfc_head"><?php esc_html_e( 'HEAD Code', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo ! empty( $auhfc['head'] ) ? $auhfc['head'] : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" type="text/css" media="all"&gt;</code></p>
				</td>
			</tr>
			<tr class="form-field term-auhfc-body">
				<th scope="row">
					<label for="auhfc_body"><?php esc_html_e( 'BODY Code', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<textarea name="auhfc[body]" id="auhfc_body" class="widefat code" rows="5"><?php echo ! empty( $auhfc['body'] ) ? $auhfc['body'] : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo get_stylesheet_directory_uri(); ?>/body-start.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
				</td>
			</tr>
			<tr class="form-field term-auhfc-footer">
				<th scope="row">
					<label for="auhfc_footer"><?php esc_html_e( 'FOOTER Code', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<textarea name="auhfc[footer]" id="auhfc_footer" class="widefat code" rows="5"><?php echo ! empty( $auhfc['footer'] ) ? $auhfc['footer'] : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo get_stylesheet_directory_uri(); ?>/script.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
				</td>
			</tr>
		</tbody>
	</table>
	<script type="text/javascript">
		(function(){
			'use strict';
			var auhfc_cm_head = wp.codeEditor.initialize(document.getElementById('auhfc_head'), cm_settings);
			auhfc_cm_head.codemirror.on('change', function(el) { el.save(); });
			var auhfc_cm_body = wp.codeEditor.initialize(document.getElementById('auhfc_body'), cm_settings);
			auhfc_cm_body.codemirror.on('change', function(el) { el.save(); });
			var auhfc_cm_footer = wp.codeEditor.initialize(document.getElementById('auhfc_footer'), cm_settings);
			auhfc_cm_footer.codemirror.on('change', function(el) { el.save(); });
		})();
		</script>
	<?php
}

/**
 * Function to update category meta
 */
function auhfc_category_save() {
	// Escape if our value is ot present in $_POST array.
	if ( empty( $_POST['auhfc'] ) || ! current_user_can( 'manage_categories' ) ) {
		return null;
	}

	/**
	 * Save category metabox form values using update_term_meta()
	 * https://developer.wordpress.org/reference/functions/update_term_meta/
	 *
	 * The term_id of Category is provided in $_POST key `tag_ID`
	 */
	update_term_meta( $_POST['tag_ID'], '_auhfc', $_POST['auhfc'] );
}
