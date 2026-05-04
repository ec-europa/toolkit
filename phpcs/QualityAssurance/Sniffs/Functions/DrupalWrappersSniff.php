<?php
/**
 * \QualityAssurance\Sniffs\Functions\DrupalWrappersSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Functions;

use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;

/**
 * \QualityAssurance\Sniffs\Functions\DrupalWrappersSniff.
 *
 * Discourage the use of functions that have a Drupal wrapper function.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalWrappersSniff extends ForbiddenFunctionsSniff
{

    /**
     * A list of forbidden functions with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the function should
     * just not be used.
     *
     * @var array|null
     */
    public $forbiddenFunctions = [
        'register_shutdown_function' => 'drupal_register_shutdown_function',
        'set_time_limit'             => 'drupal_set_time_limit',
        'xml_parser_create'          => 'drupal_xml_parser_create',
        'chmod'                      => 'FileSystemInterface::chmod',
        'dirname'                    => 'FileSystemInterface::dirname',
        'mkdir'                      => 'FileSystemInterface::mkdir',
        'move_uploaded_file'         => 'FileSystemInterface::moveUploadedFile',
        'rmdir'                      => 'FileSystemInterface::rmdir',
        'tempnam'                    => 'FileSystemInterface::tempnam',
        'unlink'                     => 'FileSystemInterface::unlink',
        'lcfirst'                    => 'Unicode::lcfirst',
        'ucwords'                    => 'Unicode::ucwords',
        'http_build_query'           => 'UrlHelper::buildQuery',
        'parse_url'                  => 'UrlHelper::parse',

        'ucfirst'                    => 'Unicode::ucfirst',
        'copy'                       => 'FileSystemInterface::copy',
        'rename'                     => 'FileSystemInterface::move',
        'substr'                     => 'mb_substr',
        'strtolower'                 => 'mb_strtolower',
        'strtoupper'                 => 'mb_strtoupper',
        'date'                       => 'Drupal::service("date.formatter")->format',
    ];

    /**
     * If true, an error will be thrown; otherwise a warning.
     *
     * @var boolean
     */
    public $error = true;

}//end class
