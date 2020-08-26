<?php
/*
Plugin Name: Excel two step import
Description: This plugin will help you import content (text and photos) from Excel or OpenOffice spreadsheets
Version: 1.0
Text Domain: excel-two-step-import
Author: Alexander Koledov
*/


namespace Excel_two_step_import;


define( 'EXCEL_TWO_STEP_IMPORT_PATH', __DIR__ );
define( 'EXCEL_TWO_STEP_IMPORT_IN_PLUGINS_PATH' , plugin_basename(__DIR__ ) );
define( 'EXCEL_TWO_STEP_IMPORT_COMPONENTS', __DIR__ . '/inc/classes/' );
define( 'EXCEL_TWO_STEP_IMPORT_INTERFACE', __DIR__ . '/inc/interface/' );
define( 'EXCEL_TWO_STEP_IMPORT_PARSER', __DIR__ . '/inc/classes/parser/' );
define( 'EXCEL_TWO_STEP_IMPORT_CREATOR', __DIR__ . '/inc/classes/creator/' );


require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Main.php';
require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Form.php';
require_once EXCEL_TWO_STEP_IMPORT_COMPONENTS . 'Sanitizer.php';
require_once EXCEL_TWO_STEP_IMPORT_CREATOR . 'Post_creator.php';


$excel_two_step_import = new Main('Post_creator' );
$excel_two_step_import->init();

