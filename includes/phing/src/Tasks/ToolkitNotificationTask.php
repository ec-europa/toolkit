<?php

/**
 * A Phing task to fetch and display notificactions from the endpoint.
 *
 * PHP Version 5 and 7
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/ToolkitNotificationTask.php
 */

namespace Phing\Toolkit\Tasks;

use BuildException;
use Project;

require_once 'phing/Task.php';

/**
 * A Phing task to fetch and display notificactions from the endpoint.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/ToolkitNotificationTask.php
 */
class ToolkitNotificationTask extends \Task
{

    /**
     * Endpoint url to fetch the notifications from.
     *
     * @var string
     */
    private $_endpointUrl = '';


    /**
     * Fetches and displays the notifications from the endpoint.
     *
     * @return void
     */
    public function main()
    {
        // Check if all required data is present.
        $this->checkRequirements();

        if (isset($this->_endpointUrl)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->_endpointUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($ch);

            // If request did not fail.
            if ($result !== false) {
                // Request was ok? check response code.
                $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($statusCode == 200) {
                    $data = json_decode($result, true);
                    foreach ($data as $notification) {
                        $this->log($notification['title'] . PHP_EOL . PHP_EOL . $notification['notification'] . PHP_EOL, Project::MSG_WARN);
                    }
                }
                else {
                    $this->log(sprintf('Curl request failed with error code %d. Skipping notification fetching.', $statusCode), Project::MSG_WARN);
                }
            }
            curl_close($ch);
        }//end if

    }//end main()

    /**
     * Checks if all properties are present.
     *
     * @throws \BuildException Thrown when a required property is not present.
     *
     * @return void
     */
    protected function checkRequirements()
    {
        $required_properties = array(
            '_endpointUrl',
        );
        foreach ($required_properties as $required_property) {
            if (empty($this->$required_property)) {
                throw new \BuildException(
                    "Missing required property '$required_property'."
                );
            }
        }
    }//end checkRequirements()


    /**
     * Sets the endpoint url.
     *
     * @param string $endpointUrl The endpoint url.
     *
     * @return void
     */
    public function setEndpointUrl($endpointUrl)
    {
        $this->_endpointUrl = $endpointUrl;
    }//end setEndpointUrl()

}//end class
