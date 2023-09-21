<?php

namespace Core\Utils;
use Core\Utils\Config\Manager as ConfigManager;
use Core\Utils\File\Manager as FileManager;

readonly class Log
{
    public function __construct(
        private FileManager   $fileManager,
        private ConfigManager $configManager,
    )
    {
    }

    public function debug(string $message): void
    {
        $this->log($message, 'DEBUG');
    }

    public function info(string $message): void
    {
        $this->log($message, 'INFO');
    }

    public function warning(string $message): void
    {
        $this->log($message, 'WARNING');
    }

    public function error(string $message): void
    {
        $this->log($message, 'ERROR');
    }

    private function log(string $message, string $level): void
    {
        $date = date('Y-m-d H:i:s');
        $log = "$date [$level] $message" . PHP_EOL;

        $logPath = $this->configManager->get('logDir') . date('Y-m-d') . '.log';
        $this->fileManager->putContents($logPath, $log, FILE_APPEND);
    }
}