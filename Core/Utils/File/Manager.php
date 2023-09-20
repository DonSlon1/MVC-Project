<?php

namespace Core\Utils\File;

use Core\Utils\Config\Manager as ConfigManager;
use DI\DependencyException;
use DI\NotFoundException;
use Core\Utils\File\Exceptions\{
    FileError,
    PermissionError
};
use DI\Container as DIContainer;

class Manager
{
    private ?array $permissions ;

    public function __construct(
        private readonly ConfigManager $configManager,
        array $permissions = null,
    )
    {
        $this->permissions = $permissions;
    }

    private function mkdir(string $path, $permission = 0775): bool
    {

        if ($permission === null) {
            $permission = "0775";
        }
        if (file_exists($path) && is_dir($path)) {
            return true;
        }
        $parentDirPath = dirname($path);

        if (!file_exists($parentDirPath)) {
            $this->mkdir($parentDirPath, $permission);
        }
        umask(0);
        return mkdir($path, $permission);

    }


    public function getPermissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }
        return $this->configManager->get('defaultPermissions');
        //return (new ConfigManager($this))->get('defaultPermissions');
    }
    public function checkCreateFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return true;
        }
        $pathParts = pathinfo($filePath);

        $dirname = $pathParts['dirname'] ?? null;


        if (!file_exists($dirname)) {

            if (!$this->mkdir($dirname, $this->getPermissions()['dir'] ?? null)) {
                throw new PermissionError('Permission denied: unable to create a folder on the server ' . $dirname);
            }
        }
        $touchResult = touch($filePath);

        if (!$touchResult) {
            return false;
        }
        $this->setPermissions($filePath, $this->getPermissions()['file'] ?? null);
        return true;
    }


    public function setPermissions(string $path,$permission = 0664): bool
    {
        if ($permission === null) {
            $permission = 0664;
        }
        if (!file_exists($path)) {
            return false;
        }

        $umask = umask(0);
        return @chmod($path, $permission);
    }

    /**
     * Get data from a PHP file.
     *
     * @param string $path
     * @throws FileError
     * @return mixed
     */
    public function getPhpContents(string $path): mixed
    {
        if (!file_exists($path)) {
            throw new FileError("File '$path' does not exist.");
        }

        if (strtolower(substr($path, -4)) !== '.php') {
            throw new FileError("File '$path' is not PHP.");
        }

        return include($path);
    }

    public function putContents(string $path, $data, int $flags = 0,): bool
    {
        if ($this->checkCreateFile($path) === false) {
            throw new PermissionError('Permission denied for '. $path);
        }

        return file_put_contents($path, $data, $flags) !== false;
    }
}