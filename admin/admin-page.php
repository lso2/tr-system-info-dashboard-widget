<?php
/**
 * Admin Settings Page Template
 * 
 * @package TR_System_Info_Dashboard_Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

include TR_SYSTEM_INFO_PATH . 'admin/header.php';
?>

<!-- Admin Settings Content -->
<div class="wrap">
    <h1>System Info Widget Settings</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('tr_system_info_settings', 'tr_system_info_nonce'); ?>
        
        <div class="tr-admin-grid">
            
            <!-- System Overview Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>System Overview</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_system_overview" <?php checked($this->settings['show_system_overview']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show System Overview Section
                    </label>
                    <p class="description">Display basic system information and server status.</p>
                </div>
            </div>
            
            <!-- Server Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Server Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_server_info" <?php checked($this->settings['show_server_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Server Information Section
                    </label>
                    <p class="description">Display web server details, PHP version, and configuration.</p>
                </div>
            </div>
            
            <!-- Database Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Database Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_database_info" <?php checked($this->settings['show_database_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Database Information Section
                    </label>
                    <p class="description">Display MySQL/MariaDB version, database size, and table information.</p>
                </div>
            </div>
            
            <!-- WordPress Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>WordPress Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_wordpress_info" <?php checked($this->settings['show_wordpress_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show WordPress Information Section
                    </label>
                    <p class="description">Display WordPress version, constants, and core settings.</p>
                </div>
            </div>
            
            <!-- Theme & Plugin Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Theme & Plugin Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_theme_plugin_info" <?php checked($this->settings['show_theme_plugin_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Theme & Plugin Information Section
                    </label>
                    <p class="description">Display active theme, installed plugins, and their versions.</p>
                </div>
            </div>
            
            <!-- Security Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Security Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_security_info" <?php checked($this->settings['show_security_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Security Information Section
                    </label>
                    <p class="description">Display security-related settings and recommendations.</p>
                </div>
            </div>
            
            <!-- Performance Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Performance Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_performance_info" <?php checked($this->settings['show_performance_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Performance Information Section
                    </label>
                    <p class="description">Display memory usage, caching status, and performance metrics.</p>
                </div>
            </div>
            
            <!-- Logs Information Card -->
            <div class="tr-admin-card">
                <div class="tr-admin-card-header">
                    <h3>Logs Information</h3>
                </div>
                <div class="tr-admin-card-body">
                    <label class="tr-toggle-switch">
                        <input type="checkbox" name="show_logs_info" <?php checked($this->settings['show_logs_info']); ?>>
                        <span class="tr-toggle-slider"></span>
                        Show Logs Information Section
                    </label>
                    <p class="description">Display recent error logs and debug information.</p>
                </div>
            </div>
            
        </div>
        
        <?php submit_button('Save Settings', 'primary', 'submit'); ?>
    </form>
</div>

<?php include TR_SYSTEM_INFO_PATH . 'admin/footer.php'; ?>
