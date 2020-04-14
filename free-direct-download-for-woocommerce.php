<?php
/*
Plugin Name: Free Direct Download for WooCommerce
Plugin URI: https://jamescollins.com.au/resources/woocommerce-free-direct-download
Description: Custom Extension for WooCommerce to allow the downloading of free items without adding to the cart.
Version: 1.0
Author: James Collins
Author URI: https://jamescollins.com.au/
*/

add_filter( 'woocommerce_loop_add_to_cart_link', 'wcefreedirectdownload_button', 100);
add_action( 'woocommerce_single_product_summary', 'wcefreedirectdownload_single_button', 1 );

if ( isset( $_GET['download_file'] ) && isset( $_GET['key'] ) && $_GET['free'] ) {
    add_action( 'init', 'wcefreedirectdownload_product_file' );
}

/**
 * Replaces the Add To Cart button with a Download button if the product is downloadable and free on the archives page
 */
function wcefreedirectdownload_button($button)
{
    global $product;

    if( $product->is_downloadable() AND $product->get_price() == 0 )
    {
        $files = $product->get_files();
        $files = array_keys($files);

        $download_url = home_url('?download_file='.$product->id.'&key='.$files[0].'&free=1' );

        $button = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button direct_download %s product_type_%s">%s</a>',
            esc_url( $download_url  ),
            esc_attr( $product->id ),
            esc_attr( $product->get_sku() ),
            esc_attr( isset( $quantity ) ? $quantity : 1 ),
            $product->is_purchasable() && $product->is_in_stock() ? '' : '',
            esc_attr( $product->product_type ),
            esc_html( 'Download' )
        );
    }
    return $button;
}


/**
 * Replaces the Add To Cart button with a Download button if the product is downloadable and free on the single product page
 */
function wcefreedirectdownload_single_button() {
    global $product;

	if( $product->is_downloadable() AND $product->get_price() == 0 ) {
        if( $product->is_type( 'variable' ) ) {
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
            add_action( 'woocommerce_single_variation', 'wcefreedirectdownload_render_button', 20 );
        }
        // For all other product types
        else {
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
            add_action( 'woocommerce_single_product_summary', 'wcefreedirectdownload_render_button', 30 );
        }
    }
}

/**
 * Renders the Download button if the product is downloadable and free on the single product page
 */
function wcefreedirectdownload_render_button(){
	global $product;

	$files = $product->get_files();
	$files = array_keys($files);

	$download_url = home_url('?download_file='.$product->id.'&key='.$files[0].'&free=1' );

	$button = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button direct_download %s product_type_%s">%s</a>',
		esc_url( $download_url  ),
		esc_attr( $product->id ),
		esc_attr( $product->get_sku() ),
		esc_attr( isset( $quantity ) ? $quantity : 1 ),
		$product->is_purchasable() && $product->is_in_stock() ? '' : '',
		esc_attr( $product->product_type ),
		esc_html( 'Download' )
	);
	
	echo $button;
}

/**
 * Handles downloading of free Downloadable products
 */
function wcefreedirectdownload_product_file()
{
    $product_id    = absint( $_GET['download_file'] );
    $_product      = wc_get_product( $product_id );

    if( $_product->get_price() == 0 ) {
        WC_Download_Handler::download( $_product->get_file_download_path( filter_var($_GET['key'], FILTER_SANITIZE_STRING)  ), $product_id );
    }
}
