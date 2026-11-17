<?php
/**
 * \QualityAssurance\Sniffs\InfoFiles\RequiredSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\InfoFiles;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * \QualityAssurance\Sniffs\InfoFiles\RequiredSniff.
 *
 * Checks the required property of php in info.yml files.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class RequiredSniff implements Sniff
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
     * @return int
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $filename      = $phpcsFile->getFilename();
        $fileExtension = strtolower(substr($filename, -9));
        if ($fileExtension !== '.info.yml') {
            return ($phpcsFile->numTokens + 1);
        }

        // Exclude config files which might contain the info.yml extension.
        $filenameWithoutExtension = substr($filename, 0, -9);
        if (strpos($filenameWithoutExtension, '.') !== false) {
            return ($phpcsFile->numTokens + 1);
        }

        $contents = file_get_contents($phpcsFile->getFilename());
        try {
            $info = Yaml::parse($contents);
        } catch (ParseException $e) {
            // If the YAML is invalid we ignore this file.
            return ($phpcsFile->numTokens + 1);
        }

        if (isset($info['name']) === false) {
            $error = "The key 'name' is missing in the info file";
            $phpcsFile->addError($error, $stackPtr, 'INFO');
        }

        if (isset($info['type']) === false) {
            $error = "The key 'type' is missing in the info file";
            $phpcsFile->addError($error, $stackPtr, 'INFO');
        }

        if (isset($info['core']) === false && isset($info['core_version_requirement']) === false) {
            $error = "One of the keys 'core' or 'core_version_requirement' is required in the info file";
            $phpcsFile->addError($error, $stackPtr, 'INFO');
        }

        if (isset($info['core']) === true && isset($info['core_version_requirement']) === true) {
            $error = "The keys 'core' and 'core_version_requirement' cannot be used together in the info file";
            $phpcsFile->addError($error, $stackPtr, 'INFO');
        }

        return ($phpcsFile->numTokens + 1);

    }//end process()


}//end class
