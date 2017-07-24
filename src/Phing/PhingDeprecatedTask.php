<?php

namespace NextEuropa\Phing;

require_once 'phing/Task.php';

use BuildException;
use PhingFile;
use Project;

class PhingDeprecatedTask extends \Task
{
    /**
     * The called Phing task.
     *
     * @var PhingTask
     */
    private $callee;

    /**
     * The target to call if subsite doesn't use it..
     *
     * @var string
     */
    private $subTargetName;

    /**
     * Whether to inherit all properties from current project.
     *
     * @var boolean
     */
    private $inheritAll = true;

    /**
     * Whether to inherit refs from current project.
     *
     * @var boolean
     */
    private $inheritRefs = false;

    /**
     *  If true, pass all properties to the new Phing project.
     *  Defaults to true. Future use.
     * @param boolean new value
     */
    public function setInheritAll($inherit)
    {
        $this->inheritAll = (boolean) $inherit;
    }

    /**
     *  If true, pass all references to the new Phing project.
     *  Defaults to false. Future use.
     *
     * @param boolean new value
     */
    public function setInheritRefs($inheritRefs)
    {
        $this->inheritRefs = (boolean) $inheritRefs;
    }

    /**
     * Target to execute, required.
     * @param $target
     */
    public function setTarget($target)
    {
        $this->subTarget = (string) $target;
    }

    /**
     *  init this task by creating new instance of the phing task and
     *  configuring it's by calling its own init method.
     */
    public function init()
    {
        $this->callee = $this->project->createTask("phing");
        $this->callee->setOwningTarget($this->getOwningTarget());
        $this->callee->setTaskName($this->getTaskName());
        $this->callee->setHaltOnFailure(true);
        $this->callee->setLocation($this->getLocation());
        $this->callee->init();
    }

    /**
     *  hand off the work to the phing task of ours, after setting it up
     * @throws BuildException on validation failure or if the target didn't
     *  execute.
     */
    public function main()
    {

        $sec = 0;
        $target =  $this->getOwningTarget();
        $targetName = $target->getName();
        $subTargetName = $this->subTarget;
        $allTargets = $this->project->getTargets();
        $usedTargets= array_filter($allTargets, function($key) {
            return strrpos($key, "." . $targetName) > 0;
        }, ARRAY_FILTER_USE_KEY);

        // If the target was redefined outside of the starterkit.
        if (!empty($usedTargets)) {
            $sec = 10;
            $newTarget = $targetName;
            $buildFile = "build.project.xml";
            $target = $allTargets[$subTargetName];
        }
        // If a deprecated target is called without redefinition.
        else {
            $sec = 5;
            $newTarget = $subTargetName;
            $buildFile = $this->project->getProperty("phing.file");
        }

        // Inform user of the deprecated target.
        $this->log("Target '" . $targetName . "' has been replaced by '" . $subTargetName . "'.", Project::MSG_WARN);
        $this->log("A " . $sec . " second penalty is assigned usage of this target.", Project::MSG_WARN);
        $this->log("Please use '" . $subTargetName . "' instead to avoid the penalty.", Project::MSG_WARN);
        $this->log("Running PhingCallTask for target '" . $subTargetName . "'", Project::MSG_DEBUG);
        sleep($sec);

        if ($this->callee === null) {
            $this->init();
        }

        if ($this->subTarget === null) {
            throw new BuildException("Attribute target is required.", $this->getLocation());
        }

        $this->callee->setPhingfile($buildFile);
        $this->callee->setTarget($newTarget);
        $this->callee->setOwningTarget($target);
        $this->callee->setInheritAll($this->inheritAll);
        $this->callee->setInheritRefs($this->inheritRefs);
        $this->callee->main();
    }
}
