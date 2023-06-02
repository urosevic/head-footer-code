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
	<h2><?php esc_html_e( 'Head & Footer Code', 'head-footer-code' ); ?></h2>
	<p>
	<?php
	if ( ! current_user_can( 'manage_options' ) ) {
		$allowed_managers = is_multisite() ? esc_html__( 'Super Admin' ) . ' ' . esc_html__( 'and' ) . ' ' . esc_html__( 'Administrator' ) : esc_html__( 'Administrator' );
	printf(
			/* translators: 1: </head>, 2: <body>, 3: </body>, 4: Plugin Settings page, 5: Allowed user roles */
			esc_html__( 'Here you can insert category specific code for Head (before the %1$s), Body (after the %2$s) and Footer (before the %3$s) sections. They work in exactly the same way as site-wide code, which %6$s can configure under %5$s. Please note, if you leave empty any of category-specific fields and choose replace behavior, site-wide code will not be removed until you add empty space or empty HTML comment %4$s here.', 'head-footer-code' ),
		'<code>&lt;/head&gt;</code>',
		'<code>&lt;body&gt;</code>',
		'<code>&lt;/body&gt;</code>',
		'<code>&lt;!-- --&gt;</code>',
			esc_html__( 'Tools' ) . ' > ' . esc_html__( 'Head &amp; Footer Code', 'head-footer-code' ),
			$allowed_managers
		);

	} else {
		printf(
			/* translators: 1: </head>, 2: <body>, 3 </body>, 4: link to Head & Footer Code Settings page */
			esc_html__( 'Here you can insert category specific code for Head (before the %1$s), Body (after the %2$s) and Footer (before the %3$s) sections. They work in exactly the same way as site-wide code, which you can configure under %5$s. Please note, if you leave empty any of category-specific fields and choose replace behavior, site-wide code will not be removed until you add empty space or empty HTML comment %4$s here.', 'head-footer-code' ),
			'<code>&lt;/head&gt;</code>',
			'<code>&lt;body&gt;</code>',
			'<code>&lt;/body&gt;</code>',
			'<code>&lt;!-- --&gt;</code>',
			'<a href="tools.php?page=' . HFC_PLUGIN_SLUG . '">' . esc_html__( 'Tools / Head &amp; Footer Code', 'head-footer-code' ) . '</a>'
		);
	}
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
					<label for="auhfc_head"><?php esc_html_e( 'Head Code', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo ! empty( $auhfc['head'] ) ? $auhfc['head'] : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" type="text/css" media="all"&gt;</code></p>
				</td>
			</tr>
			<tr class="form-field term-auhfc-body">
				<th scope="row">
					<label for="auhfc_body"><?php esc_html_e( 'Body Code', 'head-footer-code' ); ?></label>
				</th>
				<td>
					<textarea name="auhfc[body]" id="auhfc_body" class="widefat code" rows="5"><?php echo ! empty( $auhfc['body'] ) ? $auhfc['body'] : ''; ?></textarea>
					<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo get_stylesheet_directory_uri(); ?>/body-start.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
				</td>
			</tr>
			<tr class="form-field term-auhfc-footer">
				<th scope="row">
					<label for="auhfc_footer"><?php esc_html_e( 'Footer Code', 'head-footer-code' ); ?></label>
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
