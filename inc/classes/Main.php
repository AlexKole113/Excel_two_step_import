<?php
namespace Excel_two_step_import;

require_once EXCEL_TWO_STEP_IMPORT_PARSER  . 'Parser_csv.php';
require_once EXCEL_TWO_STEP_IMPORT_PARSER  . 'Parser_xls.php';
require_once EXCEL_TWO_STEP_IMPORT_PARSER  . 'Parser_xml.php';
require_once EXCEL_TWO_STEP_IMPORT_CREATOR . 'Post_creator.php';
require_once EXCEL_TWO_STEP_IMPORT_CREATOR . 'Product_creator.php';
require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Excel_two_step_import_Exception.php';

use Excel_two_step_import\Post\Form\Form as Form;
use Excel_two_step_import\Post\Exception\Excel_two_step_import_Exception as Excel_two_step_import_Exception;
use Excel_two_step_import\Post\Sanitizer\Sanitizer as Sanitizer;
use Excel_two_step_import\Parser\csv\Parser_csv as Parser_csv;
use Excel_two_step_import\Parser\xls\Parser_xls as Parser_xls;
use Excel_two_step_import\Parser\xml\Parser_xml as Parser_xml;


class Main
{

    // components
    protected $parser;
    protected $sanitizer;
    protected $admin_form;
    protected $creator;


    /**
     *  Connecting components for interaction
     *
     *  @param string $creator
     */
    public function __construct( $creator )
    {
        $creator = 'Excel_two_step_import\Post\\' . $creator . '\\' . $creator;
        $this->sanitizer        = new Sanitizer;
        $this->admin_form       = new Form( $creator );
        $this->creator          = new $creator;

    }


    /**
     *  Plugin initialization
     */
    public function init()
    {

        // Сreate page
        add_action( 'admin_menu', array( $this , 'excel_two_step_import_create_page' ) );
        add_action( 'admin_init', array( $this , 'excel_two_step_import_form_fields' ) );

        // enq js+css
        add_action( 'admin_enqueue_scripts', array( $this , 'excel_two_step_import_admin_jsCss' ) );

        // AJAX img loader
        add_action( 'wp_ajax_excel_two_step_import', array( $this , 'excel_two_step_import_ajax' ) );
        add_action( 'plugins_loaded', array( $this , 'plugin_lang' ) );

    }


    /**
     * Defines the main behavior of the plugin
     *
     * @return array
     * @throws Excel_two_step_import_exception
     */
    public function behavior( )
    {

        $file               = $_FILES[ $this->admin_form->file_name ]    ?? false;
        $form_datas         = $_POST[ $this->admin_form->data_name ]     ?? false;
        $form_prod_attrs    = $_POST[ $this->admin_form->select_name ]   ?? false;
        $form_mult_attrs    = $_POST[ $this->admin_form->multiple_name ] ?? false;
        $form_image_main    = $_POST[ $this->admin_form->img_main_name ] ?? false;
        $form_image_gal     = $_POST[ $this->admin_form->img_gal_name ]  ?? false;


        try{

            if ( isset( $file['name'] ) && file_exists( $file['tmp_name'] ) ) {

                $this->parser = $this->parser_select( $file );

                if ( !$this->parser->load_data( $file ) ){
                    throw new Excel_two_step_import_exception( __( 'Cannot read file','excel-two-step-import') );
                }

                $parsing_data = apply_filters( 'parsing_data', $this->parser->get_data() );

                if( !$parsing_data ){
                    throw new Excel_two_step_import_exception( __( 'Failed to retrieve data','excel-two-step-import' ) );
                } else {
                    $this->sanitizer->load_data( $parsing_data );
                    $this->sanitizer->clear_fields();
                    $options['fields'] =  $this->sanitizer->get_data();
                    $this->check_option();
                }

            }  elseif ( $form_datas && $form_prod_attrs ) {

                if ( ! $this->admin_form->load_form_data( $form_prod_attrs , $form_datas , $form_mult_attrs, $form_image_main, $form_image_gal  ) ) {
                    throw new Excel_two_step_import_exception( __( 'Failed to load form data','excel-two-step-import' ) );
                }

                $after_form_data = apply_filters( 'after_form_data', $this->admin_form->get_data() );
                $this->sanitizer->load_data( $after_form_data );
                $this->sanitizer->clear_fields();
                $this->creator->load_data( $this->sanitizer->get_data() );

                if( ! $this->creator->add_item() ) {
                    throw new Excel_two_step_import_exception( __( 'Failed to create post','excel-two-step-import' ) );
                }

            } else {
                $options = get_option( 'excel_two_step_import' );
            }

        } catch(  Excel_two_step_import_exception $e  ) {
            $e->wp_error();
        }  catch ( \Exception $e ) {
            add_settings_error( 'my_fatal_error', 'my_fatal_error', $e->getMessage(), 'error' );
        }

        return $options;
    }


