<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_System_Info_Admin {
	
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_save_system_info_settings', array( $this, 'ajax_save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}
	
	public function add_admin_menu() {
		add_menu_page(
			'System Info Widget Settings',
			'System Info',
			'manage_options',
			'system-info-widget-settings',
			array( $this, 'render_admin_page' ),
			'dashicons-admin-tools',
			99
		);
	}
	
	public function enqueue_admin_assets( $hook ) {
		// Load assets on both dashboard and settings pages
		if ( $hook !== 'toplevel_page_system-info-widget-settings' && $hook !== 'index.php' ) {
			return;
		}
		
		// Enqueue the dashboard CSS so sections look identical
		wp_enqueue_style( 
			'tr-system-info-dashboard-css', 
			plugin_dir_url( __FILE__ ) . 'assets/admin.css', 
			array(), 
			TRSYSTEMDASHBOARDVER 
		);
		
		// Enqueue the dashboard JS for live memory monitor
		wp_enqueue_script( 
			'tr-system-info-dashboard-js', 
			plugin_dir_url( __FILE__ ) . 'assets/admin.js', 
			array( 'jquery' ), 
			TRSYSTEMDASHBOARDVER, 
			true 
		);
		
		// Localize dashboard script with AJAX data
		wp_localize_script( 'tr-system-info-dashboard-js', 'wpSystemInfoAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'liveMemoryNonce' => wp_create_nonce( 'live_memory_nonce' ),
			'initPluginsNonce' => wp_create_nonce( 'initialize_plugins_nonce' )
		) );
		
		// Enqueue admin CSS for the settings page
		wp_enqueue_style( 
			'tr-system-info-admin-settings-css', 
			plugin_dir_url( __FILE__ ) . 'assets/admin-settings.css', 
			array( 'tr-system-info-dashboard-css' ), 
			TRSYSTEMDASHBOARDVER 
		);
		
		// Enqueue admin JS for the settings page
		wp_enqueue_script( 
			'tr-system-info-admin-settings-js', 
			plugin_dir_url( __FILE__ ) . 'assets/admin-settings.js', 
			array( 'jquery' ), 
			TRSYSTEMDASHBOARDVER, 
			true 
		);
		
		// Localize script with AJAX data
		wp_localize_script( 'tr-system-info-admin-settings-js', 'systemInfoSettings', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'system_info_settings_nonce' )
		) );
	}
	
	public function ajax_save_settings() {
		check_ajax_referer( 'system_info_settings_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}
		
		$settings = array(
			'master_widget_toggle' => isset( $_POST['master_widget_toggle'] ) && $_POST['master_widget_toggle'] === '1' ? 1 : 0,
			'show_system_overview' => isset( $_POST['show_system_overview'] ) && $_POST['show_system_overview'] === '1' ? 1 : 0,
			'show_memory_usage' => isset( $_POST['show_memory_usage'] ) && $_POST['show_memory_usage'] === '1' ? 1 : 0,
			'show_live_memory_monitor' => isset( $_POST['show_live_memory_monitor'] ) && $_POST['show_live_memory_monitor'] === '1' ? 1 : 0,
			'show_memory_measurement_tool' => isset( $_POST['show_memory_measurement_tool'] ) && $_POST['show_memory_measurement_tool'] === '1' ? 1 : 0,
			'show_cron_memory_usage' => isset( $_POST['show_cron_memory_usage'] ) && $_POST['show_cron_memory_usage'] === '1' ? 1 : 0,
			'show_plugin_memory_usage' => isset( $_POST['show_plugin_memory_usage'] ) && $_POST['show_plugin_memory_usage'] === '1' ? 1 : 0,
			'show_database_information' => isset( $_POST['show_database_information'] ) && $_POST['show_database_information'] === '1' ? 1 : 0,
			'show_php_configuration' => isset( $_POST['show_php_configuration'] ) && $_POST['show_php_configuration'] === '1' ? 1 : 0,
		);
		
		$updated = update_option( 'wp_system_info_widget_settings', $settings );
		
		if ( $updated || get_option( 'wp_system_info_widget_settings' ) === $settings ) {
			wp_send_json_success( array( 'message' => 'Settings saved successfully!' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Failed to save settings. Please try again.' ) );
		}
	}
	
	public function render_admin_page() {
		$settings = get_option( 'wp_system_info_widget_settings', array(
			'master_widget_toggle' => 1,
			'show_system_overview' => 1,
			'show_memory_usage' => 1,
			'show_live_memory_monitor' => 1,
			'show_memory_measurement_tool' => 1,
			'show_cron_memory_usage' => 1,
			'show_plugin_memory_usage' => 1,
			'show_database_information' => 1,
			'show_php_configuration' => 1,
		) );
		
		$sections = array(
			'show_system_overview' => array(
				'title' => 'System Overview',
				'description' => 'Display WordPress, PHP, MySQL versions and basic system information including server details and SSL status.',
				'icon' => '🖥️'
			),
			'show_memory_usage' => array(
				'title' => 'Memory Usage',
				'description' => 'Show current memory usage with visual progress bar, limits, and percentage utilization metrics.',
				'icon' => '🧠'
			),
			'show_live_memory_monitor' => array(
				'title' => 'Live Memory Monitor',
				'description' => 'Real-time memory usage chart with live updates every 3 seconds and historical data visualization.',
				'icon' => '📊'
			),
			'show_memory_measurement_tool' => array(
				'title' => 'Memory Measurement Tool',
				'description' => 'Advanced tool for measuring memory usage across multiple page loads to identify memory-hungry plugins.',
				'icon' => '🔄'
			),
			'show_cron_memory_usage' => array(
				'title' => 'Cron Memory Usage',
				'description' => 'Monitor memory usage during WordPress cron job execution with detailed execution time tracking.',
				'icon' => '⏰'
			),
			'show_plugin_memory_usage' => array(
				'title' => 'Plugin Memory Usage',
				'description' => 'Track memory usage by individual plugins including activation timestamps and memory footprints.',
				'icon' => '🔌'
			),
			'show_database_information' => array(
				'title' => 'Database Information',
				'description' => 'Database size analysis, table information, row counts, and comprehensive active plugins listing.',
				'icon' => '🗄️'
			),
			'show_php_configuration' => array(
				'title' => 'PHP Configuration',
				'description' => 'PHP settings overview including upload limits, execution timeouts, and loaded extensions list.',
				'icon' => '⚙️'
			)
		);
		?>
		<div class="wrap tr-system-info-admin">
			<h1>System Info Widget Settings</h1>
			
			<?php
			// Include header
			if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin/header.php' ) ) {
				include plugin_dir_path( __FILE__ ) . 'admin/header.php';
			}
			?>
			
			<div class="tr-settings-container">
				<div class="tr-settings-description">
				</div>
				
				<form id="system-info-settings-form" class="tr-settings-form">
					<?php wp_nonce_field( 'system_info_settings_nonce', 'nonce' ); ?>
					
					<!-- Dashboard Widget Sections -->
					<div class="tr-widget-sections">
					<h2>System Info Dashboard</h2>
						
						<div class="tr-sections-grid">
							<?php
							// Prepare system info data once - USE SAME METHODS AS DASHBOARD
							// Get the WP_System_Info instance to use same detection methods
							global $wp_system_info_instance;
							if (!$wp_system_info_instance) {
							// Create instance if not available
							$wp_system_info_instance = new WP_System_Info();
							}
							
							// Use the exact same get_system_info() method as dashboard
							$system_info = $wp_system_info_instance->get_system_info();
							
							// Prepare memory info data
							$memory_usage = round(memory_get_usage(true) / 1024 / 1024, 2);
							$memory_limit = (int)ini_get('memory_limit');
							$memory_percent = $memory_limit > 0 ? round($memory_usage / $memory_limit * 100, 1) : 0;
							
							// Get WP_MAX_MEMORY_LIMIT
							$wp_max_limit = WP_MAX_MEMORY_LIMIT;
							$wp_max_mb = (int)$wp_max_limit;
							$wp_max_unity = 'MB';
							if (preg_match("/G$/i", $wp_max_limit)) {
								$wp_max_mb = 1024 * (int)preg_replace("/G$/i", "", $wp_max_limit);
								$wp_max_unity = 'GB';
							}
							
							// Get PHP memory limit
							$php_limit = ini_get('memory_limit');
							$php_limit_mb = (int)$php_limit;
							$php_limit_unity = 'MB';
							if (preg_match("/G$/i", $php_limit)) {
								$php_limit_mb = 1024 * (int)preg_replace("/G$/i", "", $php_limit);
								$php_limit_unity = 'GB';
							}
							
							$memory_info = array(
								'usage' => $memory_usage,
								'wpmb' => $memory_limit,
								'wpunity' => 'MB',
								'wpmaxmb' => $wp_max_mb,
								'wpmaxunity' => $wp_max_unity,
								'phplimit' => $php_limit_mb,
								'phplimitunity' => $php_limit_unity,
								'percent' => $memory_percent,
								'color' => $memory_percent > 80 ? '#E66F00' : '#4CAF50',
								'percentwidth' => min(100, $memory_percent)
							);
							
							// System Overview Section
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/system-overview.php')) {
								echo '<div class="tr-section-card">';
								require_once plugin_dir_path(__FILE__) . 'sections/system-overview.php';
								if (function_exists('render_system_overview_section')) {
									render_system_overview_section($system_info, array('show_system_overview' => 1));
								}
								echo '</div>';
							}
							
							// Memory Usage Section
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/memory-usage.php')) {
								echo '<div class="tr-section-card">';
								require_once plugin_dir_path(__FILE__) . 'sections/memory-usage.php';
								if (function_exists('render_memory_usage_section')) {
									render_memory_usage_section($memory_info, $system_info, array('show_memory_usage' => 1));
								}
								echo '</div>';
							}
							
							// Live Memory Monitor + Cron + Plugin Memory Section (Combined)
							echo '<div class="tr-section-card tr-combined-card">';
							
							// Live Memory Monitor
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/live-memory-monitor.php')) {
								require_once plugin_dir_path(__FILE__) . 'sections/live-memory-monitor.php';
								if (function_exists('render_live_memory_monitor_section')) {
									render_live_memory_monitor_section(array('show_live_memory_monitor' => 1));
								}
							}
							
							// Cron Memory Usage
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/cron-memory-usage.php')) {
								require_once plugin_dir_path(__FILE__) . 'sections/cron-memory-usage.php';
								if (function_exists('render_cron_memory_usage_section')) {
									$cron_logs = get_option('wp_system_info_cron_logs', array());
									render_cron_memory_usage_section($cron_logs, array('show_cron_memory_usage' => 1));
								}
							}
							
							// Plugin Memory Usage
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/plugin-memory-usage.php')) {
								require_once plugin_dir_path(__FILE__) . 'sections/plugin-memory-usage.php';
								if (function_exists('render_plugin_memory_usage_section')) {
									$plugin_memory = get_option('wp_system_info_plugin_memory', array());
									render_plugin_memory_usage_section($plugin_memory, array('show_plugin_memory_usage' => 1));
								}
							}
							
							echo '</div>';
							
							// Memory Measurement Tool Section
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/memory-measurement-tool.php')) {
								echo '<div class="tr-section-card">';
								require_once plugin_dir_path(__FILE__) . 'sections/memory-measurement-tool.php';
								if (function_exists('render_memory_measurement_tool_section')) {
									render_memory_measurement_tool_section(array('show_memory_measurement_tool' => 1));
								}
								echo '</div>';
							}
							
							// Database Information Section
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/database-information.php')) {
								echo '<div class="tr-section-card">';
								require_once plugin_dir_path(__FILE__) . 'sections/database-information.php';
								if (function_exists('render_database_information_section')) {
									// Get database info
									global $wpdb;
									try {
									// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
								$tables = $wpdb->get_results("SHOW TABLE STATUS");
										$total_db_size = 0;
										$table_data = array();
										if (is_array($tables)) {
											foreach ($tables as $table) {
												if (isset($table->Data_length) && isset($table->Index_length)) {
													$size = $table->Data_length + $table->Index_length;
													$total_db_size += $size;
													$table_data[] = array(
														'name' => $table->Name ?? 'Unknown',
														'size' => size_format($size),
														'rows' => $table->Rows ?? 0
													);
												}
											}
										}
										$db_system_info = array_merge($system_info, array(
										'total_db_size' => size_format($total_db_size),
										'table_count' => count($tables),
										'table_data' => $table_data,
										'active_plugins_count' => count(get_option('active_plugins', array())),
										'total_plugins_count' => 0,
										 'theme_name' => function_exists('wp_get_theme') ? wp_get_theme()->get('Name') : 'Unknown',
												'theme_version' => function_exists('wp_get_theme') ? wp_get_theme()->get('Version') : '1.0'
											));
									} catch (Exception $e) {
										$db_system_info = array_merge($system_info, array(
										'total_db_size' => 'N/A',
										'table_count' => 0,
										'table_data' => array(),
										'active_plugins_count' => 0,
										'total_plugins_count' => 0,
										 'theme_name' => function_exists('wp_get_theme') ? wp_get_theme()->get('Name') : 'Unknown',
												'theme_version' => function_exists('wp_get_theme') ? wp_get_theme()->get('Version') : '1.0'
											));
									}
									render_database_information_section($db_system_info, array('show_database_information' => 1));
								}
								echo '</div>';
							}
							
							// PHP Configuration Section
							if (file_exists(plugin_dir_path(__FILE__) . 'sections/php-configuration.php')) {
								echo '<div class="tr-section-card">';
								require_once plugin_dir_path(__FILE__) . 'sections/php-configuration.php';
								if (function_exists('render_php_configuration_section')) {
									$php_system_info = array_merge($system_info, array(
										'upload_max_filesize' => ini_get('upload_max_filesize'),
										'post_max_size' => ini_get('post_max_size'),
										'max_execution_time' => ini_get('max_execution_time'),
										'max_input_vars' => ini_get('max_input_vars'),
										'php_extensions' => function_exists('get_loaded_extensions') ? get_loaded_extensions() : array()
									));
									render_php_configuration_section($php_system_info, array('show_php_configuration' => 1));
								}
								echo '</div>';
							}
							?>
						</div>
					</div>
					
					<!-- Settings Controls -->
					<div class="tr-settings-controls">
						<h2>Widget Settings</h2>
						<p>Click on any section below to enable or disable it in the dashboard widget:</p>
						
						<div class="tr-settings-grid">
							<!-- Master Toggle -->
							<div class="tr-setting-card tr-master-toggle <?php echo $settings['master_widget_toggle'] ? 'active' : ''; ?>">
							<div class="tr-status-tag <?php echo $settings['master_widget_toggle'] ? 'enabled' : 'disabled'; ?>">
							<?php echo $settings['master_widget_toggle'] ? 'Enabled' : 'Disabled'; ?>
							</div>
							<div class="tr-setting-header">
							<div class="tr-setting-icon">🎛️</div>
							<div class="tr-setting-info">
							<h3>Toggle Dashboard Widget</h3>
							<p>Enable or disable the entire System Info Dashboard Widget and all its sections. This is the master control for the entire widget.</p>
							</div>
							</div>
							<input type="checkbox" name="master_widget_toggle" value="1" <?php checked( $settings['master_widget_toggle'] ); ?>>
							</div>
							
							<?php foreach ( $sections as $key => $section ) : ?>
								<div class="tr-setting-card <?php echo $settings[$key] ? 'active' : ''; ?>">
									<div class="tr-status-tag <?php echo $settings[$key] ? 'enabled' : 'disabled'; ?>">
										<?php echo $settings[$key] ? 'Enabled' : 'Disabled'; ?>
									</div>
									<div class="tr-setting-header">
										<div class="tr-setting-icon"><?php echo esc_html( $section['icon'] ); ?></div>
										<div class="tr-setting-info">
											<h3><?php echo esc_html( $section['title'] ); ?></h3>
											<p><?php echo esc_html( $section['description'] ); ?></p>
										</div>
									</div>
									<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $settings[$key] ); ?>>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</form>
			</div>
			
			<?php
			// Include footer
			if ( file_exists( plugin_dir_path( __FILE__ ) . 'admin/footer.php' ) ) {
				include plugin_dir_path( __FILE__ ) . 'admin/footer.php';
			}
			?>
		</div>
		
		<!-- Popup Notification -->
		<div id="tr-notification-popup" class="tr-notification-popup">
			<div class="tr-notification-content">
				<span class="tr-notification-message">Settings saved successfully!</span>
			</div>
		</div>
		<?php
	}
}

new WP_System_Info_Admin();
