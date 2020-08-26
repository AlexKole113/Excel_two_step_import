<?php
namespace Excel_two_step_import\Parser\csv;

require_once  EXCEL_TWO_STEP_IMPORT_PARSER .'Parser.php';
require_once  EXCEL_TWO_STEP_IMPORT_PARSER .'phpoffice_phpspreadsheet_1.14.1.0_require/vendor/autoload.php';

use Excel_two_step_import\Parser\Parser as Parser;

class Parser_csv extends Parser
{
    protected $parsing_component;
    protected $dir               = EXCEL_TWO_STEP_IMPORT_PATH . '/import/';
    protected $separator         = ',';
    protected $row_length        = 100000;


    public function load_data( $file )
    {

        $file_tmp      = $file['tmp_name'];
        $file_name     = basename( $file['name'] );
        $file          = $this->dir . $file_name;
        $csv_file_name = $file;

        try {

            if( false == $this->upload_file( $file_tmp , $file_name ) ){
               return false;
            }

            if ( file_exists( $csv_file_name ) ) {

                if( !$data = $this->parse_csv( $csv_file_name ) ){
                    return false;
                } else {
                    parent::load_data( $data );
                }

            } else {
                return false;
            }


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


    /**
     *  Parsing CSV
     *
     *  @param string|null $file
     */
    protected function parse_csv( $file = '' ) {

        if ( ( $descriptor = fopen( $file, "r") ) !== FALSE ) {
            while ( ( $data = fgetcsv( $descriptor, $this->row_length, $this->separator ) ) !== FALSE) {
                $this->csv_data[] = $data;
            }
            fclose( $descriptor );
            return $this->csv_data;
        }

    }

}