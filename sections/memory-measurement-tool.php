<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_memory_measurement_tool_section($settings) {
	$display_style = isset($settings['show_memory_measurement_tool']) && $settings['show_memory_measurement_tool'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="memory-measurement-tool-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- Memory Measurement Tool -->
		<div class="wp-system-info-section">
			<h4>🔄 Memory Measurement Tool</h4>
			<?php
			$measurement_settings = get_option( 'wp_system_info_measurement_settings', array( 'nomeas' => 10, 'secrel' => 2000 ) );
			$measurements = get_option( 'wp_system_info_measurements', array() );
			$history = get_option( 'wp_system_info_measurement_history', array() );
			?>
			<div style="padding: 8px 12px; background: #e7f3ff; border-left: 4px solid #0073aa; margin-bottom: 15px; font-size: 12px;">
				<strong>How to use:</strong> This tool measures memory usage multiple times by automatically reloading the page. 
				Use it to find memory-hungry plugins by deactivating suspicious plugins before starting the test, then comparing results. 
				Higher "Max" values indicate potential memory issues.
			</div>
			<div style="padding: 10px; background: #f9f9f9; border-radius: 3px;">
				<div style="margin-bottom: 10px;">
					<strong>Settings:</strong>
					<div style="display: inline;">
						Measurements: <input type="number" id="nomeas" value="<?php echo esc_attr( $measurement_settings['nomeas'] ); ?>" min="1" max="100" style="width: 60px;" onchange="saveMemorySettings()">
						Interval (ms): <input type="number" id="secrel" value="<?php echo esc_attr( $measurement_settings['secrel'] ); ?>" min="500" max="10000" style="width: 80px;" onchange="saveMemorySettings()">
						<span id="save-status" style="margin-left: 10px; font-size: 12px;"></span>
					</div>
				</div>
				
				<div class="memory-test-container">
				<?php if ( empty( $measurements ) ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'start', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-primary">Start Memory Test</a>
				<?php elseif ( count( $measurements ) < $measurement_settings['nomeas'] ) : ?>
					<div class="wp-system-info-test-progress" data-interval="<?php echo esc_attr( $measurement_settings['secrel'] ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'memory_test' ) ); ?>">
						🔄 Memory Test in Progress<br>
						<div class="details">
							Measurement <?php echo esc_html( count( $measurements ) ); ?>/<?php echo esc_html( $measurement_settings['nomeas'] ); ?><br>
							<div class="wp-system-info-progress" style="margin-top: 5px; width: 200px;">
								<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( round( count( $measurements ) / $measurement_settings['nomeas'] * 100 ) ); ?>%; background-color: white; color: #0073aa;">
									<?php echo esc_html( round( count( $measurements ) / $measurement_settings['nomeas'] * 100 ) ); ?>%
								</div>
							</div>
							Page will reload automatically...
						</div>
						<div style="margin-top: 10px;">
							<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'cancel', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary popup-cancel-btn" onclick="return cancelMemoryTest();">✕ Cancel Test</a>
						</div>
					</div>
					<div>
						<strong>Progress:</strong> <?php echo esc_html( count( $measurements ) ); ?>/<?php echo esc_html( $measurement_settings['nomeas'] ); ?> measurements
						<div class="wp-system-info-progress" style="margin: 5px 0;">
							<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( round( count( $measurements ) / $measurement_settings['nomeas'] * 100 ) ); ?>%; background-color: #0073aa;">
							<?php echo esc_html( round( count( $measurements ) / $measurement_settings['nomeas'] * 100 ) ); ?>%
							</div>
						</div>
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'start', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary">Restart Test</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'cancel', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary inline-cancel-btn" onclick="return cancelMemoryTest();" style="margin-left: 5px;">✕ Cancel Test</a>
					</div>
				<?php else : ?>
					<div>
						<?php
						$avg = round( array_sum( $measurements ) / count( $measurements ), 2 );
						$min = min( $measurements );
						$max = max( $measurements );
						$range = round( $max - $min, 2 );
						?>
						<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 3px; border-left: 3px solid #0073aa;">
							<div style="font-weight: bold; margin-bottom: 5px;"><?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></div>
							<div style="font-size: 12px;">
								<strong>Avg:</strong> <?php echo esc_html( $avg ); ?>MB | 
								<strong>Min:</strong> <?php echo esc_html( $min ); ?>MB | 
								<strong>Max:</strong> <?php echo esc_html( $max ); ?>MB | 
								<strong>Range:</strong> <?php echo esc_html( $range ); ?>MB
							</div>
						</div>
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'reset', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary">New Test</a>
					</div>
				<?php endif; ?>
				</div>
				
				<?php 
				// Show latest test if history is available
				if ( !empty( $history ) ) :
					$latest_test = end( $history );
				?>
					<hr style="margin: 20px 0;">
					<div class="wp-system-info-section">
						<h4 style="margin: 0 0 10px 0;">📈 Latest Memory Test Result</h4>
						<div class="wp-system-info-recent-test">
						<div class="timestamp"><?php echo esc_html( $latest_test['timestamp'] ); ?></div>
						<div class="stats">
						<strong>Avg:</strong> <?php echo esc_html( $latest_test['average'] ); ?>MB | 
						<strong>Min:</strong> <?php echo esc_html( $latest_test['min'] ); ?>MB | 
						<strong>Max:</strong> <?php echo esc_html( $latest_test['max'] ); ?>MB | 
						<strong>Range:</strong> <?php echo esc_html( $latest_test['range'] ); ?>MB
						 <?php if ( isset( $latest_test['test_count'] ) && isset( $latest_test['interval_ms'] ) ) : ?>
								<br><small style="color: #666;">
									<strong>Tests:</strong> <?php echo esc_html( $latest_test['test_count'] ); ?> | 
									<strong>Interval:</strong> <?php echo esc_html( $latest_test['interval_ms'] / 1000 ); ?>s
								</small>
							<?php endif; ?>
						</div>
							<?php if ( !empty( $latest_test['deactivated_plugins'] ) && is_array( $latest_test['deactivated_plugins'] ) ) : ?>
								<div style="margin-top: 5px; font-size: 11px; color: #666;">
									<strong>Plugins deactivated during test:</strong><br>
									<?php
									if (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
										require_once(ABSPATH . 'wp-admin/includes/plugin.php');
									}
									if (function_exists('get_plugins')) {
										$all_plugins = get_plugins();
										foreach ( $latest_test['deactivated_plugins'] as $plugin_path ) {
										if ( isset( $all_plugins[$plugin_path] ) ) {
										echo '• ' . esc_html( $all_plugins[$plugin_path]['Name'] ) . '<br>';
										} else {
										echo '• ' . esc_html( $plugin_path ) . '<br>';
										}
										}
									} else {
										echo esc_html( implode( ', ', $latest_test['deactivated_plugins'] ) );
									}
									?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					<hr style="margin: 15px 0;">
					<div class="wp-system-info-header">
					<h4 style="margin: 0;">Test History</h4>
					<div>
					<span class="wp-system-info-toggle" id="history-toggle" onclick="toggleSectionWithText('memory-history', 'history-toggle')">Show History (<?php echo count( $history ); ?> tests)</span>
					</div>
					</div>
					<div id="memory-history" class="wp-system-info-collapsible wp-system-info-scroll large">
						<?php foreach ( array_reverse( $history ) as $entry ) : ?>
							<div style="margin-bottom: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px; border-left: 3px solid #0073aa;">
								<div style="font-weight: bold; margin-bottom: 5px;"><?php echo esc_html( $entry['timestamp'] ); ?></div>
								<div style="font-size: 12px;">
									<strong>Avg:</strong> <?php echo esc_html( $entry['average'] ); ?>MB | 
									<strong>Min:</strong> <?php echo esc_html( $entry['min'] ); ?>MB | 
									<strong>Max:</strong> <?php echo esc_html( $entry['max'] ); ?>MB | 
									<strong>Range:</strong> <?php echo esc_html( $entry['range'] ); ?>MB
									<?php if ( isset( $entry['test_count'] ) && isset( $entry['interval_ms'] ) ) : ?>
										<br><small style="color: #666;">
											<strong>Tests:</strong> <?php echo esc_html( $entry['test_count'] ); ?> | 
											<strong>Interval:</strong> <?php echo esc_html( $entry['interval_ms'] / 1000 ); ?>s
										</small>
									<?php endif; ?>
								</div>
								<?php if ( !empty( $entry['deactivated_plugins'] ) && is_array( $entry['deactivated_plugins'] ) ) : ?>
									<div style="margin-top: 5px; font-size: 11px; color: #666;">
										<strong>Plugins deactivated during test:</strong><br>
										<?php
										if (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
											require_once(ABSPATH . 'wp-admin/includes/plugin.php');
										}
										if (function_exists('get_plugins')) {
											$all_plugins = get_plugins();
											foreach ( $entry['deactivated_plugins'] as $plugin_path ) {
											if ( isset( $all_plugins[$plugin_path] ) ) {
											echo '• ' . esc_html( $all_plugins[$plugin_path]['Name'] ) . '<br>';
											} else {
											echo '• ' . esc_html( $plugin_path ) . '<br>';
											}
											}
										} else {
											echo esc_html( implode( ', ', $entry['deactivated_plugins'] ) );
										}
										?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<div style="margin-top: 10px;">
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'clear_history', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary" onclick="return confirm('Are you sure you want to clear all test history?');">Clear History</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
