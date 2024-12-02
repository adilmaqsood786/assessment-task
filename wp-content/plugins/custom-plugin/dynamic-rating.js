jQuery(document).ready(function($) {
    // Only run on product pages
    if ($('body').hasClass('single-product')) {
        const productId = $('input[name="add-to-cart"]').val() || $('button[name="add-to-cart"]').val();
        
        // Create rating container
        const $ratingContainer = $('<div>', {
            class: 'dynamic-rating-container',
            css: {
                'margin': '10px 0'
            }
        });
        
        // Insert container before Add to Cart button
        $('.single_add_to_cart_button').after($ratingContainer);
        
        // Function to update rating
        function updateRating() {
            $.ajax({
                url: productRatingAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_product_rating',
                    product_id: productId,
                    nonce: productRatingAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $ratingContainer.html(response.data.rating_html);
                    }
                },
                error: function() {
                    console.error('Failed to fetch rating');
                }
            });
        }
        
        // Initial rating update
        updateRating();
        
        // Update rating every 30 seconds
        setInterval(updateRating, 30000);
    }
});