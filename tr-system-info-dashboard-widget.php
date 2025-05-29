<?php
/*
Plugin Name: System Info Dashboard Widget
Version: 1.4.3
Description: Comprehensive WordPress system information plugin with memory monitoring, database analysis, and performance insights.
Author: TechReader
Author URI: https://techreader.com
Text Domain: tr-system-info-dashboard-widget
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/* block direct requests */
if ( !function_exists( 'add_action' ) ) {
	echo 'Hello, this is a plugin: You must not call me directly.';
	exit;
}
defined('ABSPATH') OR exit;

if ( is_admin() ) {	
	define( 'TRSYSTEMDASHBOARDVER', '1.4.3' );
	
	class WP_System_Info {
		private $ipadr = "";
		private $servername = "";
		private $memory = array();	
		
		public function __construct() {
		$this->get_ip_address();
			add_action( 'init', array( &$this, 'check_memory_limits' ) );
			add_action( 'wp_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );
			if ( is_multisite() ) { 
				add_action( 'wp_network_dashboard_setup', array( &$this, 'add_dashboard_widget' ) );
			}
			add_filter( 'admin_footer_text', array( &$this, 'add_admin_footer' ) );
			add_action( 'init', array( $this, 'cron_memory_start' ), 1 );
			add_action( 'shutdown', array( $this, 'cron_memory_end' ) );
			add_action( 'activated_plugin', array( $this, 'plugin_memory_activated' ), 999 );
			add_action( 'deactivated_plugin', array( $this, 'plugin_memory_deactivated' ), 999 );
			add_action( 'wp_ajax_get_live_memory', array( $this, 'ajax_get_live_memory' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			// Add AJAX handler for plugin initialization
			add_action( 'wp_ajax_initialize_plugin_memory', array( $this, 'ajax_initialize_plugin_memory' ) );
		}

		public function enqueue_admin_scripts( $hook ) {
		// Only load on dashboard page
		if ( $hook !== 'index.php' ) {
			return;
		}
		
		wp_enqueue_script( 'jquery' );
		 
		// Enqueue admin CSS
		wp_enqueue_style( 
			'tr-system-info-admin-css', 
			plugin_dir_url( __FILE__ ) . 'assets/admin.css', 
			array(), 
			TRSYSTEMDASHBOARDVER 
		);
		
		// Enqueue admin JS if it exists
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'assets/admin.js' ) ) {
			wp_enqueue_script( 
				'tr-system-info-admin-js', 
				plugin_dir_url( __FILE__ ) . 'assets/admin.js', 
				array( 'jquery' ), 
				TRSYSTEMDASHBOARDVER, 
				true 
			);
			
			// Localize script with AJAX data
			wp_localize_script( 'tr-system-info-admin-js', 'wpSystemInfoAjax', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'liveMemoryNonce' => wp_create_nonce( 'live_memory_nonce' ),
				'initPluginsNonce' => wp_create_nonce( 'initialize_plugins_nonce' )
			) );
		}
	}

		public function ajax_get_live_memory() {
			check_ajax_referer( 'live_memory_nonce', 'nonce' );
			
			$memory_data = array(
				'current' => round( memory_get_usage( true ) / 1024 / 1024, 2 ),
				'peak' => round( memory_get_peak_usage( true ) / 1024 / 1024, 2 ),
				'timestamp' => time()
			);
			
			wp_send_json_success( $memory_data );
	}

	public function ajax_initialize_plugin_memory() {
		check_ajax_referer( 'initialize_plugins_nonce', 'nonce' );
		
		if (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		
		if (function_exists('get_plugins')) {
			$all_plugins = get_plugins();
			$active_plugins = get_option('active_plugins', array());
			$usage_data = get_option( 'wp_system_info_plugin_memory', array() );
			
			// Record current memory for each active plugin
			foreach ($active_plugins as $plugin_path) {
				if (isset($all_plugins[$plugin_path])) {
					$plugin = $all_plugins[$plugin_path];
					$usage_data[$plugin_path] = array(
						'name' => $plugin['Name'],
						'version' => $plugin['Version'],
						'memory_at_activation' => round(memory_get_usage(true) / 1024 / 1024, 2),
						'activated_at' => current_time('mysql'),
						'initialized' => true
					);
				}
			}
			
			update_option( 'wp_system_info_plugin_memory', $usage_data );
			
			wp_send_json_success( array(
				'message' => 'Initialized ' . count($active_plugins) . ' active plugins for memory tracking.',
				'count' => count($active_plugins)
			) );
		} else {
			wp_send_json_error( 'Unable to access plugin functions.' );
		}
		}

		// Cron memory tracking
		public function cron_memory_start() {
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				update_option( 'wp_system_info_cron_start_memory', memory_get_usage( true ) );
				update_option( 'wp_system_info_cron_start_time', microtime( true ) );
			}
		}

		public function cron_memory_end() {
			if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
				$start_memory = get_option( 'wp_system_info_cron_start_memory' );
				$start_time = get_option( 'wp_system_info_cron_start_time' );
				
				if ( $start_memory !== false && $start_time !== false ) {
					$peak_memory = memory_get_peak_usage( true );
					$current_memory = memory_get_usage( true );
					$memory_diff = $peak_memory - $start_memory;
					$execution_time = round( ( microtime( true ) - $start_time ) * 1000, 2 );
					
					$log_entry = array(
						'timestamp' => current_time( 'mysql' ),
						'peak_memory' => round( $peak_memory / 1024 / 1024, 2 ),
						'memory_used' => round( $memory_diff / 1024 / 1024, 2 ),
						'execution_time' => $execution_time
					);
					
					$logs = get_option( 'wp_system_info_cron_logs', array() );
					$logs[] = $log_entry;
					
					// Keep only last 50 entries
					if ( count( $logs ) > 50 ) {
						$logs = array_slice( $logs, -50 );
					}
					
					wp_cache_set( 'wp_system_info_cron_logs', $logs );
					update_option( 'wp_system_info_cron_logs', $logs );
					delete_option( 'wp_system_info_cron_start_memory' );
					delete_option( 'wp_system_info_cron_start_time' );
				}
			}
		}
		
		// Plugin memory tracking
		public function plugin_memory_activated( $plugin ) {
			try {
				$memory_usage_pre = memory_get_usage( true );
				
				$plugin_data = array();
				if (function_exists('get_plugin_data')) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				} elseif (!function_exists('get_plugin_data') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
					require_once(ABSPATH . 'wp-admin/includes/plugin.php');
					if (function_exists('get_plugin_data')) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
					}
				}
				
				$usage_data = get_option( 'wp_system_info_plugin_memory', array() );
				if (!is_array($usage_data)) $usage_data = array();
				
				$usage_data[ $plugin ] = array(
					'name' => isset($plugin_data['Name']) ? $plugin_data['Name'] : $plugin,
					'version' => isset($plugin_data['Version']) ? $plugin_data['Version'] : 'Unknown',
					'memory_at_activation' => round( $memory_usage_pre / 1024 / 1024, 2 ),
					'activated_at' => function_exists('current_time') ? current_time( 'mysql' ) : gmdate('Y-m-d H:i:s')
				);
				
				update_option( 'wp_system_info_plugin_memory', $usage_data );
			} catch (Exception $e) {
				// Ignore errors in plugin memory tracking
			}
		}

		public function plugin_memory_deactivated( $plugin ) {
			try {
				$usage_data = get_option( 'wp_system_info_plugin_memory', array() );
				if (!is_array($usage_data)) $usage_data = array();
				
				if ( isset( $usage_data[ $plugin ] ) ) {
					$usage_data[ $plugin ]['deactivated_at'] = function_exists('current_time') ? current_time( 'mysql' ) : gmdate('Y-m-d H:i:s');
					$usage_data[ $plugin ]['memory_at_deactivation'] = round( memory_get_usage( true ) / 1024 / 1024, 2 );
					update_option( 'wp_system_info_plugin_memory', $usage_data );
				}
			} catch (Exception $e) {
				// Ignore errors in plugin memory tracking
			}
		}

		// System analysis
		public function check_memory_limits() {
			$this->memory['phplimit'] = "";
			$this->memory['phplimitunity'] = 'MB';
			
			if ( !is_null( ini_get( 'memory_limit' ) ) ) {
				$phplimit_memory = ini_get( 'memory_limit' );
				if ( preg_match( "/G$/i", $phplimit_memory ) ) {
					$phplimit_memory = 1024 * preg_replace( "/G$/i", "", $phplimit_memory );
				}
				$this->memory['phplimit'] = (int) $phplimit_memory;
			}
			
			$ret = $this->format_memory_limit( ini_get( 'memory_limit' ) );
			$this->memory["wpmb"] = $ret["mb"] ?? '';
			$this->memory["wpunity"] = $ret["unity"] ?? '';
			
			$ret = $this->format_memory_limit( WP_MAX_MEMORY_LIMIT );
			$this->memory["wpmaxmb"] = $ret["mb"] ?? '';
			$this->memory["wpmaxunity"] = $ret["unity"] ?? '';
		}
		
		private function check_memory_usage() {
			$this->memory['usage'] = function_exists( 'memory_get_peak_usage' ) ? round( memory_get_peak_usage( true ) / 1024 / 1024, 2 ) : 0;
			
			if ( !empty( $this->memory['usage'] ) ) {
				$this->memory['percent'] = -1;
				if ( !empty( $this->memory['wpmb'] ) && ( $this->memory['wpmb'] != 0 ) ) {
					$this->memory['percent'] = round( $this->memory['usage'] / $this->memory["wpmb"] * 100, 0 );
				}
				
				$this->memory['percentphp'] = -1;
				if ( !empty( $this->memory['phplimit'] ) && ( $this->memory['phplimit'] != 0 ) ) {
					$this->memory['percentphp'] = round( $this->memory['usage'] / $this->memory["phplimit"] * 100, 0 );
				}
				
				$this->memory['percent_pos'] = '';
				$this->memory['color'] = '#4CAF50';
				if ( $this->memory['percent'] > 80 ) $this->memory['color'] = '#E66F00';
				if ( $this->memory['percent'] > 95 ) $this->memory['color'] = '#DC3545';
				if ( $this->memory['percent'] < 10 ) $this->memory['percent_pos'] = 'margin-right: -30px; color: #444;';
				
				$this->memory['percentwidth'] = $this->memory['percent'];
				if ( $this->memory['percent'] > 100 ) {
					$this->memory['percentwidth'] = 100;
				}
			}
		}

		private function get_os_distribution() {
			// Simple, safe OS detection
			$arch = (PHP_INT_SIZE === 8) ? 'x64' : 'x86';
			
			// Start with basic info
			$os_name = php_uname('s');
			
			// Try to get better distribution name safely
			if ($os_name === 'Linux') {
				// Try lsb_release first
				$distro_info = '';
				if (function_exists('exec')) {
					$output = array();
					$return_var = 0;
					@exec('lsb_release -d 2>/dev/null', $output, $return_var);
					if ($return_var === 0 && !empty($output) && is_array($output)) {
						// Parse "Description: CentOS Linux release 7.9.2009 (Core)"
						foreach ($output as $line) {
							if (strpos($line, 'Description:') === 0) {
								$distro_info = trim(str_replace('Description:', '', $line));
								break;
							}
						}
					}
					
					// If that didn't work, try the -ds flag
					if (empty($distro_info)) {
						$output = array();
						$return_var = 0;
						@exec('lsb_release -ds 2>/dev/null', $output, $return_var);
						if ($return_var === 0 && !empty($output) && is_array($output)) {
							$distro_info = trim(str_replace('"', '', $output[0]));
						}
					}
				}
				
				if (!empty($distro_info)) {
					return $distro_info . ' (' . $arch . ')';
				}
			}
			
			// Safe fallback
			return $os_name . ' (' . $arch . ')';
		}

		private function detect_server_software() {
			$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown';
			
			// Check for common reverse proxy indicators
			$nginx_indicators = array(
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_REAL_IP',
				'HTTP_X_FORWARDED_PROTO',
				'HTTP_X_NGINX_PROXY'
			);
			
			$has_nginx_proxy = false;
			foreach ($nginx_indicators as $header) {
				if (isset($_SERVER[$header])) {
					$has_nginx_proxy = true;
					break;
				}
			}
			
			// Check if nginx is mentioned in server signature
			if (stripos($server_software, 'nginx') !== false) {
				return $server_software;
			}
			
			// If we detect nginx proxy headers but server shows Apache
			if ($has_nginx_proxy && stripos($server_software, 'apache') !== false) {
				return 'NGINX+' . $server_software;
			}
			
			return $server_software;
		}

		private function get_system_info() {
			global $wpdb;
			
			try {
				// Get database info with error handling
				$tables = array();
				$total_db_size = 0;
				$table_data = array();
				
				if ($wpdb) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$tables = $wpdb->get_results( "SHOW TABLE STATUS" );
					if (is_array($tables)) {
						foreach ( $tables as $table ) {
							if (isset($table->Data_length) && isset($table->Index_length)) {
								$size = $table->Data_length + $table->Index_length;
								$total_db_size += $size;
								$table_data[] = array(
									'name' => $table->Name ?? 'Unknown',
									'size' => function_exists('size_format') ? size_format( $size ) : round($size/1024/1024, 2) . ' MB',
									'rows' => $table->Rows ?? 0
								);
							}
						}
					}
				}
				
				// Get WordPress info with error handling
				$active_plugins = get_option( 'active_plugins', array() );
				if (!is_array($active_plugins)) $active_plugins = array();
				
				$all_plugins = array();
				if (function_exists('get_plugins')) {
					$all_plugins = get_plugins();
					if (!is_array($all_plugins)) $all_plugins = array();
				} elseif (!function_exists('get_plugins') && file_exists(ABSPATH . 'wp-admin/includes/plugin.php')) {
					require_once(ABSPATH . 'wp-admin/includes/plugin.php');
					if (function_exists('get_plugins')) {
						$all_plugins = get_plugins();
						if (!is_array($all_plugins)) $all_plugins = array();
					}
				}
				
				$theme_name = 'Unknown';
				$theme_version = 'Unknown';
				if (function_exists('wp_get_theme')) {
					$theme = wp_get_theme();
					if ($theme && is_object($theme) && !is_wp_error($theme)) {
						$theme_name = $theme->get('Name');
						$theme_version = $theme->get('Version');
						if (empty($theme_name) || $theme_name === false) $theme_name = $theme->get_stylesheet();
						if (empty($theme_version) || $theme_version === false) $theme_version = '1.0';
					}
				} else {
					// Fallback when wp_get_theme is not available
					$theme_name = get_option('stylesheet');
					$theme_version = '1.0';
				}
				
				return array(
					'wp_version' => function_exists('get_bloginfo') ? get_bloginfo( 'version' ) : 'Unknown',
					'php_version' => PHP_VERSION,
					'mysql_version' => ($wpdb && method_exists($wpdb, 'db_version')) ? $wpdb->db_version() : 'Unknown',
					'server_software' => $this->detect_server_software(),
					'os_info' => $this->get_os_distribution(),
					'hostname' => function_exists('gethostname') ? gethostname() : (isset($_SERVER['SERVER_NAME']) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : 'Unknown'),
					'is_ssl' => function_exists('is_ssl') ? is_ssl() : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
					'is_multisite' => function_exists('is_multisite') ? is_multisite() : false,
					'wp_debug' => defined( 'WP_DEBUG' ) && WP_DEBUG,
					'wp_debug_log' => defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG,
					'total_db_size' => function_exists('size_format') ? size_format( $total_db_size ) : round($total_db_size/1024/1024, 2) . ' MB',
					'table_count' => count( $tables ),
					'table_data' => $table_data,
					'active_plugins_count' => count( $active_plugins ),
					'total_plugins_count' => count( $all_plugins ),
					'theme_name' => $theme_name,
					'theme_version' => $theme_version,
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ) ?: 'Unknown',
					'post_max_size' => ini_get( 'post_max_size' ) ?: 'Unknown',
					'max_execution_time' => ini_get( 'max_execution_time' ) ?: 'Unknown',
					'max_input_vars' => ini_get( 'max_input_vars' ) ?: 'Unknown',
					'php_extensions' => function_exists('get_loaded_extensions') ? get_loaded_extensions() : array()
				);
			} catch (Exception $e) {
				// Return basic fallback data if there's an error
				return array(
					'wp_version' => 'Unknown',
					'php_version' => PHP_VERSION,
					'mysql_version' => 'Unknown',
					'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'Unknown',
					'os_info' => php_uname('s') . ' ' . php_uname('r'),
					'hostname' => 'Unknown',
					'is_ssl' => false,
					'is_multisite' => false,
					'wp_debug' => false,
					'wp_debug_log' => false,
					'total_db_size' => 'Unknown',
					'table_count' => 0,
					'table_data' => array(),
					'active_plugins_count' => 0,
					'total_plugins_count' => 0,
					'theme_name' => 'Unknown',
					'theme_version' => 'Unknown',
					'upload_max_filesize' => ini_get( 'upload_max_filesize' ) ?: 'Unknown',
					'post_max_size' => ini_get( 'post_max_size' ) ?: 'Unknown',
					'max_execution_time' => ini_get( 'max_execution_time' ) ?: 'Unknown',
					'max_input_vars' => ini_get( 'max_input_vars' ) ?: 'Unknown',
					'php_extensions' => array()
				);
			}
		}

		public function dashboard_output() {
			$this->check_memory_usage();
			$system_info = $this->get_system_info();
			$cron_logs = get_option( 'wp_system_info_cron_logs', array() );
			$plugin_memory = get_option( 'wp_system_info_plugin_memory', array() );
			
			// Add some test data if sections are empty (for demonstration)
			if (empty($cron_logs)) {
				$cron_logs = array(
					array(
						'timestamp' => current_time('mysql'),
						'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
						'memory_used' => round(memory_get_usage(true) / 1024 / 1024, 2),
						'execution_time' => 150.5
					)
				);
			}
			
			if (empty($plugin_memory)) {
			$plugin_memory = array(
			'tr-system-info-dashboard-widget/tr-system-info-dashboard-widget.php' => array(
			'name' => 'TechReader System Info Dashboard Widget',
			'version' => '1.2.3',
			'memory_at_activation' => round(memory_get_usage(true) / 1024 / 1024, 2),
			'activated_at' => current_time('mysql'),
			 'note' => 'Sample data - use Initialize button to track all active plugins'
			 )
			 );
		}
			
			?>

			<div class="wp-system-info-dashboard">
				
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
					<div class="wp-system-info-os-line">
						<strong>OS:</strong> <?php echo esc_html( $system_info['os_info'] ); ?> | 
						<strong>Hostname:</strong> <?php echo esc_html( $system_info['hostname'] ); ?> | 
						<strong>Server:</strong> <?php echo esc_html( $system_info['server_software'] ); ?>
					</div>
				</div>


			<script>
				// Live memory monitoring functions
				let memoryChart = null;
				let memoryData = [];
				let maxDataPoints = 30;

				function initMemoryChart() {
					const canvas = document.getElementById('wp-system-info-memory-chart');
					if (!canvas) return;
					const ctx = canvas.getContext('2d');
					canvas.width = canvas.offsetWidth;
					canvas.height = 80;
					memoryChart = { canvas, ctx };
					updateMemoryChart();
				}

				function updateMemoryChart() {
					if (!memoryChart) return;
					const { ctx, canvas } = memoryChart;
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					if (memoryData.length < 2) return;
					// Draw grid
					ctx.strokeStyle = '#e0e0e0';
					ctx.lineWidth = 1;
					for (let i = 0; i < 5; i++) {
						const y = (canvas.height / 4) * i;
						ctx.beginPath();
						ctx.moveTo(0, y);
						ctx.lineTo(canvas.width, y);
						ctx.stroke();
					}
					// Draw memory line
					const maxMemory = Math.max(...memoryData.map(d => d.current));
					const minMemory = Math.min(...memoryData.map(d => d.current));
					const range = maxMemory - minMemory || 1;
					ctx.strokeStyle = '#0073aa';
					ctx.lineWidth = 2;
					ctx.beginPath();
					memoryData.forEach((data, index) => {
						const x = (canvas.width / (memoryData.length - 1)) * index;
						const y = canvas.height - ((data.current - minMemory) / range) * canvas.height;
						if (index === 0) { ctx.moveTo(x, y); } else { ctx.lineTo(x, y); }
					});
					ctx.stroke();
				}

				function fetchMemoryData() {
					if (!ajaxurl) return;
					fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'action=get_live_memory&nonce=<?php echo esc_js( wp_create_nonce( 'live_memory_nonce' ) ); ?>'
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							memoryData.push(data.data);
							if (memoryData.length > maxDataPoints) memoryData.shift();
							if (document.getElementById('current-memory')) document.getElementById('current-memory').textContent = data.data.current;
							if (document.getElementById('peak-memory')) document.getElementById('peak-memory').textContent = data.data.peak;
							updateMemoryChart();
						}
					}).catch(() => {});
				}

				if (typeof jQuery !== 'undefined') {
					jQuery(document).ready(function() {
						setTimeout(() => {
							initMemoryChart();
							fetchMemoryData();
							setInterval(fetchMemoryData, 3000);
						}, 100);
					});
				}

				// Toggle functions for collapsible sections
				function toggleSection(id) {
					const element = document.getElementById(id);
					if (element) {
						element.style.display = element.style.display === 'block' ? 'none' : 'block';
					}
				}

				function toggleSectionWithText(sectionId, toggleId) {
					const element = document.getElementById(sectionId);
					const toggle = document.getElementById(toggleId);
					if (!element || !toggle) return;
					
					// Hide the other section if it's a table/plugin toggle
					if (sectionId === 'db-tables' || sectionId === 'active-plugins') {
						const otherSection = sectionId === 'db-tables' ? 'active-plugins' : 'db-tables';
						const otherToggle = sectionId === 'db-tables' ? 'plugins-toggle' : 'tables-toggle';
						const otherElement = document.getElementById(otherSection);
						const otherToggleElement = document.getElementById(otherToggle);
						
						if (otherElement && otherElement.style.display === 'block') {
							otherElement.style.display = 'none';
							if (otherToggleElement && otherToggleElement.textContent.includes('Hide')) {
								otherToggleElement.textContent = otherToggleElement.textContent.replace('Hide', 'Show');
							}
						}
					}
					
					if (element.style.display === 'none' || element.style.display === '') {
						element.style.display = 'block';
						if (toggle.textContent.includes('Show')) {
							toggle.textContent = toggle.textContent.replace('Show', 'Hide');
						}
					} else {
						element.style.display = 'none';
						if (toggle.textContent.includes('Hide')) {
							toggle.textContent = toggle.textContent.replace('Hide', 'Show');
						}
					}
				}

				function initializePluginMemory() {
					const button = document.getElementById('initialize-plugins');
					const status = document.getElementById('initialize-status');
					if (!button || !status || !ajaxurl) return;
					
					button.disabled = true;
					button.textContent = 'Initializing...';
					status.style.display = 'block';
					status.textContent = 'Recording memory usage for all active plugins...';
					
					fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: 'action=initialize_plugin_memory&nonce=<?php echo esc_js( wp_create_nonce( 'initialize_plugins_nonce' ) ); ?>'
					})
					.then(response => response.json())
					.then(data => {
						if (data.success) {
							status.textContent = data.data.message + ' Refresh the page to see updated data.';
							status.style.color = '#28a745';
							button.textContent = 'Initialized';
							setTimeout(() => location.reload(), 2000);
						} else {
							status.textContent = 'Error: ' + (data.data || 'Unknown error');
							status.style.color = '#dc3545';
							button.disabled = false;
							button.textContent = 'Initialize Plugin Memory Tracking';
						}
					})
					.catch(error => {
						status.textContent = 'Error initializing plugins.';
						status.style.color = '#dc3545';
						button.disabled = false;
						button.textContent = 'Initialize Plugin Memory Tracking';
					});
				}

				function cancelMemoryTest() {
					if (!confirm('Are you sure you want to cancel the memory test?')) {
						return false;
					}
					
					// Clear the timeout to stop automatic reload
					if (window.memoryTestTimer) {
						clearTimeout(window.memoryTestTimer);
						window.memoryTestTimer = null;
					}
					
					// Update progress popup to show canceling
					const progressPopup = document.querySelector('.wp-system-info-test-progress');
					if (progressPopup) {
						progressPopup.innerHTML = '🗑️ Memory Test Cancelled<br><div class="details">Cleaning up and redirecting...</div>';
						progressPopup.style.backgroundColor = '#6c757d';
					}
					
					return true; // Allow link to proceed
				}
			</script>				<!-- Memory Information -->
				<div class="wp-system-info-section">
					<h4>🧠 Memory Usage</h4>
					<div class="wp-system-info-item">
						<strong>Current Usage:</strong> <?php echo esc_html( $this->memory['usage'] ); ?> MB
						<div class="wp-system-info-progress">
							<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( min( $this->memory['percent'], 100 ) ); ?>%; background-color: <?php echo esc_attr( $this->memory['color'] ); ?>;">
								<?php echo esc_html( $this->memory['percent'] ); ?>%
							</div>
						</div>
					</div>
					<div class="wp-system-info-grid">
						<div>
							<div class="wp-system-info-item">
								<strong>WP Limit:</strong> <?php echo esc_html( $this->memory['wpmb'] . $this->memory['wpunity'] ); ?>
							</div>
							<div class="wp-system-info-item">
								<strong>WP Admin Limit:</strong> <?php echo esc_html( $this->memory['wpmaxmb'] . $this->memory['wpmaxunity'] ); ?>
							</div>
						</div>
						<div>
							<div class="wp-system-info-item">
								<strong>PHP Limit:</strong> <?php echo esc_html( $this->memory['phplimit'] . $this->memory['phplimitunity'] ); ?>
							</div>
							<div class="wp-system-info-item">
								<strong>Max Execution:</strong> <?php echo esc_html( $system_info['max_execution_time'] ); ?>s
							</div>
						</div>
					</div>
				</div>

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

				<!-- Memory Measurement Tool -->
				<div class="wp-system-info-section">
					<h4>🔄 Memory Measurement Tool</h4>
					<?php $this->render_memory_measurement_tool(); ?>
				</div>



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

				<!-- Developer Credits -->
				<div class="wp-system-info-credits">
					<a href="#" target="_blank">System Info Dashboard Widget</a> &nbsp; | &nbsp; 
					Created by <a href="https://techreader.com" target="_blank">TechReader</a> &nbsp; | 
					&nbsp; Plugin Version: <?php echo esc_html( TRSYSTEMDASHBOARDVER ); ?>
				</div>

			</div>


			<?php
			}

		private function render_memory_measurement_tool() {
			$settings = get_option( 'wp_system_info_measurement_settings', array( 'nomeas' => 10, 'secrel' => 2000 ) );
			$measurements = get_option( 'wp_system_info_measurements', array() );
			$history = get_option( 'wp_system_info_measurement_history', array() );
			
			$current_test = sanitize_text_field( wp_unslash( $_GET['test'] ?? '' ) );
			$action = sanitize_text_field( wp_unslash( $_GET['action'] ?? '' ) );
			
			// Handle settings update
			if ( isset($_POST['update_settings']) && isset($_POST['settings_nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['settings_nonce'] ) ), 'update_settings' ) ) {
				$nomeas = intval( sanitize_text_field( wp_unslash( $_POST['nomeas'] ?? '10' ) ) );
				$secrel = intval( sanitize_text_field( wp_unslash( $_POST['secrel'] ?? '2000' ) ) );
				if ($nomeas > 0 && $nomeas <= 100) $settings['nomeas'] = $nomeas;
				if ($secrel >= 500 && $secrel <= 10000) $settings['secrel'] = $secrel;
				update_option('wp_system_info_measurement_settings', $settings);
			}
			
			// Handle cancel test
			if ( $current_test === 'cancel' && isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'memory_test' ) ) {
				delete_option( 'wp_system_info_measurements' );
				delete_option( 'wp_system_info_current_test_plugins' );
				$measurements = array();
				$url = remove_query_arg( array( 'test', 'count', 'nonce' ) );
				?><script>window.location.href = '<?php echo esc_url( $url ); ?>';</script><?php
				return;
			}
			
			// Handle reset
			if ( $current_test === 'reset' && isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'memory_test' ) ) {
				delete_option( 'wp_system_info_measurements' );
				delete_option( 'wp_system_info_current_test_plugins' );
				$measurements = array();
				$url = remove_query_arg( array( 'test', 'count', 'nonce' ) );
				?><script>window.location.href = '<?php echo esc_url( $url ); ?>';</script><?php
				return;
			}
			
			// Handle clear history
			if ( $current_test === 'clear_history' && isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'memory_test' ) ) {
				delete_option( 'wp_system_info_measurement_history' );
				$history = array();
				$url = remove_query_arg( array( 'test', 'nonce' ) );
				?><script>window.location.href = '<?php echo esc_url( $url ); ?>';</script><?php
				return;
			}
			
			if ( $current_test === 'start' && isset($_GET['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'memory_test' ) ) {
				$measurements = array(); // Reset measurements
				$measurements[] = round( memory_get_usage( true ) / 1024 / 1024, 2 );
				update_option( 'wp_system_info_measurements', $measurements );
				
				// Save current active plugins for this test
				$active_plugins = get_option('active_plugins', array());
				update_option( 'wp_system_info_current_test_plugins', $active_plugins );
			}
			
			$test_count = intval( sanitize_text_field( wp_unslash( $_GET['count'] ?? '1' ) ) );
			if ( $current_test === 'continue' && count( $measurements ) < $settings['nomeas'] ) {
				$measurements[] = round( memory_get_usage( true ) / 1024 / 1024, 2 );
				update_option( 'wp_system_info_measurements', $measurements );
			}
			
			// If test is complete, save to history and redirect to clean URL
			if ( count( $measurements ) >= $settings['nomeas'] && !empty($measurements) && $current_test !== 'complete' ) {
				$avg = round( array_sum( $measurements ) / count( $measurements ), 2 );
				$min = min( $measurements );
				$max = max( $measurements );
				$range = round( $max - $min, 2 );
				
				$test_plugins = get_option( 'wp_system_info_current_test_plugins', array() );
				$current_plugins = get_option('active_plugins', array());
				$deactivated_plugins = array_diff( $test_plugins, $current_plugins );
				
				$history_entry = array(
					'timestamp' => current_time( 'mysql' ),
					'average' => $avg,
					'min' => $min,
					'max' => $max,
					'range' => $range,
					'measurements' => count( $measurements ),
					'deactivated_plugins' => $deactivated_plugins
				);
				
				$history[] = $history_entry;
				// Keep only last 10 history entries
				if ( count( $history ) > 10 ) {
					$history = array_slice( $history, -10 );
				}
				update_option( 'wp_system_info_measurement_history', $history );
				
				// Clear the measurements and redirect to clean URL
				delete_option( 'wp_system_info_measurements' );
				delete_option( 'wp_system_info_current_test_plugins' );
				$measurements = array();
				
				$url = remove_query_arg( array( 'test', 'count', 'nonce' ) );
				?><script>window.location.href = '<?php echo esc_url( $url ); ?>';</script><?php
				return;
			}
			
			?>
			<div style="padding: 8px 12px; background: #e7f3ff; border-left: 4px solid #0073aa; margin-bottom: 15px; font-size: 12px;">
				<strong>How to use:</strong> This tool measures memory usage multiple times by automatically reloading the page. 
				Use it to find memory-hungry plugins by deactivating suspicious plugins before starting the test, then comparing results. 
				Higher "Max" values indicate potential memory issues.
			</div>
			<div style="padding: 10px; background: #f9f9f9; border-radius: 3px;">
				<div style="margin-bottom: 10px;">
					<strong>Settings:</strong>
					<form method="post" style="display: inline;">
						<?php wp_nonce_field( 'update_settings', 'settings_nonce' ); ?>
						Measurements: <input type="number" name="nomeas" value="<?php echo esc_attr( $settings['nomeas'] ); ?>" min="1" max="100" style="width: 60px;">
						Interval (ms): <input type="number" name="secrel" value="<?php echo esc_attr( $settings['secrel'] ); ?>" min="500" max="10000" style="width: 80px;">
						<input type="submit" name="update_settings" value="Save" class="button-secondary">
					</form>
				</div>
				
				<?php if ( empty( $measurements ) ) : ?>
					<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'start', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-primary">Start Memory Test</a>
				<?php elseif ( count( $measurements ) < $settings['nomeas'] ) : ?>
					<div class="wp-system-info-test-progress">
						🔄 Memory Test in Progress<br>
						<div class="details">
							Measurement <?php echo esc_html( count( $measurements ) ); ?>/<?php echo esc_html( $settings['nomeas'] ); ?><br>
							<div class="wp-system-info-progress" style="margin-top: 5px; width: 200px;">
								<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( round( count( $measurements ) / $settings['nomeas'] * 100 ) ); ?>%; background-color: white; color: #0073aa;">
									<?php echo esc_html( round( count( $measurements ) / $settings['nomeas'] * 100 ) ); ?>%
								</div>
							</div>
							Page will reload automatically...
						</div>
						<div style="margin-top: 10px;">
							<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'cancel', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary" style="border-color: #666; color: #666;" onclick="return cancelMemoryTest();">✕ Cancel Test</a>
						</div>
					</div>
					<div>
						<strong>Progress:</strong> <?php echo esc_html( count( $measurements ) ); ?>/<?php echo esc_html( $settings['nomeas'] ); ?> measurements
						<div class="wp-system-info-progress" style="margin: 5px 0;">
							<div class="wp-system-info-progress-bar" style="width: <?php echo esc_attr( round( count( $measurements ) / $settings['nomeas'] * 100 ) ); ?>%; background-color: #0073aa;">
							<?php echo esc_html( round( count( $measurements ) / $settings['nomeas'] * 100 ) ); ?>%
							</div>
						</div>
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'start', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary">Restart Test</a>
						<a href="<?php echo esc_url( add_query_arg( array( 'test' => 'cancel', 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>" class="button-secondary" style="border-color: #666; color: #666; margin-left: 5px;" onclick="return cancelMemoryTest();">✕ Cancel Test</a>
					</div>
					<script>
						window.memoryTestTimer = setTimeout(function() {
							window.location.href = '<?php echo esc_url( add_query_arg( array( 'test' => 'continue', 'count' => count( $measurements ) + 1, 'nonce' => wp_create_nonce( 'memory_test' ) ) ) ); ?>';
						}, <?php echo esc_js( $settings['secrel'] ); ?>);
					</script>
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
			<?php
		}
		 
		public function add_dashboard_widget() {
			$servertime = gmdate( 'Y-m-d H:i:s' );
			wp_add_dashboard_widget( 
				'wp_system_info_dashboard', 
				'System Information & Memory Monitor - ' . $servertime, 
				array( &$this, 'dashboard_output' ) 
			);
		}
		
		private function format_memory_limit( $value ) {
			$value = $value ?? '';
			if ( empty( $value ) ) {
				return array( 'mb' => 0, 'unity' => 'MB' );
			}
			
			if ( preg_match( "/G$/i", $value ) ) {
				$value_tmp = preg_replace( "/G$/i", "", $value );
				$value_no = 1024 * $value_tmp;
				$value = $value_no . "M";
			}
			
			$size = strtolower( substr( $value, -1 ) );
			$number = (int) substr( $value, 0, -1 );
			$ret = array();
			
			switch ( $size ) {
				case 'k': $ret["mb"] = ( $number / 1024 ); $ret["unity"] = "KB"; break;
				case 'm': $ret["mb"] = $number; $ret["unity"] = "MB"; break;
				case 'g': $ret["mb"] = ( $number * 1024 ); $ret["unity"] = "GB"; break;
				case 't': $ret["mb"] = ( $number * 1024 * 1024 ); $ret["unity"] = "TB"; break;
				default: $ret["mb"] = $number; $ret["unity"] = "MB"; break;
			}
			
			return $ret;
		}

		private function get_ip_address() {
			if ( isset( $_SERVER['SERVER_ADDR'] ) && !empty( $_SERVER['SERVER_ADDR'] ) ) {
				$this->ipadr = sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) );
			}
			if ( empty( $this->ipadr ) && isset( $_SERVER['LOCAL_ADDR'] ) && !empty( $_SERVER['LOCAL_ADDR'] ) ) {
				$this->ipadr = sanitize_text_field( wp_unslash( $_SERVER['LOCAL_ADDR'] ) );
			}
			
			if ( isset( $_SERVER['SERVER_NAME'] ) && !empty( $_SERVER['SERVER_NAME'] ) ) {
				$this->servername = " (" . sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) . ")";
			}
		}

		public function add_admin_footer( $content ) {
			$this->check_memory_usage();
			$content .= ' | Memory: ' . $this->memory['usage'] . 'MB/' . $this->memory["wpmb"] . $this->memory["wpunity"] . ' (' . $this->memory['percent'] . '%)';
			$content .= ' | PHP: ' . PHP_VERSION;
			$content .= ' | Server: ' . $this->ipadr . $this->servername;
			return $content;
		}
	}

	// Initialize the plugin
	function wp_system_info_init() { 
		return new WP_System_Info();
	}
	add_action( 'plugins_loaded', 'wp_system_info_init', 10, 1 );
}
