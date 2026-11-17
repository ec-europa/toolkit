<?php
/**
 * \QualityAssurance\Sniffs\Functions\DrupalDeprecatedSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\DeprecatedFunctionsSniff;

/**
 * \QualityAssurance\Sniffs\Functions\DrupalDeprecatedSniff.
 *
 * Discourage the use of functions that are marked as deprecated.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalDeprecatedSniff extends DeprecatedFunctionsSniff
{

    /**
     * A list of deprecated functions with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the function should
     * just not be used.
     *
     * @var array|null
     */
    public $forbiddenFunctions = ['debug' => 'dump'];

    /**
     * If true, an error will be thrown; otherwise a warning.
     *
     * @var boolean
     */
    public $error = true;


    /**
     * Generates the error or warning for this sniff.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the forbidden function
     *                                               in the token array.
     * @param string                      $function  The name of the forbidden function.
     * @param string                      $pattern   The pattern used for the match.
     *
     * @return void
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern=null)
    {
        $data  = [$function];
        $error = 'Function %s() has been deprecated';
        $type  = 'Deprecated';

        if (empty($this->forbiddenFunctions[$function]) === false) {
            $data[] = $this->forbiddenFunctions[$function];
            $error .= '; use %s() instead';
        }

        if ($this->error === true) {
            $phpcsFile->addError($error, $stackPtr, $type, $data);
        } else {
            $phpcsFile->addWarning($error, $stackPtr, $type, $data);
        }

    }//end addError()


}//end class
