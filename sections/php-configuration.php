<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_php_configuration_section($system_info, $settings) {
	$display_style = isset($settings['show_php_configuration']) && $settings['show_php_configuration'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="php-configuration-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- PHP Configuration -->
		<div class="wp-system-info-section">
			<h4>⚙️ PHP Configuration</h4>
			<div class="wp-system-info-grid">
				<div>
					<div class="wp-system-info-item">
						<strong>Upload Max:</strong> <?php echo esc_html( $system_info['upload_max_filesize'] ); ?>
					</div>
					<div class="wp-system-info-item">
						<strong>Post Max:</strong> <?php echo esc_html( $system_info['post_max_size'] ); ?>
					</div>
				</div>
				<div>
					<div class="wp-system-info-item">
						<strong>Max Input Vars:</strong> <?php echo esc_html( $system_info['max_input_vars'] ); ?>
					</div>
					<div class="wp-system-info-item">
						<strong>PHP Extensions:</strong> <?php echo esc_html( count( $system_info['php_extensions'] ) ); ?>
					</div>
				</div>
			</div>
			<div style="text-align: center; margin-top: 10px;">
				<span class="wp-system-info-toggle" id="extensions-toggle" onclick="toggleSectionWithText('php-extensions', 'extensions-toggle')">Show PHP Extensions (<?php echo count($system_info['php_extensions']); ?> loaded)</span>
			</div>
			<div id="php-extensions" class="wp-system-info-collapsible wp-system-info-scroll">
				<div class="wp-system-info-extensions-grid">
					<?php foreach ($system_info['php_extensions'] as $extension) : ?>
						<div class="wp-system-info-extension-item">
							<?php echo esc_html($extension); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
