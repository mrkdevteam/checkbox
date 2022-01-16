<?php

namespace Checkbox;

/** Logger Instance */
class KLoggerDecorator
{
    private $logger;

    private $log_path = 'checkbox.log';

    private $extension = 'log';

    private $enabled;

    public function __construct(bool $is_enabled)
    {
        $this->enabled = $is_enabled;

        $this->logger = new \Katzgrau\KLogger\Logger(__DIR__ . '/../logs', \Psr\Log\LogLevel::DEBUG, array (
            'filename' => $this->log_path,
            'extension' => $this->extension
        ));
    }

    /**
     * Log info message
     *
     * @param string $msg
     * @return void
     */
    public function info(string $msg): void
    {
        if ($this->enabled) {
            $this->logger->info($msg);
        }
    }

    /**
     * Log error message
     *
     * @param string $msg
     * @return void
     */
    public function error(string $msg): void
    {
        if ($this->enabled) {
            $this->logger->error($msg);
        }
    }

    public function debug(string $msg, $obj)
    {
        if ($this->enabled) {
            $this->logger->debug($msg, $obj);
        }
    }
}
