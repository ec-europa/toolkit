<?php
/**
 * \QualityAssurance\Sniffs\InstallFiles\HookUpdateNSniff.
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
 * \QualityAssurance\Sniffs\InstallFiles\HookUpdateNSniff.
 *
 * Checks the hook_update_N function comment.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class HookUpdateNSniff implements Sniff
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
        if (preg_match('/'.$fileName.'_update_\d{4}$/', $functionName) === false) {
            return;
        }

        $find       = Tokens::$methodPrefixes;
        $find[]     = T_WHITESPACE;
        $commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
        if ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT
        ) {
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];
        $empty        = [
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_STAR,
        ];
        $short        = $phpcsFile->findNext($empty, ($commentStart + 1), $commentEnd, true);
        if ($short === false) {
            // No content at all.
            return;
        }

        // Account for the fact that a short description might cover
        // multiple lines.
        $shortContent = $tokens[$short]['content'];
        $shortEnd     = $short;
        for ($i = ($short + 1); $i < $commentEnd; $i++) {
            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                if ($tokens[$i]['line'] === ($tokens[$shortEnd]['line'] + 1)) {
                    $shortContent .= $tokens[$i]['content'];
                    $shortEnd      = $i;
                } else {
                    break;
                }
            }
        }

        // Check if hook_update_N implementation doc is formated correctly.
        if ($shortContent === 'Implements hook_update_N();') {
            $phpcsFile->addError('Replace "'.$shortContent.'" with a short description on the hooks functionality.', $short, "UpdateNDescription");
        }//end if

    }//end process()


}//end class
