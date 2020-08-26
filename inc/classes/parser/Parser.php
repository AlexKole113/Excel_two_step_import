<?php
namespace Excel_two_step_import\Parser;

require_once EXCEL_TWO_STEP_IMPORT_INTERFACE . 'Excel_two_step_import_Parser_interface.php';

class Parser implements \Excel_two_step_import\Parser\Parser_interface
{
    protected $data;


    /**
     *  Parsing the received data
     *  and loading data
     *
     *  @param array|string|null $data
     */
    public function load_data($data)
    {
        $this->data = $data;
    }


    /**
     *  Unloading data from a component
     *
     *  @return array|string
     */
    public function get_data()
    {
        return $this->data;
    }
}