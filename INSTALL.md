# Quick Installation Guide

## 📋 Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- For WooCommerce features: WooCommerce 3.0+

## 🚀 Installation Steps

### 1. Download & Upload

**Option A: Direct Upload**
1. Download the plugin files
2. Create a zip file of the entire plugin folder
3. Go to WordPress Admin → Plugins → Add New → Upload Plugin
4. Choose the zip file and click "Install Now"

**Option B: FTP Upload**
1. Upload the `advanced-filter-plugin` folder to `/wp-content/plugins/`
2. Ensure proper file permissions (644 for files, 755 for folders)

### 2. Activate Plugin

1. Go to WordPress Admin → Plugins
2. Find "Advanced Filter Plugin"
3. Click "Activate"

### 3. Basic Configuration

1. Go to WordPress Admin → Settings → Advanced Filter
2. Configure your preferred settings:
   - Default post type (posts or products)
   - Posts per page
   - Primary color
   - Enable/disable AJAX

### 4. Add to Your Site

**Using Shortcode:**
```php
[advanced_filter]
```

**Using Widget:**
1. Go to Appearance → Widgets
2. Add "Advanced Filter Widget" to your sidebar

**In Template:**
```php
<?php echo do_shortcode('[advanced_filter]'); ?>
```

## ✅ Verification

After installation, verify everything works:

1. **Frontend Check**: Visit a page with the filter
2. **Filter Test**: Try filtering by categories
3. **AJAX Test**: Ensure no page reloads occur
4. **Mobile Test**: Check responsive design
5. **Admin Access**: Verify settings page loads

## 🔧 Common Setup Issues

### Plugin Not Appearing
- Check file permissions
- Ensure all files uploaded correctly
- Check PHP error logs

### Filters Not Working
- Verify jQuery is loaded
- Check for JavaScript errors in browser console
- Ensure AJAX is enabled in settings

### Styling Issues
- Check for theme conflicts
- Verify CSS files are loading
- Clear any caching plugins

## 📞 Need Help?

- Check the full README.md for detailed documentation
- Review troubleshooting section
- Check WordPress debug logs
- Contact support if issues persist

---

**Installation typically takes 2-3 minutes!**