<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

/**
 * A simple Junit XML generator.
 */
final class JunitXmlGenerator
{

    protected static array $data = [];
    private static string $xsd = 'https://raw.githubusercontent.com/junit-team/junit5/r5.5.1/platform-tests/src/test/resources/jenkins-junit.xsd';
    private static string $xsi = 'http://www.w3.org/2001/XMLSchema-instance';

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
     * Add a test case to the data array.
     *
     * @param string $name
     *   The test case name.
     */
    public static function addTestCase(string $name)
    {
        self::$data[$name] = [];
    }

    /**
     * Add a result to a test case.
     *
     * @param string $testCase
     *   The test case to add the result to.
     * @param string $message
     *   The result message.
     * @param string $type
     *   The result type.
     */
    public static function addResult(string $testCase, string $message, string $type = 'error')
    {
        self::$data[$testCase][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Generate the Junit XML file.
     *
     * @param string $rootName
     *   The root element name attribute.
     * @param string $filename
     *   The filename to export.
     * @param array|null $data
     *   The data to export.
     */
    public static function generate(string $rootName, string $filename = 'junit.xml', array $data = null)
    {
        if (!empty($data)) {
            self::setData($data);
        }
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $root = $xml->createElement('testsuites');
        $root->setAttribute('name', $rootName);
        $root->setAttribute('xmlns:xsi', self::$xsi);
        $root->setAttribute('xsi:noNamespaceSchemaLocation', self::$xsd);

        $testsCount = $failuresCount = 0;
        foreach (self::getData() as $testCase => $results) {
            $testElement = $xml->createElement('testcase');
            $testElement->setAttribute('name', $testCase);
            $testsCount++;
            foreach ($results as $result) {
                $failureElement = $xml->createElement('failure');
                $failureElement->setAttribute('type', $result['type']);
                $failureElement->setAttribute('message', $result['message']);
                $testElement->appendChild($failureElement);
                $failuresCount++;
            }
            $root->appendChild($testElement);
        }

        $root->setAttribute('tests', (string) $testsCount);
        $root->setAttribute('failures', (string) $failuresCount);
        $xml->appendChild($root);

        $xml->save($filename);
    }

}
