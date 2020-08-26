<?php
namespace Excel_two_step_import\Parser;

interface Parser_interface
{
    /**
     * Loading data into a component
     *
     * @param array|string $data
     */
    public function load_data( $data );


    /**
     * Retrieves data from a component
     *
     * @return array
     */
    public function get_data();
}