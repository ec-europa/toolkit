<?php
/**
 * \QualityAssurance\Sniffs\InstallFiles\InstallUpdateCallbacksSniff.
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
 * \QualityAssurance\Sniffs\InstallFiles\InstallUpdateCallbacksSniff.
 *
 * Checks the presence of update callback functions in hook install.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class InstallUpdateCallbacksSniff implements Sniff
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
        if ($functionName !== $fileName.'_install') {
            return;
        }

        $string = $phpcsFile->findNext(
            T_STRING,
            $tokens[$stackPtr]['scope_opener'],
            $tokens[$stackPtr]['scope_closer']
        );
        while ($string !== false) {
            if (empty(preg_match('/^'.$fileName.'_update_\d{4}$/', $tokens[$string]['content'])) === false) {
                $opener = $phpcsFile->findNext(
                    Tokens::$emptyTokens,
                    ($string + 1),
                    null,
                    true
                );
                if ($opener !== false
                    && $tokens[$opener]['code'] === T_OPEN_PARENTHESIS
                ) {
                    $warning = 'Do not call "%s()" in hook_install().';
                    $phpcsFile->addWarning($warning, $string, 'CallbackNotAllowed', [$tokens[$string]['content']]);
                }
            }

            $string = $phpcsFile->findNext(
                T_STRING,
                ($string + 1),
                $tokens[$stackPtr]['scope_closer']
            );
        }//end while

    }//end process()


}//end class
