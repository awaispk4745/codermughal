<?php
/**
 * Admin Settings Page for Advanced Filter Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class AFP_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Advanced Filter Settings', 'advanced-filter'),
            __('Advanced Filter', 'advanced-filter'),
            'manage_options',
            'advanced-filter-settings',
            array($this, 'admin_page')
        );
    }
    
    public function init_settings() {
        register_setting('afp_settings_group', 'afp_settings');
        
        add_settings_section(
            'afp_general_section',
            __('General Settings', 'advanced-filter'),
            array($this, 'general_section_callback'),
            'advanced-filter-settings'
        );
        
        add_settings_field(
            'default_post_type',
            __('Default Post Type', 'advanced-filter'),
            array($this, 'default_post_type_callback'),
            'advanced-filter-settings',
            'afp_general_section'
        );
        
        add_settings_field(
            'posts_per_page',
            __('Posts Per Page', 'advanced-filter'),
            array($this, 'posts_per_page_callback'),
            'advanced-filter-settings',
            'afp_general_section'
        );
        
        add_settings_field(
            'enable_ajax',
            __('Enable AJAX Filtering', 'advanced-filter'),
            array($this, 'enable_ajax_callback'),
            'advanced-filter-settings',
            'afp_general_section'
        );
        
        add_settings_field(
            'price_range_max',
            __('Maximum Price Range', 'advanced-filter'),
            array($this, 'price_range_max_callback'),
            'advanced-filter-settings',
            'afp_general_section'
        );
        
        add_settings_section(
            'afp_style_section',
            __('Style Settings', 'advanced-filter'),
            array($this, 'style_section_callback'),
            'advanced-filter-settings'
        );
        
        add_settings_field(
            'primary_color',
            __('Primary Color', 'advanced-filter'),
            array($this, 'primary_color_callback'),
            'advanced-filter-settings',
            'afp_style_section'
        );
        
        add_settings_field(
            'custom_css',
            __('Custom CSS', 'advanced-filter'),
            array($this, 'custom_css_callback'),
            'advanced-filter-settings',
            'afp_style_section'
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_advanced-filter-settings') {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('afp-admin', AFP_PLUGIN_URL . 'admin/js/admin.js', array('jquery', 'wp-color-picker'), AFP_VERSION, true);
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Advanced Filter Settings', 'advanced-filter'); ?></h1>
            
            <div class="afp-admin-container">
                <div class="afp-admin-main">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('afp_settings_group');
                        do_settings_sections('advanced-filter-settings');
                        submit_button();
                        ?>
                    </form>
                </div>
                
                <div class="afp-admin-sidebar">
                    <div class="afp-admin-box">
                        <h3><?php _e('Usage Instructions', 'advanced-filter'); ?></h3>
                        <p><?php _e('Use the shortcode to display the filter:', 'advanced-filter'); ?></p>
                        <code>[advanced_filter]</code>
                        
                        <h4><?php _e('Shortcode Parameters:', 'advanced-filter'); ?></h4>
                        <ul>
                            <li><code>post_type</code> - post, product (default: post)</li>
                            <li><code>show_categories</code> - true/false (default: true)</li>
                            <li><code>show_price</code> - true/false (default: true)</li>
                            <li><code>show_date</code> - true/false (default: true)</li>
                            <li><code>show_search</code> - true/false (default: true)</li>
                            <li><code>posts_per_page</code> - number (default: 10)</li>
                        </ul>
                        
                        <h4><?php _e('Example:', 'advanced-filter'); ?></h4>
                        <code>[advanced_filter post_type="product" posts_per_page="12"]</code>
                    </div>
                    
                    <div class="afp-admin-box">
                        <h3><?php _e('Widget', 'advanced-filter'); ?></h3>
                        <p><?php _e('You can also use the Advanced Filter Widget in your sidebar or widget areas.', 'advanced-filter'); ?></p>
                        <p><a href="<?php echo admin_url('widgets.php'); ?>" class="button"><?php _e('Manage Widgets', 'advanced-filter'); ?></a></p>
                    </div>
                    
                    <div class="afp-admin-box">
                        <h3><?php _e('Support', 'advanced-filter'); ?></h3>
                        <p><?php _e('Need help? Check out our documentation or contact support.', 'advanced-filter'); ?></p>
                        <p>
                            <a href="#" class="button button-secondary"><?php _e('Documentation', 'advanced-filter'); ?></a>
                            <a href="#" class="button button-secondary"><?php _e('Support', 'advanced-filter'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .afp-admin-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .afp-admin-main {
            flex: 1;
        }
        
        .afp-admin-sidebar {
            flex: 0 0 300px;
        }
        
        .afp-admin-box {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .afp-admin-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #23282d;
        }
        
        .afp-admin-box h4 {
            margin-top: 15px;
            margin-bottom: 10px;
            color: #23282d;
        }
        
        .afp-admin-box code {
            background: #f1f1f1;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
        }
        
        .afp-admin-box ul {
            margin-left: 20px;
        }
        
        .afp-admin-box ul li {
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .afp-admin-container {
                flex-direction: column;
            }
            
            .afp-admin-sidebar {
                flex: none;
            }
        }
        </style>
        <?php
    }
    
    public function general_section_callback() {
        echo '<p>' . __('Configure general settings for the Advanced Filter plugin.', 'advanced-filter') . '</p>';
    }
    
    public function style_section_callback() {
        echo '<p>' . __('Customize the appearance of the filter.', 'advanced-filter') . '</p>';
    }
    
    public function default_post_type_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['default_post_type']) ? $options['default_post_type'] : 'post';
        ?>
        <select name="afp_settings[default_post_type]">
            <option value="post" <?php selected($value, 'post'); ?>><?php _e('Blog Posts', 'advanced-filter'); ?></option>
            <option value="product" <?php selected($value, 'product'); ?>><?php _e('Products (WooCommerce)', 'advanced-filter'); ?></option>
        </select>
        <p class="description"><?php _e('Default post type for the filter when not specified in shortcode.', 'advanced-filter'); ?></p>
        <?php
    }
    
    public function posts_per_page_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['posts_per_page']) ? $options['posts_per_page'] : 10;
        ?>
        <input type="number" name="afp_settings[posts_per_page]" value="<?php echo esc_attr($value); ?>" min="1" max="100" />
        <p class="description"><?php _e('Number of posts to display per page.', 'advanced-filter'); ?></p>
        <?php
    }
    
    public function enable_ajax_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['enable_ajax']) ? $options['enable_ajax'] : 1;
        ?>
        <label>
            <input type="checkbox" name="afp_settings[enable_ajax]" value="1" <?php checked($value, 1); ?> />
            <?php _e('Enable AJAX filtering (recommended)', 'advanced-filter'); ?>
        </label>
        <p class="description"><?php _e('When enabled, filters will update results without page reload.', 'advanced-filter'); ?></p>
        <?php
    }
    
    public function price_range_max_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['price_range_max']) ? $options['price_range_max'] : 1000;
        ?>
        <input type="number" name="afp_settings[price_range_max]" value="<?php echo esc_attr($value); ?>" min="100" step="10" />
        <p class="description"><?php _e('Maximum value for the price range slider.', 'advanced-filter'); ?></p>
        <?php
    }
    
    public function primary_color_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['primary_color']) ? $options['primary_color'] : '#007cba';
        ?>
        <input type="text" name="afp_settings[primary_color]" value="<?php echo esc_attr($value); ?>" class="afp-color-picker" />
        <p class="description"><?php _e('Primary color for buttons and highlights.', 'advanced-filter'); ?></p>
        <?php
    }
    
    public function custom_css_callback() {
        $options = get_option('afp_settings');
        $value = isset($options['custom_css']) ? $options['custom_css'] : '';
        ?>
        <textarea name="afp_settings[custom_css]" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($value); ?></textarea>
        <p class="description"><?php _e('Add custom CSS to override default styles.', 'advanced-filter'); ?></p>
        <?php
    }
}

// Initialize admin class
new AFP_Admin();