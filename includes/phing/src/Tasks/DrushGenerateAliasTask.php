<?php

/**
 * Generate an aliases.drushrc.php file.
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

use Project;
use Symfony\Component\Finder\Finder;

require_once 'phing/Task.php';

/**
 * A Phing task to generate an aliases.drushrc.php file.
 *
 * @category Documentation
 * @package  SSK
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/ssk/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class DrushGenerateAliasTask extends \Task
{

    /**
     * The name of the alias to generate.
     *
     * @var string
     */
    private $_aliasName = '';

    /**
     * The uri of the alias to generate.
     *
     * @var string
     */
    private $_aliasUri = '';

    /**
     * The root directory of the website.
     *
     * @var string
     */
    private $_siteRoot = '';

    /**
     * The directory to save the aliases in.
     *
     * @var string
     */
    private $_drushDir = '/sites/all/drush';


    /**
     * Generates an aliases.drushrc.php file.
     *
     * Either generates a file for:
     *  - all sites in the sites directory.
     *  - a single site to be added to the aliases file (appending).
     *
     * @return void
     */
    public function main()
    {
        // Check if all required data is present.
        $this->checkRequirements();

        $drushDir    = $this->_drushDir == '/sites/all/drush' ? $this->_siteRoot.$this->_drushDir : $this->_drushDir;
        $aliasesFile = $drushDir.'/aliases.drushrc.php';

        $aliases = array(
                    'default' => array(
                        'uri'  => 'default',
                        'root' => $this->_siteRoot,
                      ),
                    );

        if (empty($this->_aliasName)) {
            $sites = new Finder();
            $sites
                ->directories()
                ->depth('== 0')
                ->exclude('all')
                ->in($this->_siteRoot.'/sites');

            foreach ($sites as $site) {
                $aliases[$site->getBasename()] = array(
                                                  'uri'  => $site->getBasename(),
                                                  'root' => $aliases['default']['root'],
                                                 );
            }
        } else {
            $aliases += $this->loadAliases($aliasesFile);
            $aliases[$this->_aliasName] = array(
                                          'uri'  => $this->_aliasName,
                                          'root' => $aliases['default']['root'],
                                         );
        }//end if

        $aliasesArray = "<?php \n\n\$aliases = ".var_export($aliases, true).";";

        if (file_put_contents($aliasesFile, $aliasesArray)) {
            $this->log("Succesfully wrote aliases to file '".$aliasesFile."'", Project::MSG_INFO);
        } else {
            $this->log("Was unable to write aliases to file '".$aliasesFile."'", Project::MSG_WARN);
        }
    }//end main()

    /**
     * Checks if all properties required for generating the aliases file are present.
     *
     * @throws \BuildException
     *   Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array('_siteRoot');
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException("Missing required property '$required_property'.");
            }
        }
    }//end checkRequirements()

    /**
     * Load drush aliases.
     *
     * @param string $aliasesFile File with alias
     *
     * @return array|mixed
     */
    protected function loadAliases($aliasesFile)
    {
        if (is_file($aliasesFile)) {
            return include $aliasesFile;
        }

        return array();
    }//end loadAliases()

    /**
     * Sets the name of the alias to set.
     *
     * @param string $aliasName The name of the alias to set.
     *
     * @return void
     */
    public function setAliasName($aliasName)
    {
        $this->_aliasName = $aliasName;
    }//end setAliasName()

    /**
     * Sets the uri of tha alias to set.
     *
     * @param string $aliasUri The uri of the alias to set.
     *
     * @return void
     */
    public function setAliasUri($aliasUri)
    {
        $this->_aliasUri = $aliasUri;
    }//end setAliasUri()

    /**
     * Sets the root of the Drupal site.
     *
     * @param string $siteRoot The root of the Drupal site.
     *
     * @return void
     */
    public function setSiteRoot($siteRoot)
    {
        $this->_siteRoot = $siteRoot;
    }//end setSiteRoot()

    /**
     * Sets the diurectory of drush to place the aliases in.
     *
     * @param string $drushDir The Drush directory to place the aliases in
     *
     * @return void
     *
     * @todo: validate if it is a registered location of drush.
     * @link: https://github.com/drush-ops/drush/blob/master/examples/example.aliases.drushrc.php#L57
     */
    public function setDrushDir($drushDir)
    {
        $this->_drushDir = $drushDir;
    }//end setDrushDir()

}//end class
