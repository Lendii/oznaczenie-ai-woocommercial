<?php
/**
 * Plugin Name: AI Content Label dla WooCommerce
 * Description: Oznacza produkty jako "AI generated" / "AI modified" zgodnie z Art. 50 AI Act.
 * Version: 1.2
 * Requires PHP: 7.4
 * Text Domain: ai-content-label
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Ochrona przed podwójnym załadowaniem (np. plik w mu-plugins + snippet w WPCode).
if ( defined( 'AI_CONTENT_LABEL_LOADED' ) ) return;
define( 'AI_CONTENT_LABEL_LOADED', true );

/**
 * Zwraca 'ai_generated', 'ai_modified' albo '' (brak oznaczenia).
 */
if ( ! function_exists( 'ai_content_label_get' ) ) {
    function ai_content_label_get( $product ) {
        if ( ! $product instanceof WC_Product ) return '';
        $label = $product->get_meta( '_ai_content_label' );
        return in_array( $label, [ 'ai_generated', 'ai_modified' ], true ) ? $label : '';
    }
}

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
    // Nonce weryfikuje WooCommerce przed wywołaniem tego hooka.
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    $product = wc_get_product( $post_id );
    if ( ! $product ) return;
    $value = isset( $_POST['_ai_content_label'] ) ? sanitize_text_field( wp_unslash( $_POST['_ai_content_label'] ) ) : '';
    if ( ! in_array( $value, [ '', 'ai_generated', 'ai_modified' ], true ) ) return;
    if ( $value === '' ) {
        $product->delete_meta_data( '_ai_content_label' );
    } else {
        $product->update_meta_data( '_ai_content_label', $value );
    }
    $product->save_meta_data();
} );

// Lista sklepu: otwarcie wrappera przed miniaturą (priorytet 9)...
add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( ! ai_content_label_get( $product ) ) return;
    echo '<span class="ai-image-wrap">';
}, 9 );

// ...i badge z zamknięciem wrappera po miniaturze (priorytet 11).
add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( ! ai_content_label_get( $product ) ) return;
    echo '<span class="ai-content-badge ai-content-badge--loop">AI</span></span>';
}, 11 );

// Strona produktu: badge dokładany do galerii przez JS, bo każdy motyw ma inną strukturę galerii.
add_action( 'wp_footer', function () {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) return;
    $product = wc_get_product( get_queried_object_id() );
    $label   = ai_content_label_get( $product );
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
        badge.textContent = <?php echo wp_json_encode( $text ); ?>;
        gallery.appendChild(badge);
    });
    </script>
    <?php
}, 99 );

add_action( 'woocommerce_single_product_summary', function () {
    global $product;
    $label = ai_content_label_get( $product );
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
