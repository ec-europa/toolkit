<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

use Exception;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Handle communications with QA api.
 */
class Website
{
    /**
     * The default base url.
     *
     * @var string
     */
    protected static string $url = 'https://webgate.ec.europa.eu/fpfis/qa';

    /**
     * Returns the QA website base url.
     *
     * @return string
     *   The base url.
     */
    public static function url(): string
    {
        return self::$url;
    }

    /**
     * Set the url to be used.
     *
     * @param string $url
     *   The url to use.
     */
    public static function setUrl(string $url)
    {
        self::$url = $url;
    }

    /**
     * Return the QA API BASIC AUTH from token or from questions.
     *
     * @return string
     *   The Basic auth or empty string if fails.
     */
    public static function basicAuth(): string
    {
        if (!empty($GLOBALS['basic_auth'])) {
            return $GLOBALS['basic_auth'];
        }

        $auth = getenv('QA_API_BASIC_AUTH');
        if (empty($auth)) {
            $io = new SymfonyStyle(new ArgvInput(), new ConsoleOutput());
            $io->writeln('Missing env var QA_API_BASIC_AUTH, asking for access.');
            if (empty($user = $io->ask('Please insert your username:'))) {
                $io->writeln('<error>The username cannot be empty!</error>');
                return '';
            }
            if (empty($pass = $io->askHidden('Please insert your password:'))) {
                $io->writeln('<error>The password cannot be empty!</error>');
                return '';
            }
            $auth = base64_encode("$user:$pass");
            $io->writeln([
                'Your token has been generated, please add it to your environment variables.',
                '    export QA_API_BASIC_AUTH="' . $auth . '"',
            ]);
            $GLOBALS['basic_auth'] = $auth;
        }

        return $auth;
    }

    /**
     * Curl function to access endpoint with or without authentication.
     *
     * This function is made publicly available as a static function for other
     * projects to call. Then we have to maintain less code.
     *
     * @param string $url
     *   The QA endpoint url.
     * @param string $basicAuth
     *   The basic auth.
     *
     * @return string
     *   The endpoint content, or empty string if no session is generated.
     *
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    public static function get(string $url, string $basicAuth = ''): string
    {
        if (!($token = self::getSessionToken())) {
            return '';
        }

        $content = '';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        if ($basicAuth !== '') {
            $header = [
                "Authorization: Basic $basicAuth",
                "X-CSRF-Token: $token",
            ];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        $result = curl_exec($curl);

        if ($result !== false) {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            switch ($statusCode) {
                // Upon success set the content to be returned.
                case 200:
                    $content = $result;
                    break;

                // Upon other status codes.
                default:
                    if ($basicAuth === '') {
                        $message = 'Curl request to endpoint "%s" returned a %u.';
                        throw new Exception(sprintf($message, $url, $statusCode));
                    }
                    // If we tried with authentication, retry without.
                    $content = self::get($url);
            }
        }
        if ($result === false) {
            throw new Exception(sprintf('Curl request to endpoint "%s" failed.', $url));
        }
        curl_close($curl);

        return $content;
    }

    /**
     * Helper to return the session token.
     *
     * @return string
     *   The token or false if the request failed.
     */
    public static function getSessionToken(): string
    {
        if (!empty($GLOBALS['session_token'])) {
            return $GLOBALS['session_token'];
        }
        $options = [
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_ENCODING       => '',     // handle compressed
            CURLOPT_USERAGENT      => 'Quality Assurance pipeline', // name of client
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        ];
        $ch = curl_init(self::url() . '/session/token');
        curl_setopt_array($ch, $options);
        $token = (string) curl_exec($ch);
        curl_close($ch);
        $GLOBALS['session_token'] = $token;
        return $token;
    }

    /**
     * Helper to send a payload to the QA Website.
     *
     * @param array $fields
     *   Data to send.
     * @param string $auth
     *   The Basic auth.
     *
     * @return string
     *   The endpoint response code, or empty string if no session is generated.
     *
     * @throws Exception
     */
    public static function post(array $fields, string $auth): string
    {
        if (!($token = self::getSessionToken())) {
            return '';
        }
        $ch = curl_init(self::url() . '/node?_format=hal_json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/hal+json',
            "X-CSRF-Token: $token",
            "Authorization: Basic $auth",
        ]);
        curl_exec($ch);
        $code = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }

    /**
     * Returns the Project information from the endpoint.
     *
     * @param string $project_id
     *   The project ID to use.
     *
     * @return false|array
     *   An array with the Project information, false if fails.
     *
     * @throws Exception
     */
    public static function projectInformation(string $project_id)
    {
        if (!isset($GLOBALS['projects'])) {
            $GLOBALS['projects'] = [];
        }
        if (!empty($GLOBALS['projects'][$project_id])) {
            return $GLOBALS['projects'][$project_id];
        }
        $endpoint = "/api/v1/project/ec-europa/$project_id-reference/information";
        $response = self::get(self::url() . $endpoint, self::basicAuth());
        $data = json_decode($response, true);
        $data = reset($data);
        if (!empty($data['name']) && $data['name'] === "$project_id-reference") {
            $GLOBALS['projects'][$project_id] = $data;
            return $data;
        }

        return false;
    }

    /**
     * Returns the Project constraints from the endpoint.
     *
     * @param string $project_id
     *   The project ID to use.
     *
     * @return false|array
     *   An array with the constraints, false if fails.
     *
     * @throws Exception
     */
    public static function projectConstraints(string $project_id)
    {
        if (!isset($GLOBALS['constraints'])) {
            $GLOBALS['constraints'] = [];
        } elseif (!empty($GLOBALS['constraints'])) {
            return $GLOBALS['constraints'];
        }
        $endpoint = '/api/v1/project/ec-europa/' . $project_id . '-reference/information/constraints';
        $response = self::get(self::url() . $endpoint, self::basicAuth());
        $data = json_decode($response, true);
        if (empty($data) || !isset($data['constraints'])) {
            return false;
        }
        $GLOBALS['constraints'] = $data['constraints'];
        return $data['constraints'];
    }

    /**
     * Returns the toolkit requirements from the endpoint.
     *
     * @return array|false|mixed
     * @throws Exception
     */
    public static function requirements()
    {
        if (!isset($GLOBALS['requirements'])) {
            $GLOBALS['requirements'] = [];
        }
        if (!empty($GLOBALS['requirements'])) {
            return $GLOBALS['requirements'];
        }
        $response = self::get(self::url() . '/api/v1/toolkit-requirements', self::basicAuth());
        if (empty($response)) {
            return false;
        }
        $data = json_decode($response, true);
        $GLOBALS['requirements'] = $data;
        return $data;
    }

}
