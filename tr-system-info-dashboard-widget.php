<?php
/*
Plugin Name: System Info Dashboard Widget
Version: 2.0.5
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
	define( 'TRSYSTEMDASHBOARDVER', '2.0.5' );
	define( 'TR_SYSTEM_INFO_FILE', __FILE__ );
	
	class WP_System_Info {
		private $ipadr = "";
		private $servername = "";
		private $memory = array();	
		
		public function __construct() {
		$this->get_ip_address();
		
		// Include admin functionality
		if ( is_admin() ) {
			require_once plugin_dir_path( __FILE__ ) . 'admin.php';
		}
		
		// Handle memory test actions early
		add_action( 'admin_init', array( $this, 'handle_memory_test_actions' ) );
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
			// Add AJAX handler for memory settings
			add_action( 'wp_ajax_save_memory_settings', array( $this, 'ajax_save_memory_settings' ) );
		}

		public function handle_memory_test_actions() {
			// Only process memory test actions on admin pages
			if ( !is_admin() || !current_user_can( 'manage_options' ) ) {
				return;
			}
			
			$current_test = sanitize_text_field( wp_unslash( $_GET['test'] ?? '' ) );
			
			if ( empty( $current_test ) ) {
				return;
			}
			
			// Verify nonce for all test actions
			if ( !isset( $_GET['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'memory_test' ) ) {
				return;
			}
			
			$measurement_settings = get_option( 'wp_system_info_measurement_settings', array( 'nomeas' => 10, 'secrel' => 2000 ) );
			$measurements = get_option( 'wp_system_info_measurements', array() );
			$history = get_option( 'wp_system_info_measurement_history', array() );
			
			switch ( $current_test ) {
				case 'cancel':
					delete_option( 'wp_system_info_measurements' );
					delete_option( 'wp_system_info_current_test_plugins' );
					$this->redirect_clean_url();
					break;
					
				case 'reset':
					delete_option( 'wp_system_info_measurements' );
					delete_option( 'wp_system_info_current_test_plugins' );
					$this->redirect_clean_url();
					break;
					
				case 'clear_history':
					delete_option( 'wp_system_info_measurement_history' );
					$this->redirect_clean_url();
					break;
					
				case 'start':
					$measurements = array(); // Reset measurements
					$measurements[] = round( memory_get_usage( true ) / 1024 / 1024, 2 );
					update_option( 'wp_system_info_measurements', $measurements );
					
					// Save current active plugins for this test
					$active_plugins = get_option('active_plugins', array());
					update_option( 'wp_system_info_current_test_plugins', $active_plugins );
					
					// Redirect to clean URL to show test in progress
					$this->redirect_clean_url();
					break;
					
				case 'continue':
					// Handle continue action but don't redirect - let JavaScript handle the flow
					if ( count( $measurements ) < $measurement_settings['nomeas'] ) {
						$measurements[] = round( memory_get_usage( true ) / 1024 / 1024, 2 );
						update_option( 'wp_system_info_measurements', $measurements );
						
						// Check if test is now complete
						if ( count( $measurements ) >= $measurement_settings['nomeas'] ) {
							$this->complete_memory_test( $measurements, $history );
							$this->redirect_clean_url();
						}
					}
					break;
			}
		}
		
		private function complete_memory_test( $measurements, $history ) {
			$measurement_settings = get_option( 'wp_system_info_measurement_settings', array( 'nomeas' => 10, 'secrel' => 2000 ) );
			
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
				'test_count' => $measurement_settings['nomeas'],
				'interval_ms' => $measurement_settings['secrel'],
				'deactivated_plugins' => $deactivated_plugins
			);
			
			$history[] = $history_entry;
			// Keep only last 10 history entries
			if ( count( $history ) > 10 ) {
				$history = array_slice( $history, -10 );
			}
			update_option( 'wp_system_info_measurement_history', $history );
			
			// Clear the measurements
			delete_option( 'wp_system_info_measurements' );
			delete_option( 'wp_system_info_current_test_plugins' );
		}
		
		private function redirect_clean_url() {
			$url = remove_query_arg( array( 'test', 'count', 'nonce' ) );
			wp_redirect( $url );
			exit;
		}
		
		public function enqueue_admin_scripts( $hook ) {
		// Load on dashboard page and settings page
		if ( $hook !== 'index.php' && $hook !== 'toplevel_page_system-info-widget-settings' ) {
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
		
		// Enqueue memory measurement tool JS
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'assets/memory-measurement-tool.js' ) ) {
			wp_enqueue_script( 
				'tr-system-info-memory-tool-js', 
				plugin_dir_url( __FILE__ ) . 'assets/memory-measurement-tool.js', 
				array( 'jquery' ), 
				TRSYSTEMDASHBOARDVER, 
				true 
			);
			
			// Localize memory tool script
			wp_localize_script( 'tr-system-info-memory-tool-js', 'memoryToolAjax', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'memorySettingsNonce' => wp_create_nonce( 'memory_settings_nonce' )
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

	public function ajax_save_memory_settings() {
		check_ajax_referer( 'memory_settings_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}
		
		$nomeas = intval( sanitize_text_field( wp_unslash( $_POST['nomeas'] ?? '10' ) ) );
		$secrel = intval( sanitize_text_field( wp_unslash( $_POST['secrel'] ?? '2000' ) ) );
		
		if ($nomeas <= 0 || $nomeas > 100) {
			wp_send_json_error( array( 'message' => 'Invalid measurements value' ) );
		}
		
		if ($secrel < 500 || $secrel > 10000) {
			wp_send_json_error( array( 'message' => 'Invalid interval value' ) );
		}
		
		$settings = array( 'nomeas' => $nomeas, 'secrel' => $secrel );
		$updated = update_option( 'wp_system_info_measurement_settings', $settings );
		
		if ( $updated || get_option( 'wp_system_info_measurement_settings' ) == $settings ) {
			wp_send_json_success( array( 'message' => 'Settings saved!' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Save failed' ) );
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

		public function get_system_info() {
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
		
		// Get widget settings
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
		
		// Check if master widget toggle is disabled - if so, don't show the widget at all
		if ( !isset($settings['master_widget_toggle']) || !$settings['master_widget_toggle'] ) {
			?>
			<div class="wp-system-info-dashboard">
				<div style="padding: 20px; text-align: center; color: #666; background: #f9f9f9; border-radius: 8px;">
					<h4 style="margin: 0 0 10px 0; color: #333;">📊 System Info Dashboard Widget</h4>
					<p style="margin: 0;">This widget is currently disabled. Enable it in <a href="<?php echo esc_url( admin_url( 'admin.php?page=system-info-widget-settings' ) ); ?>">System Info Settings</a>.</p>
				</div>
			</div>
			<?php
			return;
		}
		
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
			
			// Load section files
			require_once plugin_dir_path( __FILE__ ) . 'sections/system-overview.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/memory-usage.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/live-memory-monitor.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/memory-measurement-tool.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/cron-memory-usage.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/plugin-memory-usage.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/database-information.php';
			require_once plugin_dir_path( __FILE__ ) . 'sections/php-configuration.php';
			
			// Make instance available globally for sections that need it
			global $wp_system_info_instance;
			$wp_system_info_instance = $this;
			
			?>

			<div class="wp-system-info-dashboard">
				
				<?php
				// Render all sections using the new modular approach
				render_system_overview_section($system_info, $settings);
				render_memory_usage_section($this->memory, $system_info, $settings);
				render_live_memory_monitor_section($settings);
				render_memory_measurement_tool_section($settings);
				render_cron_memory_usage_section($cron_logs, $settings);
				render_plugin_memory_usage_section($plugin_memory, $settings);
				render_database_information_section($system_info, $settings);
				render_php_configuration_section($system_info, $settings);
				?>
				
				<!-- Developer Credits -->
				<div class="wp-system-info-credits">
					<a href="#" target="_blank">System Info Dashboard Widget</a> &nbsp; | &nbsp; 
					Created by <a href="https://techreader.com" target="_blank">TechReader</a> &nbsp; | 
					&nbsp; Plugin Version: <?php echo esc_html( TRSYSTEMDASHBOARDVER ); ?>
				</div>

			</div>


			<?php
			}


		 
		public function add_dashboard_widget() {
		// Check if master widget toggle is disabled - if so, don't add the widget at all
		$settings = get_option( 'wp_system_info_widget_settings', array( 'master_widget_toggle' => 1 ) );
		if ( !isset($settings['master_widget_toggle']) || !$settings['master_widget_toggle'] ) {
		return; // Don't add the widget if it's disabled
		}
		
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
			$this->check_memory_limits();
			$this->check_memory_usage();
			$content .= ' | Memory: ' . $this->memory['usage'] . 'MB/' . ($this->memory["wpmb"] ?? '0') . ($this->memory["wpunity"] ?? 'MB') . ' (' . ($this->memory['percent'] ?? '0') . '%)';
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
