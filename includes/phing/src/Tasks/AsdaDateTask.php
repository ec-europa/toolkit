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
    // Display information about ASDA creation date.
    $dumpData = substr(substr($latest, strpos($latest, ' ') + 2), 0, 15);
    $dumpDate = date_parse_from_format("Ymd-His", $dumpData);
    $dumpTimestamp = mktime($dumpDate['hour'], $dumpDate['minute'], $dumpDate['second'], $dumpDate['month'], $dumpDate['day'], $dumpDate['year']);
    $dumpHrdate = 'ASDA DATE: ' . $dumpDate['day'] . ' ' . date('M', $dumpTimestamp) . ' ' . $dumpDate['year'] . ' at ' . $dumpDate['hour'] . ':' . $dumpDate['minute'];
    echo $dumpHrdate;
  }
}
