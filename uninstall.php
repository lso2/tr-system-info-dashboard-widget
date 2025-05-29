<?php
/**
 * WP System Info Uninstall Script
 * 
 * This file is executed when the plugin is uninstalled.
 * It removes all plugin data from the database.
 */

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

// Remove all plugin options
wp_system_info_cleanup_data();

function wp_system_info_cleanup_data() {
    // Remove measurement data
    delete_option( 'wp_system_info_measurements' );
    delete_option( 'wp_system_info_measurement_settings' );
    
    // Remove cron monitoring data
    delete_option( 'wp_system_info_cron_logs' );
    delete_option( 'wp_system_info_cron_start_memory' );
    delete_option( 'wp_system_info_cron_start_time' );
    
    // Remove plugin memory tracking data
    delete_option( 'wp_system_info_plugin_memory' );
    
    // Remove any legacy options from the old plugin
    delete_option( 'wpmemoryusage_emopt' );
    delete_option( 'wpmemoryusage_settings' );
    delete_option( 'wpmemoryusage_cron_start_memory' );
    delete_option( 'cron_memory_usage_logs' );
    delete_option( 'plugin_memory_usage' );
    
    // Clean up any transients
    delete_transient( 'wp_system_info_cache' );
    
    // For multisite installations, clean up network-wide options
    if ( is_multisite() ) {
        delete_site_option( 'wp_system_info_network_data' );
    }
}

// Log uninstallation (optional, for debugging purposes)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    // Plugin uninstalled and data cleaned up - logged via WordPress debug
}
