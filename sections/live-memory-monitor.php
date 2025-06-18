<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_live_memory_monitor_section($settings) {
	$display_style = isset($settings['show_live_memory_monitor']) && $settings['show_live_memory_monitor'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="live-memory-monitor-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- Live Memory Graph -->
		<div class="wp-system-info-section">
			<h4>📊 Live Memory Monitor</h4>
			<div class="wp-system-info-live-memory">
				<canvas id="wp-system-info-memory-chart"></canvas>
				<div id="wp-system-info-memory-stats" style="margin-top: 10px; font-size: 12px;">
					<span>Current: <strong id="current-memory">--</strong> MB</span> | 
					<span>Peak: <strong id="peak-memory">--</strong> MB</span>
				</div>
			</div>
		</div>
	</div>
	<?php
}
