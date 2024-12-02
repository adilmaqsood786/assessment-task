<?php
/**
 * Plugin Name: WooCommerce Product Reviews Extended
 * Description: Adds custom product reviews, checkout fields, and product statistics
 * Version: 1.0
 * Author: Your Name
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) exit;

class WooCommerce_Product_Reviews_Extended {
    
    public function __construct() {
        // Initialize plugin
        add_action('init', array($this, 'register_product_review_cpt'));
        add_action('add_meta_boxes', array($this, 'add_product_review_metaboxes'));
        // add_action('save_post_product_review', array($this, 'save_product_review_meta'));
        add_action('woocommerce_after_order_notes', array($this, 'add_custom_checkout_field'));
        add_action('woocommerce_checkout_process', array($this, 'validate_custom_checkout_field'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_custom_checkout_field'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_checkout_styles'));
        add_action('woocommerce_admin_order_data_after_order_notes', array($this, 'display_custom_field_order_admin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
//        add_shortcode('product_review_form', array($this, 'render_review_form'));
        add_filter('the_content', array($this, 'display_review_form_on_page'));

        add_shortcode('product_sold_count', array($this, 'product_sold_count_shortcode'));
        add_action('wp_ajax_get_product_rating', array($this, 'get_product_rating_ajax'));
        add_action('wp_ajax_nopriv_get_product_rating', array($this, 'get_product_rating_ajax'));
        //add_action('save_post_product_review', array($this, 'send_admin_email_notification'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_review_form_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_rating_styles'));
        add_action('init', array($this, 'handle_review_form_submission'));
        add_action('wp_head', array($this, 'add_fontawesome_cdn'), 1);
        add_filter('manage_product_review_posts_columns', array($this, 'set_custom_product_review_columns'));
        add_action('manage_product_review_posts_custom_column', array($this, 'custom_product_review_column'), 10, 2);
        add_filter('manage_edit-product_review_sortable_columns', array($this, 'set_custom_product_review_sortable_columns'));
        add_action('pre_get_posts', array($this, 'custom_product_review_orderby'));

    }
    public function display_review_form_on_page($content) {
        // Check if it's the specific page where the form should be displayed
        if (is_page('test-page')) { // Replace 'product-review' with the slug of your target page
            $content .= $this->render_review_form();
        }
        return $content;
    }
    
    public function set_custom_product_review_columns($columns) {
        $new_columns = array();
        
        // Reorder and add new columns
        foreach($columns as $key => $title) {
            if ($key == 'title') { // Skip the original title column
                continue;
            }
            if ($key == 'date') { // Add our custom columns before the date
                $new_columns['reviewer_name'] = __('Reviewer Name', 'woocommerce');
                $new_columns['product'] = __('Product', 'woocommerce');
                $new_columns['rating'] = __('Rating', 'woocommerce');
                $new_columns['review'] = __('Review', 'woocommerce');
            }
            $new_columns[$key] = $title;
        }
        
        return $new_columns;
    }

    public function custom_product_review_column($column, $post_id) {
        switch ($column) {
            case 'review':
                $post = get_post($post_id);
                $content = $post->post_content;
                // Truncate content if too long
                if (strlen($content) > 150) {
                    $content = substr($content, 0, 147) . '...';
                }
                echo '<div class="review-content">' . esc_html($content) . '</div>';
                break;
            case 'reviewer_name':
                $reviewer_name = get_post_meta($post_id, '_reviewer_name', true);
                echo esc_html($reviewer_name);
                break;
                
            case 'product':
                $product_id = get_post_meta($post_id, '_product_id', true);
                if ($product_id) {
                    $product = wc_get_product($product_id);
                    if ($product) {
                        echo '<a href="' . esc_url(get_edit_post_link($product_id)) . '">' 
                            . esc_html($product->get_name()) . '</a>';
                    } else {
                        echo __('Product not found', 'woocommerce');
                    }
                }
                break;
                
            case 'rating':
                $rating = get_post_meta($post_id, '_rating', true);
                if ($rating) {
                    // Display stars using Font Awesome
                    echo '<div class="review-rating-display">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                            echo '<span class="star-rated"><i class="fas fa-star"></i></span>';
                        } else {
                            echo '<span class="star-empty"><i class="far fa-star"></i></span>';
                        }
                    }
                    echo ' (' . $rating . '/5)';
                    echo '</div>';
                }
                break;
        }
    }

    public function set_custom_product_review_sortable_columns($columns) {
        $columns['reviewer_name'] = 'reviewer_name';
        $columns['rating'] = 'rating';
        $columns['product'] = 'product';
        return $columns;
    }

    public function custom_product_review_orderby($query) {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'product_review') {
            return;
        }

        $orderby = $query->get('orderby');

        switch ($orderby) {
            case 'reviewer_name':
                $query->set('meta_key', '_reviewer_name');
                $query->set('orderby', 'meta_value');
                break;
                
            case 'rating':
                $query->set('meta_key', '_rating');
                $query->set('orderby', 'meta_value_num');
                break;
                
            case 'product':
                $query->set('meta_key', '_product_id');
                $query->set('orderby', 'meta_value_num');
                break;
        }
    }
    
    public function add_fontawesome_cdn() {
        ?>
        <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        </noscript>
        <?php
    }
    
    public function enqueue_checkout_styles() {
        if (is_checkout()) {
            wp_register_style(
                'custom-checkout-styles',
                false,
                array(),
                '1.0'
            );
            
            $custom_css = "
                .custom-special-instructions {
                    margin: 20px 0 30px;
                    padding: 20px;
                    background: #f8f8f8;
                    border-radius: 4px;
                    border: 1px solid #e0e0e0;
                }
                
                .custom-special-instructions h3 {
                    margin: 0 0 15px;
                    padding: 0;
                    font-size: 1.2em;
                    color: #333;
                }
                
                .custom-special-instructions textarea {
                    width: 100%;
                    padding: 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    min-height: 100px;
                    font-size: 14px;
                    line-height: 1.5;
                    background: #fff;
                }
                
                .custom-special-instructions textarea:focus {
                    border-color: #666;
                    outline: none;
                    box-shadow: 0 0 5px rgba(0,0,0,0.1);
                }
                
                .custom-special-instructions .description {
                    margin-top: 8px;
                    font-size: 0.9em;
                    color: #666;
                    font-style: italic;
                }
                
                .custom-special-instructions.has-error textarea {
                    border-color: #dc3232;
                }
                
                .custom-special-instructions .error-message {
                    color: #dc3232;
                    font-size: 0.9em;
                    margin-top: 5px;
                }
            ";
            
            wp_add_inline_style('custom-checkout-styles', $custom_css);
            wp_enqueue_style('custom-checkout-styles');
        }
    }

    // 1. Custom Post Type Creation
    public function register_product_review_cpt() {
        $args = array(
            'labels' => array(
                'name' => 'Product Reviews',
                'singular_name' => 'Product Review'
            ),
            'public' => true,
            'menu_icon' => 'dashicons-star-filled',
            'supports' => array('title', 'editor'),
            'show_in_menu' => true
        );
        register_post_type('product_review', $args);
    }

    public function add_product_review_metaboxes() {
        add_meta_box(
            'product_review_meta',
            'Review Details',
            array($this, 'render_review_metabox'),
            'product_review',
            'normal',
            'high'
        );
    }
    
    public function enqueue_review_form_styles() {
        wp_register_style(
            'product-review-form-styles',
            false,
            array(),
            '1.0'
        );
        
        $custom_css = "
            .product-review-form {
                max-width: 600px;
                margin: 20px auto;
                padding: 25px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .product-review-form p {
                margin-bottom: 20px;
            }

            .product-review-form label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #333;
            }

            .product-review-form input[type='text'],
            .product-review-form select,
            .product-review-form textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                line-height: 1.4;
                box-sizing: border-box;
                transition: border-color 0.3s ease;
            }

            .product-review-form input[type='text']:focus,
            .product-review-form select:focus,
            .product-review-form textarea:focus {
                border-color: #2271b1;
                outline: none;
                box-shadow: 0 0 5px rgba(34,113,177,0.2);
            }

            .product-review-form textarea {
                min-height: 120px;
                resize: vertical;
            }

            .product-review-form input[type='submit'] {
                background-color: #2271b1;
                color: #fff;
                padding: 12px 24px;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .product-review-form input[type='submit']:hover {
                background-color: #135e96;
            }

            .product-review-form input[type='submit']:active {
                transform: translateY(1px);
            }

            .review-success-message {
                background-color: #d4edda;
                color: #155724;
                padding: 15px;
                border-radius: 4px;
                border: 1px solid #c3e6cb;
                margin-bottom: 20px;
                text-align: center;
                font-weight: 500;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .product-review-form {
                    padding: 15px;
                    margin: 10px;
                }
            }
        ";
        
        wp_add_inline_style('product-review-form-styles', $custom_css);
        wp_enqueue_style('product-review-form-styles');
    }
    
    public function enqueue_rating_styles() {
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'
        );
        wp_register_style(
            'product-rating-styles',
            false,
            array(),
            '1.0'
        );
        
        $custom_css = "
            .custom-star-rating {
                background: #fff;
                padding: 15px;
                border-radius: 4px;
                margin: 10px 0;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .custom-star-rating .rating-summary {
                font-size: 16px;
                color: #333;
                margin-bottom: 8px;
                display: block;
            }

            .custom-star-rating .stars {
                display: inline-flex;
                align-items: center;
                gap: 2px;
            }

            .custom-star-rating .star {
                color: #ffd700;
                font-size: 24px;
                line-height: 1;
                display: inline-block;
                position: relative;
                vertical-align: middle;
                font-family: 'Font Awesome 5 Free';
                font-weight: 900;
            }

            .custom-star-rating .star.empty {
                color: #ccc;
            }

            .custom-star-rating .star.half {
                position: relative;
            }

            .custom-star-rating .star.half:after {
                content: '\\f089';
                position: absolute;
                left: 0;
                top: 0;
                width: 50%;
                overflow: hidden;
                color: #ffd700;
            }

            .no-reviews {
                color: #666;
                font-style: italic;
                padding: 10px;
                text-align: center;
                background: #f5f5f5;
                border-radius: 4px;
            }
        ";
        
        wp_add_inline_style('product-rating-styles', $custom_css);
        wp_enqueue_style('product-rating-styles');
        
        // Enqueue Font Awesome for better star icons
        
    }


    public function render_review_metabox($post) {
        $reviewer_name = get_post_meta($post->ID, '_reviewer_name', true);
        $product_id = get_post_meta($post->ID, '_product_id', true);
        $rating = get_post_meta($post->ID, '_rating', true);
        
        wp_nonce_field('product_review_meta', 'product_review_meta_nonce');
        ?>
        <p>
            <label for="reviewer_name">Reviewer Name:</label>
            <input type="text" id="reviewer_name" name="reviewer_name" value="<?php echo esc_attr($reviewer_name); ?>">
        </p>
        <p>
            <label for="product_id">Product:</label>
            <select name="product_id" id="product_id">
                <?php
                $products = wc_get_products(array('status' => 'publish', 'limit' => -1));
                foreach ($products as $product) {
                    echo '<option value="' . esc_attr($product->get_id()) . '" ' . selected($product_id, $product->get_id(), false) . '>' 
                        . esc_html($product->get_name()) . '</option>';
                }
                ?>
            </select>
        </p>
        <p>
            <label for="rating">Rating:</label>
            <select name="rating" id="rating">
                <?php for ($i = 1; $i <= 5; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php selected($rating, $i); ?>><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <?php
    }

    public function handle_review_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['review_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['review_nonce'], 'submit_product_review')) {
            wp_die('Invalid nonce specified', 'Error', array('response' => 403));
        }

        // Validate required fields
        if (empty($_POST['reviewer_name']) || empty($_POST['product_id']) || 
            empty($_POST['rating']) || empty($_POST['review'])) {
            wp_die('Please fill in all required fields', 'Error', array('response' => 400));
        }

        // Create new review post
        $review_data = array(
            'post_title'    => wp_strip_all_tags($_POST['reviewer_name'] . "'s Review"),
            'post_content'  => wp_kses_post($_POST['review']),
            'post_status'   => 'publish',
            'post_type'     => 'product_review'
        );

        // Insert the review
        $post_id = wp_insert_post($review_data);

        if (!is_wp_error($post_id)) {
            // Save review meta data
            update_post_meta($post_id, '_reviewer_name', sanitize_text_field($_POST['reviewer_name']));
            update_post_meta($post_id, '_product_id', absint($_POST['product_id']));
            update_post_meta($post_id, '_rating', absint($_POST['rating']));
            $this->send_admin_email_notification(sanitize_text_field($_POST['reviewer_name']),$_POST['review'],absint($_POST['rating']),absint($_POST['product_id']))
;
            // Redirect to success page or reload with success message
            $redirect_url = add_query_arg('review_submitted', 'true', wp_get_referer());
            wp_safe_redirect($redirect_url);
            exit;
        }else{
            $redirect_url = add_query_arg('review_submitted', 'false', wp_get_referer());
            wp_safe_redirect($redirect_url);
            exit;
        }
    }

    public function render_review_form() {
        ob_start();
        
        if (isset($_GET['review_submitted']) && $_GET['review_submitted'] === 'true') {
            echo '<div class="review-success-message">Thank you! Your review has been submitted successfully.</div>';
        }
        ?>
        <form id="product-review-form" class="product-review-form" method="post" action="">
            <?php wp_nonce_field('submit_product_review', 'review_nonce'); ?>
            <p>
                <label for="reviewer_name">Enter your Name</label>
                <input type="text" id="reviewer_name" name="reviewer_name" required>
            </p>
            <p>
                <label for="product_id">Select your product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select a product...</option>
                    <?php
                    $products = wc_get_products(array('status' => 'publish', 'limit' => -1));
                    foreach ($products as $product) {
                        echo '<option value="' . esc_attr($product->get_id()) . '">' 
                            . esc_html($product->get_name()) . '</option>';
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="rating">Select your rating stars</label>
                <select id="rating" name="rating" required>
                    <?php for ($i = 1; $i <= 5; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?></option>
                    <?php endfor; ?>
                </select>
            </p>
            <p>
                <label for="review">Enter your review</label>
                <textarea id="review" name="review" required></textarea>
            </p>
            <p>
                <input type="submit" value="Submit Review">
            </p>
        </form>
        <?php
        return ob_get_clean();
    }


   
    public function send_admin_email_notification($reviewer_name,$review,$rating,$product_id) {
        // if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        
        // $post = get_post($post_id);
        // if ($post->post_type !== 'product_review') return;

        // Gather review data with validation
        // $reviewer_name = get_post_meta($post_id, '_reviewer_name', true);
        // $product_id = get_post_meta($post_id, '_product_id', true);
        // $rating = intval(get_post_meta($post_id, '_rating', true));
        $product = wc_get_product($product_id);
        
        // Validate data
        $reviewer_name = !empty($reviewer_name) ? $reviewer_name : 'Anonymous';
        $product_name = ($product && $product->get_name()) ? $product->get_name() : 'Unknown Product';
        $rating = max(1, min(5, $rating));
        
        // Format the rating stars
        $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

        $message_html = '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; color: #333;">
            <div style="background-color: #f8f9fa; padding: 20px; border-bottom: 3px solid #0073aa;">
                <h1 style="color: #0073aa; margin: 0; font-size: 24px;">New Product Review</h1>
            </div>
            
            <div style="padding: 20px; background-color: #ffffff; border: 1px solid #e5e5e5;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee; width: 120px;"><strong>Reviewer</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">' . esc_html($reviewer_name) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;"><strong>Product</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">' . esc_html($product_name) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;"><strong>Rating</strong></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eeeeee;">
                            <span style="color: #FFB900;">' . $stars . '</span>
                            <span style="margin-left: 10px; color: #666;">(' . $rating . ' out of 5)</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px;"><strong>Review</strong></td>
                        <td style="padding: 10px;">' . nl2br(esc_html($review)) . '</td>
                    </tr>
                </table>
            </div>
            
            <div style="padding: 20px; background-color: #f8f9fa; text-align: center; font-size: 14px; color: #666;">
                <p style="margin: 0;">You can view all reviews in your 
                   <a href="' . esc_url(admin_url('edit.php?post_type=product_review')) . '" 
                      style="color: #0073aa; text-decoration: none;">WordPress admin panel</a>
                </p>
            </div>
        </div>';

        // Email setup
        $to = get_option('admin_email');
        $subject = sprintf('[%s] New Product Review Submitted', get_bloginfo('name'));
        $headers = array(
            'Content-Type: text/html; charset=UTF-8'
        );

        // Send email
        wp_mail($to, $subject, $message_html, $headers);
    }

    // 2. Custom Checkout Field
    public function add_custom_checkout_field($checkout) {
        // Get any existing value
        $current_value = $checkout->get_value('special_instructions') ? 
                        $checkout->get_value('special_instructions') : 
                        WC()->session->get('special_instructions');
        
        echo '<div class="custom-special-instructions">';
        echo '<h3>' . __('Special Instructions', 'woocommerce') . '</h3>';
        
        woocommerce_form_field('special_instructions', array(
            'type' => 'textarea',
            'class' => array('special-instructions-field'),
            'clear' => true,
//            'description' => __('Add any special instructions or requests for your order here.', 'woocommerce'),
            'placeholder' => __('Enter your special instructions...', 'woocommerce'),
            'custom_attributes' => array(
                'data-autoresize' => 'true',
                'rows' => '4'
            )
        ), $current_value);
        
        echo '</div>';

        // Add auto-resize script
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Save instructions when changed
                $('#special_instructions').on('change', function() {
                    $.ajax({
                        type: 'POST',
                        url: wc_checkout_params.ajax_url,
                        data: {
                            action: 'save_special_instructions',
                            instructions: $(this).val(),
                            security: '<?php echo wp_create_nonce("save-special-instructions"); ?>'
                        }
                    });
                });

                // Auto-resize textarea
                function autoResize(textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = textarea.scrollHeight + 'px';
                }

                const textarea = document.getElementById('special_instructions');
                if (textarea) {
                    textarea.addEventListener('input', function() {
                        autoResize(this);
                    });
                    // Initial resize
                    autoResize(textarea);
                }
            });
        </script>
        <?php
    }
    
    public function validate_custom_checkout_field() {
        // Optional validation if needed
        // if (isset($_POST['special_instructions']) && empty($_POST['special_instructions'])) {
        //     wc_add_notice(__('Please enter special instructions.', 'woocommerce'), 'error');
        // }
    }

    public function save_custom_checkout_field($order_id) {
        if (isset($_POST['special_instructions']) && !empty($_POST['special_instructions'])) {
            $instructions = sanitize_textarea_field($_POST['special_instructions']);
            update_post_meta($order_id, '_special_instructions', $instructions);
            
            // Add instructions to order notes as well
            $order = wc_get_order($order_id);
            if ($order) {
                $order->add_order_note(
                    __('Special Instructions: ', 'woocommerce') . $instructions,
                    0, // Not customer-facing
                    true // Added by system
                );
            }
        }
    }

    // Add AJAX handler for saving instructions to session
    public function init_ajax() {
        add_action('wp_ajax_save_special_instructions', array($this, 'ajax_save_special_instructions'));
        add_action('wp_ajax_nopriv_save_special_instructions', array($this, 'ajax_save_special_instructions'));
    }

    public function ajax_save_special_instructions() {
        check_ajax_referer('save-special-instructions', 'security');
        
        if (isset($_POST['instructions'])) {
            WC()->session->set('special_instructions', sanitize_textarea_field($_POST['instructions']));
        }
        wp_die();
    }


    public function display_custom_field_order_admin($order) {
        $special_instructions = get_post_meta($order->get_id(), '_special_instructions', true);
        if ($special_instructions) {
            echo '<p><strong>Special Instructions:</strong> ' . esc_html($special_instructions) . '</p>';
        }
    }

    // 3. Product Sold Count Function
    public function get_product_sold_count($product_id) {
        global $wpdb;
        
        $sold_count = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(order_item_meta.meta_value) 
            FROM {$wpdb->prefix}woocommerce_order_items as order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_status IN ('wc-completed', 'wc-processing')
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_qty'
            AND order_items.order_item_id IN (
                SELECT order_item_id 
                FROM {$wpdb->prefix}woocommerce_order_itemmeta 
                WHERE meta_key = '_product_id' 
                AND meta_value = %d
            )",
            $product_id
        ));
        
        return $sold_count ? $sold_count : 0;
    }

    public function product_sold_count_shortcode($atts) {
        $atts = shortcode_atts(array(
            'product_id' => 0
        ), $atts);

        if (empty($atts['product_id'])) return 'Product ID is required.';

        $product = wc_get_product($atts['product_id']);
        if (!$product) return 'Invalid product ID.';

        $sold_count = get_post_meta( $atts['product_id'], 'total_sales', true ); //$this->get_product_sold_count($atts['product_id']);
        return sprintf('%s has sold %d units.', $product->get_name(), $sold_count);
    }

    // 4. Dynamic Rating Display
    public function enqueue_scripts() {
        if (is_product()) {
            wp_enqueue_script('product-rating', plugins_url('dynamic-rating.js', __FILE__), array('jquery'), '2.0', true);
            wp_localize_script('product-rating', 'productRatingAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('get_product_rating')
            ));
        }
    }

    public function get_product_rating_ajax() {
        check_ajax_referer('get_product_rating', 'nonce');
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$product_id) {
            wp_send_json_error('Invalid product ID');
        }

        $args = array(
            'post_type' => 'product_review',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_product_id',
                    'value' => $product_id,
                    'compare' => '='
                )
            )
        );

        $reviews = get_posts($args);
        
        if (empty($reviews)) {
            wp_send_json_success(array(
                'rating' => 0,
                'rating_html' => '<div class="custom-star-rating"><div class="no-reviews">No reviews yet</div></div>'
            ));
        }

        $total_rating = 0;
        $count = 0;

        foreach ($reviews as $review) {
            $rating = get_post_meta($review->ID, '_rating', true);
            if ($rating) {
                $total_rating += intval($rating);
                $count++;
            }
        }

        $average_rating = $count > 0 ? round($total_rating / $count, 2) : 0;

        $rating_html = '<div class="custom-star-rating">';
        $rating_html .= sprintf(
            '<span class="rating-summary">Average Rating: %.1f out of 5 (%d review%s)</span>',
            $average_rating,
            $count,
            $count !== 1 ? 's' : ''
        );
        
        $rating_html .= '<div class="stars">';
        
        // Generate star display using Font Awesome icons
        $fullStars = floor($average_rating);
        $halfStar = ($average_rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        // Add full stars
        for ($i = 0; $i < $fullStars; $i++) {
            $rating_html .= '<span class="star"><i class="fas fa-star"></i></span>';
        }

        // Add half star if needed
        if ($halfStar) {
            $rating_html .= '<span class="star"><i class="fas fa-star-half-alt"></i></span>';
        }

        // Add empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            $rating_html .= '<span class="star empty"><i class="far fa-star"></i></span>';
        }

        $rating_html .= '</div></div>';

        wp_send_json_success(array(
            'rating' => $average_rating,
            'rating_html' => $rating_html,
            'review_count' => $count
        ));
    }

    // Add this new method to calculate rating for use elsewhere in the plugin
    public function get_product_average_rating($product_id) {
        $args = array(
            'post_type' => 'product_review',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_product_id',
                    'value' => $product_id,
                    'compare' => '='
                )
            )
        );

        $reviews = get_posts($args);
        
        if (empty($reviews)) {
            return 0;
        }

        $total_rating = 0;
        $count = 0;

        foreach ($reviews as $review) {
            $rating = get_post_meta($review->ID, '_rating', true);
            if ($rating) {
                $total_rating += intval($rating);
                $count++;
            }
        }

        return $count > 0 ? round($total_rating / $count, 2) : 0;
    }
}

 new WooCommerce_Product_Reviews_Extended();

