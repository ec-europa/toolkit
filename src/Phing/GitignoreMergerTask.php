<?php

namespace NextEuropa\Phing;

require_once 'phing/Task.php';

use BuildException;

class GitignoreMergerTask extends \Task
{
  /**
   * An array of gitignore gitignoreFiles to merge.
   * @var string
   */
  protected $gitignoreFiles = '';

  /**
   * Sets the list gitignore gitignoreFiles to merge.
   *
   * @param string gitignoreFiles
   */
  public function setGitignoreFiles($gitignoreFiles) {
    $this->gitignoreFiles = array();
    $token = ' ,;';
    $file = strtok($gitignoreFiles, $token);
    while ($file !== FALSE) {
      $this->gitignoreFiles[] = $file;
      $file = strtok($token);
    }
  }

  /**
   *  Run the task.
   *
   * @throws BuildException  trouble, probably file IO
   */
  public function main()
  {
    $gitignoreFiles = $this->gitignoreFiles;
    $gitignoreMerged = array();
    $gitignoreFileArray = array();
    if (!empty($gitignoreFiles) && is_array($gitignoreFiles)) {
      foreach ($gitignoreFiles as $gitignoreFile) {
        if (is_file($gitignoreFile)) {
          $gitignoreFileArray = array_merge($gitignoreFileArray, file($gitignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }
      }
    }

    foreach ($gitignoreFileArray as $gitignoreLine) {
      if (strpos($gitignoreLine, '#') === 0) {
        $section = $gitignoreLine;
        if (!isset($gitignoreMerged[$section])) {
          $gitignoreMerged[$section] = array();
        }
      }
      else {
        $gitignoreMerged[$section][] = $gitignoreLine;
      }
    }
    asort($gitignoreMerged);
    foreach ($gitignoreMerged as &$merged) {
      $merged = array_unique($merged);
      sort($merged);
   }
  }
}
