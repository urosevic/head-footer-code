<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Head & Footer Code General Settings page template
 *
 * @category Template
 * @package Head & Footer Code
 * @author Aleksandar Urosevic
 * @license https://www.gnu.org/copyleft/gpl-3.0.html GNU General Public License v3.0
 * @link https://urosevic.net
 */

?>
<div class="wrap" id="head_footer_code_settings">
	<h1><?php esc_attr_e( 'Head & Footer Code', 'head-footer-code' ); ?></h2>
	<em>Plugin version: <?php echo WPAU_HEAD_FOOTER_CODE_VER; ?></em>
	<div class="head_footer_code_wrapper">
		<div class="content_cell">
			<form method="post" action="options.php">
			<?php
				@settings_fields( 'head_footer_code_sitewide_settings' );
				@settings_fields( 'head_footer_code_article_settings' );
				@do_settings_sections( 'head_footer_code' );
				@submit_button();
			?>
			</form>
		</div><!-- .content_cell -->

		<div class="sidebar_container">
			<a href="https://urosevic.net/wordpress/donate/?donate_for=head-footer-code" class="auhfc-button paypal_donate" target="_blank">Donate</a>
			<br />
			<a href="https://wordpress.org/plugins/head-footer-code/faq/" class="auhfc-button" target="_blank">FAQ</a>
			<br />
			<a href="https://wordpress.org/support/plugin/head-footer-code" class="auhfc-button" target="_blank">Community Support</a>
			<br />
			<a href="https://wordpress.org/support/view/plugin-reviews/head-footer-code#postform" class="auhfc-button" target="_blank">Review this plugin</a>
		</div><!-- .sidebar_container -->
	</div><!-- .head_footer_code_wrapper -->
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	wp.codeEditor.initialize($('#auhfc_settings_head'), cm_settings);
	wp.codeEditor.initialize($('#auhfc_settings_footer'), cm_settings);
});
</script>