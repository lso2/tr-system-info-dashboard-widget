<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_cron_memory_usage_section($cron_logs, $settings) {
	$display_style = isset($settings['show_cron_memory_usage']) && $settings['show_cron_memory_usage'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="cron-memory-usage-wrapper" style="display: <?php echo esc_attr($display_style); ?>; padding-top: 25px;">
		<!-- Cron Memory Usage -->
		<div class="wp-system-info-section">
			<div class="wp-system-info-header">
				<h4 style="margin: 0;">⏰ Cron Job Memory Usage</h4>
				<?php if ( !empty( $cron_logs ) ) : ?>
					<span class="wp-system-info-toggle" id="cron-toggle" onclick="toggleSectionWithText('cron-logs', 'cron-toggle')">Hide Recent Logs (<?php echo count( $cron_logs ); ?> entries)</span>
				<?php endif; ?>
			</div>
			<?php if ( !empty( $cron_logs ) ) : ?>
			<div id="cron-logs" class="wp-system-info-collapsible" style="display: block;">
					<?php foreach ( array_slice( $cron_logs, -10 ) as $log ) : ?>
						<div style="margin-bottom: 5px; font-size: 12px; padding: 5px; background: #f8f9fa; border-radius: 3px;">
							<strong><?php echo esc_html( $log['timestamp'] ); ?>:</strong>
							Peak: <strong><?php echo esc_html( $log['peak_memory'] ); ?>MB</strong>, 
							Used: <strong><?php echo esc_html( $log['memory_used'] ); ?>MB</strong>, 
							Time: <strong><?php echo esc_html( $log['execution_time'] ); ?>ms</strong>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div style="padding: 10px; background: #f0f0f0; border-radius: 3px; font-size: 12px; color: #666;">
					No cron job memory data available yet. Data will appear after WordPress cron jobs run.
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
