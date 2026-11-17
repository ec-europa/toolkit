<?php
/**
 * \QualityAssurance\Sniffs\Generic\DeprecatedConstantsSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Generic;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\Generic\DeprecatedConstantsSniff.
 *
 * Discourage the use of deprecated constants.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DeprecatedConstantsSniff implements Sniff
{

    /**
     * A list of forbidden constants with their alternatives.
     *
     * The value is NULL if no alternative exists, i.e., the constant should
     * just not be used.
     *
     * @var array|null
     */
    public $deprecatedConstants = ['REQUEST_TIME' => 'Drupal::time()->getRequestTime()'];


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [ T_STRING ];

    }//end register()


    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token
     *                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $const = $tokens[$stackPtr]['content'];
        if (array_key_exists($const, $this->deprecatedConstants) === false) {
            return;
        }

        $data = [$const];
        $error = 'The constant %s is deprecated';
        if (empty($this->deprecatedConstants[$const]) === false) {
            $data[] = $this->deprecatedConstants[$const];
            $error .= '; use %s instead';
        }

        $phpcsFile->addError($error, $stackPtr, 'Found', $data);

    }//end process()


}//end class
