<?php

/**
 * A Phing helper to get dump date.
 *
 * PHP Version 5 and 7
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */

namespace Phing\Toolkit\Tasks;

require_once "phing/Task.php";

class AsdaDateTask extends \Task
{
  protected $path;

  public function setPath($path)
  {
    $this->path = $path;
  }

  public function init()
  {
  }

  public function main()
  {
    $latest = file_get_contents($this->path . '/latest.sh1');
    $filename = substr($latest, strpos($latest, ' ') + 2);

    // Display information about ASDA creation date.
    preg_match('/(\d{8})(?:-)?(\d{4})(\d{2})?/', $filename, $matches);
    $date = date_parse_from_format('YmdHis', $matches[1] . $matches[2] . ($matches[3] ?? '00'));
    if ($date['year'] && $date['month'] && $date['day']) {
      $dumpTimestamp = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
      echo sprintf('ASDA DATE: %d %s %d at %s:%s', $date['day'], date('M', $dumpTimestamp), $date['year'], $date['hour'], $date['minute']);
    } else {
      echo $filename;
    }
  }
}
