<?php
/**
 * \QualityAssurance\Sniffs\Generic\CredentialsSniff.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */

namespace QualityAssurance\Sniffs\Generic;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * \QualityAssurance\Sniffs\Generic\CredentialsSniff.
 *
 * Checks docker-compose.yml for credentials.
 *
 * @category PHP
 * @package  PHP_CodeSniffer
 * @link     http://pear.php.net/package/PHP_CodeSniffer
 */
class CredentialsSniff implements Sniff
{


    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [ T_INLINE_HTML ];

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
        $end = (count($phpcsFile->getTokens()) + 1);
        $filePath = $phpcsFile->getFilename();
        $fileName = basename($filePath);

        if (preg_match('/^docker-compose*/i', $fileName) !== 1) {
            return $end;
        }

        $fileContent = file($filePath);
        $checkEnvVars = [
            'asda_user',
            'asda_password',
            'nextcloud_user',
            'nextcloud_pass',
            'api_token',
        ];
        try {
            $yaml = Yaml::parseFile($filePath);
        } catch (ParseException $e) {
            $phpcsFile->addError($e->getMessage(), $stackPtr, 'Yaml');
            return $end;
        }

        // Parse the environment variables.
        if (isset($yaml['services']) === true) {
            foreach ($yaml['services'] as $service) {
                // Check if environment variables contain credentials.
                if (isset($service['environment']) === true) {
                    foreach ($service['environment'] as $envVarName => $envVarValue) {
                        foreach ($checkEnvVars as $checkEnvVar) {
                            $envVarNameLower = strtolower($envVarName);
                            if (strpos($envVarNameLower, $checkEnvVar) !== false && $envVarValue !== '' && $envVarValue !== null) {
                                $lines = preg_grep("/($envVarName)/s", $fileContent);
                                $message = "Do not commit credentials in the '$fileName' file! '$envVarName' has a value. It should remain empty.";
                                $phpcsFile->addError($message, key($lines), 'Credentials');
                            }
                        }
                    }
                }
            }//end foreach
        }//end if

        // Only run this sniff once on the file.
        return $end;

    }//end process()


}//end class
