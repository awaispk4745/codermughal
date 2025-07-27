jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize color picker
    $('.afp-color-picker').wpColorPicker({
        defaultColor: '#007cba',
        change: function(event, ui) {
            // Update any preview elements if needed
            updateColorPreview(ui.color.toString());
        },
        clear: function() {
            // Reset to default color
            updateColorPreview('#007cba');
        }
    });
    
    function updateColorPreview(color) {
        // You can add real-time preview functionality here
        console.log('Color changed to:', color);
    }
    
    // Add preview functionality for custom CSS
    $('#afp_settings\\[custom_css\\]').on('input', function() {
        // Debounce the input to avoid too many updates
        clearTimeout(window.cssPreviewTimeout);
        window.cssPreviewTimeout = setTimeout(function() {
            updateCSSPreview();
        }, 500);
    });
    
    function updateCSSPreview() {
        const customCSS = $('#afp_settings\\[custom_css\\]').val();
        
        // Remove existing preview style
        $('#afp-css-preview').remove();
        
        if (customCSS.trim()) {
            // Add new preview style
            $('<style id="afp-css-preview">' + customCSS + '</style>').appendTo('head');
        }
    }
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Validate posts per page
        const postsPerPage = $('input[name="afp_settings[posts_per_page]"]').val();
        if (postsPerPage < 1 || postsPerPage > 100) {
            alert('Posts per page must be between 1 and 100.');
            isValid = false;
        }
        
        // Validate price range max
        const priceRangeMax = $('input[name="afp_settings[price_range_max]"]').val();
        if (priceRangeMax < 100) {
            alert('Maximum price range must be at least 100.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Add help tooltips
    $('.description').each(function() {
        const $this = $(this);
        const text = $this.text();
        
        if (text.length > 50) {
            $this.addClass('has-tooltip');
            $this.attr('title', text);
        }
    });
    
    // Initialize tooltips
    $('.has-tooltip').tooltip({
        position: {
            my: "left+15 center",
            at: "right center"
        }
    });
    
    // Add confirmation for reset actions
    $('.reset-settings').on('click', function(e) {
        if (!confirm('Are you sure you want to reset all settings to default values?')) {
            e.preventDefault();
        }
    });
    
    // Auto-save draft functionality
    let autoSaveTimer;
    const autoSaveInterval = 30000; // 30 seconds
    
    function startAutoSave() {
        autoSaveTimer = setInterval(function() {
            saveDraft();
        }, autoSaveInterval);
    }
    
    function saveDraft() {
        const formData = $('form').serialize();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'afp_save_draft',
                nonce: $('#_wpnonce').val(),
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    showNotice('Draft saved automatically', 'info', 2000);
                }
            }
        });
    }
    
    function showNotice(message, type, duration) {
        type = type || 'success';
        duration = duration || 3000;
        
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, duration);
    }
    
    // Start auto-save when form is modified
    $('form input, form textarea, form select').on('change input', function() {
        if (!autoSaveTimer) {
            startAutoSave();
        }
    });
    
    // Stop auto-save when form is submitted
    $('form').on('submit', function() {
        if (autoSaveTimer) {
            clearInterval(autoSaveTimer);
        }
    });
    
    // Import/Export settings
    $('#export-settings').on('click', function(e) {
        e.preventDefault();
        exportSettings();
    });
    
    $('#import-settings').on('change', function() {
        importSettings(this.files[0]);
    });
    
    function exportSettings() {
        const settings = {};
        
        $('form input, form textarea, form select').each(function() {
            const $field = $(this);
            const name = $field.attr('name');
            let value = $field.val();
            
            if ($field.attr('type') === 'checkbox') {
                value = $field.is(':checked');
            }
            
            if (name) {
                settings[name] = value;
            }
        });
        
        const dataStr = JSON.stringify(settings, null, 2);
        const dataBlob = new Blob([dataStr], {type: 'application/json'});
        const url = URL.createObjectURL(dataBlob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = 'advanced-filter-settings.json';
        link.click();
        
        URL.revokeObjectURL(url);
        showNotice('Settings exported successfully', 'success');
    }
    
    function importSettings(file) {
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const settings = JSON.parse(e.target.result);
                
                Object.keys(settings).forEach(function(name) {
                    const $field = $('[name="' + name + '"]');
                    const value = settings[name];
                    
                    if ($field.attr('type') === 'checkbox') {
                        $field.prop('checked', value);
                    } else {
                        $field.val(value);
                    }
                    
                    // Trigger change event for color pickers
                    if ($field.hasClass('afp-color-picker')) {
                        $field.wpColorPicker('color', value);
                    }
                });
                
                showNotice('Settings imported successfully', 'success');
            } catch (error) {
                showNotice('Error importing settings: Invalid file format', 'error');
            }
        };
        
        reader.readAsText(file);
    }
    
    // Add import/export buttons to the form
    const importExportHTML = `
        <div class="afp-import-export" style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
            <h3>Import/Export Settings</h3>
            <p>
                <button type="button" id="export-settings" class="button button-secondary">Export Settings</button>
                <input type="file" id="import-settings" accept=".json" style="display: none;">
                <button type="button" class="button button-secondary" onclick="document.getElementById('import-settings').click();">Import Settings</button>
            </p>
        </div>
    `;
    
    $('form').after(importExportHTML);
    
    // Real-time preview for settings
    function updatePreview() {
        const primaryColor = $('.afp-color-picker').val();
        const customCSS = $('#afp_settings\\[custom_css\\]').val();
        
        // Create preview styles
        let previewCSS = `
            .afp-preview .btn-primary {
                background-color: ${primaryColor} !important;
            }
            .afp-preview .ui-slider .ui-slider-range {
                background-color: ${primaryColor} !important;
            }
            .afp-preview .ui-slider .ui-slider-handle {
                background-color: ${primaryColor} !important;
            }
            ${customCSS}
        `;
        
        // Update preview style
        $('#afp-preview-style').remove();
        $('<style id="afp-preview-style">' + previewCSS + '</style>').appendTo('head');
    }
    
    // Add live preview section
    const previewHTML = `
        <div class="afp-preview-section" style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
            <h3>Live Preview</h3>
            <div class="afp-preview">
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <button class="btn btn-primary" style="padding: 8px 16px; border: none; border-radius: 4px; color: white; cursor: pointer;">Apply Filters</button>
                    <button class="btn btn-secondary" style="padding: 8px 16px; border: none; border-radius: 4px; background: #6c757d; color: white; cursor: pointer;">Reset</button>
                </div>
                <div style="margin: 15px 0;">
                    <div class="ui-slider" style="height: 6px; background: #e9ecef; border-radius: 3px; position: relative;">
                        <div class="ui-slider-range" style="position: absolute; height: 100%; background: #007cba; border-radius: 3px; width: 60%; left: 20%;"></div>
                        <div class="ui-slider-handle" style="position: absolute; width: 18px; height: 18px; background: #007cba; border-radius: 50%; top: -6px; left: 20%; cursor: pointer;"></div>
                        <div class="ui-slider-handle" style="position: absolute; width: 18px; height: 18px; background: #007cba; border-radius: 50%; top: -6px; left: 80%; cursor: pointer;"></div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('.afp-admin-main form').after(previewHTML);
    
    // Update preview when settings change
    $('.afp-color-picker, #afp_settings\\[custom_css\\]').on('change input', function() {
        setTimeout(updatePreview, 100);
    });
    
    // Initial preview update
    updatePreview();
});