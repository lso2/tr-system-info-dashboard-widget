<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_memory_usage_section($memory, $system_info, $settings) {
	$display_style = isset($settings['show_memory_usage']) && $settings['show_memory_usage'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="memory-usage-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- Memory Information -->
		<div class="wp-system-info-section">
			<h4>🧠 Memory Usage</h4>
			<div class="wp-system-info-item">
				<strong>Current Usage:</strong> <?php echo esc_html( $memory['usage'] ); ?> MB
				<div class="wp-system-info-progress">
					<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( min( $memory['percent'], 100 ) ); ?>%; background-color: <?php echo esc_attr( $memory['color'] ); ?>;">
						<?php echo esc_html( $memory['percent'] ); ?>%
					</div>
				</div>
			</div>
			<div class="wp-system-info-grid">
				<div>
					<div class="wp-system-info-item">
						<strong>WP Limit:</strong> <?php echo esc_html( $memory['wpmb'] . $memory['wpunity'] ); ?>
					</div>
					<div class="wp-system-info-item">
						<strong>WP Admin Limit:</strong> <?php echo esc_html( $memory['wpmaxmb'] . $memory['wpmaxunity'] ); ?>
					</div>
				</div>
				<div>
					<div class="wp-system-info-item">
						<strong>PHP Limit:</strong> <?php echo esc_html( $memory['phplimit'] . $memory['phplimitunity'] ); ?>
					</div>
					<div class="wp-system-info-item">
						<strong>Max Execution:</strong> <?php echo esc_html( $system_info['max_execution_time'] ); ?>s
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
