<?php
/**
 * \QualityAssurance\Sniffs\InstallFiles\FunctionDeclarationsSniff.
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
 * \QualityAssurance\Sniffs\InstallFiles\FunctionDeclarationsSniff.
 *
 * Checks the presence of helper function in the install file.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class FunctionDeclarationsSniff implements Sniff
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
        $allowedHooks = [
            $fileName.'_install',
            $fileName.'_install_tasks',
            $fileName.'_uninstall',
            $fileName.'_enable',
            $fileName.'_disable',
            $fileName.'_schema',
            $fileName.'_field_schema',
            $fileName.'_requirements',
            $fileName.'_update_last_removed',
            $fileName.'_update_dependencies',
        ];
        if (in_array($functionName, $allowedHooks) === false && empty(preg_match('/'.$fileName.'_update_\d{4}/', $functionName)) === true) {
            $warning = 'Move the "%s" function declaration to a helper class implementing ContainerInjectionInterface. Example can be found at https://git.drupalcode.org/project/drupal/blob/8.7.5/core/profiles/demo_umami/modules/demo_umami_content/demo_umami_content.install';
            $phpcsFile->addError($warning, $stackPtr, 'NonHookFound', [$functionName, $fileName, $fileName]);
        }

    }//end process()


}//end class
