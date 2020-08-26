<?php
namespace Excel_two_step_import\Post\Form;

require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Post.php';
require_once EXCEL_TWO_STEP_IMPORT_CREATOR . 'Product_creator.php';

use Excel_two_step_import\Post\Post as Post;
use Excel_two_step_import\Post\Product_creator\Product_creator as Product_creator;
use Excel_two_step_import\Post\Post_creator\Post_creator as Post_creator;

class Form extends Post
{

    // inputs names
    public $file_name       = 'excel_two_step_import_file';
    public $data_name       = 'excel_two_step_import_all_data';
    public $select_name     = 'excel_two_step_import_prod_attrs';
    public $multiple_name   = 'excel_two_step_import_multiple';
    public $img_main_name   = 'excel_two_step_import_img_main';
    public $img_gal_name    = 'excel_two_step_import_img_gal';

    // Number of columns on page
    public $cols_count      = 0;

    protected $load_data;
    protected $form_type;


    /**
     *  Connecting components for interaction
     *
     *  @param string $creator
     */
    function __construct( $creator )
    {
        $this->form_type = new $creator;
        $this->attrs = $this->form_type->attrs;
    }


    public function load_data( $data )
    {
        $this->load_data = $data;
    }


    /**
     *  Connecting a form template
     */
    public function render_form()
    {

        if ( $this->load_data ) {
            $this->get_cols_count();
            $cols_cont  = $this->cols_count;
            $fields     = $this->load_data;
            $prod_prop  = $this->attrs;

            require_once EXCEL_TWO_STEP_IMPORT_PATH .'/templates/import-data-form.php';
        } else {
            require_once EXCEL_TWO_STEP_IMPORT_PATH .'/templates/import-file-form.php';
        }
    }


    /**
     *  Creation input type text
     *
     *  @param string $value
     *  @param string $row
     *  @param string $col
     */
    public function input_text( $value, $row, $col )
    {
        if( !is_string( $value ) ){
            $value = ' ';
        }

        echo '<input name="' .$this->data_name. '['.$row.']['.$col.']" type="text" value="' . esc_attr( $value ) . '">';
    }


    /**
     *  Create a select tag with
     *  post characteristics
     *
     *  @param string $col_num
     */
    public function show_prod_props( $col_num )
    {

        $this->form_type->get_prop_names();
        $this->form_type->get_local_prop_names();

        $names  = $this->form_type->local_names_attrs;
        $values = $this->form_type->attrs;

        echo "<div class='show_prod_props'>";
        echo "<select name='{$this->select_name}[$col_num]'>";
            echo '<option value="0">'. esc_html__('select value', 'excel-two-step-import') . '</option>';
            foreach ( $values as $prop => $val ){
                echo "<option value ='" . esc_attr( $prop ) . "'>" . esc_attr( $names[$prop] ) . "</option>";
            }
        echo '</select>';
        echo '</div>';
    }


    /**
     *  Creates a "Multiple Value" checkbox
     *
     *  @param string $col
     */
    public function multiple_val( $col )
    {
        echo '<div class="multiple_val">';
        echo "<label for='multiple-val-$col'>" . esc_html__('Several meanings','excel-two-step-import'  ) . "</label>";
        echo "<input id='multiple-val-$col' type='checkbox'name='{$this->multiple_name}[$col]'>";
        echo '</div>';
    }


    /**
     *  Creates a form for uploading images
     *
     *  @param string $row
     */
    public function form_product_images ( $row )
    {
        echo '<td class="td-img-main">';
        echo  "<div id='img_main_$row'></div>";
        echo  "<input data-img-type='img_main' data-row='$row' class='file-upload-input img_main' type='file' name='excel_two_step_import_img_main[]' id='excel_two_step_import_img_main-$row' accept='image/*,image/jpeg,image/png' />
               <label class='file-label' for='excel_two_step_import_img_main-$row'>+" . esc_html__('Add Main Photo','excel-two-step-import'  ) . "</label>";
        echo '</td>';

        if( $this->form_type->has_gallery ) {
            echo '<td class="td-img-gal">';
            echo  "<div id='img_gal_$row'></div>";
            echo  "<input data-img-type='img_gal' data-row='$row' class='file-upload-input img_gal' type='file' name='excel_two_step_import_img_gal[]' id='excel_two_step_import_img_gal-$row' multiple accept='image/*,image/jpeg,image/jpng' />
               <label class='file-label' for='excel_two_step_import_img_gal-$row'>+" . esc_html__('Add Gallery Photos','excel-two-step-import'  ) . "</label>";
            echo '</td>';
        }

    }


    /**
     *  Combines all data received from the form into one array
     *  for further preparation for the importer component
     *
     *  @param array $attrs
     *  @param array $data
     *  @param array $multiple
     *  @param array $img_main
     *  @param array $img_gal
     *
     *  @return array Data ready to pass WP function to add content
     */
    public function load_form_data( $attrs, $data, $multiple, $img_main, $img_gal )
    {
        $form_data = array(
           'multiple' => $multiple,
           'attrs'    => $attrs,
           'datas'    => $data,
           'img_main' => $img_main,
        );

        if( $this->form_type->has_gallery ) {
            $form_data['img_gal'] = $img_gal;
        }


         return $this->prepare_data( $form_data );
    }


    /**
     *  Gets the number of columns in a table
     */
    protected function get_cols_count()
    {

        foreach ( $this->load_data as $k => $v ) {

            if( count( $v ) > $this->cols_count  ) {
                $this->cols_count = count( $v );
            }

            if( empty( $this->load_data[ $k ] ) ) {
                unset( $this->load_data[ $k ] );
            }

            $this->load_data[ $k ] = array_values( $v );
        }

    }


    /**
     *  Processing data from a field with multiple values
     *
     *  @param array $several_prods
     *  @param string $value
     *  @param string $key
     *
     *  @return array
     */
    protected function several_meanings( $several_prods, $value, $key )
    {

        if( isset( $several_prods[ $key ] ) && $several_prods[ $key ] == 'on' ) {
            if ( count( explode(',', $value ) ) > 1 ) {
                return explode(',', $value );
            }
            return $value;
        } else {
            return $value;
        }
    }


    /**
     *  Preparing data for WP function to add content
     *
     *  @param array $all_data
     *
     *  @return bool
     */
    protected function prepare_data( $all_data )
    {

        $prepare_data = array();
        try {

            if ( !isset( $all_data['datas'] ) ||  !isset( $all_data['attrs'] ) ) {
                return false;
            }

            foreach( $all_data['datas'] as $num_prod => $prod_array ) {
                if( is_array( $prod_array ) ) {

                    $prod_item = array();

                    foreach ( $all_data['attrs'] as $atr_key => $atr_val ) {
                        if( (bool) $atr_val && isset( $prod_array[ $atr_key ] ) ) {

                            $prod_item[ $atr_val ] = $this->several_meanings( $all_data['multiple'], $prod_array[ $atr_key ], $atr_key );
                            $prod_item['img_main'] = $all_data['img_main'][ $num_prod ] ?? '';

                            if( $this->form_type->has_gallery ) {
                                $prod_item['img_gal']  = $all_data['img_gal'][ $num_prod ]  ?? '';;
                            }

                        }
                    }

                    $prepare_data[] = $prod_item;
                }

            }

        } catch ( \Exception $e ) {
            add_settings_error( 'my_fatal_error', 'my_fatal_error', $e->getMessage(), 'error' );
        }

        parent::load_data( $prepare_data );

        return true;
    }

}