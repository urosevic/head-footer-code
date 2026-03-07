<?php
/**
 * General plugin settings page template.
 *
 * @package    Head_Footer_Code
 * @category   Template
 * @since      1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap" id="head_footer_code_settings">
	<h1 class="wp-heading-inline">
		<?php
		printf(
			/* translators: Plugin name */
			esc_html__( '%s Settings', 'head-footer-code' ),
			esc_html( HFC_PLUGIN_NAME )
		);
		?>
		<span class="ver">v. <?php echo esc_html( HFC_VER ); ?></span>
		<span class="actions long-header">
			<a href="https://wordpress.org/plugins/head-footer-code/#faq" class="page-title-action" target="_blank"><?php esc_html_e( 'FAQ', 'head-footer-code' ); ?></a>
			<a href="https://wordpress.org/support/plugin/head-footer-code/" class="page-title-action" target="_blank"><?php esc_html_e( 'Community Support', 'head-footer-code' ); ?></a>
			<a href="https://wordpress.org/support/plugin/head-footer-code/reviews/#new-post" class="page-title-action" target="_blank">
				<?php
				printf(
					/* translators: %s will be replaced with plugin name Head & Footer Code */
					esc_html__( 'Review %s', 'head-footer-code' ),
					esc_html( HFC_PLUGIN_NAME )
				);
				?>
			</a>
		</span>
	</h1>
	<form method="post" action="options.php">
	<?php
		settings_fields( 'head_footer_code_settings' );
		do_settings_sections( HFC_PLUGIN_SLUG );
		submit_button();
	?>
	</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#head_footer_code_settings .codeEditor').each( function(index, value) {
		wp.codeEditor.initialize(this, cm_settings);
	});
});
</script>
