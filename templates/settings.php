<?php
/**
 * Head & Footer Code General Settings page template
 *
 * @category Template
 * @package Head_Footer_Code
 * @author Aleksandar Urosevic
 * @license https://www.gnu.org/copyleft/gpl-3.0.html GNU General Public License v3.0
 * @link https://urosevic.net
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="wrap" id="head_footer_code_settings">
	<h2>
		<?php echo esc_html( HFC_PLUGIN_NAME . ' ' . __( 'Settings' ) ); ?>
		<span class="ver">v. <?php echo esc_html( HFC_VER ); ?></span>
	</h2>
	<div class="head_footer_code_wrapper">
		<div class="content_cell">
			<form method="post" action="options.php">
			<?php
				settings_fields( 'head_footer_code_settings' );
				do_settings_sections( HFC_PLUGIN_SLUG );
				submit_button();
			?>
			</form>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://wordpress.org/plugins/head-footer-code/#faq" class="auhfc-button" target="_blank"><?php esc_html_e( 'FAQ', 'head-footer-code' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/plugin/head-footer-code/" class="auhfc-button" target="_blank"><?php esc_html_e( 'Community Support', 'head-footer-code' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/plugin/head-footer-code/reviews/#new-post" class="auhfc-button" target="_blank">
				<?php
				printf(
					/* translators: %s will be replaced with plugin name Head & Footer Code */
					esc_html__( 'Review %s plugin', 'head-footer-code' ),
					HFC_PLUGIN_NAME
				);
				?>
			</a>
		</div><!-- .sidebar_container -->
	</div><!-- .head_footer_code_wrapper -->
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#head_footer_code_settings .codeEditor').each( function(index, value) {
		wp.codeEditor.initialize(this, cm_settings);
	});
});
</script>
