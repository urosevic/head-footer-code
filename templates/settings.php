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
	<h2><?php esc_html_e( 'Head & Footer Code', 'head-footer-code' ); ?></h2>
	<em><?php esc_html_e( 'Plugin version', 'head-footer-code' ); ?>: <?php echo WPAU_HEAD_FOOTER_CODE_VER; ?></em>
	<div class="head_footer_code_wrapper">
		<div class="content_cell">
			<form method="post" action="options.php">
			<?php
				settings_fields( 'head_footer_code_settings' );
				do_settings_sections( 'head_footer_code' );
				submit_button();
			?>
			</form>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://wordpress.org/plugins/head-footer-code/#faq" class="auhfc-button" target="_blank"><?php _e( 'FAQ', 'head-footer-code' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/plugin/head-footer-code/" class="auhfc-button" target="_blank"><?php _e( 'Community Support', 'head-footer-code' ); ?></a>
			<br />
			<a href="https://wordpress.org/support/plugin/head-footer-code/reviews/#new-post" class="auhfc-button" target="_blank">
				<?php
				printf(
					/* translators: %s will be replaced with plugin name Head & Footer Code */
					esc_html__( 'Review %s plugin', 'head-footer-code' ),
					esc_html__( 'Head & Footer Code', 'head-footer-code' )
				);
				?>
			</a>
			<br />
			<a href="https://urosevic.net/wordpress/donate/?donate_for=head-footer-code" class="auhfc-button paypal" target="_blank">
				<?php
				printf(
					/* translators: %s: coloured PayPal */
					esc_html__( 'Donate via %s', 'head-footer-code' ),
					'<em><i>Pay</i><i>Pal</i></em>'
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
