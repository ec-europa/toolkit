<?php

/**
 * Generates relative symlinks based on a target / link combination.
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

require_once 'phing/Task.php';

use FileSystem;
use PhingFile;
use Project;

/**
 * Generates relative symlinks based on a target / link combination.
 *
 * @category BuildSystem
 * @package  DrupalToolkit
 * @author   DIGIT NEXTEUROPA QA <DIGIT-NEXTEUROPA-QA@ec.europa.eu>
 * @license  https://ec.europa.eu/info/european-union-public-licence_en EUPL
 * @link     https://github.com/ec-europa/toolkit/blob/master/includes/phing/src/Tasks/DocGeneratorTask.php
 */
class RelativeSymlinkTask extends \SymlinkTask
{

    /**
     * Convert an absolute end path to a relative path.
     *
     * @param string $endPath   Absolute path of target.
     * @param string $startPath Absolute path where traversal begins.
     *
     * @return string Path of target relative to starting path.
     */
    public function makePathRelative($endPath, $startPath)
    {
        // Normalize separators on Windows.
        if ('\\' === DIRECTORY_SEPARATOR) {
            $endPath   = str_replace('\\', '/', $endPath);
            $startPath = str_replace('\\', '/', $startPath);
        }

        // Split the paths into arrays.
        $startPathArr = explode('/', trim($startPath, '/'));
        $endPathArr   = explode('/', trim($endPath, '/'));

        // Find for which directory the common path stops.
        $index = 0;
        while (
            isset($startPathArr[$index]) &&
            isset($endPathArr[$index]) &&
            $startPathArr[$index] === $endPathArr[$index]
        ) {
            ++$index;
        }

        // Determine how deep the start path is relative to the
        // common path (ie, "web/bundles" = 2 levels).
        $depth = (count($startPathArr) - $index);

        // Repeated "../" for each level need to reach the common path.
        $traverser = str_repeat('../', $depth);

        $endPathRemainder = implode('/', array_slice($endPathArr, $index));

        // Construct $endPath from traversing to the common path, then to the
        // remaining $endPath.
        $relativePath = $traverser.('' !== $endPathRemainder ? $endPathRemainder.'/' : '');

        return '' === $relativePath ? './' : $relativePath;

    }//end makePathRelative()


    /**
     * Create the Symlink.
     *
     * @param string      $target   Symlink target
     * @param string      $link     Symlink name
     * @param string bool $logShort Control type of link
     *
     * @return bool
     */
    protected function symlink($target, $link, $logShort = false)
    {
        $fs = FileSystem::getFileSystem();

        // Check if target exists.
        if (!file_exists($target)) {
            $this->log(sprintf('Target "%s" do not exists, skiping.', $target), Project::MSG_WARN);
            return true;
        }

        // Convert target to relative path.
        $absolutePath = (new PhingFile($link))->getAbsolutePath();
        $link         = $absolutePath;

        if ($logShort) {
            $relativePath = str_replace(
                $this->getProject()->getBaseDir(),
                "", $absolutePath
            );
            $linkName     = basename($absolutePath);
        } else {
            $linkName = $link;
        }

        // @codingStandardsIgnoreLine: MULTISITE-17111
        $target = rtrim(
            $this->makePathRelative($target, dirname($link)),
            '/'
        );

        if (is_link($link) && @readlink($link) == $target) {
            $this->log('Link exists: '.$linkName, Project::MSG_INFO);

            return true;
        }

        if (file_exists($link) || is_link($link)) {
            if (!$this->getOverwrite()) {
                $this->log(
                    'Not overwriting existing link '.$link,
                    Project::MSG_ERR
                );

                return false;
            }

            if (is_link($link) || is_file($link)) {
                $fs->unlink($link);
                $this->log(
                    'Link removed: '.$linkName,
                    Project::MSG_INFO
                );
            } else {
                $fs->rmdir($link, true);
                $this->log(
                    'Directory removed: '.$linkName,
                    Project::MSG_INFO
                );
            }
        }

        $this->log('Linking: '.$linkName.' to '.$target, Project::MSG_INFO);

        return $fs->symlink($target, $link);

    }//end symlink()


}//end class
