<?php
/**
 * Advanced Filter Plugin - Usage Examples
 * 
 * This file contains various examples of how to use the Advanced Filter Plugin
 * in different scenarios and configurations.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Example 1: Basic Blog Filter
 * Simple filter for blog posts with all default options
 */
function example_basic_blog_filter() {
    return do_shortcode('[advanced_filter]');
}

/**
 * Example 2: WooCommerce Product Filter
 * Product filter with price range and custom posts per page
 */
function example_product_filter() {
    return do_shortcode('[advanced_filter post_type="product" posts_per_page="12"]');
}

/**
 * Example 3: Minimal Filter
 * Only categories and search, no price or date filters
 */
function example_minimal_filter() {
    return do_shortcode('[advanced_filter show_price="false" show_date="false"]');
}

/**
 * Example 4: Search-Only Filter
 * Just search functionality, no other filters
 */
function example_search_only_filter() {
    return do_shortcode('[advanced_filter show_categories="false" show_price="false" show_date="false"]');
}

/**
 * Example 5: Custom Template Integration
 * How to integrate the filter in a custom page template
 */
function example_custom_template_integration() {
    ?>
    <div class="custom-filter-page">
        <h1>Shop Our Products</h1>
        <p>Use the filters below to find exactly what you're looking for:</p>
        
        <?php echo do_shortcode('[advanced_filter post_type="product" posts_per_page="16"]'); ?>
        
        <div class="additional-content">
            <h2>Why Shop With Us?</h2>
            <p>We offer the best products at competitive prices...</p>
        </div>
    </div>
    <?php
}

/**
 * Example 6: Sidebar Widget Implementation
 * How to add the filter widget programmatically
 */
function example_register_sidebar_with_filter() {
    register_sidebar(array(
        'name' => 'Shop Sidebar',
        'id' => 'shop-sidebar',
        'description' => 'Sidebar for shop pages with filters',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
    
    // Add filter widget to sidebar
    add_action('wp_loaded', function() {
        if (is_active_sidebar('shop-sidebar')) {
            the_widget('AFP_Filter_Widget', array(
                'title' => 'Filter Products',
                'post_type' => 'product',
                'show_categories' => true,
                'show_price' => true,
                'posts_per_page' => 12
            ));
        }
    });
}

/**
 * Example 7: Custom Styling Integration
 * How to add custom styles that match your theme
 */
function example_custom_styling() {
    ?>
    <style>
    /* Custom theme integration */
    #advanced-filter-container {
        font-family: 'Your Theme Font', sans-serif;
    }
    
    .filter-sidebar {
        background: var(--theme-background-color);
        border: 1px solid var(--theme-border-color);
    }
    
    .btn-primary {
        background: var(--theme-primary-color) !important;
        border-color: var(--theme-primary-color) !important;
    }
    
    .btn-primary:hover {
        background: var(--theme-primary-hover-color) !important;
    }
    
    /* Match theme's card style */
    .filter-post-item {
        box-shadow: var(--theme-card-shadow);
        border-radius: var(--theme-border-radius);
    }
    </style>
    
    <?php echo do_shortcode('[advanced_filter]'); ?>
    <?php
}

/**
 * Example 8: AJAX Integration with Custom JavaScript
 * How to extend the filter with custom JavaScript functionality
 */
function example_custom_javascript_integration() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Custom event handler for when filters are applied
        $(document).on('advanced_filter_results_loaded', function(event, data) {
            console.log('Filter results loaded:', data);
            
            // Custom analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'filter_applied', {
                    'event_category': 'shop',
                    'event_label': 'product_filter',
                    'value': data.found_posts
                });
            }
            
            // Custom notification
            showCustomNotification(data.found_posts + ' products found');
        });
        
        // Custom notification function
        function showCustomNotification(message) {
            $('<div class="custom-notification">' + message + '</div>')
                .appendTo('body')
                .fadeIn()
                .delay(3000)
                .fadeOut();
        }
    });
    </script>
    
    <?php echo do_shortcode('[advanced_filter post_type="product"]'); ?>
    <?php
}

/**
 * Example 9: Multi-Column Layout
 * How to create a custom layout with filters in a column
 */
