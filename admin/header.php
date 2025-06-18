<?php
/**
 * Header Donate Section Template
 * 
 * @package TR_System_Info_Dashboard_Widget
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Fix deprecated strip_tags warning
if (!empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST']));
}
if (!empty($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = esc_url_raw(wp_unslash($_SERVER['REQUEST_URI']));
}

$plugin_data = get_plugin_data(TR_SYSTEM_INFO_FILE);
?>
<!-- Header Donate Section -->
<div class="tr-donate-section-compact">
    <div class="tr-donate-compact-content">
        <a href="https://techreader.com/donate" target="_blank" class="tr-compact-donate-btn">Buy me a coffee!</a>
        <span class="tr-donate-icon">☕</span>
        <div class="tr-donate-text">
            <strong>Enjoying this plugin?</strong> Support development with a coffee!
        </div>
        <div><?php echo esc_html($plugin_data['Name']); ?>: <strong><?php echo esc_html($plugin_data['Version']); ?></strong> by <a href="https://techreader.com" target="_blank">TechReader</a></div>
    </div>
</div>
