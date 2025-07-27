# Advanced Filter Plugin for WordPress

A comprehensive WordPress plugin that provides advanced filtering capabilities for both blog posts and WooCommerce products. Perfect for shop pages, blog pages, and any content that needs sophisticated filtering options.

## 🚀 Features

### Core Filtering Options
- **Category Filter**: Checkbox-based category selection with post counts
- **Price Range Filter**: Interactive slider with manual input for WooCommerce products
- **Date Range Filter**: Filter by publication date (last week, month, year)
- **Search Filter**: Real-time search functionality
- **Sorting Options**: Sort by date, title, or price

### Advanced Features
- **AJAX-powered**: Smooth filtering without page reloads
- **Responsive Design**: Mobile-optimized interface
- **Multiple Display Options**: Shortcode and widget support
- **Customizable**: Admin settings for colors, styles, and behavior
- **Accessibility**: WCAG compliant with keyboard navigation
- **Performance Optimized**: Lazy loading and debounced inputs
- **Browser History**: Maintains filter state in URL for bookmarking

### Admin Features
- **Settings Page**: Comprehensive admin interface
- **Color Customization**: Built-in color picker
- **Custom CSS**: Add your own styles
- **Import/Export**: Backup and restore settings
- **Live Preview**: See changes in real-time

## 📦 Installation

### Method 1: Manual Installation

1. **Download the Plugin**
   ```bash
   git clone https://github.com/your-username/advanced-filter-plugin.git
   ```

2. **Upload to WordPress**
   - Upload the entire `advanced-filter-plugin` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

3. **Activate the Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Advanced Filter Plugin" and click "Activate"

### Method 2: WordPress Admin Upload

1. Go to WordPress Admin → Plugins → Add New
2. Click "Upload Plugin"
3. Choose the plugin zip file
4. Click "Install Now" and then "Activate"

## 🎯 Usage

### Using the Shortcode

The simplest way to use the plugin is with the shortcode:

```php
[advanced_filter]
```

### Shortcode Parameters

Customize the filter with these parameters:

```php
[advanced_filter 
    post_type="product" 
    show_categories="true" 
    show_price="true" 
    show_date="true" 
    show_search="true" 
    posts_per_page="12"
]
```

#### Parameter Details

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `post_type` | string | `post` | Post type to filter (`post`, `product`) |
| `show_categories` | boolean | `true` | Show category checkboxes |
| `show_price` | boolean | `true` | Show price range slider (products only) |
| `show_date` | boolean | `true` | Show date range filter |
| `show_search` | boolean | `true` | Show search input |
| `posts_per_page` | integer | `10` | Number of posts per page |

### Using the Widget

1. Go to WordPress Admin → Appearance → Widgets
2. Find "Advanced Filter Widget"
3. Drag it to your desired widget area
4. Configure the options
5. Save

### Template Integration

You can also integrate the filter directly in your theme:

```php
<?php
if (function_exists('advanced_filter_display')) {
    advanced_filter_display(array(
        'post_type' => 'product',
        'posts_per_page' => 12
    ));
}
?>
```

## ⚙️ Configuration

### Admin Settings

Access the settings at **WordPress Admin → Settings → Advanced Filter**

#### General Settings
- **Default Post Type**: Choose between posts and products
- **Posts Per Page**: Set default pagination
- **Enable AJAX**: Toggle AJAX functionality
- **Maximum Price Range**: Set the upper limit for price slider

#### Style Settings
- **Primary Color**: Customize the main theme color
- **Custom CSS**: Add your own styles

### Customization Examples

#### Custom Colors
```css
/* Change primary color */
.btn-primary {
    background-color: #e91e63 !important;
}

.ui-slider .ui-slider-range {
    background-color: #e91e63 !important;
}
```

#### Custom Layout
```css
/* Stack filters vertically on mobile */
@media (max-width: 768px) {
    .filter-sidebar {
        width: 100% !important;
    }
}
```

## 🎨 Styling & Theming

### CSS Classes Reference

The plugin uses semantic CSS classes for easy customization:

```css
/* Main container */
#advanced-filter-container { }

/* Sidebar with filters */
.filter-sidebar { }

/* Individual filter sections */
.filter-section { }

/* Category checkboxes */
.category-checkboxes { }

/* Price slider */
#price-slider { }

/* Results container */
.filter-results { }

/* Individual post items */
.filter-post-item { }
```

