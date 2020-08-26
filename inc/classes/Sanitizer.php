<?php
namespace Excel_two_step_import\Post\Sanitizer;

require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Post.php';

use Excel_two_step_import\Post\Post as Post;

class Sanitizer extends Post
{


    public function clear_fields(){
        $this->data = $this->array_clear( $this->data, 'wp_kses_post');
    }


    /**
     *  Array or object iteration and cleanup
     *
     *  @param array $data
     *  @param string $wp_clear_foo WP function name
     *
     *  @return array|string
     */
    protected function array_clear( &$data, $wp_clear_foo ){

        if( is_array( $data ) ){
            foreach ( $data as $k => $v ) {

                if ( is_object( $v ) ) {
                    $data[ $k ] = (array) $data[$k];
                }

                if( !empty( $data[ $k ] ) ){
                    $data[ $k ] = $this->array_clear( $v, $wp_clear_foo );
                }
            }
            return $data;
        } else {
           return $wp_clear_foo( $data ) ;
        }
    }

}