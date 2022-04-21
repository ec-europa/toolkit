<?php

/**
 * A Phing helper to execute php function urlencode().
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/PhpUrlencodeTask.php
 */

namespace Phing\Toolkit\Tasks;

require_once "phing/Task.php";

class PhpUrlencodeTask extends \Task
{
  protected $value;

  protected $returnProperty;

  public function setValue($value)
  {
    $this->value = $value;
  }

  public function setReturnProperty($prop)
  {
    $this->returnProperty = $prop;
  }

  public function init()
  {
  }

  public function main()
  {
    $this->project->setProperty($this->returnProperty, urlencode($this->value));
  }
}
