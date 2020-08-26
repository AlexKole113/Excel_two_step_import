<?php
namespace Excel_two_step_import\Post\Exception;


class Excel_two_step_import_Exception extends \Exception
{
    public function wp_error() {
        add_settings_error( 'my_fatal_error', 'my_fatal_error', $this->getMessage(), 'error' );
    }
}