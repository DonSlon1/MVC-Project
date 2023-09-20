<?php

namespace Core\Utils\Config;

use Core\Utils\{
    File\Manager as FileManager,
};

class Manager
{
    private ?array $data = null;
    private string $configPath = '../App/Config/config.php';
    private readonly FileManager $fileManager;
    public function __construct()
    {
        $this->fileManager = new FileManager($this);
    }

    public function get(string $name, $default = null): mixed
    {
        $keys = explode('.', $name);

        $lastBranch = $this->getData();

        foreach ($keys as $key) {
            if (!is_array($lastBranch) && !is_object($lastBranch)) {
                return $default;
            }

            if (is_array($lastBranch) && !array_key_exists($key, $lastBranch)) {
                return $default;
            }

            if (is_object($lastBranch) && !property_exists($lastBranch, $key)) {
                return $default;
            }

            if (is_array($lastBranch)) {
                $lastBranch = $lastBranch[$key];

                continue;
            }

            $lastBranch = $lastBranch->$key;
        }

        return $lastBranch;
    }

    private function getData(): array
    {
        if (!$this->isLoaded()) {
            $this->load();
        }

        assert($this->data !== null);

        return $this->data;
    }

    private function isLoaded(): bool
    {
        return isset($this->data);
    }

    private function load(): void
    {
        $this->data = $this->fileManager->getPhpContents($this->configPath);
    }


}