    /**
     * Enqueue script & style
     *
     * @param string $slug
     */
    public function excel_two_step_import_admin_jsCss ( $slug )
    {

        if ( $slug != 'tools_page_excel_two_step_import' ) {
            return;
        }


        wp_enqueue_style('excel_two_step_import-css', plugins_url('/Excel_two_step_import/assets/css/style.css')  );
        wp_enqueue_script( 'excel_two_step_import-js', plugins_url('/Excel_two_step_import/assets/js/main.js') , array( 'jquery' ), '1.0', true );

        wp_localize_script( 'excel_two_step_import-js', 'excel_two_step_import_object', array(
                'plugin_url'        => plugins_url(),
                'input_img_main'    => $this->admin_form->img_main_name,
                'input_img_gal'     => $this->admin_form->img_gal_name,
            )

        );
    }


    /**
     *  Page creation - add_management_page
     */
    public function excel_two_step_import_create_page()
    {
        add_management_page( 'excel_two_step_import', __( 'Import from Excel file', 'excel-two-step-import' ), 'manage_options', 'excel_two_step_import' , array( $this, 'excel_two_step_import_render_page' ) );
    }


    /**
     *  Callback page creation
     */
    public function excel_two_step_import_render_page()
    {

        settings_errors();

        ?>
        <div id="excel-two-step-import" class="wrap">
            <h2><?php esc_attr_e('Import from excel file','excel-two-step-import' ); ?></h2>
            <form action="options.php" method="post" enctype="multipart/form-data">
                <?php settings_fields( 'excel_two_step_import' ); ?>
                <?php do_settings_sections( 'excel_two_step_import' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }


    /**
     *  register_setting, add_settings_section, add_settings_field
     */
    public function excel_two_step_import_form_fields()
    {

        if( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = get_option( 'excel_two_step_import' );
        if ( isset( $options['fields'] ) && !empty( $options['fields'] ) ) {
            $this->admin_form->load_data( $options['fields'] );
        }

        $args = array(
            'sanitize_callback' => array( $this, 'behavior' ),
        );

        // Создание настройки и секции
        register_setting( 'excel_two_step_import', 'excel_two_step_import', $args );
        add_settings_section( 'excel_two_step_import_section', __( 'Upload file','excel-two-step-import' ), '', 'excel_two_step_import' );
        add_settings_field( 'excel_two_step_import_form', '', array( $this->admin_form, 'render_form'), 'excel_two_step_import', 'excel_two_step_import_section'  );

    }


    /**
     * loading images via ajax
     */
    public function excel_two_step_import_ajax()
    {

        if( isset( $_POST['excel_two_step_delete_attach'] ) && !empty( $_POST['excel_two_step_delete_attach'] ) ) {

            if( false !== wp_delete_attachment( $_POST['excel_two_step_delete_attach'], true ) ) {
                echo true;
            } else {
                echo false;
            }
            wp_die();
        }

        if( empty( $_FILES[0] ) ) {
            wp_die();
        }

        $ids = array();
        foreach ( $_FILES as $file ) {
            $ids[] = media_handle_sideload( $file, 0 );
        }

        $src = array();
        foreach ( $ids as $id ) {
            $src[] = wp_get_attachment_url( $id );
        }

        $response = array(
            'id'  =>  $ids,
            'src' =>  $src
        );

        echo json_encode( $response );
        wp_die();
    }


    /**
     * load plugin textdomain
     */
    public function plugin_lang() {
        load_plugin_textdomain('excel-two-step-import', false, EXCEL_TWO_STEP_IMPORT_IN_PLUGINS_PATH . '/languages/' );
    }


    /**
     * Selects a parser depending on
     * the format of the received file
     *
     * @param string $file
     *
     * @return Parser_xml|Parser_xls
     */
    protected function parser_select( $file ) {
        if( $file['type'] == 'text/xml' ) {
            return new Parser_xml;
        } elseif ( $file['type'] == 'text/csv' ) {
            return new Parser_xls;
        } else {
            return new Parser_xls;
        }
    }


    /**
     * If false then need to prepare option
     *
     * @param array $file
     */
    protected function check_option() {
        if( get_option( 'excel_two_step_import' ) === false ) {
            add_option('excel_two_step_import', array(), '' ,false );
        }
    }


}