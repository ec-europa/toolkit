<?php

/**
 * Github Collaborators integration.
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

use Symfony\Component\Yaml\Dumper;
use League\CLImate\CLImate;

require_once 'phing/Task.php';

/**
 * A Phing task to manage repository collaborators.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class RepositoryCollaboratorsTask extends \Task
{

    private $_githubUser        = '';
    private $_githubPass        = '';
    private $_githubCredentials = '';

    public $reference     = '';
    public $forks         = [];
    public $collaborators = [];
    public $maintainer    = '';
    public $op            = '';
    public $projectId     = '';

    /**
     * Sets the project ID.
     *
     * @param string $projectId The project ID.
     *
     * @return void
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        $this->_githubUser = getenv('GITHUB_USER');
        $this->_githubPass = getenv('GITHUB_PASS');
        $this->_githubCredentials = $this->_githubUser. ":" .$this->_githubPass;
    }

    /**
     * Sets the repository reference name.
     *
     * @param string $reference The reference repository name.
     *
     * @return void
     */
    public function setReference($reference)
    {
        $this->reference  = $reference;
    }

    /**
     * Sets the project maintainer.
     *
     * @param string $maintainer The repository maintainer.
     *
     * @return void
     */
    public function setMaintainer($maintainer)
    {
        $this->maintainer = $maintainer;
    }

    /**
     * Sets the operation to be executed.
     *
     * @param string $op The operation name.
     *
     * @return void
     */
    public function setOp($op)
    {
        $this->op = $op;
    }

    /**
     * The init method: Do init steps.
     *
     * @return void
     */
    public function init()
    {
//        $this->_githubUser = getenv('GITHUB_USER');
//        $this->_githubPass = getenv('GITHUB_PASS');
//        $this->_githubCredentials = $this->_githubUser. ":" .$this->_githubPass;
    }

    /**
     * Checks if all properties required for generating the makefile are present.
     *
     * @throws \BuildException
     *   Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array('projectId');
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException(
                    "Missing required property " . $required_property . "."
                );
            }
        }
    }

    /**
     * Generates a Drush make file.
     *
     * @return void
     */
    public function main()
    {

        // Check if all required data is present.
        $this->checkRequirements();

        // Init repository.
        $this->setForks();

        // Init contributor information.
        $this->setCollaborators($this->reference);
        foreach ($this->forks as $fullName => $url) {
            $this->setCollaborators($fullName);
        }

        switch ($this->op) {
        case 'add':
            $this->add();
            break;
        case 'remove':
            $this->remove();
            break;
        default:
            $this->overview();
            break;
        }
    }

    /**
     * List all forks for a given repository.
     *
     * @return void
     */
    protected function setForks()
    {
        $endpoint = 'repos/' . $this->reference . '/forks';
        $result = $this->repositoryQuery($endpoint);

        $forksData = json_decode($result['data']);

        if ($forksData->message != 'Not Found') {
            foreach ($forksData as $fork) {
                $this->forks[$fork->full_name] = $fork->html_url;
            }
        }
    }

    /**
     * List all collaborators for a given repository.
     *
     * @param string $repository   Repository name.
     * @param bool   $ignore_admin Include or not admins in the list.
     *
     * @return void
     */
    protected function setCollaborators($repository, $ignore_admin = false)
    {
        $collaborators_temp = [];

        // Get contributors for reference repository.
        $endpoint = 'repos/' . $repository . '/collaborators';
        $result = $this->repositoryQuery($endpoint);

        $collaborators = json_decode($result['data']);

        foreach ($collaborators as $key => $collaborator) {
            $collaborators_temp[] = [
                'name' => $collaborator->login,
                'pull' => $collaborator->permissions->pull,
                'push' => $collaborator->permissions->push,
                'admin' => $collaborator->permissions->admin,
            ];
        }

        $this->collaborators[$repository] = $collaborators_temp;
    }

    /**
     * List all collaborators for a given repository.
     *
     * @return void
     */
    protected function overview()
    {
        // List reference.
        echo " https://github.com/" . $this->reference;
        foreach ($this->collaborators[$this->reference] as $collaborator) {

            echo "\n " . $collaborator['pull'] .
                " " . $collaborator['push'] .
                " " . $collaborator['admin'] .
                "\t" . $collaborator['name'];
        }

        // List forks.
        if (count($this->forks) > 0 ) {
            foreach ($this->forks as $fullname => $url) {
                echo "\n\n" . $url . " (fork):\n";
                echo " R W A\tUser";

                foreach ($this->collaborators[$fullname] as $collaborator) {

                    echo "\n " . $collaborator['pull'] .
                        " " . $collaborator['push'] .
                        " " . $collaborator['admin'] .
                        "\t" . $collaborator['name'];
                }
            }
        }
    }

    /**
     * List all collaborators for a given repository.
     *
     * @return void
     */
    protected function add()
    {
        $endpoint = 'repos/' . $this->reference . '/collaborators/' .
            $this->maintainer;

        $result = $this->repositoryQuery($endpoint);

        if ($result['status'] == '204') {
            echo "Collaborator " . $this->maintainer .
                " already in the list of collaborators, no action required.\n";
        } else {
            $result = $this->repositoryQuery($endpoint, 'PUT');

            if ($result['status'] == '204') {
                echo "Collaborator " . $this->maintainer .
                    " have now READ access to repository.\n";
            } else {
                if ($result['status'] == '201') {
                    echo "Collaborator " . $this->maintainer .
                        " is now invited, waiting acceptance.\n";
                } else {
                    echo "Not possible add " . $this->maintainer .
                        " to the list of colaborators, operation failed.\n";
                }
            }
        }
    }

    /**
     * Remove specific user from repository.
     *
     * @return void
     */
    protected function remove()
    {
        $endpoint = 'repos/' . $this->reference . '/collaborators/' .
            $this->maintainer;
        $result = $this->repositoryQuery($endpoint);

        if ($result['status'] == '204') {
            $result = $this->repositoryQuery($endpoint, 'DELETE');
            if ($result['status'] == '204') {
                echo "Collaborator " . $this->maintainer .
                    " removed from repository.\n";
            } else {
                echo "Something happened, checking...\n";
            }
        } else {
            echo "Collaborator " . $this->maintainer . " not found.\n";
        }
    }

    /**
     * Execute a query against gitHub REST API 3.
     *
     * @param string $endpoint gitHub endpoint.
     * @param bool   $type     Type of POST to execute, false by default.
     *
     * @return array Return query result.
     */
    protected function repositoryQuery($endpoint, $type = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/' . $endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_githubCredentials);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Toolkit Drupal');

        if ($type != false) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-length: 0"]);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
            if ($type == 'PUT') {
                curl_setopt(
                    $ch, CURLOPT_POSTFIELDS,
                    http_build_query(['permission' => 'pull'])
                );
            }
        }

        $result=curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
        curl_close($ch);

        return [
            'status' => $status_code,
            'data' => $result
        ];
    }

}

