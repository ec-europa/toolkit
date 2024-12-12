<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * A simple Junit XML generator.
 */
final class JunitXmlGenerator
{

    protected static array $data = [];
    protected static string $dir = 'junit-export';

    /**
     * Sets the data array.
     *
     * @param array $data
     *   The data to set.
     */
    public static function setData(array $data): void
    {
        self::$data = $data;
    }

    /**
     * Get the existing data.
     */
    public static function getData(): array
    {
        return self::$data;
    }

    /**
     * Add a test suite to the data array.
     *
     * @param string $testSuite
     *   The test case name.
     */
    public static function addTestSuite(string $testSuite)
    {
        self::$data[$testSuite] = [];
    }

    /**
     * Add a test case to a test suite in the data array.
     *
     * @param string $testSuite
     *   The test suite to add the test case.
     * @param string $testCase
     *   The test case name.
     */
    public static function addTestCase(string $testSuite, string $testCase)
    {
        self::$data[$testSuite][$testCase] = [];
    }

    /**
     * Add a result to a test case.
     *
     * @param string $testSuite
     *   The test suite to add.
     * @param string $testCase
     *   The test case to add the result to.
     * @param string $message
     *   The result message.
     * @param string $type
     *   The result type.
     */
    public static function addResult(string $testSuite, string $testCase, string $message, string $type = 'error')
    {
        self::$data[$testSuite][$testCase][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Generate the Junit XML file.
     *
     * @param string $filename
     *   The filename to export.
     * @param array|null $data
     *   The data to export.
     */
    public static function generate(string $filename = 'junit.xml', array $data = null)
    {
        if (!empty($data)) {
            self::setData($data);
        }
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $root = $xml->createElement('testsuites');
        foreach (self::getData() as $testSuite => $testCases) {
            $testsCount = $failuresCount = 0;
            $testSuiteElement = $xml->createElement('testsuite');
            $testSuiteElement->setAttribute('name', $testSuite);
            $testSuiteElement->setAttribute('time', '0');
            foreach ($testCases as $testCase => $results) {
                $testCaseElement = $xml->createElement('testcase');
                $testCaseElement->setAttribute('name', $testCase);
                $testsCount++;
                foreach ($results as $result) {
                    $failureElement = $xml->createElement('failure');
                    $failureElement->setAttribute('type', $result['type']);
                    $failureElement->setAttribute('message', $result['message']);
                    $testCaseElement->appendChild($failureElement);
                    $failuresCount++;
                }
                $testSuiteElement->appendChild($testCaseElement);
            }
            $testSuiteElement->setAttribute('tests', (string) $testsCount);
            $testSuiteElement->setAttribute('failures', (string) $failuresCount);
            $testSuiteElement->setAttribute('errors', (string) $failuresCount);
            $root->appendChild($testSuiteElement);
        }
        $xml->appendChild($root);
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir);
        }
        $xml->save(self::$dir . '/' . $filename);
    }

    /**
     * Helper to merge multiple files into a single one.
     *
     * @param string $destination
     *   The destination file where multiple files will be merged.
     * @param string $directory
     *   The directory where the files are.
     */
    public static function mergeFiles(string $destination, string $directory)
    {
        $suites = PHP_EOL;
        foreach (glob("$directory/*.xml") as $file) {
            $content = simplexml_load_file($file);
            foreach ($content->testsuite as $item) {
                $suites .= $item->asXML() . PHP_EOL;
            }
        }
        $content = '<?xml version="1.0" encoding="UTF-8"?><testsuites>' . $suites . '</testsuites>';
        $xml = new \SimpleXMLElement($content);
        if (!is_dir(self::$dir)) {
            mkdir(self::$dir);
        }
        $xml->asXML(self::$dir . '/' . $destination);
    }

}
