<?php
/**
 * Package Manager
 *
 * @copyright Copyright 2017 id[+] Technology
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
  * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
  *
  * From http://stackoverflow.com/questions/3933668/convert-array-into-csv
  * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
  */
function package_manager_array_to_csv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ( $fields as $field ) {
        if ($field === null && $nullToMysqlNull) {
            $output[] = 'NULL';
            continue;
        }

		// fix csv line break
		$field = str_replace(array("\r\n", "\n\r", "\n", "\r"), '', $field); 
		
        // Enclose fields containing $delimiter, $enclosure or whitespace
        if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        }
        else {
            $output[] = $field;
        }
    }

    return implode( $delimiter, $output );
}


function package_manager_array_same_content($a, $b) {
    return (empty(array_diff($a, $b)) && empty(array_diff($b, $a)));
}


require_once("Spyc.php");

class Zend_Form_Element_Html extends Zend_Form_Element_Xhtml {
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNote';

    public function isValid($value, $context = null) {
        return true;
    }
}