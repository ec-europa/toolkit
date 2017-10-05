<?php

namespace Phing\Toolkit\Tasks;

use Symfony\Component\Yaml\Dumper;

require_once 'phing/Task.php';

/**
 * A Phing task to manage repository collaborators.
 */
class RepositoryContributorsTask extends \Task {

  /**
   * The repository owner username.
   *
   * @var string
   */
  private $repoOwner = '';

  /**
   * The repository owner password.
   *
   * @var string
   */
  private $repoOwnerPass = '';

  /**
   * The repository name.
   *
   * @var int
   */
  private $repoName = '';

  /**
   * The repository collaborators.
   *
   * @var int
   */
  private $repoUsers = '';

  /**
   * Operation to be executed.
   *
   * @var string
   */
  private $op = '';

  /**
   * The init method: Do init steps.
   */
  public function init() {
    // nothing to do here
  }


  /**
   * Generates a Drush make file.
   */
  public function main() {
    // Check if all required data is present.
    $this->checkRequirements();

      switch ($this->op) {
        case 'add':
          $this->add();
          break;

        case 'list':
          $this->list($this->repoName);
          break;

        case 'remove':
          $this->remove($this->repoUsers);
          break;

        default:
          $this->list($this->repoName);
          break;
      }
  }

  /**
   * List all collaborators for a given repository.
   */
  protected function list($repository) {

    $endpoint = 'repos/' . $this->repoOwner . '/' . $repository . '/collaborators';
    echo $endpoint;
    echo "\n\n";
    $result = $this->repositoryQuery($endpoint);

    $collaborators = json_decode($result['data']);


    echo "#\tPULL\tPUSH\tADMIN\tUSER\n";
    foreach ($collaborators as $key => $collaborator) {
      echo "#" . $key
       . "\t".  $collaborator->permissions->pull
       . "\t". $collaborator->permissions->push
       . "\t". $collaborator->permissions->admin
       . "\t". $collaborator->login
       . "\n";
    }
  }

  /**
   * List all collaborators for a given repository.
   */
  protected function add() {
    $collaborators = explode(" ", $this->repoUsers);

    foreach ($collaborators as $collaborator) {

      $endpoint = 'repos/' . $this->repoOwner . '/' . $this->repoName . '/collaborators/' . $collaborator;
      $result = $this->repositoryQuery($endpoint);

      if ($result['status'] == '204') {
        echo "Collaborator " . $collaborator . " already in the list of collaborators, no action required.\n";
      }
      else {
        $params = [
          'permission' => 'pull',
        ];

        $result = $this->repositoryQuery($endpoint, $params);

        if ($result['status'] == '204') {
          echo "Collaborator " . $collaborator . " have now READ access to repository.\n";
        }
        else {
          var_dump($endpoint);
          var_dump($result);
          echo "Not possible add " . $collaborator . " to the list of colaborators, operation failed.\n";
        }
      }

    }

  }

  /**
   * Remove specific user from repository.
   */
  protected function remove($collaborator) {
    echo "Remove USER\n";
  }

  protected function repositoryQuery($endpoint, $params = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://api.github.com/' . $endpoint);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_USERPWD, 'jonhy81:jjrs2012');
    curl_setopt($ch, CURLOPT_USERAGENT,'Starterkit Drupal');

    if (count($params) > 0 ) {
      curl_setopt($ch, CURLOPT_HTTPHEADER, 'Accept: application/vnd.github.swamp-thing-preview+json');
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
      var_dump(http_build_query($params));
    }

    $result=curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
    curl_close ($ch);

    return [
      'status' => $status_code,
      'data' => $result
    ];
  }

  /**
   * Checks if all properties required for generating the makefile are present.
   *
   * @throws \BuildException
   *   Thrown when a required property is not present.
   */
  protected function checkRequirements() {
    $required_properties = array('repoOwner', 'repoOwnerPass', 'repoName');
    foreach ($required_properties as $required_property) {
      if (empty($this->$required_property)) {
        throw new \BuildException("Missing required property '$required_property'.");
      }
    }
  }

  /**
   * Sets the repository owner name.
   *
   * @param string $repoOwner
   *   The repository owner name.
   */
  public function setRepoOwner($repoOwner) {
    $this->repoOwner = $repoOwner;
  }

  /**
   * Sets the repository owner password.
   *
   * @param string $repoOwnerPass
   *   The repository owner password.
   */
  public function setRepoOwnerPass($repoOwnerPass) {
    $this->repoOwnerPass = $repoOwnerPass;
  }

  /**
   * Sets the repository name.
   *
   * @param string $repoName
   *   The repository name.
   */
  public function setRepoName($repoName) {
    $this->repoName = $repoName;
  }

  /**
   * Sets the repository collaborators.
   *
   * @param string $repoUsers
   *   The repository collaborators.
   */
  public function setRepoUsers($repoUsers) {
    $this->repoUsers = $repoUsers;
  }

    /**
   * Sets the operation to be executed.
   *
   * @param string $op
   *   The operation name.
   */
  public function setOp($op) {
    $this->op = $op;
  }

}

