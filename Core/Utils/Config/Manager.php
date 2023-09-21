<?php

namespace Core\Utils\Config;

use RuntimeException;
use Core\Utils\{
    File\Manager as FileManager,
};

class Manager
{
    private ?array $data = null;
    private string $configPath = '../App/Config/config.php';
    private readonly FileManager $fileManager;
    private array $changedData = [];
    protected array $associativeArrayAttributeList = [
        'currencyRates',
        'database',
        'logger',
        'defaultPermissions',
    ];
    public function __construct()
    {
        $this->fileManager = new FileManager($this);
    }

    /**
     * Get a config value.
     *
     * @param string $name Name of the config value.
     * @param mixed|null $default Default value if some name dont exist.
     *
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
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

    /**
     * Update the config data.
     */
    public function update():void
    {
        $this->data = $this->fileManager->getPhpContents($this->configPath);
        $this->changedData = [];
    }

    /**
     * Set a config value.
     *
     * @param string $name Name of the config value.
     * @param mixed $value Value of the config parameter.
     */
    public function set(string $name, mixed $value): void
    {
        if (in_array($name, $this->associativeArrayAttributeList)) {
            $value = (array) $value;
        }
        $this->changedData[$name] = $value;
    }

    /**
     * Set multiple config values.
     *
     * @param array $data
     */
    public function setMultiple(array $data): void
    {
        foreach ($data as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * Save the config  to config file.
     *
     * @throws RuntimeException
     */
    public function save(): void
    {
        $data = $this->fileManager->getPhpContents($this->configPath);
        if (!is_array($data)) {
            throw new RuntimeException("Could not read config.");
        }
        $data = array_merge($data, $this->changedData);
        $this->fileManager->putPhpContents($this->configPath, $data);

        $this->update();
    }

}