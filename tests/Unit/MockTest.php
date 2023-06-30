<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Tests\Unit;

use EcEuropa\Toolkit\Tests\AbstractTest;
use EcEuropa\Toolkit\Website;
use PHPUnit\Framework\TestCase;

class MockTest extends TestCase
{

    /**
     * Data provider for testMock.
     *
     * @return array
     *   An array of test data arrays with assertions.
     */
    public function dataProvider()
    {
        return [
            [200, '/tests/mock/api/v1/toolkit-requirements'],
            [200, '/tests/mock/api/v1/package-reviews'],
            [200, '/tests/mock/api/v1/package-reviews?version=8.x'],
            [200, '/tests/mock/api/v1/package-reviews?version=7.x'],
            [200, '/tests/mock/api/v1/project/ec-europa/digit-qa-reference/information'],
            [200, '/tests/mock/api/v1/project/ec-europa/toolkit-reference/information/constraints'],
            [200, '/tests/mock/api/v1/forbidden-permissions'],
            [404, '/tests/mock/api/v1/fake'],
        ];
    }

    /**
     * Test mock endpoints.
     *
     * @dataProvider dataProvider
     */
    public function testMock($code, $endpoint)
    {
        $result = $this->call(AbstractTest::getMockBaseUrl() . $endpoint);

        $this->assertEquals($code, $result['code']);

        $this->assertNotEmpty($result['response']);
    }

    /**
     * Test mock set up.
     */
    public function testSetUpMock()
    {
        $initial_url = Website::url();

        AbstractTest::setUpMock();

        $new_url = Website::url();

        $this->assertNotEquals($initial_url, $new_url);

        $this->assertEquals($new_url, AbstractTest::getMockBaseUrl() . '/tests/mock');
    }

    /**
     * Make a curl request.
     *
     * @param $url
     *   The url to request.
     *
     * @return array
     *   An array keyed with code and response.
     *
     * @throws \Exception
     *   If the request fails.
     */
    private function call($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        if ($result === false) {
            throw new \Exception(sprintf('Curl request to endpoint "%s" failed.', $url));
        }
        curl_exec($curl);
        $code = (string) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return ['code' => $code, 'response' => $result];
    }

}
