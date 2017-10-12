<?php

/**
 * Github Teams integration.
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

define('GITHUB_API', 'https://api.github.com/');

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Dumper;

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
class RepositoryTeamsTask extends \Task
{
    public $collaborators = [];
    public $githubData    = [];
    public $operation     = '';
    public $reference     = '';
    public $team          = '';

    protected $githubTeams = '';
    protected $githubCollaborators = [];

    /**
     * The init method: Do init steps.
     *
     * @return void
     */
    public function init()
    {
        // Get default properties from project.
        $propertyMap = array(
            'setCollaborators' => 'project.collaborators',
            'setReference'     => 'project.repository.name',
            'setTeam'          => 'project.repository.team'
        );

        foreach ($propertyMap as $method => $property) {
            if ($property = $this->getProject()->getProperty($property)) {
                call_user_func(array($this, $method), $property);
            }
        }
    }

    /**
     * Sets the Collaborators Prop.
     *
     * @param string $value The collaborators value.
     *
     * @return void
     */
    public function setCollaborators($value)
    {
        $this->collaborators = $value;
    }

    /**
     * Sets the reference repository Prop.
     *
     * @param string $value The collaborators value.
     *
     * @return void
     */
    public function setReference($value)
    {
        $this->reference = $value;
    }

    /**
     * Sets the team name Prop.
     *
     * @param string $value The team name.
     *
     * @return void
     */
    public function setTeam($value)
    {
        $this->team = $value;
    }

    /**
     * Sets the operation to be executed.
     *
     * @param string $operation The operation name.
     *
     * @return void
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
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
        $requiredProperties = array('operation');
        foreach ($requiredProperties as $requiredProperty) {
            if (empty($this->$requiredProperty)) {
                throw new \BuildException(
                    "Missing required property " . $requiredProperty . "."
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
        // Check requirements.
        $this->checkRequirements();

        // Init forks, collaborators and invites information.
        $this->getGithubTeams($this->reference);
        $this->getGithubCollaborators($this->reference);

        switch ($this->operation) {
        case 'add':
            // Code here.
            break;
        case 'remove':
            // Code here.
            break;
        default:
            $this->overview();
            break;
        }
    }

    /**
     * Print the current teams in the screen.
     *
     * @return void
     */
    public function overview()
    {

        $output = new ConsoleOutput();
        $output->writeln("Information in project props:");

        $header = ['Name', 'Value'];
        $props = [
            'Team' => $this->team,
            'Collaborators' => $this->collaborators,
        ];

        $this->_table($header, $props);
        $props = [];

        $output->writeln("Information in github repository:");

        $props['Team'] = '';
        $props['Members'] = '';
        $props['Collaborators'] = '';

        foreach ($this->githubTeams as $team) {
            $props['Team'][] = $team['name'];
            $props['Members'][] = implode("\n", $team['members']);
        }

        $props['Team'] = implode(', ', $props['Team']);
        $props['Members'] = implode("\n", $props['Members']);
        $props['Collaborators'] = implode("\n", $this->githubCollaborators);

        $this->_table($header, $props);

        return;
    }

    /**
     * Prints a table in terminal.
     *
     * @param array $header Table header.
     * @param array $rows   Table rows.
     *
     * @return void
     */
    private function _table($header, $rows)
    {
        $output = new ConsoleOutput();
        $table  = new Table($output);
        $table->setHeaders([$header]);

        foreach ($rows as $name => $value) {
            $table->addRow([$name, $value]);
            $table->addRow(new TableSeparator());
        }

        $table->setColumnWidths(array(10, 0, 30));

        $table->render();
        $output->writeln("");
    }

    /**
     * Execute a query against gitHub REST API 3.
     *
     * @param string $endpoint gitHub endpoint.
     * @param string $type     Type of POST to execute, false by default.
     *
     * @return array Return query result.
     */
    private function _query($endpoint, $type = 'POST')
    {
        $endpoint    = GITHUB_API . '' . $endpoint;
        $credentials = getenv('GITHUB_USER') . ":" . getenv('GITHUB_PASS');
        $curlHandle  = curl_init();
        curl_setopt(
            $curlHandle,
            CURLOPT_URL,
            $endpoint
        );
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 30);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlHandle, CURLOPT_USERPWD, $credentials);
        curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Toolkit Drupal');

        if ($type != 'POST') {
            curl_setopt($curlHandle, CURLOPT_HTTPHEADER, ["content-length: 0"]);
            curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, $type);
            if ($type == 'PUT') {
                curl_setopt(
                    $curlHandle,
                    CURLOPT_POSTFIELDS,
                    http_build_query(['permission' => 'pull'])
                );
            }
        }

        $result     = curl_exec($curlHandle);
        $statusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        curl_close($curlHandle);

        return [
            'status' => $statusCode,
            'data'   => json_decode($result)
        ];
    }

    /**
     * Get list of all teams available in github.
     *
     * @param string $repository Repository name.
     *
     * @return void
     */
    protected function getGithubTeams($repository)
    {
        // Get contributors for reference repository.
        $endpoint = 'repos/' . $repository . '/teams';
        $teams = $this->_query($endpoint);

        foreach ($teams['data'] as $team) {
            $this->githubTeams[] = [
                'name'        => $team->name,
                'description' => $team->description,
                'id'          => $team->id,
                'members'     => $this->getGithubTeamsMembers($team),
            ];
        }
    }

    /**
     * Get list of team members.
     *
     * @param int $team The team object.
     *
     * @return string $members Members of the team.
     */
    protected function getGithubTeamsMembers($team)
    {
        $members = '';

        // Get contributors for reference repository.
        $endpoint = 'teams/' . $team->id . '/members';
        $data     = $this->_query($endpoint);

        foreach ($data['data'] as $member) {
            $members[] = $member->login;
        }

        return $members;
    }

    /**
     * Get all collaborators for a given repository.
     *
     * @return void
     */
    protected function getGithubCollaborators()
    {
        // Get contributors for reference repository.
        $endpoint = 'repos/' . $this->reference . '/collaborators';
        $result = $this->_query($endpoint);

        foreach ($result['data'] as $collaborator) {
            $this->githubCollaborators[] = $collaborator->login;
        }

    }
}

