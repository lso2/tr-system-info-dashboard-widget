# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.5] - 2025-06-18

### Added
- Test configuration details in memory test history (number of tests and interval)
- Test settings display in latest test result for better context
- Complete test parameter tracking in historical entries

### Enhanced
- Memory test analysis now includes test configuration context
- Better test result interpretation with configuration details

### Technical
- Added `test_count` and `interval_ms` fields to test history entries

## [2.0.4] - 2025-06-18

### Fixed
- Settings page card hover jumping issue when hovering over popup elements
- Memory test popup positioning conflicts with card hover effects

### Enhanced
- Stable popup positioning that doesn't interfere with page layout
- Smooth card interactions without popup interference

### Technical
- Improved CSS specificity for popup elements within cards
- Added CSS containment rules to prevent layout recalculation issues

## [2.0.3] - 2025-06-18

### Fixed
- Cancel button functionality in memory test popup now works properly
- Popup button interaction issues caused by CSS pointer-events conflicts

### Enhanced
- Memory test cancellation with proper confirmation dialogs
- Popup button hover effects now work as expected
- Consistent button behavior across all memory test interfaces

### Technical
- Resolved CSS specificity issues with fixed positioning and pointer events
- Improved button targeting for popup vs inline cancel buttons

## [2.0.2] - 2025-06-18

### Fixed
- Memory test functionality now works on both dashboard and plugin settings pages
- Undefined array key warnings in admin footer for 'wpmb' and 'wpunity'
- WordPress security compliance - properly escaped wp_create_nonce output

### Enhanced
- Centralized memory test logic for consistent behavior across admin pages
- Better error handling with fallback values for memory limit detection

### Security
- Enhanced output escaping for JavaScript embedded nonce values

### Technical
- Improved nonce handling and URL parameter management for memory tests
- Removed debugging code for clean production deployment

## [2.0.1] 2025-06-17

### Added
- Dashboard widget sections preview - shows actual widget content in admin settings
- Complete admin settings page redesign with modern card-based layout

### Fixed
- Migrated memory test functionality globally so it works on settings page and dashboard
- OS/Hostname/Server section styling to match original dashboard appearance
- Plugin check compliance - fixed all escaping and sanitization

### Enhanced
- Variable sanitization with proper validation
- Security with proper output escaping throughout admin interface

### Technical
- Fixed direct database query caching warnings

## [1.9.0] - 2025-06-16

### Added
- Master "Toggle Dashboard Widget" control - toggles all sections and widget visibility
- Instant AJAX saving - settings save automatically when toggled

### Fixed
- Toggle Dashboard Widget state persistence - properly reflects enabled/disabled on reload
- Toggle Dashboard Widget styling - removed dark green border, uses same colors as other cards
- Toggle Dashboard Widget master control - now hides entire widget when disabled
- WP Limit/PHP Limit section styling with proper min-width and formatting

### Changed
- Menu title from "System Info Widget" to "System Info"

## [1.7.0] - 2025-06-15

### Added
- Click-to-toggle cards - entire card clickable with status tags
- Popup notifications with specific enable/disable messages
- Widget preview sections showing real system data in grid layout
- Header and footer integration with donation support
- 6-card layout with combined Live Memory Monitor + Cron + Plugin Memory section

### Enhanced
- No more form submissions - everything happens in real-time
- Complete visual overhaul with focus on user experience
- Widget sections display identically to dashboard in beautiful grid format

### Technical
- Uses actual section module files - no code duplication

## [1.6.0] 2025-06-14

### Added
- Card-based design with hover effects and professional styling
- Responsive design that works perfectly on mobile devices
- Status tags in top-right corners (green "ENABLED", grey "DISABLED")
- Loading states and success indicators for better user feedback
- Accessibility improvements with keyboard navigation support

### Fixed
- All checkmarks removed from interface - clean design without visual clutter
- Donate button styling - consistent orange color throughout

### Enhanced
- Clean, modern design that matches WordPress admin styling
- Menu moved to top-level position at bottom of admin menu

### Technical
- Separated all JavaScript and CSS from PHP files for better maintainability
- Added proper asset enqueuing with dashboard CSS/JS for identical widget appearance

## [1.5.0] 2025-06-12

### Added
- Missing memory data fields (wpmaxmb, phplimit, etc.) in memory usage section
- Missing theme_name and theme_version data in database information