function example_multi_column_layout() {
    ?>
    <div class="shop-layout">
        <div class="shop-filters">
            <?php echo do_shortcode('[advanced_filter post_type="product"]'); ?>
        </div>
        
        <div class="shop-content">
            <div class="shop-header">
                <h1>Our Products</h1>
                <p>Browse our complete collection of high-quality products.</p>
            </div>
            
            <!-- Results will be loaded here by the filter -->
        </div>
        
        <div class="shop-sidebar">
            <h3>Featured Categories</h3>
            <!-- Additional content -->
        </div>
    </div>
    
    <style>
    .shop-layout {
        display: grid;
        grid-template-columns: 300px 1fr 250px;
        gap: 20px;
        margin: 20px 0;
    }
    
    @media (max-width: 768px) {
        .shop-layout {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php
}

/**
 * Example 10: Conditional Filter Display
 * Show different filters based on user role or page context
 */
function example_conditional_filter_display() {
    $current_user = wp_get_current_user();
    
    if (in_array('administrator', $current_user->roles)) {
        // Admin sees all filters
        return do_shortcode('[advanced_filter post_type="product" posts_per_page="20"]');
    } elseif (in_array('subscriber', $current_user->roles)) {
        // Subscribers see limited filters
        return do_shortcode('[advanced_filter show_price="false"]');
    } else {
        // Guests see basic filter
        return do_shortcode('[advanced_filter show_date="false" posts_per_page="8"]');
    }
}

/**
 * Example 11: Filter with Custom Query Modifications
 * How to modify the filter query using WordPress hooks
 */
function example_custom_query_modifications() {
    // Add custom filter to modify query
    add_filter('afp_query_args', function($args, $post_type) {
        if ($post_type === 'product') {
            // Only show products in stock
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            );
            
            // Exclude certain categories
            if (!isset($args['tax_query'])) {
                $args['tax_query'] = array();
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array('hidden-category'),
                'operator' => 'NOT IN'
            );
        }
        
        return $args;
    }, 10, 2);
    
    return do_shortcode('[advanced_filter post_type="product"]');
}

/**
 * Example 12: Mobile-Optimized Filter
 * Filter configuration optimized for mobile devices
 */
function example_mobile_optimized_filter() {
    $is_mobile = wp_is_mobile();
    
    if ($is_mobile) {
        // Mobile-optimized settings
        return do_shortcode('[advanced_filter posts_per_page="6" show_date="false"]');
    } else {
        // Desktop settings
        return do_shortcode('[advanced_filter posts_per_page="12"]');
    }
}

/**
 * Example 13: Filter with Custom Post Item Template
 * How to customize the appearance of filtered post items
 */
function example_custom_post_item_template() {
    // Add custom filter for post item HTML
    add_filter('afp_post_item_html', function($html, $post, $post_type) {
        if ($post_type === 'product') {
            // Custom product item template
            $product = wc_get_product($post->ID);
            $custom_html = '<div class="custom-product-item">';
            $custom_html .= '<div class="product-image">' . get_the_post_thumbnail($post->ID, 'medium') . '</div>';
            $custom_html .= '<div class="product-info">';
            $custom_html .= '<h3>' . get_the_title($post->ID) . '</h3>';
            $custom_html .= '<div class="product-price">' . $product->get_price_html() . '</div>';
            $custom_html .= '<div class="product-rating">' . wc_get_rating_html($product->get_average_rating()) . '</div>';
            $custom_html .= '<a href="' . get_permalink($post->ID) . '" class="view-product">View Product</a>';
            $custom_html .= '</div>';
            $custom_html .= '</div>';
            
            return $custom_html;
        }
        
        return $html;
    }, 10, 3);
    
    return do_shortcode('[advanced_filter post_type="product"]');
}

/**
 * Example 14: Page Template Integration
 * Complete page template example with the filter
 */
function example_complete_page_template() {
    get_header(); ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Shop</h1>
            <div class="page-description">
                <p>Discover our amazing collection of products. Use the filters to find exactly what you need.</p>
            </div>
        </div>
        
        <div class="shop-content">
            <?php echo do_shortcode('[advanced_filter post_type="product" posts_per_page="16"]'); ?>
        </div>
        
        <div class="shop-footer">
            <div class="shipping-info">
                <h3>Free Shipping</h3>
                <p>On orders over $50</p>
            </div>
            <div class="return-policy">
                <h3>Easy Returns</h3>
                <p>30-day return policy</p>
            </div>
        </div>
    </div>
    
    <?php get_footer();
}

/**
 * Example 15: Archive Page Integration
 * How to add the filter to category/archive pages
 */
function example_archive_page_integration() {
    // Add to category pages
    add_action('woocommerce_archive_description', function() {
        if (is_product_category()) {
            echo '<div class="category-filter">';
            echo do_shortcode('[advanced_filter post_type="product"]');
            echo '</div>';
        }
    });
    
    // Add to blog archive pages
    add_action('loop_start', function() {
        if (is_category() && !is_admin() && is_main_query()) {
            echo '<div class="blog-filter">';
            echo do_shortcode('[advanced_filter]');
            echo '</div>';
        }
    });
}

/**
 * Example Usage in Templates:
 * 
 * In your theme files (page.php, archive.php, etc.), you can use:
 * 
 * 1. Simple shortcode:
 *    <?php echo do_shortcode('[advanced_filter]'); ?>
 * 
 * 2. With parameters:
 *    <?php echo do_shortcode('[advanced_filter post_type="product" posts_per_page="12"]'); ?>
 * 
 * 3. Conditional display:
 *    <?php if (function_exists('advanced_filter_display')) {
 *        echo example_conditional_filter_display();
 *    } ?>
 * 
 * 4. In widget areas:
 *    Add the "Advanced Filter Widget" through Appearance > Widgets
 */