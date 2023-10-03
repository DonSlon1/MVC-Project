<?php

namespace Core\Utils;

use Core\Utils\File\Manager as FileManager;
use Exception;
use RecursiveDirectoryIterator;
use Core\Utils\Config\Manager as ConfigManager;
readonly class ClassFinder
{
    public function __construct(
        private ConfigManager $configManager,
        private FileManager   $fileManager,
    )
    {
    }

    /**
     * return array of classes found in folder with name $classDir
     * @param string $className
     * @param string $classDir
     * @return array
     */
    function findClasses(string $className, string $classDir): array
    {
        return $this->findClassesFiles($className, $classDir);
    }

    /**
     * return first class found in folder with name $classDir
     * @param string $className
     * @param string $classDir
     * @return string
     * @throws Exception
     */
    public  function findClass(string $className, string $classDir): string
    {
        $classes = $this->findClassesFiles($className, $classDir);
        if (count($classes) === 0) {
            throw new Exception("Class with $className not found");
        }
        return $classes[0];
    }

    private function findClassesFiles(string $className, string $classDir): array
    {
        $dirs = $this->fileManager->findDirs($this->configManager->get('rootDir'), $classDir);
        $classes = [];
        foreach ($dirs as $dir) {
            $classDirs = $this->findClassesInDir($className, $dir);
            if ($classDirs !== []) {
                $classes = array_merge($classes, $classDirs);
            }
        }
        return $classes;
    }

    /**
     * return array of classes found in folder with name $dir
     * @param string $className
     * @param string $dir
     * @return array
     */
    private function findClassesInDir(string $className, string $dir): array
    {
        $iterator = new RecursiveDirectoryIterator($dir);
        $classes = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileName = $file->getFilename();
                $class = str_replace('.php', '', $fileName);
                $content = file_get_contents($file->getPathname());

                // Use regular expressions to extract namespaces
                if (preg_match('/namespace\s+([a-zA-Z\\\\]+)/', $content, $matches) && $class === $className) {
                    $namespace = $matches[1];
                    $classes[] = $namespace . '\\' . $class;
                }
            }
        }
        return $classes;
    }

}