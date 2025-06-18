// TechReader System Info Dashboard Widget - Admin Settings JavaScript

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize the settings page
    initializeSettingsPage();
    
    function initializeSettingsPage() {
        // Add event listeners for card clicks
        $('.tr-setting-card').on('click', handleCardClick);
        
        // Initialize card states based on current settings
        updateCardStates();
    }
    
    function handleMasterToggle($masterCard, newState) {
        const $masterToggle = $masterCard.find('input[type="checkbox"]');
        
        // Update master toggle state
        $masterToggle.prop('checked', newState);
        
        // Update all other toggles to match master state
        $('.tr-setting-card').not('.tr-master-toggle').each(function() {
            const $card = $(this);
            const $toggle = $card.find('input[type="checkbox"]');
            $toggle.prop('checked', newState);
            updateCardState($card, newState);
        });
        
        // Update master card state
        updateCardState($masterCard, newState);
        
        // Add loading state to master card
        $masterCard.addClass('saving');
        
        // Prepare form data with all settings
        const formData = {
            action: 'save_system_info_settings',
            nonce: systemInfoSettings.nonce,
            master_widget_toggle: newState ? '1' : '0'
        };
        
        // Set all section toggles to the master state
        $('.tr-setting-card input[type="checkbox"]').not('[name="master_widget_toggle"]').each(function() {
            const name = $(this).attr('name');
            formData[name] = newState ? '1' : '0';
        });
        
        // Send AJAX request
        $.ajax({
            url: systemInfoSettings.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                $masterCard.removeClass('saving');
                if (response.success) {
                    $masterCard.addClass('success');
                    const action = newState ? 'enabled' : 'disabled';
                    const message = `Dashboard Widget ${action} successfully!`;
                    showNotification(message, 'success');
                    
                    setTimeout(function() {
                        $masterCard.removeClass('success');
                    }, 2000);
                } else {
                    showNotification('Failed to save settings', 'error');
                    // Revert all states on error
                    $masterToggle.prop('checked', !newState);
                    updateCardState($masterCard, !newState);
                    $('.tr-setting-card').not('.tr-master-toggle').each(function() {
                        const $card = $(this);
                        const $toggle = $card.find('input[type="checkbox"]');
                        $toggle.prop('checked', !newState);
                        updateCardState($card, !newState);
                    });
                }
            },
            error: function() {
                $masterCard.removeClass('saving');
                showNotification('Network error occurred', 'error');
                // Revert all states on error
                $masterToggle.prop('checked', !newState);
                updateCardState($masterCard, !newState);
                $('.tr-setting-card').not('.tr-master-toggle').each(function() {
                    const $card = $(this);
                    const $toggle = $card.find('input[type="checkbox"]');
                    $toggle.prop('checked', !newState);
                    updateCardState($card, !newState);
                });
            }
        });
    }
    
    function handleCardClick(event) {
        const $card = $(event.currentTarget);
        const $toggle = $card.find('input[type="checkbox"]');
        const settingName = $toggle.attr('name');
        const currentlyEnabled = $toggle.is(':checked');
        const newState = !currentlyEnabled;
        
        // Handle master toggle differently
        if (settingName === 'master_widget_toggle') {
            handleMasterToggle($card, newState);
            return;
        }
        
        // Update toggle state
        $toggle.prop('checked', newState);
        
        // Add loading state to card
        $card.addClass('saving');
        
        // Prepare form data
        const formData = {
            action: 'save_system_info_settings',
            nonce: systemInfoSettings.nonce
        };
        
        // Get all current toggle states
        $('.tr-setting-card input[type="checkbox"]').each(function() {
            const name = $(this).attr('name');
            const value = $(this).is(':checked') ? '1' : '0';
            formData[name] = value;
        });
        
        // Send AJAX request
        $.ajax({
            url: systemInfoSettings.ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                handleSaveSuccess(response, $card, settingName, newState);
            },
            error: function(xhr, status, error) {
                handleSaveError(error, $card, $toggle, currentlyEnabled);
            }
        });
    }
    
    function handleSaveSuccess(response, $card, settingName, isEnabled) {
        $card.removeClass('saving');
        
        if (response.success) {
            // Add success state
            $card.addClass('success');
            
            // Update card state and status tag
            updateCardState($card, isEnabled);
            
            // Get section name for notification
            const sectionTitle = $card.find('.tr-setting-info h3').text();
            const action = isEnabled ? 'enabled' : 'disabled';
            const message = `${sectionTitle} ${action} successfully!`;
            
            // Show notification popup
            showNotification(message, 'success');
            
            // Remove success state after animation
            setTimeout(function() {
                $card.removeClass('success');
            }, 2000);
            
        } else {
            // Handle error
            const errorMessage = response.data && response.data.message 
                ? response.data.message 
                : 'Failed to save settings';
            showNotification(errorMessage, 'error');
            
            // Revert toggle state
            const $toggle = $card.find('input[type="checkbox"]');
            $toggle.prop('checked', !$toggle.is(':checked'));
            updateCardState($card, !isEnabled);
        }
    }
    
    function handleSaveError(error, $card, $toggle, originalState) {
        $card.removeClass('saving');
        
        // Show error notification
        showNotification('Network error occurred while saving settings', 'error');
        
        // Revert toggle state
        $toggle.prop('checked', originalState);
        updateCardState($card, originalState);
        
        console.error('AJAX Error:', error);
    }
    
    function updateCardStates() {
        $('.tr-setting-card').each(function() {
            const $card = $(this);
            const $toggle = $card.find('input[type="checkbox"]');
            const isEnabled = $toggle.is(':checked');
            
            updateCardState($card, isEnabled);
        });
    }
    
    function updateCardState($card, isEnabled) {
        const $statusTag = $card.find('.tr-status-tag');
        
        if (isEnabled) {
            $card.addClass('active');
            $statusTag.removeClass('disabled').addClass('enabled').text('Enabled');
        } else {
            $card.removeClass('active');
            $statusTag.removeClass('enabled').addClass('disabled').text('Disabled');
        }
    }
    
    function showNotification(message, type = 'success') {
        const $popup = $('#tr-notification-popup');
        const $message = $popup.find('.tr-notification-message');
        
        // Update message
        $message.text(message);
        
        // Update styling based on type
        if (type === 'success') {
            $popup.css('background-color', '#28a745');
        } else if (type === 'error') {
            $popup.css('background-color', '#dc3545');
        }
        
        // Show notification
        $popup.addClass('show');
        
        // Hide notification after 3 seconds
        setTimeout(function() {
            $popup.removeClass('show');
        }, 3000);
    }
    
    // Smooth animations for card interactions
    $('.tr-setting-card').on('mouseenter', function() {
        if (!$(this).hasClass('saving')) {
            $(this).addClass('hover');
        }
    }).on('mouseleave', function() {
        $(this).removeClass('hover');
    });
    
    // Prevent form submission (we handle everything via AJAX)
    $('#system-info-settings-form').on('submit', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Handle card accessibility
    $('.tr-setting-card').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
        }
    });
    
    // Add tabindex for keyboard navigation
    $('.tr-setting-card').attr('tabindex', '0');
    
    // Add ripple effect to cards
    $('.tr-setting-card').on('click', function(e) {
        if ($(this).hasClass('saving')) return;
        
        const $card = $(this);
        const ripple = $('<span class="ripple"></span>');
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ripple.css({
            left: x + 'px',
            top: y + 'px'
        });
        
        $card.append(ripple);
        
        setTimeout(function() {
            ripple.remove();
        }, 600);
    });
    
    // Initialize tooltips if needed
    if ($.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Add dashboard widget functions for the sections to work properly
    window.toggleSection = function(id) {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = element.style.display === 'block' ? 'none' : 'block';
        }
    };
    
    window.toggleSectionWithText = function(sectionId, toggleId) {
        const element = document.getElementById(sectionId);
        const toggle = document.getElementById(toggleId);
        if (!element || !toggle) return;
        
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
    };
    
    window.cancelMemoryTest = function(event) {
        // Always prevent default link behavior first
        if (event) {
            event.preventDefault();
        }
        
        // Clear the timer immediately to stop auto-reload
        if (window.memoryTestTimer) {
            clearTimeout(window.memoryTestTimer);
            window.memoryTestTimer = null;
        }
        
        // Show confirmation dialog
        if (confirm('Are you sure you want to cancel the memory test?')) {
            // User confirmed - proceed with cancellation
            // Get the cancel URL from the link that was clicked
            const cancelUrl = event && event.target ? event.target.href : null;
            
            if (cancelUrl) {
                // Navigate to the cancel URL
                window.location.href = cancelUrl;
            } else {
                // Fallback: construct cancel URL manually
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('test', 'cancel');
                // Use existing nonce if available
                const testProgress = document.querySelector('.wp-system-info-test-progress');
                if (testProgress && testProgress.getAttribute('data-nonce')) {
                    currentUrl.searchParams.set('nonce', testProgress.getAttribute('data-nonce'));
                }
                window.location.href = currentUrl.toString();
            }
        } else {
            // User cancelled the confirmation - restart the timer if it was running
            const testProgress = document.querySelector('.wp-system-info-test-progress');
            if (testProgress) {
                const intervalMs = parseInt(testProgress.getAttribute('data-interval')) || 2000;
                const nonce = testProgress.getAttribute('data-nonce') || '';
                if (nonce) {
                    // Re-setup the timer (assuming setupMemoryTestTimer is available)
                    if (typeof setupMemoryTestTimer === 'function') {
                        setupMemoryTestTimer(intervalMs, nonce);
                    } else {
                        // Fallback timer setup
                        window.memoryTestTimer = setTimeout(function() {
                            const currentUrl = new URL(window.location.href);
                            currentUrl.searchParams.set('test', 'continue');
                            if (!currentUrl.searchParams.has('nonce')) {
                                currentUrl.searchParams.set('nonce', nonce);
                            }
                            window.location.href = currentUrl.toString();
                        }, intervalMs);
                    }
                }
            }
        }
        
        return false; // Always prevent default link behavior
    };
});

// CSS for ripple effect (added dynamically)
jQuery(document).ready(function($) {
    if (!$('#tr-ripple-styles').length) {
        $('head').append(`
            <style id="tr-ripple-styles">
                .tr-setting-card {
                    position: relative;
                    overflow: hidden;
                }
                .ripple {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(0, 123, 170, 0.3);
                    transform: scale(0);
                    animation: ripple-animation 0.6s linear;
                    pointer-events: none;
                    width: 20px;
                    height: 20px;
                    margin-left: -10px;
                    margin-top: -10px;
                }
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            </style>
        `);
    }
});
