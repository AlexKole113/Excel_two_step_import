<?php
namespace Excel_two_step_import\Parser\xml;

require_once  EXCEL_TWO_STEP_IMPORT_PARSER .'Parser.php';

use Excel_two_step_import\Parser\Parser as Parser;

class Parser_xml extends Parser
{
    protected $parsing_component;
    protected $dir               = EXCEL_TWO_STEP_IMPORT_PATH . '/import/';

    public function load_data( $file )
    {

        $file_tmp      = $file['tmp_name'];
        $file_name     = basename( $file['name'] );
        $file          = $this->dir . $file_name;

        try {
            if( false == $this->upload_file( $file_tmp , $file_name ) ){
                return false;
            }

            $xml = simplexml_load_file( $file );
            $data = array();
            foreach ( $xml->children() as $child ) {
                $child = (array) $child;
                if( !empty( $child  ) ) {
                    $data[] = $child;
                }
            }

            parent::load_data( $data );

        }  catch ( \Exception $e ) {
            add_settings_error( 'my_fatal_error', 'my_fatal_error', $e->getMessage(), 'error' );
        }

        return true;
    }


    /**
     *  Saving a file
     *
     *  @param string|null $tmp_name
     *  @param string|null $filename
     *
     * @return string
     */
    protected function upload_file( $tmp_name = '' , $filename = '' ) {

        $file = $this->dir. $filename;

        //clearing folder
        $trash_files = glob( $this->dir . '*' );
        foreach ( $trash_files as $trash_file ) {
            if ( is_file( $trash_file ) ) {
                unlink( $trash_file );
            }
        }

        //save file
        if( !empty( $tmp_name ) ) {
            return move_uploaded_file( $tmp_name,  $file  );
        }

    }

}