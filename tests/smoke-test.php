<?php
/**
 * Smoke test logiki wtyczki na atrapie WordPressa i WooCommerce (bez prawdziwego WP).
 * Uruchomienie: php tests/smoke-test.php
 * Sprawdza zapis, walidację, uprawnienia, wyjście hooków i podwójne załadowanie.
 * Nie zastępuje testu na prawdziwym sklepie.
 */
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
define( 'ABSPATH', '/x/' );

$GLOBALS['hooks']      = [];
$GLOBALS['is_product'] = true;
$GLOBALS['qid']        = 7;
$GLOBALS['can']        = true;

function add_action( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['hooks'][ $h ][ $p ][] = $cb; }
function run( $h ) {
    if ( empty( $GLOBALS['hooks'][ $h ] ) ) return;
    ksort( $GLOBALS['hooks'][ $h ] );
    foreach ( $GLOBALS['hooks'][ $h ] as $cbs ) foreach ( $cbs as $cb ) $cb( 7 );
}
function __( $s, $d = '' ) { return $s; }
function esc_html( $s ) { return htmlspecialchars( $s ); }
function wp_json_encode( $s ) { return json_encode( $s, JSON_UNESCAPED_UNICODE ); }
function sanitize_text_field( $s ) { return trim( strip_tags( $s ) ); }
function wp_unslash( $s ) { return $s; }
function current_user_can( $c, $id = 0 ) { return $GLOBALS['can']; }
function is_product() { return $GLOBALS['is_product']; }
function get_queried_object_id() { return $GLOBALS['qid']; }
function woocommerce_wp_select( $a ) { echo "[select {$a['id']}]"; }

class WC_Product {
    public $m = [];
    function get_meta( $k ) { return $this->m[ $k ] ?? ''; }
    function update_meta_data( $k, $v ) { $this->m[ $k ] = $v; }
    function delete_meta_data( $k ) { unset( $this->m[ $k ] ); }
    function save_meta_data() {}
}
$GLOBALS['P'] = new WC_Product;
function wc_get_product( $id ) { return $id == 7 ? $GLOBALS['P'] : false; }

$plugin = __DIR__ . '/../woocommerce-ai-label.php';
require $plugin;

$failed = 0;
$ok = function ( $name, $cond ) use ( &$failed ) {
    if ( ! $cond ) $failed++;
    echo ( $cond ? 'PASS' : 'FAIL' ) . " $name\n";
};
$P = $GLOBALS['P'];

// Zapis
$_POST['_ai_content_label'] = 'ai_generated'; run( 'woocommerce_process_product_meta' );
$ok( 'zapis ai_generated', $P->m['_ai_content_label'] === 'ai_generated' );
$_POST['_ai_content_label'] = 'nieznana'; run( 'woocommerce_process_product_meta' );
$ok( 'odrzuca nieznaną wartość', $P->m['_ai_content_label'] === 'ai_generated' );
$GLOBALS['can'] = false; $_POST['_ai_content_label'] = 'ai_modified'; run( 'woocommerce_process_product_meta' );
$ok( 'brak uprawnień = brak zapisu', $P->m['_ai_content_label'] === 'ai_generated' );
$GLOBALS['can'] = true;

// Wyświetlanie
$GLOBALS['product'] = $P;
ob_start(); run( 'woocommerce_before_shop_loop_item_title' ); $o = ob_get_clean();
$ok( 'lista sklepu: wrapper + badge, tagi zbalansowane', substr_count( $o, '<span' ) == 2 && substr_count( $o, '</span>' ) == 2 );
ob_start(); run( 'woocommerce_single_product_summary' ); $o = ob_get_clean();
$ok( 'notatka dla ai_generated', str_contains( $o, 'w całości wygenerowane' ) );
ob_start(); run( 'wp_footer' ); $o = ob_get_clean();
$ok( 'skrypt w stopce z AI GENERATED', str_contains( $o, 'textContent = "AI GENERATED";' ) );

// Brak oznaczenia i dane skrajne
$_POST['_ai_content_label'] = ''; run( 'woocommerce_process_product_meta' );
$ok( 'pusta wartość usuwa meta', ! isset( $P->m['_ai_content_label'] ) );
ob_start(); run( 'woocommerce_before_shop_loop_item_title' ); run( 'woocommerce_single_product_summary' ); run( 'wp_footer' ); $o = ob_get_clean();
$ok( 'brak oznaczenia = brak outputu', $o === '' );
$GLOBALS['product'] = null;
ob_start(); run( 'woocommerce_before_shop_loop_item_title' ); run( 'woocommerce_single_product_summary' ); $o = ob_get_clean();
$ok( '$product = null bez błędu', $o === '' );
$GLOBALS['qid'] = 99;
ob_start(); run( 'wp_footer' ); $o = ob_get_clean();
$ok( 'nieistniejący ID bez błędu', $o === '' );

// Podwójne załadowanie
$n = count( $GLOBALS['hooks']['wp_footer'][99] );
require $plugin;
$ok( 'drugie załadowanie nie dubluje hooków', count( $GLOBALS['hooks']['wp_footer'][99] ) === $n );

exit( $failed ? 1 : 0 );
