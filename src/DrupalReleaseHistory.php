<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit;

use Composer\Semver\Semver;

class DrupalReleaseHistory
{

    /**
     * Call release history of d.org to confirm security alert.
     *
     * @param string $package
     *   The package to check.
     * @param string $version
     *   The version to check.
     * @param string $core
     *   The package core version.
     *
     * @return array|int
     *   Array with package info from d.org, 1
     *   if no release history found.
     */
    public function getPackageDetails(string $package, string $version, string $core)
    {
        $name = explode('/', $package)[1];
        // Drupal core is an exception, we should use '/drupal/current'.
        if ($package === 'drupal/core') {
            $url = 'https://updates.drupal.org/release-history/drupal/current';
        } else {
            $url = 'https://updates.drupal.org/release-history/' . $name . '/' . $core;
        }

        $releaseHistory = $fullReleaseHistory = [];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/hal+json']);
        $result = curl_exec($curl);

        if ($result !== false) {
            $fullReleaseHistory[$name] = simplexml_load_string($result);
            $terms = [];
            foreach ($fullReleaseHistory[$name]->releases as $release) {
                foreach ($release as $releaseItem) {
                    $versionTmp = str_replace($core . '-', '', (string) $releaseItem->version);

                    if (!is_null($version) && Semver::satisfies($versionTmp, $version)) {
                        foreach ($releaseItem->terms as $term) {
                            foreach ($term as $termItem) {
                                $terms[] = strtolower((string) $termItem->value);
                            }
                        }

                        $releaseHistory = [
                            'name' => $name,
                            'version' => (string) $releaseItem->versions,
                            'terms' => $terms,
                            'date' => (string) $releaseItem->date,
                        ];
                    }
                }
            }
            return $releaseHistory;
        }

        return 1;
    }

}
