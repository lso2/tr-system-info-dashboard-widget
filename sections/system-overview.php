<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_system_overview_section($system_info, $settings) {
	$display_style = isset($settings['show_system_overview']) && $settings['show_system_overview'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="system-overview-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- System Overview -->
		<div class="wp-system-info-section">
			<h4>🖥️ System Overview</h4>
			<div class="wp-system-info-grid">
				<div>
					<div class="wp-system-info-item">
						<strong>WordPress:</strong> <?php echo esc_html( $system_info['wp_version'] ); ?>
					</div>
					<div class="wp-system-info-item">
						<strong>PHP Version:</strong> <?php echo esc_html( $system_info['php_version'] ); ?>
						<span class="wp-system-info-<?php echo version_compare( PHP_VERSION, '8.0', '>=' ) ? 'success' : 'warning'; ?>">
							(<?php echo esc_html( PHP_INT_SIZE * 8 ); ?>-bit)
						</span>
					</div>
					<div class="wp-system-info-item">
						<strong>MySQL:</strong> <?php echo esc_html( $system_info['mysql_version'] ); ?>
					</div>
				</div>
				<div>
					<div class="wp-system-info-item">
						<strong>SSL:</strong> 
						<span class="wp-system-info-<?php echo $system_info['is_ssl'] ? 'success' : 'warning'; ?>">
							<?php echo $system_info['is_ssl'] ? 'Enabled' : 'Disabled'; ?>
						</span>
					</div>
					<div class="wp-system-info-item">
						<strong>Multisite:</strong> <?php echo $system_info['is_multisite'] ? 'Yes' : 'No'; ?>
					</div>
					<div class="wp-system-info-item">
						<strong>WP Debug:</strong> 
						<span class="wp-system-info-<?php echo $system_info['wp_debug'] ? 'warning' : 'success'; ?>">
							<?php echo $system_info['wp_debug'] ? 'Enabled' : 'Disabled'; ?>
						</span>
					</div>
				</div>
			</div>
			<div style="margin-top: 10px; font-size: 12px; color: #666; border-top: 1px solid #eee; padding-top: 8px;">
				<strong>OS:</strong> <?php echo esc_html( $system_info['os_info'] ); ?> | 
				<strong>Hostname:</strong> <?php echo esc_html( $system_info['hostname'] ); ?> | 
				<strong>Server:</strong> <?php echo esc_html( $system_info['server_software'] ); ?>
			</div>
		</div>
	</div>
	<?php
}
