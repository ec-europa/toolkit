<?php
/**
 * \QualityAssurance\Sniffs\InstallFiles\HookUpdate0Sniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\InstallFiles;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * \QualityAssurance\Sniffs\InstallFiles\HookUpdate0Sniff.
 *
 * Checks the presence of hook_update_X000 functions.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class HookUpdate0Sniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];

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
        $fileExtension = strtolower(substr($phpcsFile->getFilename(), -7));
        if ($fileExtension !== 'install') {
            return;
        }

        $tokens       = $phpcsFile->getTokens();
        $functionName = $tokens[($stackPtr + 2)]['content'];
        $fileName     = substr(basename($phpcsFile->getFilename()), 0, -8);
        if (str_starts_with($functionName, "{$fileName}_update_") === true && str_ends_with($functionName, '000') === true) {
            $number = preg_replace('/[^0-9]/', '', $functionName);
            $phpcsFile->addError("Update schema $number is reserved for upgrading.", $stackPtr, "Update{$number}NotAllowed");
        }

    }//end process()


}//end class
