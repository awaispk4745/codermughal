jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize filter functionality
    function initializeFilter() {
        const $container = $('#advanced-filter-container');
        if (!$container.length) return;
        
        const postType = $container.data('post-type') || 'post';
        const postsPerPage = $container.data('posts-per-page') || 10;
        let currentPage = 1;
        let isLoading = false;
        
        // Initialize price slider if it exists
        initializePriceSlider();
        
        // Load initial results
        loadResults();
        
        // Event handlers
        setupEventHandlers();
        
        function initializePriceSlider() {
            const $priceSlider = $('#price-slider');
            if (!$priceSlider.length) return;
            
            // Get price range from products (you might want to make this dynamic)
            const minPrice = 0;
            const maxPrice = 1000;
            
            $priceSlider.slider({
                range: true,
                min: minPrice,
                max: maxPrice,
                values: [minPrice, maxPrice],
                slide: function(event, ui) {
                    $('#min-price').val(ui.values[0]);
                    $('#max-price').val(ui.values[1]);
                },
                stop: function(event, ui) {
                    // Auto-apply filter on slider change
                    setTimeout(loadResults, 300);
                }
            });
            
            // Initialize input values
            $('#min-price').val(minPrice);
            $('#max-price').val(maxPrice);
            
            // Handle manual input changes
            $('#min-price, #max-price').on('change', function() {
                const minVal = parseInt($('#min-price').val()) || minPrice;
                const maxVal = parseInt($('#max-price').val()) || maxPrice;
                
                $priceSlider.slider('values', [minVal, maxVal]);
                setTimeout(loadResults, 300);
            });
        }
        
        function setupEventHandlers() {
            // Apply filters button
            $('#apply-filters').on('click', function(e) {
                e.preventDefault();
                currentPage = 1;
                loadResults();
            });
            
            // Reset filters button
            $('#reset-filters').on('click', function(e) {
                e.preventDefault();
                resetFilters();
            });
            
            // Category checkboxes
            $('.category-checkboxes input[type="checkbox"]').on('change', function() {
                // Auto-apply filter on checkbox change
                setTimeout(function() {
                    currentPage = 1;
                    loadResults();
                }, 100);
            });
            
            // Search input with debounce
            let searchTimeout;
            $('#search-input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentPage = 1;
                    loadResults();
                }, 500);
            });
            
            // Date range filter
            $('#date-range').on('change', function() {
                currentPage = 1;
                loadResults();
            });
            
            // Sort by dropdown
            $('#sort-by').on('change', function() {
                currentPage = 1;
                loadResults();
            });
            
            // Pagination clicks (delegated event)
            $(document).on('click', '#filter-pagination a.page-numbers', function(e) {
                e.preventDefault();
                const href = $(this).attr('href');
                const pageMatch = href.match(/paged=(\d+)/);
                if (pageMatch) {
                    currentPage = parseInt(pageMatch[1]);
                    loadResults();
                    
                    // Scroll to results
                    $('html, body').animate({
                        scrollTop: $('#filter-results-container').offset().top - 100
                    }, 500);
                }
            });
            
            // Handle enter key in search input
            $('#search-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    currentPage = 1;
                    loadResults();
                }
            });
            
            // Handle enter key in price inputs
            $('#min-price, #max-price').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const minVal = parseInt($('#min-price').val()) || 0;
                    const maxVal = parseInt($('#max-price').val()) || 1000;
                    $('#price-slider').slider('values', [minVal, maxVal]);
                    currentPage = 1;
                    loadResults();
                }
            });
        }
        
        function resetFilters() {
            // Reset checkboxes
            $('.category-checkboxes input[type="checkbox"]').prop('checked', false);
            
            // Reset search
            $('#search-input').val('');
            
            // Reset date range
            $('#date-range').val('');
            
            // Reset price slider
            const $priceSlider = $('#price-slider');
            if ($priceSlider.length) {
                const min = $priceSlider.slider('option', 'min');
                const max = $priceSlider.slider('option', 'max');
                $priceSlider.slider('values', [min, max]);
                $('#min-price').val(min);
                $('#max-price').val(max);
            }
            
            // Reset sort
            $('#sort-by').val('date');
            
            // Reset page
            currentPage = 1;
            
            // Load results
            loadResults();
        }
        
        function loadResults() {
            if (isLoading) return;
            
            isLoading = true;
            const $resultsContainer = $('#filter-results-container');
            const $pagination = $('#filter-pagination');
            const $resultsCount = $('#results-found');
            
            // Show loading state
            $resultsContainer.addClass('loading');
            $resultsCount.text('Loading...');
            
            // Gather filter data
            const filterData = {
                action: 'filter_posts',
                nonce: afp_ajax.nonce,
                post_type: postType,
                posts_per_page: postsPerPage,
                paged: currentPage,
                categories: [],
                min_price: $('#min-price').val() || 0,
                max_price: $('#max-price').val() || 999999,
                date_range: $('#date-range').val(),
                search_term: $('#search-input').val(),
                sort_by: $('#sort-by').val()
            };
            
            // Collect selected categories
            $('.category-checkboxes input[type="checkbox"]:checked').each(function() {
                filterData.categories.push($(this).val());
            });
            
            // AJAX request
            $.ajax({
                url: afp_ajax.ajax_url,
                type: 'POST',
                data: filterData,
                success: function(response) {
                    if (response.success) {
                        // Update results
                        $resultsContainer.html(response.data.posts);
                        $pagination.html(response.data.pagination);
                        
                        // Update results count
                        const foundPosts = response.data.found_posts;
                        let countText = foundPosts + ' result';
                        if (foundPosts !== 1) countText += 's';
                        countText += ' found';
                        $resultsCount.text(countText);
                        
                        // Animate new results
                        $resultsContainer.find('.filter-post-item').each(function(index) {
                            $(this).css({
                                opacity: 0,
                                transform: 'translateY(20px)'
                            }).delay(index * 100).animate({
                                opacity: 1
                            }, 300).animate({
                                transform: 'translateY(0)'
                            }, 300);
                        });
                        
                    } else {
                        $resultsContainer.html('<div class="no-results">Error loading results. Please try again.</div>');
                        $resultsCount.text('Error');
                    }
                },
                error: function() {
                    $resultsContainer.html('<div class="no-results">Error loading results. Please try again.</div>');
                    $resultsCount.text('Error');
                },
                complete: function() {
                    isLoading = false;
                    $resultsContainer.removeClass('loading');
                }
            });
        }
    }
    
    // Initialize filter when document is ready
    initializeFilter();
    
    // Re-initialize if content is loaded dynamically
    $(document).on('advanced_filter_loaded', function() {
        initializeFilter();
    });
    
    // Additional utility functions
    
    // Smooth scroll to element
    function smoothScrollTo(element, offset = 0) {
        if ($(element).length) {
            $('html, body').animate({
                scrollTop: $(element).offset().top - offset
            }, 500);
        }
    }
    
    // Format price for display
    function formatPrice(price, currency = '$') {
        return currency + parseFloat(price).toFixed(2);
    }
    
    // Debounce function for performance
    function debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.filterData) {
            // Restore filter state from history
            restoreFilterState(e.state.filterData);
        }
    });
    
    function saveFilterState() {
        const filterData = {
            categories: [],
            search: $('#search-input').val(),
            dateRange: $('#date-range').val(),
            minPrice: $('#min-price').val(),
            maxPrice: $('#max-price').val(),
            sortBy: $('#sort-by').val()
        };
        
        $('.category-checkboxes input[type="checkbox"]:checked').each(function() {
            filterData.categories.push($(this).val());
        });
        
        // Save to browser history
        const url = new URL(window.location);
        url.searchParams.set('filter_state', btoa(JSON.stringify(filterData)));
        history.pushState({filterData: filterData}, '', url);
    }
    
    function restoreFilterState(filterData) {
        // Restore checkboxes
        $('.category-checkboxes input[type="checkbox"]').prop('checked', false);
        filterData.categories.forEach(function(categoryId) {
            $('.category-checkboxes input[value="' + categoryId + '"]').prop('checked', true);
        });
        
        // Restore other filters
        $('#search-input').val(filterData.search || '');
        $('#date-range').val(filterData.dateRange || '');
        $('#sort-by').val(filterData.sortBy || 'date');
        
        // Restore price slider
        if (filterData.minPrice && filterData.maxPrice) {
            $('#min-price').val(filterData.minPrice);
            $('#max-price').val(filterData.maxPrice);
            if ($('#price-slider').length) {
                $('#price-slider').slider('values', [filterData.minPrice, filterData.maxPrice]);
            }
        }
        
        // Reload results
        initializeFilter();
    }
    
    // Load filter state from URL on page load
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const filterState = urlParams.get('filter_state');
        if (filterState) {
            try {
                const filterData = JSON.parse(atob(filterState));
                restoreFilterState(filterData);
            } catch (e) {
                console.log('Could not restore filter state from URL');
            }
        }
    });
    
    // Accessibility improvements
    $(document).ready(function() {
        // Add ARIA labels
        $('#search-input').attr('aria-label', 'Search posts');
        $('#date-range').attr('aria-label', 'Filter by date range');
        $('#sort-by').attr('aria-label', 'Sort results by');
        $('#min-price').attr('aria-label', 'Minimum price');
        $('#max-price').attr('aria-label', 'Maximum price');
        
        // Add keyboard navigation for checkboxes
        $('.checkbox-label').on('keydown', function(e) {
            if (e.which === 32) { // Space key
                e.preventDefault();
                $(this).find('input[type="checkbox"]').click();
            }
        });
        
        // Focus management
        $('#apply-filters').on('click', function() {
            setTimeout(function() {
                $('#filter-results-container').attr('tabindex', '-1').focus();
            }, 500);
        });
    });
    
    // Performance optimizations
    
    // Lazy loading for images
    function lazyLoadImages() {
        const images = document.querySelectorAll('.filter-post-item img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
    
    // Call lazy loading after results are loaded
    $(document).on('ajaxComplete', function(event, xhr, settings) {
        if (settings.data && settings.data.includes('action=filter_posts')) {
            setTimeout(lazyLoadImages, 100);
        }
    });
    
    // Mobile-specific enhancements
    if (window.innerWidth <= 768) {
        // Collapsible filter sections on mobile
        $('.filter-section h4').on('click', function() {
            $(this).next().slideToggle();
            $(this).toggleClass('collapsed');
        });
        
        // Add collapse indicators
        $('.filter-section h4').append('<span class="collapse-indicator">−</span>');
        
        // Initially collapse some sections on mobile
        $('.price-filter, .date-filter').find('h4').next().hide();
        $('.price-filter h4, .date-filter h4').addClass('collapsed');
        $('.collapsed .collapse-indicator').text('+');
    }
    
    // Update collapse indicators
    $(document).on('click', '.filter-section h4', function() {
        const indicator = $(this).find('.collapse-indicator');
        if ($(this).hasClass('collapsed')) {
            indicator.text('+');
        } else {
            indicator.text('−');
        }
    });
    
    // Export functions for external use
    window.AdvancedFilter = {
        reload: initializeFilter,
        smoothScrollTo: smoothScrollTo,
        formatPrice: formatPrice,
        debounce: debounce
    };
});

// CSS for collapse indicators
const mobileCSS = `
@media (max-width: 768px) {
    .filter-section h4 {
        cursor: pointer;
        position: relative;
        padding-right: 25px;
    }
    
    .collapse-indicator {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        font-weight: bold;
        color: #007cba;
    }
    
    .filter-section h4.collapsed + * {
        display: none;
    }
}
`;

// Inject mobile CSS
if (window.innerWidth <= 768) {
    const style = document.createElement('style');
    style.textContent = mobileCSS;
    document.head.appendChild(style);
}