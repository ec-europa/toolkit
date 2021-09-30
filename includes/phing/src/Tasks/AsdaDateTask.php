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
    protected $project_id;

    public function setProjectId($project_id)
    {
        $this->project_id = $project_id;
    }

    public function init()
    {
    }

    public function main()
    {
      $dumpLocation = '/tmp/toolkit/subsites/packages/database/' . $this->project_id;
      // Display information about ASDA creation date.
      $dumpData = substr(substr(file_get_contents($dumpLocation . '/latest.sh1'), (strpos(file_get_contents($dumpLocation . '/latest.sh1'), ' ')) + 2), 0, 15);
      $dumpDate = date_parse_from_format("Ymd-His", $dumpData);
      $dumpTimestamp = mktime($dumpDate['hour'], $dumpDate['minute'], $dumpDate['second'], $dumpDate['month'], $dumpDate['day'], $dumpDate['year']);
      $dumpHrdate = 'ASDA DATE: ' . $dumpDate['day'] . ' ' . date('M', $dumpTimestamp) . ' ' . $dumpDate['year'] . ' at ' . $dumpDate['hour'] . ':' . $dumpDate['minute'];
      echo $dumpHrdate;
    }
}

  
  