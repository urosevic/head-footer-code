<?php
/**
 * Template to render article and category specific metaboxes.
 *
 * @package Head_Footer_Code
 * @since: 1.4.0
 */
$auhfc_demo_url = get_stylesheet_directory_uri();
?>
<p>
<?php
printf(
	/* translators: 1: category or article specific, 2: </head>, 3: <body>, 4: </body> */
	esc_html__( 'Here you can insert %1$s code for HEAD (before the %2$s), BODY (after the %3$s) and FOOTER (before the %4$s) sections.', 'head-footer-code' ),
	esc_html( $form_scope ),
	'<code>&lt;/head&gt;</code>',
	'<code>&lt;body&gt;</code>',
	'<code>&lt;/body&gt;</code>'
);
echo '<br>';

// One who can manage options and modify category settings
if ( ! current_user_can( 'manage_options' ) ) {
	$auhfc_allowed_managers  = is_multisite() ? esc_html__( 'Super Admin', 'head-footer-code' ) . ' ' . esc_html__( 'and', 'head-footer-code' ) : '';
	$auhfc_allowed_managers .= esc_html__( 'Administrator', 'head-footer-code' );
	printf(
		/* translators: 1: User role(s) that can manage options (Super Admin and/or Administrator), 2: Path/Name of Plugin Settings page */
		esc_html__( 'They work in exactly the same way as site-wide code, which %1$s can configure under %2$s.', 'head-footer-code' ),
		esc_html__( 'Tools', 'head-footer-code' ) . ' > ' . esc_html( HFC_PLUGIN_NAME ),
		esc_html( $auhfc_allowed_managers )
	);
} else {
	printf(
		/* translators: Link to Plugin Settings page */
		esc_html__( 'They work in exactly the same way as site-wide code, which you can configure under %s.', 'head-footer-code' ),
		'<a href="tools.php?page=' . esc_attr( HFC_PLUGIN_SLUG ) . '">' . esc_html__( 'Tools', 'head-footer-code' ) . ' > ' . esc_html( HFC_PLUGIN_NAME ) . '</a>'
	);
}
echo '<br>';

printf(
	/* translators: 1: category or article specific, HTML comment code */
	esc_html__( 'Please note, if you leave empty any of %1$s fields and choose replace behavior, site-wide code will not be removed until you add empty space or empty HTML comment %2$s here.', 'head-footer-code' ),
	esc_html( $form_scope ),
	'<code>&lt;!-- --&gt;</code>'
);
?>
</p>

<table class="form-table" role="presentation">
	<tbody>
		<tr class="form-field auhfc-behavior">
			<th scope="row">
				<label for="auhfc_behavior"><?php esc_html_e( 'Behavior', 'head-footer-code' ); ?></label>
			</th>
			<td>
				<select name="auhfc[behavior]" id="auhfc_behavior">
					<option value="append" <?php echo ( ! empty( $auhfc_form_data['behavior'] ) && 'append' === $auhfc_form_data['behavior'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Append to the site-wide code', 'head-footer-code' ); ?></option>
					<option value="replace" <?php echo ( ! empty( $auhfc_form_data['behavior'] ) && 'replace' === $auhfc_form_data['behavior'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Replace the site-wide code', 'head-footer-code' ); ?></option>
				</select>
			</td>
		</tr>

		<tr class="form-field auhfc-head">
			<th scope="row">
				<label for="auhfc_head"><?php esc_html_e( 'HEAD Code', 'head-footer-code' ); ?></label>
			</th>
			<td>
				<div class="description"><?php echo $security_risk_notice; ?></div>
				<textarea name="auhfc[head]" id="auhfc_head" class="widefat code" rows="5"><?php echo ! empty( $auhfc_form_data['head'] ) ? esc_textarea( $auhfc_form_data['head'] ) : ''; ?></textarea>
				<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;link&nbsp;rel="stylesheet" href="<?php echo esc_url( $auhfc_demo_url ); ?>/custom-style.css" type="text/css" media="all"&gt;</code></p>
			</td>
		</tr>
		<tr class="form-field auhfc-body">
			<th scope="row">
				<label for="auhfc_body"><?php esc_html_e( 'BODY Code', 'head-footer-code' ); ?></label>
			</th>
			<td>
				<div class="description"><?php echo $security_risk_notice; ?></div>
				<textarea name="auhfc[body]" id="auhfc_body" class="widefat code" rows="5"><?php echo ! empty( $auhfc_form_data['body'] ) ? esc_textarea( $auhfc_form_data['body'] ) : ''; ?></textarea>
				<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo esc_url( $auhfc_demo_url ); ?>/body-script.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
			</td>
		</tr>
		<tr class="form-field auhfc-footer">
			<th scope="row">
				<label for="auhfc_footer"><?php esc_html_e( 'FOOTER Code', 'head-footer-code' ); ?></label>
			</th>
			<td>
				<div class="description"><?php echo $security_risk_notice; ?></div>
				<textarea name="auhfc[footer]" id="auhfc_footer" class="widefat code" rows="5"><?php echo ! empty( $auhfc_form_data['footer'] ) ? esc_textarea( $auhfc_form_data['footer'] ) : ''; ?></textarea>
				<p class="description"><?php esc_html_e( 'Example', 'head-footer-code' ); ?>: <code>&lt;script src="<?php echo esc_url( $auhfc_demo_url ); ?>/footer-script.js" type="text/javascript"&gt;&lt;/script&gt;</code></p>
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
