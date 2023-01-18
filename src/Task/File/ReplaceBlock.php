<?php

declare(strict_types=1);

namespace EcEuropa\Toolkit\Task\File;

use Robo\Exception\TaskException;
use Robo\Result;
use Robo\Task\BaseTask;

/**
 * Replace a block of content in given file.
 *
 * ``` php
 * <?php
 * // Insert content between the 'start' and 'end'.
 * $this->taskReplaceBlock('file.txt')
 *   ->start('#block-start')
 *   ->end('#block-end')
 *   ->content('This content will be between the start and the end')
 *   ->run();
 * // Insert content between the 'start' and 'end', and exclude 'start' and 'end'.
 * $this->taskReplaceBlock('file.txt')
 *   ->start('#block-start')
 *   ->end('#block-end')
 *   ->excludeStartEnd()
 *   ->content('This content will be between the start and the end')
 *   ->run();
 * ?>
 * ```
 */
class ReplaceBlock extends BaseTask
{
    /**
     * The file to process.
     *
     * @var string
     */
    protected string $filename;

    /**
     * The start string to match.
     *
     * @var string
     */
    protected string $start;

    /**
     * The end string to match.
     *
     * @var string
     */
    protected string $end;

    /**
     * The content to write into the file.
     *
     * @var string
     */
    protected string $content = '';

    /**
     * If true, the start and end matches will be removed.
     *
     * @var bool
     */
    private bool $excludeStartEnd = false;

    /**
     * Class constructor.
     */
    public function __construct(string $filename = '')
    {
        if (!empty($filename)) {
            $this->filename($filename);
        }
    }

    /**
     * Set the filename.
     *
     * @param string $filename
     *   The file to process.
     *
     * @return $this
     *
     * @throws TaskException
     */
    public function filename(string $filename)
    {
        if (!file_exists($filename)) {
            throw new TaskException(__CLASS__, "The file $filename could not be found.");
        }

        $this->filename = $filename;
        return $this;
    }

    /**
     * Set the start block.
     *
     * @param string $start
     *   The start block.
     *
     * @return $this
     */
    public function start(string $start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * Set the end block.
     *
     * @param string $end
     *   The end block.
     *
     * @return $this
     */
    public function end(string $end)
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Set the content.
     *
     * @param string $content
     *   The content to insert.
     *
     * @return $this
     */
    public function content(string $content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Mark the start and end to be removed.
     *
     * @param bool $exclude
     *   Whether to exclude the 'start' and 'end'.
     *
     * @return $this
     */
    public function excludeStartEnd(bool $exclude = true)
    {
        $this->excludeStartEnd = $exclude;
        return $this;
    }

    /**
     * Run the task.
     *
     * @return Result
     */
    public function run()
    {
        $error_message = "You must provide a '%s' value.";
        if (empty($this->filename)) {
            return Result::error($this, sprintf($error_message, 'filename'));
        }
        if (empty($this->start)) {
            return Result::error($this, sprintf($error_message, 'start'));
        }
        if (empty($this->end)) {
            return Result::error($this, sprintf($error_message, 'end'));
        }

        $pattern = '~(' . preg_quote($this->start) . ')(.+?)(' . preg_quote($this->end) . ')~s';

        $file = file_get_contents($this->filename);

        if (!$this->excludeStartEnd) {
            $this->content = '\1' . $this->content . '\3';
        }

        $result = preg_replace($pattern, $this->content, $file, -1, $count);
        if ($count > 0) {
            if (file_put_contents($this->filename, $result) === false) {
                return Result::error($this, sprintf('Error writing to file %s.', $this->filename));
            }
            $this->printTaskSuccess('{filename} updated. {count} items replaced', ['filename' => $this->filename, 'count' => $count]);
        } else {
            $this->printTaskInfo('{filename} unchanged. {count} items replaced', ['filename' => $this->filename, 'count' => $count]);
        }

        return Result::success($this);
    }

}
