<?php

/**
 * Gitigone Merger.
 *
 * PHP Version 5 and 7
 *
 * @category Tools
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
namespace Phing\Ssk\Tasks;

require_once 'phing/Task.php';

use BuildException;

/**
 * A Phing task to merge ignore file.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class GitignoreMergerTask extends \Task
{
    /**
     * An array of gitignore gitignoreFiles to merge.
     *
     * @var string
     */
    protected $gitignoreFiles = '';


    /**
     * Sets the list gitignore gitignoreFiles to merge.
     *
     * @param string $gitignoreFiles list of ignore files to be merged
     *
     * @return void
     */
    public function setGitignoreFiles($gitignoreFiles)
    {
        $this->gitignoreFiles = array();
        $token = ' ,;';
        $file  = strtok($gitignoreFiles, $token);
        while ($file !== false) {
            $this->gitignoreFiles[] = $file;
            $file = strtok($token);
        }

    }//end setGitignoreFiles()


    /**
     *  Run the task.
     *
     * @throws BuildException  trouble, probably file IO
     *
     * @return void
     */
    public function main()
    {
        $gitignoreFiles     = $this->gitignoreFiles;
        $gitignoreMerged    = array();
        $gitignoreFileArray = array();
        if (!empty($gitignoreFiles) && is_array($gitignoreFiles)) {
            foreach ($gitignoreFiles as $gitignoreFile) {
                if (is_file($gitignoreFile)) {
                    $gitignoreFileArray = array_merge(
                        $gitignoreFileArray,
                        file(
                            $gitignoreFile,
                            (FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
                        )
                    );
                }
            }
        }

        foreach ($gitignoreFileArray as $gitignoreLine) {
            if (strpos($gitignoreLine, '#') === 0) {
                $section = $gitignoreLine;
                if (!isset($gitignoreMerged[$section])) {
                    $gitignoreMerged[$section] = array();
                }
            } else {
                $gitignoreMerged[$section][] = $gitignoreLine;
            }
        }

        asort($gitignoreMerged);
        foreach ($gitignoreMerged as &$merged) {
            $merged = array_unique($merged);
            sort($merged);
        }

    }//end main()


}//end class
