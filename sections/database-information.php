<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function render_database_information_section($system_info, $settings) {
	$display_style = isset($settings['show_database_information']) && $settings['show_database_information'] ? 'block' : 'none';
	?>
	<div class="wp-system-info-section-wrapper" id="database-information-wrapper" style="display: <?php echo esc_attr($display_style); ?>;">
		<!-- Database Information -->
		<div class="wp-system-info-section">
			<h4>🗄️ Database Information</h4>
			<div class="wp-system-info-grid">
				<div>
					<div class="wp-system-info-item db-section-left">
						<strong>Total Size:</strong> <?php echo esc_html( $system_info['total_db_size'] ); ?>
					</div>
					<div class="wp-system-info-item db-section-left">
						<strong>Table Count:</strong> <?php echo esc_html( $system_info['table_count'] ); ?>
					</div>
				</div>
				<div>
					<div class="wp-system-info-item db-section-right">
						<strong>Theme:</strong> <?php echo esc_html( $system_info['theme_name'] ); ?> (<?php echo esc_html( $system_info['theme_version'] ); ?>)
					</div>
					<div class="wp-system-info-item db-section-right">
						<strong>Active Plugins:</strong> <?php echo esc_html( $system_info['active_plugins_count'] ); ?>/<?php echo esc_html( $system_info['total_plugins_count'] ); ?>
					</div>
				</div>
			</div>
			<div class="wp-system-info-toggle-row">
				<div class="wp-system-info-toggle-cell">
					<span class="wp-system-info-toggle" id="tables-toggle" onclick="toggleSectionWithText('db-tables', 'tables-toggle')">Show Table Details (<?php echo count($system_info['table_data']); ?> tables)</span>
				</div>
				<div class="wp-system-info-toggle-cell right">
					<span class="wp-system-info-toggle" id="plugins-toggle" onclick="toggleSectionWithText('active-plugins', 'plugins-toggle')">Show Active Plugins</span>
				</div>
			</div>
			<div id="db-tables" class="wp-system-info-collapsible wp-system-info-scroll large">
				<table class="wp-system-info-db-table">
					<thead>
						<tr>
							<th>Table Name</th>
							<th>Size</th>
							<th>Rows</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $system_info['table_data'] as $table ) : ?>
							<tr>
								<td class="name"><?php echo esc_html( $table['name'] ); ?></td>
								<td class="size"><?php echo esc_html( $table['size'] ); ?></td>
								<td class="rows"><?php echo esc_html( number_format( $table['rows'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<div id="active-plugins" class="wp-system-info-collapsible wp-system-info-scroll">
				<div class="wp-system-info-plugin-grid">
				<?php 
				if (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
				require_once(ABSPATH . 'wp-admin/includes/plugin.php');
				}
				if (function_exists('get_plugins')) {
				$all_plugins = get_plugins();
				$active_plugins = get_option('active_plugins', array());
				foreach ($active_plugins as $plugin_path) {
				if (isset($all_plugins[$plugin_path])) {
				$plugin = $all_plugins[$plugin_path];
				echo '<div class="wp-system-info-plugin-item">';
				echo '<strong>' . esc_html($plugin['Name']) . '</strong>';
				echo '<div>v' . esc_html($plugin['Version']) . '</div>';
				 echo '</div>';
				 }
				 }
				} else {
				 echo '<div style="grid-column: 1 / -1; font-size: 12px; color: #666; text-align: center;">Plugin information not available.</div>';
				}
				 ?>
						</div>
			</div>
		</div>
	</div>
	<?php
}
