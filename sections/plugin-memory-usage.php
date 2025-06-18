<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_plugin_memory_usage_section($plugin_memory, $settings) {
	$display_style = isset($settings['show_plugin_memory_usage']) && $settings['show_plugin_memory_usage'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="plugin-memory-usage-wrapper" style="display: <?php echo esc_attr($display_style); ?>; padding-top: 25px;">
		<!-- Plugin Memory Usage -->
		<div class="wp-system-info-section">
		<div class="wp-system-info-header">
		<h4 style="margin: 0;">🔌 Detailed Plugin Memory Usage</h4>
		<?php if ( !empty( $plugin_memory ) ) : ?>
		<span class="wp-system-info-toggle" id="plugin-toggle" onclick="toggleSectionWithText('plugin-memory', 'plugin-toggle')">Show Plugin Data (<?php echo count( $plugin_memory ); ?> plugins tracked)</span>
		<?php endif; ?>
		</div>
		<div style="margin-bottom: 10px; padding: 8px; background: #f9f9f9; border-left: 3px solid #0073aa; border-radius: 3px;">
			<div style="font-size: 12px; color: #555; margin-bottom: 8px; line-height: 1.4;">
				<strong>Plugin Memory Initialization:</strong> Records current memory usage for all active plugins to establish baseline tracking. This captures the memory state at plugin activation for future comparison and analysis.
			</div>
			<button id="initialize-plugins" class="button-secondary" onclick="initializePluginMemory()" style="margin-bottom: 8px;">
				Initialize Plugin Memory Tracking
			</button>
			<div id="initialize-status" style="font-size: 12px; color: #666; display: none;"></div>
		</div>
		<?php if ( !empty( $plugin_memory ) ) : ?>
		 <?php
		 // Get top 3 memory usage plugins
		 $sorted_plugins = $plugin_memory;
		 uasort($sorted_plugins, function($a, $b) {
		 return ($b['memory_at_activation'] ?? 0) - ($a['memory_at_activation'] ?? 0);
		 });
		 $top_plugins = array_slice($sorted_plugins, 0, 3, true);
		 
		 // Check if any plugin has high memory usage (over 50MB)
		 $has_high_usage = false;
		 foreach ($top_plugins as $plugin => $data) {
		  if (($data['memory_at_activation'] ?? 0) > 50) {
		   $has_high_usage = true;
		 break;
		 }
		 }
		 ?>
		 <?php if ( $has_high_usage && count($top_plugins) > 0 ) : ?>
			<div class="wp-system-info-top-plugins">
			<h5>Plugins with highest memory</h5>
			<?php foreach ( $top_plugins as $plugin => $data ) : ?>
				<?php if (($data['memory_at_activation'] ?? 0) > 50) : ?>
				<div class="wp-system-info-top-plugin">
					<strong><?php echo esc_html( $data['name'] ); ?>:</strong> <?php echo esc_html( $data['memory_at_activation'] ); ?>MB
					<?php if ( isset( $data['note'] ) ) : ?>
						<span style="color: #888; font-style: italic;"> (<?php echo esc_html( $data['note'] ); ?>)</span>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
		<?php else : ?>
				<!-- <div style="font-style: italic; padding: 8px; text-align: center;">
					No plugins detected with concerning memory usage<br>(all plugins using less than 50MB)
				</div> -->
		<?php endif; ?>
			<div id="plugin-memory" class="wp-system-info-collapsible wp-system-info-scroll">
				<?php foreach ( array_slice( $plugin_memory, -15 ) as $plugin => $data ) : ?>
					<div style="margin-bottom: 5px; font-size: 12px; padding: 5px; background: #f8f9fa; border-radius: 3px;">
						<strong><?php echo esc_html( $data['name'] ); ?>:</strong>
						Activation: <strong><?php echo esc_html( $data['memory_at_activation'] ); ?>MB</strong>
						<?php if ( isset( $data['deactivated_at'] ) ) : ?>
							| Deactivation: <strong><?php echo esc_html( $data['memory_at_deactivation'] ); ?>MB</strong>
						<?php endif; ?>
						<br><small style="color: #666;">Activated: <?php echo esc_html( $data['activated_at'] ); ?></small>
						<?php if ( isset( $data['note'] ) ) : ?>
							<br><em style="color: #888; font-size: 11px;"><?php echo esc_html( $data['note'] ); ?></em>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div><br>
		<?php else : ?>
			<div style="padding: 10px; background: #f0f0f0; border-radius: 3px; font-size: 12px; color: #666;">
				No plugin memory tracking data available. Click "Initialize" to start tracking all active plugins.
			</div>
		<?php endif; ?>
	</div>
	</div>
	<?php
}