### Fixed
- Live Memory Monitor - now displays functional real-time chart
- Memory Measurement Tool - fixed "Show History" toggle and "Start Memory Test" functionality

### Enhanced
- AJAX error handling and user feedback
- Security with nonce verification and proper sanitization

## [1.4.3] 2025-06-11

### Added
- Visual feedback when canceling (progress popup shows "Cancelled")
- Confirmation dialog and visual state change for cancel operation

### Fixed
- Memory test cancel functionality properly stops the automatic reload timer
- Cancel button prevents the test from continuing to restart

### Enhanced
- Cancel button behavior with proper timer cleanup

### Technical
- Memory test timer stored in global variable for proper cancellation

## [1.4.2] 2025-06-10

### Fixed
- Live memory monitor chart displays properly without errors
- Memory test cancel functionality now works correctly
- JavaScript chart drawing now works with current page structure

### Enhanced
- Simplified memory chart rendering for better performance

## [1.4.1] 2025-06-09

### Added
- Cancel button for memory measurement test in progress
- User confirmation dialog when canceling memory test
- Proper cleanup of test data when canceled
- Red styled cancel button for clear visual distinction
- Confirmation prompt to prevent accidental cancellation

### Enhanced
- Memory test user experience with ability to stop running tests

### Fixed
- Memory test sessions can now be properly terminated by user
- Test data cleanup when user cancels measurement process

## [1.4.0] 2025-06-09

### Added
- Proper asset enqueuing for both CSS and JS files
- Script localization with AJAX URLs and nonce values
- External admin.js file for better code organization

### Fixed
- CSS stylesheet not loading properly in admin dashboard
- JavaScript variables not being passed to external script file
- Inline JavaScript code duplication and maintenance issues

### Enhanced
- Admin script loading with file existence checks
- Better code organization and maintainability
- Asset loading with proper WordPress hooks

### Changed
- Version number consistency throughout plugin files
- Removed redundant inline JavaScript from dashboard output

## [1.3.7] - 2025-06-09

### Fixed
- WordPress Plugin Check compliance issues
- Server variable sanitization

### Enhanced
- Security improvements and input validation

## [1.3.6] - 2025-06-09

### Fixed
- Plugin Check tool sanitization requirements

### Enhanced
- WordPress coding standards compliance

## [1.3.5] - 2025-06-08

### Fixed
- Plugin Check compliance issues

### Enhanced
- Input validation and output escaping

## [1.3.4] - 2025-06-07

### Fixed
- Security improvements and input validation

### Enhanced
- WordPress Plugin Check compliance

## [1.3.3] - 2025-06-06

### Added
- WordPress 6.8 compatibility

### Fixed
- WordPress Plugin Check compliance
- Deprecated function replacements
- Security improvements

## [1.3.0] - 2025-06-05

### Added
- Latest memory test results display

### Enhanced
- Smart memory usage display

### Changed
- User interface organization

## [1.2.8] - 2025-06-04

### Added
- Top memory usage plugins section

### Fixed
- Plugin grid display (4 columns)

### Enhanced
- Plugin styling

## [1.2.6] - 2025-06-03

### Fixed
- Active plugins display layout

### Enhanced
- Database table styling
- Memory test functionality

## [1.2.5] - 2025-06-02

### Added
- Clear history functionality

### Fixed
- Memory test issues and reset functionality

### Enhanced
- Toggle links state indication

## [1.2.4] - 2025-06-01

### Added
- Plugin memory tracking system
- Test history with plugin impact

### Enhanced
- Memory measurement tools

## [1.2.0] - 2025-05-31

### Added
- Advanced OS and server detection

### Changed
- Plugin rebranding and enhancement

### Enhanced
- Memory measurement tools

## [1.1.0] - 2025-05-31

### Added
- Live memory monitoring
- Database analysis and interactive dashboard

### Changed
- Complete plugin rewrite

## [1.0.0] - 2025-05-31

### Added
- Initial release
- Basic system information display
- WordPress dashboard widget
- Memory usage monitoring
- Database information display
- PHP configuration details

[Unreleased]: https://github.com/lso2/tr-system-info-dashboard-widget/compare/v2.0.5...HEAD
[2.0.5]: https://github.com/lso2/tr-system-info-dashboard-widget/compare/v1.4.3...v2.0.5
[1.4.3]: https://github.com/lso2/tr-system-info-dashboard-widget/compare/v1.0.0...v1.4.3
