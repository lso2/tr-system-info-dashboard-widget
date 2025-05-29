# Changelog

## [1.4.3]

### Fixed
- Memory test cancel functionality now properly stops the automatic reload timer
- Cancel button now prevents the test from continuing to restart
- Fixed issue where cancel would only reset counter but test would continue

### Enhanced
- Added visual feedback when canceling (progress popup shows "Cancelled")
- Improved cancel button behavior with proper timer cleanup
- Cancel now shows confirmation dialog and visual state change
- Better user experience with immediate visual feedback

### Technical
- Memory test timer now stored in global variable for proper cancellation
- Added cancelMemoryTest() function for proper cleanup
- Improved JavaScript error handling for cancel operations

## [1.4.2]

### Fixed
- Memory test cancel functionality now works properly
- Live memory monitor chart display restored with inline JavaScript
- Cancel button styling improved (grey instead of red on blue)
- Script loading optimization to only load on dashboard page

### Enhanced
- Added ✕ symbol to cancel buttons for better UX
- Memory test progress popup includes functional cancel option
- Improved error handling in memory monitoring

### Changed
- Cancel button styling: grey border and text instead of red
- Added confirmation dialog when canceling memory tests
- Script enqueuing optimized for dashboard page only

## [1.4.0]

### Fixed
- CSS stylesheet not loading properly in admin dashboard
- Admin styles now properly enqueued with versioning
- JavaScript variables not being passed to external script file
- Inline JavaScript code duplication and maintenance issues

### Enhanced
- Added proper asset enqueuing for both CSS and JS files
- Improved admin script loading with file existence checks
- Added script localization with AJAX URLs and nonce values
- Separated inline JavaScript into external admin.js file
- Better code organization and maintainability

### Updated
- Version number consistency throughout plugin files
- Removed redundant inline JavaScript from dashboard output
- Improved asset loading with proper WordPress hooks

## [1.3.7]

### Fixed
- WordPress Plugin Check compliance issues
- Server variable sanitization

### Enhanced
- Security improvements and input validation

## [1.3.6]

### Fixed
- Plugin Check tool sanitization requirements

### Enhanced
- WordPress coding standards compliance

## [1.3.5]

### Fixed
- Plugin Check compliance issues

### Enhanced
- Input validation and output escaping

## [1.3.4]

### Fixed
- Security improvements and input validation

### Enhanced
- WordPress Plugin Check compliance

## [1.3.3]

### Fixed
- WordPress Plugin Check compliance
- Deprecated function replacements
- Security improvements

### Updated
- WordPress 6.8 compatibility

## [1.3.0]

### Enhanced
- Smart memory usage display
- User interface organization

### Added
- Latest memory test results

## [1.2.8]

### Fixed
- Plugin grid display (4 columns)

### Added
- Top memory usage plugins section

### Improved
- Plugin styling

## [1.2.6]

### Fixed
- Active plugins display layout

### Enhanced
- Database table styling

### Improved
- Memory test functionality

## [1.2.5]

### Fixed
- Memory test issues and reset functionality

### Enhanced
- Toggle links state indication

### Added
- Clear history functionality

## [1.2.4]

### Added
- Plugin memory tracking system
- Test history with plugin impact

### Enhanced
- Memory measurement tools

## [1.2.0]

### Major Changes
- Plugin rebranding and enhancement

### Added
- Advanced OS and server detection

### Improved
- Memory measurement tools

## [1.1.0]

### Major Changes
- Complete plugin rewrite

### Added
- Live memory monitoring
- Database analysis and interactive dashboard

## [1.0.0]

### Added
- Initial release
