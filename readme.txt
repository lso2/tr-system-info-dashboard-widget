=== System Info Dashboard Widget ===
Contributors: techreader
Donate link: https://techreader.com/donate
Tags: system info, memory monitor, performance, dashboard, admin
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.5
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Comprehensive WordPress system information plugin with real-time memory monitoring, plugin analysis, and performance insights in your dashboard.

== Description ==

**System Info Dashboard Widget** is a powerful system monitoring plugin that provides comprehensive insights into your WordPress installation's performance, memory usage, and system health - all from a convenient dashboard widget.

= 🚀 Key Features =

**Real-Time Monitoring**
* Live memory usage graph with dynamic scaling
* Real-time system performance metrics
* Automatic updates every 3 seconds

**Advanced Plugin Analysis**
* Individual plugin memory footprint tracking
* Smart alerts for high memory usage plugins
* Plugin activation/deactivation impact analysis

**Comprehensive System Information**
* WordPress, PHP, MySQL version details
* Server software and OS distribution detection
* SSL status, debug mode, and security indicators

**Database Insights**
* Complete database size breakdown
* Table-level analysis with row counts
* Database optimization recommendations

**Memory Testing Tools**
* Multi-point memory measurement system
* Historical test data with plugin impact tracking
* Configurable test intervals and measurement counts

**Performance Monitoring**
* Cron job memory usage and execution time tracking
* Background process performance analysis
* System resource utilization metrics

= 🎯 Perfect For =

* **Developers** - Debug memory issues and optimize plugin performance
* **Site Administrators** - Monitor system health and resource usage
* **Performance Enthusiasts** - Identify optimization opportunities
* **Troubleshooters** - Diagnose site performance problems

= 🔧 Advanced Features =

* **Intelligent Alerts**: Smart notifications when plugins exceed memory thresholds
* **Historical Analysis**: Track memory usage patterns over time
* **Interactive Dashboard**: Collapsible sections with modern, responsive design
* **Mobile Friendly**: Works perfectly on all device sizes
* **Zero Configuration**: Works out of the box with sensible defaults

= 💡 Use Cases =

1. **Plugin Testing**: Compare memory usage before and after plugin installations
2. **Performance Optimization**: Identify resource-heavy plugins and themes
3. **System Monitoring**: Keep track of server health and WordPress environment
4. **Troubleshooting**: Quickly diagnose memory-related issues
5. **Development**: Monitor application performance during development

= 🛡️ Security & Privacy =

* No external connections or data transmission
* All data stored locally in WordPress database
* Secure AJAX requests with proper nonce verification
* Follows WordPress security best practices

== Installation ==

= Automatic Installation =
1. Go to Plugins > Add New in your WordPress admin
2. Search for "System Info Dashboard Widget"
3. Click Install Now and then Activate

