<?php

namespace NextEuropa\Phing;

require_once 'phing/Task.php';

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
     * The target to call.
     *
     * @var string
     */
    private $subTarget;

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
     * Alias for createProperty
     * @see createProperty()
     */
    public function createParam()
    {
        if ($this->callee === null) {
            $this->init();
        }

        return $this->callee->createProperty();
    }

    /**
     * Property to pass to the invoked target.
     */
    public function createProperty()
    {
        if ($this->callee === null) {
            $this->init();
        }

        return $this->callee->createProperty();
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
     *                        execute
     */
    public function main()
    {
        if ($this->getOwningTarget()->getName() === "") {
            $this->log("Cowardly refusing to call target '{$this->subTarget}' from the root", Project::MSG_WARN);
            return;
        }

        $this->log("Target '" . $this->getOwningTarget()->getName() . "' is deprecated and has been replaced by '" . $this->subTarget . "'.", Project::MSG_WARN);
        $this->log("A 10 second penalty is assigned to this target, please update your build.", Project::MSG_WARN);
        sleep(10);

        if ($this->callee === null) {
            $this->init();
        }

        if ($this->subTarget === null) {
            throw new BuildException("Attribute target is required.", $this->getLocation());
        }

        $this->callee->setPhingfile($this->project->getProperty("phing.file"));
        $this->callee->setTarget($this->subTarget);
        $this->callee->setInheritAll($this->inheritAll);
        $this->callee->setInheritRefs($this->inheritRefs);
        $this->callee->main();
    }

}
