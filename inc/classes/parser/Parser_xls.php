<?php
namespace Excel_two_step_import\Parser\xls;

require_once  EXCEL_TWO_STEP_IMPORT_PARSER .'Parser_csv.php';
require_once  EXCEL_TWO_STEP_IMPORT_PARSER .'phpoffice_phpspreadsheet_1.14.1.0_require/vendor/autoload.php';

use \Excel_two_step_import\Parser\csv\Parser_csv as Parser_csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;



class Parser_xls extends Parser_csv
{
    protected $parsing_component = EXCEL_TWO_STEP_IMPORT_PATH . '/inc/classes/parser/PhpSpreadsheet/';


    public function load_data( $file )
    {

        $file_tmp      = $file['tmp_name'];
        $file_name     = basename( $file['name'] );
        $file          = $this->dir . $file_name;
        $csv_file_name = $this->dir . '/parse.csv';

        try {

            if( false == $this->upload_file( $file_tmp , $file_name ) ){
                return false;
            }

            $reader = IOFactory::createReaderForFile( $file );
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load( $file );

            // Export to CSV file.
            $writer = IOFactory::createWriter( $spreadsheet, "Csv" );
            $writer->setSheetIndex(0);   // Select which sheet to export.
            $writer->setDelimiter( $this->separator );  // Set delimiter.
            $writer->save( $csv_file_name );

            if ( file_exists( $csv_file_name ) ) {
                if( !$data = $this->parse_csv( $csv_file_name ) ){
                    return false;
                } else {
                    $this->data = $data;
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
    protected function upload_file( $tmp_name = '' ,$filename = '' ) {

        $file = $this->dir . $filename;

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