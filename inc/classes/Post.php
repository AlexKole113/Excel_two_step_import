<?php
namespace Excel_two_step_import\Post;

require_once EXCEL_TWO_STEP_IMPORT_INTERFACE . 'Excel_two_step_import_interface.php';

class Post implements \Excel_two_step_import\Post\Post_interface
{
    // data received
    protected $data;

    // post characteristics (Post, Product, etc).
    protected $attrs;


    /**
     *  Loading data into a component
     *
     *  @param array|string|null $data
     */
    public function load_data( $data )
    {
        $this->data = $data;
    }


    /**
     *  Unloading data from a component
     *
     *  @return array|string|null
     */
    public function get_data()
    {
        return $this->data;
    }

}