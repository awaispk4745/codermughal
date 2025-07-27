<?php
/**
 * Plugin Name: Advanced Filter Plugin
 * Plugin URI: https://example.com/advanced-filter-plugin
 * Description: Advanced filtering system for WordPress shop and blog pages with categories, price range, and multiple filter options.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: advanced-filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AFP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AFP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AFP_VERSION', '1.0.0');

// Main plugin class
class AdvancedFilterPlugin {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('wp_ajax_nopriv_filter_posts', array($this, 'ajax_filter_posts'));
        add_action('widgets_init', array($this, 'register_widget'));
        add_shortcode('advanced_filter', array($this, 'filter_shortcode'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('advanced-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('afp-script', AFP_PLUGIN_URL . 'assets/js/filter.js', array('jquery', 'jquery-ui-slider'), AFP_VERSION, true);
        wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css');
        wp_enqueue_style('afp-style', AFP_PLUGIN_URL . 'assets/css/filter.css', array(), AFP_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('afp-script', 'afp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('afp_nonce')
        ));
    }
    
    public function ajax_filter_posts() {
        check_ajax_referer('afp_nonce', 'nonce');
        
        $categories = isset($_POST['categories']) ? $_POST['categories'] : array();
        $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
        $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 999999;
        $date_range = isset($_POST['date_range']) ? sanitize_text_field($_POST['date_range']) : '';
        $search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';
        $posts_per_page = isset($_POST['posts_per_page']) ? intval($_POST['posts_per_page']) : 10;
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'post_status' => 'publish'
        );
        
        // Category filter
        if (!empty($categories)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => ($post_type === 'product') ? 'product_cat' : 'category',
                    'field' => 'term_id',
                    'terms' => $categories,
                    'operator' => 'IN'
                )
            );
        }
        
        // Price filter for WooCommerce products
        if ($post_type === 'product' && ($min_price > 0 || $max_price < 999999)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN'
                )
            );
        }
        
        // Date range filter
        if (!empty($date_range)) {
            switch ($date_range) {
                case 'last_week':
                    $args['date_query'] = array(
                        array(
                            'after' => '1 week ago'
                        )
                    );
                    break;
                case 'last_month':
                    $args['date_query'] = array(
                        array(
                            'after' => '1 month ago'
                        )
                    );
                    break;
                case 'last_year':
                    $args['date_query'] = array(
                        array(
                            'after' => '1 year ago'
                        )
                    );
                    break;
            }
        }
        
        // Search term filter
        if (!empty($search_term)) {
            $args['s'] = $search_term;
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_post_item(get_post(), $post_type);
            }
            wp_reset_postdata();
        } else {
            echo '<div class="no-results">' . __('No results found.', 'advanced-filter') . '</div>';
        }
        
        $posts_html = ob_get_clean();
        
        // Pagination
        $pagination = '';
        if ($query->max_num_pages > 1) {
            $pagination = paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'type' => 'list',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;'
            ));
        }
        
        wp_send_json_success(array(
            'posts' => $posts_html,
            'pagination' => $pagination,
            'found_posts' => $query->found_posts
        ));
    }
    
    private function render_post_item($post, $post_type) {
        $post_id = $post->ID;
        $title = get_the_title($post_id);
        $permalink = get_permalink($post_id);
        $excerpt = get_the_excerpt($post_id);
        $thumbnail = get_the_post_thumbnail($post_id, 'medium');
        $date = get_the_date('F j, Y', $post_id);
        
        echo '<div class="filter-post-item" data-post-id="' . $post_id . '">';
        echo '<div class="post-thumbnail">' . $thumbnail . '</div>';
        echo '<div class="post-content">';
        echo '<h3><a href="' . $permalink . '">' . $title . '</a></h3>';
        echo '<div class="post-meta">';
        echo '<span class="post-date">' . $date . '</span>';
        
        if ($post_type === 'product' && function_exists('wc_get_product')) {
            $product = wc_get_product($post_id);
            if ($product) {
                echo '<span class="product-price">' . $product->get_price_html() . '</span>';
            }
        }
        
        echo '</div>';
        echo '<div class="post-excerpt">' . $excerpt . '</div>';
        echo '<a href="' . $permalink . '" class="read-more">' . __('Read More', 'advanced-filter') . '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    public function filter_shortcode($atts) {
        $atts = shortcode_atts(array(
            'post_type' => 'post',
            'show_categories' => 'true',
            'show_price' => 'true',
            'show_date' => 'true',
            'show_search' => 'true',
            'posts_per_page' => 10
        ), $atts);
        
        ob_start();
        $this->render_filter_form($atts);
        return ob_get_clean();
    }
    
    private function render_filter_form($atts) {
        $post_type = $atts['post_type'];
        $taxonomy = ($post_type === 'product') ? 'product_cat' : 'category';
        
        ?>
        <div id="advanced-filter-container" data-post-type="<?php echo esc_attr($post_type); ?>" data-posts-per-page="<?php echo esc_attr($atts['posts_per_page']); ?>">
            <div class="filter-sidebar">
                <h3><?php _e('Filter Options', 'advanced-filter'); ?></h3>
                
                <?php if ($atts['show_search'] === 'true'): ?>
                <div class="filter-section search-filter">
                    <h4><?php _e('Search', 'advanced-filter'); ?></h4>
                    <input type="text" id="search-input" placeholder="<?php _e('Search...', 'advanced-filter'); ?>">
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_categories'] === 'true'): ?>
                <div class="filter-section category-filter">
                    <h4><?php _e('Categories', 'advanced-filter'); ?></h4>
                    <div class="category-checkboxes">
                        <?php
                        $categories = get_terms(array(
                            'taxonomy' => $taxonomy,
                            'hide_empty' => true
                        ));
                        
                        foreach ($categories as $category) {
                            echo '<label class="checkbox-label">';
                            echo '<input type="checkbox" name="categories[]" value="' . $category->term_id . '">';
                            echo '<span class="checkmark"></span>';
                            echo $category->name . ' (' . $category->count . ')';
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_price'] === 'true' && $post_type === 'product'): ?>
                <div class="filter-section price-filter">
                    <h4><?php _e('Price Range', 'advanced-filter'); ?></h4>
                    <div class="price-range-container">
                        <div id="price-slider"></div>
                        <div class="price-inputs">
                            <input type="number" id="min-price" placeholder="Min" min="0">
                            <span>-</span>
                            <input type="number" id="max-price" placeholder="Max" min="0">
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['show_date'] === 'true'): ?>
                <div class="filter-section date-filter">
                    <h4><?php _e('Date Range', 'advanced-filter'); ?></h4>
                    <select id="date-range">
                        <option value=""><?php _e('All Time', 'advanced-filter'); ?></option>
                        <option value="last_week"><?php _e('Last Week', 'advanced-filter'); ?></option>
                        <option value="last_month"><?php _e('Last Month', 'advanced-filter'); ?></option>
                        <option value="last_year"><?php _e('Last Year', 'advanced-filter'); ?></option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="filter-actions">
                    <button id="apply-filters" class="btn btn-primary"><?php _e('Apply Filters', 'advanced-filter'); ?></button>
                    <button id="reset-filters" class="btn btn-secondary"><?php _e('Reset', 'advanced-filter'); ?></button>
                </div>
            </div>
            
            <div class="filter-results">
                <div class="results-header">
                    <div class="results-count">
                        <span id="results-found"><?php _e('Loading...', 'advanced-filter'); ?></span>
                    </div>
                    <div class="results-sorting">
                        <select id="sort-by">
                            <option value="date"><?php _e('Sort by Date', 'advanced-filter'); ?></option>
                            <option value="title"><?php _e('Sort by Title', 'advanced-filter'); ?></option>
                            <?php if ($post_type === 'product'): ?>
                            <option value="price"><?php _e('Sort by Price', 'advanced-filter'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div id="filter-results-container">
                    <!-- Results will be loaded here via AJAX -->
                </div>
                
                <div id="filter-pagination">
                    <!-- Pagination will be loaded here via AJAX -->
                </div>
            </div>
        </div>
        <?php
    }
    
    public function register_widget() {
        register_widget('AFP_Filter_Widget');
    }
}

// Widget class
class AFP_Filter_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'afp_filter_widget',
            __('Advanced Filter Widget', 'advanced-filter'),
            array('description' => __('Display advanced filter options', 'advanced-filter'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $shortcode_atts = array(
            'post_type' => isset($instance['post_type']) ? $instance['post_type'] : 'post',
            'show_categories' => isset($instance['show_categories']) ? 'true' : 'false',
            'show_price' => isset($instance['show_price']) ? 'true' : 'false',
            'show_date' => isset($instance['show_date']) ? 'true' : 'false',
            'show_search' => isset($instance['show_search']) ? 'true' : 'false',
            'posts_per_page' => isset($instance['posts_per_page']) ? $instance['posts_per_page'] : 10
        );
        
        $filter_plugin = new AdvancedFilterPlugin();
        echo $filter_plugin->filter_shortcode($shortcode_atts);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Filter', 'advanced-filter');
        $post_type = !empty($instance['post_type']) ? $instance['post_type'] : 'post';
        $posts_per_page = !empty($instance['posts_per_page']) ? $instance['posts_per_page'] : 10;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>"><?php _e('Post Type:', 'advanced-filter'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                <option value="post" <?php selected($post_type, 'post'); ?>><?php _e('Blog Posts', 'advanced-filter'); ?></option>
                <option value="product" <?php selected($post_type, 'product'); ?>><?php _e('Products', 'advanced-filter'); ?></option>
            </select>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked(isset($instance['show_categories']) ? $instance['show_categories'] : 0); ?> id="<?php echo $this->get_field_id('show_categories'); ?>" name="<?php echo $this->get_field_name('show_categories'); ?>" />
            <label for="<?php echo $this->get_field_id('show_categories'); ?>"><?php _e('Show Categories', 'advanced-filter'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked(isset($instance['show_price']) ? $instance['show_price'] : 0); ?> id="<?php echo $this->get_field_id('show_price'); ?>" name="<?php echo $this->get_field_name('show_price'); ?>" />
            <label for="<?php echo $this->get_field_id('show_price'); ?>"><?php _e('Show Price Filter', 'advanced-filter'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked(isset($instance['show_date']) ? $instance['show_date'] : 0); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
            <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php _e('Show Date Filter', 'advanced-filter'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked(isset($instance['show_search']) ? $instance['show_search'] : 0); ?> id="<?php echo $this->get_field_id('show_search'); ?>" name="<?php echo $this->get_field_name('show_search'); ?>" />
            <label for="<?php echo $this->get_field_id('show_search'); ?>"><?php _e('Show Search', 'advanced-filter'); ?></label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>"><?php _e('Posts Per Page:', 'advanced-filter'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="number" value="<?php echo esc_attr($posts_per_page); ?>" min="1">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['post_type'] = (!empty($new_instance['post_type'])) ? strip_tags($new_instance['post_type']) : 'post';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? intval($new_instance['posts_per_page']) : 10;
        $instance['show_categories'] = !empty($new_instance['show_categories']);
        $instance['show_price'] = !empty($new_instance['show_price']);
        $instance['show_date'] = !empty($new_instance['show_date']);
        $instance['show_search'] = !empty($new_instance['show_search']);
        
        return $instance;
    }
}

// Include admin functionality
if (is_admin()) {
    require_once AFP_PLUGIN_PATH . 'admin/admin-page.php';
}

// Initialize the plugin
new AdvancedFilterPlugin();

// Activation hook
register_activation_hook(__FILE__, 'afp_activate');
function afp_activate() {
    // Set default options
    $default_options = array(
        'default_post_type' => 'post',
        'posts_per_page' => 10,
        'enable_ajax' => 1,
        'price_range_max' => 1000,
        'primary_color' => '#007cba',
        'custom_css' => ''
    );
    
    add_option('afp_settings', $default_options);
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'afp_deactivate');
function afp_deactivate() {
    flush_rewrite_rules();
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'afp_uninstall');
function afp_uninstall() {
    delete_option('afp_settings');
}