### Theme Integration

For better theme integration, you can override the default styles:

1. Copy styles from `assets/css/filter.css`
2. Modify colors, fonts, and spacing to match your theme
3. Add the custom CSS in the admin settings or your theme's `style.css`

## 🔧 Developer Reference

### Hooks and Filters

#### Actions
```php
// Before filter form is rendered
do_action('afp_before_filter_form', $post_type, $attributes);

// After filter form is rendered
do_action('afp_after_filter_form', $post_type, $attributes);

// Before results are displayed
do_action('afp_before_results', $query, $post_type);
```

#### Filters
```php
// Modify query arguments
$args = apply_filters('afp_query_args', $args, $post_type);

// Customize post item output
$html = apply_filters('afp_post_item_html', $html, $post, $post_type);

// Modify price range
$max_price = apply_filters('afp_max_price', 1000, $post_type);
```

### JavaScript API

The plugin exposes a JavaScript API for advanced customization:

```javascript
// Reload filters
AdvancedFilter.reload();

// Smooth scroll to element
AdvancedFilter.smoothScrollTo('#results', 100);

// Format price
const formattedPrice = AdvancedFilter.formatPrice(29.99, '$');

// Debounce function
const debouncedFunction = AdvancedFilter.debounce(myFunction, 300);
```

### Custom Post Types

To add support for custom post types:

```php
add_filter('afp_supported_post_types', function($post_types) {
    $post_types['my_custom_type'] = 'My Custom Type';
    return $post_types;
});
```

## 🌐 Internationalization

The plugin is translation-ready. To translate:

1. Use a translation plugin like Loco Translate
2. Create translations for the `advanced-filter` text domain
3. Or create `.po` files in the `languages` folder

### Available Languages
- English (default)
- Ready for translation to any language

## 🔍 Troubleshooting

### Common Issues

#### Filters Not Working
1. Check if JavaScript is enabled
2. Verify jQuery is loaded
3. Check browser console for errors
4. Ensure AJAX is enabled in settings

#### Styling Issues
1. Check for theme CSS conflicts
2. Use browser developer tools to inspect elements
3. Add `!important` to custom CSS if needed
4. Clear any caching plugins

#### Performance Issues
1. Reduce posts per page
2. Enable lazy loading for images
3. Use a caching plugin
4. Optimize database queries

### Debug Mode

Enable WordPress debug mode to see detailed error messages:

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📱 Mobile Optimization

The plugin is fully responsive and includes:

- Collapsible filter sections on mobile
- Touch-friendly controls
- Optimized layouts for small screens
- Fast loading on mobile networks

## 🚀 Performance

### Optimization Features
- **AJAX Loading**: No page reloads
- **Lazy Loading**: Images load as needed
- **Debounced Inputs**: Reduces server requests
- **Caching Support**: Works with popular caching plugins
- **Minified Assets**: Optimized CSS and JS files

### Performance Tips
1. Use a caching plugin
2. Optimize your images
3. Keep the number of categories reasonable
4. Use appropriate posts per page settings

## 🤝 Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Development Setup

```bash
# Clone the repository
git clone https://github.com/your-username/advanced-filter-plugin.git

# Install dependencies (if any)
cd advanced-filter-plugin

# Make your changes
# Test thoroughly
# Submit PR
```

## 📄 License

This plugin is licensed under the GPL v2 or later.

## 🆘 Support

- **Documentation**: Check this README and inline comments
- **Issues**: Report bugs on GitHub Issues
- **Community**: WordPress.org support forums
- **Premium Support**: Contact for custom development

## 📋 Changelog

### Version 1.0.0
- Initial release
- Category filtering with checkboxes
- Price range slider for WooCommerce
- Date range filtering
- Search functionality
- AJAX-powered filtering
- Responsive design
- Admin settings page
- Widget support
- Shortcode implementation

## 🔮 Roadmap

### Upcoming Features
- **Multi-select Dropdowns**: Alternative to checkboxes
- **Advanced Search**: Search in custom fields
- **Filter Presets**: Save and load filter combinations
- **Analytics**: Track popular filters
- **More Post Types**: Support for events, portfolios, etc.
- **Visual Filter Builder**: Drag-and-drop interface

---

**Made with ❤️ for the WordPress community**