= Manual Installation =
1. Upload the plugin files to `/wp-content/plugins/tr-system-info-dashboard-widget/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to your Dashboard to see the new System Information widget

= Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* MySQL 5.6 or higher
* 64MB memory minimum (128MB recommended)

== Frequently Asked Questions ==

= Does this plugin slow down my website? =
No! The plugin is designed to have minimal impact on your site's performance. It only loads in the admin dashboard and uses efficient caching mechanisms.

= Will this work with my hosting provider? =
Yes! The plugin works on shared hosting, VPS, dedicated servers, and managed WordPress hosting. It automatically adapts to different server configurations.

= Can I use this on multisite installations? =
Absolutely! The plugin is fully compatible with WordPress multisite networks.

= Does this plugin collect any personal data? =
No, the plugin only monitors your server's system information locally. No data is transmitted externally.

= How do I interpret the memory usage data? =
The plugin shows memory usage in MB. Values over 50MB per plugin may indicate optimization opportunities. The live graph helps visualize memory patterns over time.

= Can I export the system information? =
Currently, the data is displayed in the dashboard widget. Export functionality is planned for future versions.

= What should I do if I find a plugin using too much memory? =
First, check if the plugin is essential. Consider alternatives or contact the plugin developer. You can also test memory usage before/after deactivating plugins.

= Is this plugin compatible with caching plugins? =
Yes! The plugin works alongside caching solutions and can actually help you monitor their effectiveness.

== Screenshots ==

1. **Dashboard Overview** - Main system information widget showing comprehensive system details
2. **Live Memory Monitor** - Real-time memory usage graph with dynamic scaling
3. **Plugin Memory Analysis** - Individual plugin memory usage tracking and top consumers
4. **Database Information** - Complete database breakdown with table-level details
5. **Memory Testing Tool** - Advanced memory measurement system with historical data
6. **System Diagnostics** - Detailed server and PHP configuration information

== Changelog ==

= 2.0.5 =
* Enhanced: Memory test history now includes test configuration details (number of tests and interval)
* Enhanced: Latest test result display shows test settings for better context
* Enhanced: Historical test entries now display how each test was configured
* Improved: Memory test analysis with complete test parameter tracking
* Technical: Added test_count and interval_ms fields to test history entries
* UI/UX: Better test result interpretation with configuration context

= 2.0.4 =
* Fixed: Settings page card hover jumping issue when hovering over popup elements
* Fixed: Memory test popup positioning conflicts with card hover effects
* Enhanced: Stable popup positioning that doesn't interfere with page layout
* Technical: Improved CSS specificity for popup elements within cards
* Technical: Added CSS containment rules to prevent layout recalculation issues
* UI/UX: Smooth card interactions without popup interference

= 2.0.3 =
* Fixed: Cancel button functionality in memory test popup - now works properly
* Fixed: Popup button interaction issues caused by CSS pointer-events conflicts
* Enhanced: Memory test cancellation with proper confirmation dialogs
* Enhanced: Popup button hover effects now work as expected
* Technical: Resolved CSS specificity issues with fixed positioning and pointer events
* Technical: Improved button targeting for popup vs inline cancel buttons
* UI/UX: Consistent button behavior across all memory test interfaces

= 2.0.2 =
* Fixed: Memory test functionality now works on both dashboard and plugin settings pages
* Fixed: Undefined array key warnings in admin footer for 'wpmb' and 'wpunity'
* Fixed: WordPress security compliance - properly escaped wp_create_nonce output
* Enhanced: Centralized memory test logic for consistent behavior across admin pages
* Enhanced: Better error handling with fallback values for memory limit detection
* Technical: Improved nonce handling and URL parameter management for memory tests
* Technical: Removed debugging code for clean production deployment
* Security: Enhanced output escaping for JavaScript embedded nonce values

= 2.0.1 =
* Major: Complete admin settings page redesign with modern card-based layout
* Added: Dashboard widget sections preview - shows actual widget content in admin settings
* Fixed: Migrated memory test functionality globally to it works on settings page and dashboard
* Fixed: OS/Hostname/Server section styling to match original dashboard appearance
* Enhanced: Plugin check compliance - fixed all escaping and sanitization 
* Technical: Improved variable sanitization with proper validation
* Technical: Enhanced security with proper output escaping throughout admin interface
* Technical: Fixed direct database query caching warnings

= 1.9.0 =
* Added: Master "Toggle Dashboard Widget" control - toggles all sections and widget visibility
* Added: Instant AJAX saving - settings save automatically when toggled
* Fixed: Toggle Dashboard Widget state persistence - properly reflects enabled/disabled on reload
* Fixed: Toggle Dashboard Widget styling - removed dark green border, uses same colors as other cards
* Fixed: Toggle Dashboard Widget master control - now hides entire widget when disabled
* Fixed: WP Limit/PHP Limit section styling with proper min-width and formatting
* Changed: Menu title from "System Info Widget" to "System Info"

= 1.7.0 =
* Added: Click-to-toggle cards - entire card clickable with status tags (no complex toggle switches)
* Added: Popup notifications with specific enable/disable messages
* Added: Widget preview sections showing real system data in grid layout
* Added: Header and footer integration with donation support
* Enhanced: 6-card layout with combined Live Memory Monitor + Cron + Plugin Memory section
* Enhanced: No more form submissions - everything happens in real-time
* Technical: Uses actual section module files - no code duplication
* UI/UX: Complete visual overhaul with focus on user experience
* UI/UX: Widget sections display identically to dashboard in beautiful grid format

= 1.6.0 =
* Enhanced: Card-based design with hover effects and professional styling
* Enhanced: Responsive design that works perfectly on mobile devices
* Enhanced: Status tags in top-right corners (green "ENABLED", grey "DISABLED")
* Enhanced: Loading states and success indicators for better user feedback
* Enhanced: Accessibility improvements with keyboard navigation support
* Fixed: All checkmarks removed from interface - clean design without visual clutter
* Fixed: Donate button styling - consistent orange color throughout
* Technical: Separated all JavaScript and CSS from PHP files for better maintainability
* Technical: Added proper asset enqueuing with dashboard CSS/JS for identical widget appearance
* UI/UX: Clean, modern design that matches WordPress admin styling
* UI/UX: Menu moved to top-level position at bottom of admin menu

= 1.5.0 =
* Fixed: Memory usage section - added all missing memory data fields (wpmaxmb, phplimit, etc.)
* Fixed: Live Memory Monitor - now displays functional real-time chart
* Fixed: Memory Measurement Tool - fixed "Show History" toggle and "Start Memory Test" functionality
* Fixed: Database Information - added missing theme_name and theme_version data
* Technical: Improved AJAX error handling and user feedback
* Technical: Enhanced security with nonce verification and proper sanitization

= 1.4.3 =
* Fixed: Memory test cancel functionality properly stops the automatic reload timer
* Fixed: Cancel button prevents the test from continuing to restart
* Enhanced: Added visual feedback when canceling (progress popup shows "Cancelled")
* Enhanced: Improved cancel button behavior with proper timer cleanup
* Enhanced: Cancel shows confirmation dialog and visual state change
* Technical: Memory test timer stored in global variable for proper cancellation

= 1.4.2 =
* Fixed: Live memory monitor chart displays properly without errors
* Fixed: Memory test cancel functionality now works correctly
* Enhanced: Simplified memory chart rendering for better performance
* Fixed: JavaScript chart drawing now works with current page structure

= 1.4.1 =
* Added: Cancel button for memory measurement test in progress
* Added: User confirmation dialog when canceling memory test
* Added: Proper cleanup of test data when canceled
* Enhanced: Memory test user experience with ability to stop running tests
* Enhanced: Red styled cancel button for clear visual distinction
* Enhanced: Confirmation prompt to prevent accidental cancellation
* Fixed: Memory test sessions can now be properly terminated by user
* Fixed: Test data cleanup when user cancels measurement process

= 1.4.0 =
* Fixed: CSS stylesheet not loading properly in admin dashboard
* Fixed: JavaScript variables not being passed to external script file
* Fixed: Inline JavaScript code duplication and maintenance issues
* Enhanced: Added proper asset enqueuing for both CSS and JS files
* Enhanced: Improved admin script loading with file existence checks
* Enhanced: Added script localization with AJAX URLs and nonce values
* Enhanced: Separated inline JavaScript into external admin.js file
* Enhanced: Better code organization and maintainability
* Updated: Version number consistency throughout plugin files
* Updated: Removed redundant inline JavaScript from dashboard output
* Updated: Improved asset loading with proper WordPress hooks

= 1.3.7 =
* Fixed: WordPress Plugin Check compliance issues
* Enhanced: Security improvements and input validation
* Fixed: Server variable sanitization

= 1.3.6 =
* Fixed: Plugin Check tool sanitization requirements
* Enhanced: WordPress coding standards compliance

= 1.3.5 =
* Fixed: Plugin Check compliance issues
* Enhanced: Input validation and output escaping

= 1.3.4 =
* Fixed: Security improvements and input validation
* Enhanced: WordPress Plugin Check compliance

= 1.3.3 =
* Fixed: WordPress Plugin Check compliance
* Fixed: Deprecated function replacements
* Fixed: Security improvements
* Updated: WordPress 6.8 compatibility

= 1.3.0 =
* Enhanced: Smart memory usage display
* Added: Latest memory test results
* Improved: User interface organization

= 1.2.8 =
* Fixed: Plugin grid display (4 columns)
* Added: Top memory usage plugins section
* Improved: Plugin styling

= 1.2.6 =
* Fixed: Active plugins display layout
* Enhanced: Database table styling
* Improved: Memory test functionality

= 1.2.5 =
* Fixed: Memory test issues and reset functionality
* Enhanced: Toggle links state indication
* Added: Clear history functionality

= 1.2.4 =
* Added: Plugin memory tracking system
* Enhanced: Memory measurement tools
* Added: Test history with plugin impact

= 1.2.0 =
* Major: Plugin rebranding and enhancement
* Added: Advanced OS and server detection
* Improved: Memory measurement tools

= 1.1.0 =
* Major: Complete plugin rewrite
* Added: Live memory monitoring
* Added: Database analysis and interactive dashboard

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 2.0.5 =
Enhanced memory test history with complete test configuration details for better analysis and context.

= 2.0.4 =
Fixed settings page card hover jumping issues and improved popup positioning stability.

= 2.0.3 =
Fixed cancel button functionality in memory test popup and resolved button interaction issues.

= 2.0.2 =
Fixed memory test functionality across all admin pages, resolved PHP warnings, and enhanced security compliance.

= 1.9.7 =
Critical fixes for PHP warnings, improved Toggle Dashboard Widget functionality, enhanced security compliance, and better styling consistency.

= 1.7.0 =
Major admin interface redesign with dashboard widget preview sections, master toggle control, and modern card-based layout with instant AJAX saving.

= 1.4.2 =
Fixed live memory monitor display and improved cancel functionality.

= 1.4.1 =
Added cancel button for memory tests - users can now stop running tests.

= 1.4.0 =
Fixed CSS loading issue - dashboard styling now works properly.

= 1.3.7 =
WordPress Plugin Check compliance complete.

= 1.3.6 =
Plugin Check tool compliance improvements.

= 1.3.5 =
Security fixes and compliance updates.

= 1.3.4 =
Security improvements and input validation.

= 1.3.3 =
Major security update and WordPress 6.8 compatibility.

= 1.3.0 =
Improved user interface and memory display.

= 1.2.8 =
Plugin display fixes and top memory usage section.

= 1.2.4 =
New plugin memory tracking system.

== Support ==

For support, please visit:

* [Plugin Support Forum](https://wordpress.org/support/plugin/tr-system-info-dashboard-widget/)
* [GitHub Repository](https://github.com/techreader/tr-system-info-dashboard-widget)
* [TechReader Website](https://techreader.com/)

== Contributing ==

We welcome contributions! Please visit our [GitHub repository](https://github.com/lso2/tr-system-info-dashboard-widget) to:

* Report bugs
* Suggest new features
* Submit pull requests
* View source code

== Privacy Policy ==

This plugin does not:
* Collect personal information
* Track user behavior
* Make external HTTP requests
* Store data outside your WordPress installation

All system information is processed and stored locally on your server.

== Credits ==

Developed with ❤️ by **TechReader** for the WordPress community.

Special thanks to all contributors and users who provide feedback to make this plugin better.
