<?php
/**
 * Plugin Name: AI Content Label dla WooCommerce
 * Description: Oznacza produkty jako "AI generated" / "AI modified" zgodnie z Art. 50 AI Act.
 * Version: 1.1
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'woocommerce_product_options_general_product_data', function () {
    echo '<div class="options_group">';
    woocommerce_wp_select( [
        'id'          => '_ai_content_label',
        'label'       => __( 'Oznaczenie AI (Art. 50 AI Act)', 'ai-content-label' ),
        'options'     => [
            ''             => __( 'Brak — treść nie wymaga oznaczenia', 'ai-content-label' ),
            'ai_generated' => __( 'AI GENERATED — zdjęcie/grafika w całości wygenerowana', 'ai-content-label' ),
            'ai_modified'  => __( 'AI MODIFIED — zdjęcie zmodyfikowane przez AI', 'ai-content-label' ),
        ],
        'desc_tip'    => true,
        'description' => __( 'Wybierz, jeśli główne zdjęcie produktu jest realistyczną grafiką AI mogącą zostać wzięta za autentyczne zdjęcie.', 'ai-content-label' ),
    ] );
    echo '</div>';
} );

add_action( 'woocommerce_process_product_meta', function ( $post_id ) {
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $allowed = [ '', 'ai_generated', 'ai_modified' ];
    $value   = isset( $_POST['_ai_content_label'] ) ? sanitize_text_field( wp_unslash( $_POST['_ai_content_label'] ) ) : '';
    if ( ! in_array( $value, $allowed, true ) ) return;
    update_post_meta( $post_id, '_ai_content_label', $value );
} );

add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( ! $product instanceof WC_Product ) return;
    $label = get_post_meta( $product->get_id(), '_ai_content_label', true );
    if ( ! $label ) return;
    echo '<span class="ai-image-wrap">';
}, 9 );

add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( ! $product instanceof WC_Product ) return;
    $label = get_post_meta( $product->get_id(), '_ai_content_label', true );
    if ( ! $label ) return;
    echo '<span class="ai-content-badge ai-content-badge--loop">AI</span></span>';
}, 11 );

add_action( 'wp_footer', function () {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) return;
    $product_id = get_queried_object_id();
    if ( ! $product_id ) return;
    $label = get_post_meta( $product_id, '_ai_content_label', true );
    if ( ! $label ) return;
    $text = $label === 'ai_generated' ? 'AI GENERATED' : 'AI MODIFIED';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var selectors = [
            '.woocommerce-product-gallery',
            '.wc-block-product-gallery',
            '.kadence-product-gallery',
            '.kt-product-gallery',
            '.product .images',
            '.woocommerce-product-gallery__wrapper'
        ];
        var gallery = null;
        for (var i = 0; i < selectors.length; i++) {
            gallery = document.querySelector(selectors[i]);
            if (gallery) break;
        }
        if (!gallery) {
            gallery = document.querySelector('.product') || document.querySelector('main');
        }
        if (!gallery) return;
        gallery.style.position = 'relative';
        var badge = document.createElement('span');
        badge.className = 'ai-content-badge';
        badge.textContent = '<?php echo esc_js( $text ); ?>';
        gallery.appendChild(badge);
    });
    </script>
    <?php
}, 99 );

add_action( 'woocommerce_single_product_summary', function () {
    global $product;
    if ( ! $product instanceof WC_Product ) return;
    $label = get_post_meta( $product->get_id(), '_ai_content_label', true );
    if ( ! $label ) return;
    $desc = $label === 'ai_generated'
        ? __( 'Zdjęcie tego produktu zostało w całości wygenerowane przez sztuczną inteligencję.', 'ai-content-label' )
        : __( 'Zdjęcie tego produktu zostało zmodyfikowane przy użyciu sztucznej inteligencji.', 'ai-content-label' );
    echo '<p class="ai-content-note">' . esc_html( $desc ) . '</p>';
}, 25 );

add_action( 'wp_head', function () { ?>
<style>
.ai-content-badge {
    position: absolute;
    bottom: 12px;
    left: 12px;
    z-index: 5;
    background: #F3E9FF;
    color: #5B21B6;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    padding: 4px 10px;
    border-radius: 4px;
    pointer-events: none;
}
.ai-content-badge--loop {
    position: absolute;
    bottom: 8px;
    left: 8px;
    top: auto;
    background: #F3E9FF;
    color: #5B21B6;
    font-family: inherit;
    font-size: 9px;
    padding: 2px 6px;
    z-index: 2;
}
.ai-image-wrap {
    position: relative;
    display: block;
}
.ai-content-note {
    font-size: 12px;
    color: #666;
    font-style: italic;
    margin-top: 8px;
}
</style>
<?php } );
