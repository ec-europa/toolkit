<?php

namespace NextEuropa\Phing;

require_once 'phing/Task.php';

use BuildException;
use ProjectConfigurator;
use PhingFile;
use Project;
use Properties;

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

        $targets = $this->project->getTargets();
        $owningTarget = $this->getOwningTarget()->getName();
        $calledTargets= array_filter($targets, function($key) {
            return strrpos($key, "." . $owningTarget) > 0;
        }, ARRAY_FILTER_USE_KEY);

        $this->log("Target '$owningTarget' has been replaced by '$this->subTarget'.", Project::MSG_WARN);
        $this->log("A 10 second penalty is assigned to use of this target.", Project::MSG_WARN);
        $this->log("Please use '$this->subTarget' from now on to avoid the penalty.", Project::MSG_WARN);
        sleep(10);

        if (!empty($calledTargets)) {
            $calledTargets = reset(array_reverse($calledTargets));
            $reference = strtok(key($calledTargets), '.');
            $buildFile = $this->project->getProperty("phing.file." . $reference);
            $this->callee->setPhingfile('build.project.xml');
            $this->callee->setOwningTarget($targets[$this->subTarget]);
            $this->callee->setTarget($owningTarget);
        }
        else {
           $this->callee->setPhingfile($this->project->getProperty("phing.file"));
           $this->callee->setTarget($this->subTarget);
        }

        $this->log("Running PhingCallTask for target '" . $this->subTarget . "'", Project::MSG_DEBUG);
        if ($this->callee === null) {
            $this->init();
        }

        if ($this->subTarget === null) {
            throw new BuildException("Attribute target is required.", $this->getLocation());
        }

        $this->callee->setInheritAll($this->inheritAll);
        $this->callee->setInheritRefs($this->inheritRefs);
        $this->callee->main();
    }

}
