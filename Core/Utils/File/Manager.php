<?php

namespace Core\Utils\File;

use Core\Utils\Config\Manager as ConfigManager;
use Core\Utils\File\Exceptions\{
    FileError,
    PermissionError
};

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

    /**
     * Create a directory recursively with permissions.
     * @param string $path
     * @param ?int $permission
     * @return bool
     */
    private function mkdir(string $path, ?int $permission = 0775): bool
    {

        if ($permission === null) {
            $permission = 0775;
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

    /**
     * Get permissions for files and directories.
     *
     * @return array
     */
    public function getPermissions(): array
    {
        if ($this->permissions !== null) {
            return $this->permissions;
        }
        return $this->configManager->get('defaultPermissions');
    }

    /**
     * Check if a file exists and create it if not.
     *
     * @param string $filePath
     * @return bool
     * @throws PermissionError
     */
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

    /**
     * Set permissions for a file or directory.
     *
     * @param string $path
     * @param ?int $permission
     * @return bool
     */
    public function setPermissions(string $path, ?int $permission = 0664): bool
    {
        if ($permission === null) {
            $permission = 0664;
        }
        if (!file_exists($path)) {
            return false;
        }

        umask(0);
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
        if (!$this->getContents($path)) {
            throw new FileError("File '$path' does not exist.");
        }

        if (strtolower(substr($path, -4)) !== '.php') {
            throw new FileError("File '$path' is not PHP.");
        }

        return include($path);
    }

    /**
     * Put data to a PHP file.
     *
     * @param string $path
     * @param mixed $data
     * @param int $flags Flags for file_put_contents().
     * @return bool
     */
    public function putPhpContents(string $path, mixed $data, int $flags=0): bool
    {

        return $this->putContents($path, $this->wrapForDataExport($data),$flags);
    }

    /**
     * Wrap data for export to PHP file.
     *
     * @param array $data
     * @return string|false
     */
    public function wrapForDataExport(array $data): false|string
    {
        if (!isset($data)) {
            return false;
        }

        return "<?php\n" .
            "return " . var_export($data, true) . ";\n";

    }

    /**
     * Get data from a file.
     *
     * @param string $path
     * @param $data
     * @param int $flags Flags for file_put_contents().
     * @return bool
     */
    public function putContents(string $path, $data, int $flags = 0): bool
    {
        if ($this->checkCreateFile($path) === false) {
            throw new PermissionError('Permission denied for '. $path);
        }

        return file_put_contents($path, $data, $flags) !== false;
    }

    /**
     * Get data from a file.
     *
     * @param string $path
     * @return string|false
     * @throws FileError
     */
    public function getContents(string $path): string|false
    {
        if (!$this->fileExists($path)) {
            throw new FileError("File '$path' does not exist.");
        }

        return file_get_contents($path);
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    public function getJsonContents(string $path): mixed
    {
        if (!$this->fileExists($path)) {
            throw new FileError("File '$path' does not exist.");
        }

        if (strtolower(substr($path, -5)) !== '.json') {
            throw new FileError("File '$path' is not JSON.");
        }

        $contents = $this->getContents($path);

        if ($contents === false) {
            throw new FileError("Unable to read file '$path'.");
        }

        $data = json_decode($contents, true);

        if ($data === null) {
            throw new FileError("Unable to decode JSON from file '$path'.");
        }

        return $data;
    }

    public function putJsonContents(string $path, mixed $data, int $flags = 0): bool
    {
        if (!$this->checkCreateFile($path)) {
            throw new PermissionError("Unable to create file '$path'.");
        }

        $contents = json_encode($data, JSON_PRETTY_PRINT);

        if ($contents === false) {
            throw new FileError("Unable to encode JSON for file '$path'.");
        }

        return $this->putContents($path, $contents, $flags);
    }

}