<?php
/**
 * \QualityAssurance\Sniffs\InfoFiles\ForbiddenSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\InfoFiles;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * \QualityAssurance\Sniffs\InfoFiles\ForbiddenSniff.
 *
 * Checks the forbidden properties in info.yml files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class ForbiddenSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_INLINE_HTML];

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
        // Only run this sniff once per info file.
        if (preg_match('/\.info\.yml$/', $phpcsFile->getFilename()) === 1) {
            // Drupal 8 style info.yml file.
            $contents = file_get_contents($phpcsFile->getFilename());
            try {
                $info = \Symfony\Component\Yaml\Yaml::parse($contents);
            } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
                // If the YAML is invalid we ignore this file.
                return ($phpcsFile->numTokens + 1);
            }
        } else {
            return ($phpcsFile->numTokens + 1);
        }

        // Since we don't have forbidden properties for D8 yet we use a dummy in
        // this sniff for testing purposes. Whenever we have an addition to the
        // forbidden properties list we can adapt this code to reflect that
        // change.
        if (isset($info['no_forbidden_yet']) === true) {
            $warning = 'Remove "no_forbidden_yet" from the info file.';
            $phpcsFile->addWarning($warning, $stackPtr, 'Project');
        }

        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
