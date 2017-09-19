<?php

/**
 * Deprecated tasks.
 *
 * PHP Version 5 and 7
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
namespace Phing\Ssk\Tasks;

require_once 'phing/Task.php';

use BuildException;
use PhingFile;
use Project;

/**
 * A Phing task to for deprecated tasks.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class PhingDeprecatedTask extends \Task
{
    /**
     * The called Phing task.
     *
     * @var PhingTask
     */
    private $_callee;

    /**
     * The target to call if subsite doesn't use it..
     *
     * @var string
     */
    private $_subTargetName;

    /**
     * Whether to inherit all properties from current project.
     *
     * @var boolean
     */
    private $_inheritAll = true;

    /**
     * Whether to inherit refs from current project.
     *
     * @var boolean
     */
    private $_inheritRefs = false;


    /**
     *  If true, pass all properties to the new Phing project.
     *  Defaults to true. Future use.
     *
     * @param boolean $inherit new value
     *
     * @return void
     */
    public function setInheritAll($inherit)
    {
        $this->_inheritAll = (boolean) $inherit;

    }//end setInheritAll()


    /**
     *  If true, pass all references to the new Phing project.
     *  Defaults to false. Future use.
     *
     * @param boolean $inheritRefs new value
     *
     * @return void
     */
    public function setInheritRefs($inheritRefs)
    {
        $this->_inheritRefs = (boolean) $inheritRefs;

    }//end setInheritRefs()


    /**
     * Target to execute, required.
     *
     * @param string $target Target to be called
     *
     * @return void
     */
    public function setTarget($target)
    {
        $this->subTarget = (string) $target;

    }//end setTarget()


    /**
     *  Init this task by creating new instance of the phing task and
     *  configuring it's by calling its own init method.
     *
     * @return void
     */
    public function init()
    {
        $this->_callee = $this->project->createTask("phing");
        $this->_callee->setOwningTarget($this->getOwningTarget());
        $this->_callee->setTaskName($this->getTaskName());
        $this->_callee->setHaltOnFailure(true);
        $this->_callee->setLocation($this->getLocation());
        $this->_callee->init();

    }//end init()


    /**
     *  Hand off the work to the phing task of ours, after setting it up
     *
     * @throws BuildException on validation failure or if the target didn't
     *  execute.
     *
     * @return void
     */
    public function main()
    {

        $sec           = 0;
        $target        = $this->getOwningTarget();
        $targetName    = $target->getName();
        $subTargetName = $this->subTarget;
        $allTargets    = $this->project->getTargets();
        $usedTargets   = array_filter(
            $allTargets,
            function ($key) {
                return strrpos($key, ".".$targetName) > 0;
            },
            ARRAY_FILTER_USE_KEY
        );

        // If the target was redefined outside of the starterkit.
        if (!empty($usedTargets)) {
            $sec       = 10;
            $newTarget = $targetName;
            $buildFile = "build.project.xml";
            $target    = $allTargets[$subTargetName];
        } else {
            // If a deprecated target is called without redefinition.
            $sec       = 5;
            $newTarget = $subTargetName;
            $buildFile = $this->project->getProperty("phing.file");
        }

        // Inform user of the deprecated target.
        $this->log(
            "Target '".$targetName."' has been replaced by '".$subTargetName."'.",
            Project::MSG_WARN
        );
        $this->log(
            "A ".$sec." second penalty is assigned usage of this target.",
            Project::MSG_WARN
        );
        $this->log(
            "Please use '".$subTargetName."' instead to avoid the penalty.",
            Project::MSG_WARN
        );
        $this->log(
            "Running PhingCallTask for target '".$subTargetName."'",
            Project::MSG_DEBUG
        );
        sleep($sec);

        if ($this->_callee === null) {
            $this->init();
        }

        if ($this->subTarget === null) {
            throw new BuildException(
                "Attribute target is required.",
                $this->getLocation()
            );
        }

        $this->_callee->setPhingfile($buildFile);
        $this->_callee->setTarget($newTarget);
        $this->_callee->setOwningTarget($target);
        $this->_callee->setInheritAll($this->_inheritAll);
        $this->_callee->setInheritRefs($this->_inheritRefs);
        $this->_callee->main();

    }//end main()


}//end class
