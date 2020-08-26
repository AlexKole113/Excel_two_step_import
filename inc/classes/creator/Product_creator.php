<?php
namespace Excel_two_step_import\Post\Product_creator;

require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Post.php';

use Excel_two_step_import\Post\Post as Post;

class Product_creator extends Post
{

    public $attrs = array(
        'sku'                => '',
        'name'               => '',
        'featured'           => false,
        'description'        => '',
        'short_description'  => '',
        'price'              => '',
        'regular_price'      => '',
        'sale_price'         => '',
        'total_sales'        => '0',
        'tax_status'         => 'taxable',
        'tax_class'          => '',
        'manage_stock'       => 'no',
        'stock_quantity'     => null,
        'stock_status'       => 'instock',
        'low_stock_amount'   => '',
        'sold_individually'  => false,
        'weight'             => '',
        'length'             => '',
        'width'              => '',
        'height'             => '',
        'upsell_ids'         => array(),
        'cross_sell_ids'     => array(),
        'purchase_note'      => '',
        'attributes'         => array(),
        'category_ids'       => array(),
        'categorie_name'     => '',
        'shipping_class_id'  => 0,
        'image_id'           => '',
        'gallery_image_ids'  => array(),
    );
    public $has_gallery = true;
    public $local_names_attrs;


    /**
     *  Adds Post
     *
     *  @return bool
     */
    public function add_item()
    {

        if( empty( $this->data ) ) {
            return false;
        }

        foreach ( $this->data as $datas ) {

            // проверка существования товара с таким SKU
            global $wpdb;
            $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $datas['sku'] ) );
            if( $product_id ) {
                wp_delete_post( $product_id, true );
            }

            // проверка существования категории
            $datas['categorie_name'] = $datas['categorie_name'] ?? 'Выгрузка';
            $datas['categorie_name'] = trim( $datas['categorie_name'] );
            $category = get_term_by( 'name', $datas['categorie_name'], 'product_cat', 'ARRAY_A' );

            if( $category ) {
                $category = (int) $category['term_id'];
            } else {
                $category = wp_insert_category(
                    array(
                        'cat_ID' => 0,
                        'cat_name' => $datas['categorie_name'],
                        'taxonomy' => 'product_cat'
                    )
                );
            }

            $post_id = wp_insert_post( array(
                'post_author' => 1,
                'post_title' => $datas['name'], //Обязательно
                'post_content' => ( isset( $datas['description'] ) ) ? $datas['description'] : '&nbsp;' ,
                'post_status' => 'publish',
                'post_type' => 'product',
            ) );

            if( !$post_id ) return false;


            wp_set_object_terms( $post_id,  $datas['theme_brand'], 'product-brand' );
            wp_set_object_terms( $post_id,  $category, 'product_cat' );
            wp_set_object_terms( $post_id, 'simple', 'product_type' );



            $arrAttributes = [];
            foreach ($datas as $key_prod_attr => $val_prod_attr ) {
                if ( strripos ( $key_prod_attr, 'pa_' ) === 0 ) {
                    $arrAttributes[ $key_prod_attr ] = [
                        'name'           => htmlspecialchars("$key_prod_attr"),
                        'value'          => htmlspecialchars("$val_prod_attr"),
                        'is_visible'     => '1',
                        'is_variation'   => '0',
                        'is_taxonomy'    => '1'
                    ];
                }
            }


            update_post_meta( $post_id, '_product_attributes', $arrAttributes );

            update_post_meta( $post_id, '_visibility',          'visible' );
            update_post_meta( $post_id, '_stock_status',        $data['stock_status'] ?? 'instock' );
            update_post_meta( $post_id, 'total_sales',          $data['total_sales'] ?? 0 );
            update_post_meta( $post_id, '_downloadable',        'no' );
            update_post_meta( $post_id, '_virtual',             'no' );
            update_post_meta( $post_id, '_regular_price',       $datas['regular_price'] ?? '' );
            update_post_meta( $post_id, '_sale_price',          $datas['sale_price'] ?? '' );
            update_post_meta( $post_id, '_price',               $datas['price'] ?? '' );
            update_post_meta( $post_id, '_purchase_note',       $datas['purchase_note'] ?? '' );
            update_post_meta( $post_id, '_featured',            $datas['featured'] ?? '' );
            update_post_meta( $post_id, '_weight',              $datas['weight'] ?? '' );
            update_post_meta( $post_id, '_length',              $datas['length'] ?? '' );
            update_post_meta( $post_id, '_width',               $datas['width'] ?? '' );
            update_post_meta( $post_id, '_height',              $datas['height'] ?? '');
            update_post_meta( $post_id, '_sku',                            $datas['sku'] );
            update_post_meta( $post_id, '_sale_price_dates_from','' );
            update_post_meta( $post_id, '_sale_price_dates_to', '' );
            update_post_meta( $post_id, '_sold_individually',   $datas['sold_individually'] ??'' );
            update_post_meta( $post_id, '_manage_stock',        'yes' );
            update_post_meta( $post_id, '_stock',               $datas['stock_quantity'] ?? 1 );
            update_post_meta( $post_id, '_low_stock_amount',      $datas['low_stock_amount'] ?? 3 );
            update_post_meta( $post_id, '_backorders',          'no' );


