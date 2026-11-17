<?php
/**
 * \QualityAssurance\Sniffs\Functions\DrupalFormAlterSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\Functions\DrupalFormAlterSniff.
 *
 * Checks the presence of form_alter function on .module files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class DrupalForbiddenHooksSniff implements Sniff
{

    /**
     * A list of forbidden hooks with their alternatives.
     *
     * The value is empty string if no alternative exists, i.e. the hook should
     * just not be used.
     *
     * @var array|null
     */
    public $forbiddenHooks = ['hook_form_alter' => 'hook_form_FORM_ID_alter() or hook_form_BASE_FORM_ID_alter()'];

    /**
     * A list of file extensions to check.
     *
     * @var array
     */
    public $extensions = [
        'inc',
        'module',
        'theme',
    ];


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
        $filename  = basename($phpcsFile->getFilename());
        $exploded  = explode('.', $filename);
        $extension = end($exploded);
        if (false === in_array($extension, $this->extensions)) {
            return;
        }

        $module   = $exploded[0];
        $tokens   = $phpcsFile->getTokens();
        $function = $tokens[($stackPtr + 2)]['content'];
        foreach ($this->forbiddenHooks as $hook => $replacement) {
            if (true === ($function === str_replace('hook', $module, $hook))) {
                $warning = 'The usage of the hook %s() is forbidden';
                $data = [$hook];
                if (false === empty($replacement)) {
                    $warning .= ', instead use %s.';
                    $data[] = $replacement;
                } else {
                    $warning .= '.';
                }

                $phpcsFile->addError($warning, $stackPtr, 'ForbiddenHook', $data);
            }
        }

    }//end process()


}//end class
