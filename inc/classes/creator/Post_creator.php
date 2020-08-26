<?php
namespace Excel_two_step_import\Post\Post_creator;

require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Post.php';

use Excel_two_step_import\Post\Post as Post;

class Post_creator extends Post
{

    public $attrs       = array(
        'ID'             => '',
        'post_title'     => 'Post title',
        'post_content'   => '',
        'post_author'    => '',
        'post_date'      => '',
        'post_excerpt'   => '',
        'post_status'    => 'publish',
        'post_type'      => 'post',
        'comment_status' => 'open',
        'ping_status'    => 'open',
        'post_password'  => '',
        'post_name'      => '',
        'post_parent'    => '',
        );

    public $has_gallery = false;
    public $local_names_attrs;
    public $post_types   = array( 'post', 'page' );


    /**
     *  Adds Post
     *
     *  @return bool
     */
    public function add_item()
    {


        if(  empty( $this->data )  )   return false;
        if( !is_array( $this->data ) ) return false;

        foreach( $this->data as $data ) {

            if( !isset( $data['post_title'] ) || empty( $data['post_title'] ) ) continue;
            if( !isset( $data['post_content'] ) || empty( $data['post_content'] ) ) continue;
            if( isset( $data['img_main'] ) ) {
                if( isset( $data['img_main'][0] ) ){
                    $img = $data['img_main'][0];
                }
                unset( $data['img_main'] );
            }
            if( isset( $data['post_date'] ) ) {
                $data['post_date'] = strtotime( $data['post_date'] );
                $data['post_date'] = date("Y-m-d H:i:s", $data['post_date'] );
            }
            
            foreach( $data as $name_attr => $data_attr ) {
                if( empty( $data_attr ) ) {
                    unset( $data[$name_attr] );
                }
            }

            $post_id = wp_insert_post( $data , true );
            if( is_wp_error( $post_id ) ){
                return false;
            }


            $taxes = get_taxonomies( array(),'objects' );
            foreach ( $taxes as $tax_name => $val ) {
                if( isset( $val->object_type ) && !empty( $val->object_type ) ) {
                    if( is_array($val->object_type) ) {
                        foreach ($val->object_type as $object_type ){
                            foreach ( $this->post_types as $post_type ) {
                                if( $object_type == $post_type && isset( $data[ $tax_name ] ) ){
                                    wp_set_object_terms( $post_id,  $data[ $tax_name ], $tax_name );
                                }
                            }
                        }
                    }
                }
            }


            if ( isset( $img )) {
                set_post_thumbnail( $post_id, $img );
            }

        }


        return true;
    }


    /**
     * Getting taxonomies post
     */
    public function get_prop_names()
    {

        $all_taxonomies = get_taxonomies( array(),'objects' );
        foreach ( $all_taxonomies as $tax_name => $val ) {


            if( isset( $val->object_type ) && !empty( $val->object_type ) ) {
                if( is_array($val->object_type) ) {
                    foreach ($val->object_type as $object_type ){
                        foreach ( $this->post_types as $post_type ) {
                            if( $object_type == $post_type ){
                                $this->attrs[$tax_name] = '';
                            }
                        }
                    }
                }
            }
        }


    }


    /**
     * Get taxonomy labels post
     */
    public function get_local_prop_names()
    {
        $this->set_local_names();
        $all = get_taxonomies( array(),'objects' );

        foreach ( $all as $name => $val ){
            if( isset( $this->attrs[$name] ) ){
                $this->local_names_attrs[$name] = $val->labels->singular_name;
            }
        }


    }


    /**
     * Retrieving Local Attribute Names
     */
    protected function set_local_names()
    {
        $this->local_names_attrs = array(
            'ID'             => __('ID','excel-two-step-import' ),
            'menu_order'     => __('Menu order','excel-two-step-import' ),
            'comment_status' => __('Comment status (closed or open)','excel-two-step-import' ),
            'ping_status'    => __('Ping status (closed or open)','excel-two-step-import' ),
            'pinged'         => __('Pinged','excel-two-step-import' ),
            'post_author'    => __('Post author ID','excel-two-step-import' ),
            'post_content'   => __('Post content (required)','excel-two-step-import' ),
            'post_date'      => __('Post date (required for "future" post)','excel-two-step-import' ),
            'post_date_gmt'  => __('Post date gmt','excel-two-step-import' ),
            'post_excerpt'   => __('Excerpt','excel-two-step-import' ),
            'post_name'      => __('Post name','excel-two-step-import' ),
            'post_parent'    => __('Post parent','excel-two-step-import' ),
            'post_password'  => __('Post password','excel-two-step-import' ),
            'post_status'    => __('Post status','excel-two-step-import' ),
            'post_title'     => __('Post title (required)','excel-two-step-import' ),
            'post_type'      => __('Post type','excel-two-step-import' ),
            'post_category'  => __('Post category ID','excel-two-step-import' ),
            'tags_input'     => __('Tags input','excel-two-step-import' ),
            'tax_input'      => __('Taxonomy input','excel-two-step-import' ),
            'to_ping'        => __('Ping','excel-two-step-import' ),
            'meta_input'     => __('Meta input','excel-two-step-import' ),
        );

    }
}