            update_post_meta( $post_id, '_shipping_class_id',   $datas['shipping_class_id'] ?? 0 );
            update_post_meta( $post_id, '_category_ids',        $datas['category_ids'] ?? '' );
            update_post_meta( $post_id, '_tax_status',          $datas['tax_status'] ?? 'taxable' );
            update_post_meta( $post_id, '_tax_class',           $datas['tax_class'] ?? 'taxable' );
            update_post_meta( $post_id, '_upsell_ids',          $datas['upsell_ids'] ?? '' );
            update_post_meta( $post_id, '_cross_sell_ids',      $datas['cross_sell_ids'] ?? '' );




            if ( isset( $datas['img_main'][0] ) || !empty( $datas['img_main'][0] ) ) {
                set_post_thumbnail( $post_id, $datas['img_main'][0] );
            }

            // Галерея продукта
            if( isset( $datas['img_gal'] ) || !empty( $datas['img_gal'] ) ){
                update_post_meta( $post_id, '_product_image_gallery', implode(", ", $datas['img_gal'] ) );
            }

            wc_delete_product_transients( $post_id );
        }

    }


    /**
     * Getting taxonomies post
     */
    public function get_prop_names()
    {

        foreach ( wc_get_attribute_taxonomies() as $attr ) {
            $this->attrs[$attr->attribute_name] = '';
        }

    }


    /**
     * Get taxonomy labels post
     */
    public function get_local_prop_names()
    {
        $this->set_local_names();

        foreach ( wc_get_attribute_taxonomies() as $attr ) {
            foreach ( $this->attrs as $attr_slug => $val ) {
                if( $attr->attribute_name == $attr_slug ){
                    $this->local_names_attrs[$attr_slug] = $attr->attribute_label;
                }
            }
        }

    }


    /**
     * Retrieving Local Attribute Names
     */
    protected function set_local_names()
    {
        $this->local_names_attrs = array(
            'sku'                => __('SKU (Required)','excel-two-step-import' ),
            'name'               => __('Name of product', 'excel-two-step-import' ),
            'slug'               => __('Slug of product','excel-two-step-import'),
            'date_created'       => __('Date created','excel-two-step-import'),
            'date_modified'      => __('Date modified','excel-two-step-import'),
            'status'             => __('Status','excel-two-step-import'),
            'featured'           => __('Featured','excel-two-step-import'),
            'catalog_visibility' => __('Catalog visibility','excel-two-step-import'),
            'description'        => __('Description (main)','excel-two-step-import'),
            'short_description'  => __('Short description','excel-two-step-import'),
            'price'              => __('Price','excel-two-step-import'),
            'regular_price'      => __('Regular price','excel-two-step-import'),
            'sale_price'         => __('Sale price','excel-two-step-import'),
            'total_sales'        => __('Total sales','excel-two-step-import'),
            'tax_status'         => __('Tax status','excel-two-step-import'),
            'tax_class'          => __('Tax class','excel-two-step-import'),
            'manage_stock'       => __('Manage stock','excel-two-step-import'),
            'stock_quantity'     => __('Stock quantity','excel-two-step-import'),
            'stock_status'       => __('Stock status','excel-two-step-import'),
            'low_stock_amount'   => __('Low stock amount','excel-two-step-import'),
            'sold_individually'  => __('Sold individually','excel-two-step-import'),
            'weight'             => __('Weight','excel-two-step-import'),
            'length'             => __('Length','excel-two-step-import'),
            'width'              => __('Width','excel-two-step-import'),
            'height'             => __('Height','excel-two-step-import'),
            'upsell_ids'         => __('Upsell ids','excel-two-step-import'),
            'cross_sell_ids'     => __('Cross sell ids','excel-two-step-import'),
            'reviews_allowed'    => __('Reviews allowed','excel-two-step-import'),
            'purchase_note'      => __('Purchase note','excel-two-step-import'),
            'attributes'         => __('Attributes','excel-two-step-import'),
            'category_ids'       => __('Category ids','excel-two-step-import'),
            'categorie_name'     => __('Category name','excel-two-step-import'),
            'shipping_class_id'  => __('Shipping class id','excel-two-step-import'),
            'image_id'           => __('Image id','excel-two-step-import'),
            'gallery_image_ids'  => __('Gallery image ids','excel-two-step-import'),
        );


    }


}