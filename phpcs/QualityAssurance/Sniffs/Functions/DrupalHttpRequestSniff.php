<?php
/**
 * \QualityAssurance\Sniffs\Functions\DrupalHttpRequestSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;

/**
 * \QualityAssurance\Sniffs\Functions\DrupalHttpRequestSniff.
 *
 * Reject curl functions, they should use \Drupal::httpClient() instead.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalHttpRequestSniff extends ForbiddenFunctionsSniff
{

    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the function should
     * just not be used.
     *
     * @var array|null
     */
    public $forbiddenFunctions = ['curl_init' => '\Drupal::httpClient'];

    /**
     * If true, an error will be thrown; otherwise a warning.
     *
     * @var boolean
     */
    public $error = true;

}//end class
