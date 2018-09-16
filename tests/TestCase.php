<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Recursively remove a directory and its contents.
     *
     * @param string $path Path to the folder.
     * @param bool $keepStartingFolder Whether to keep the starting folder or not.  If true this functions as an 'empty'.
     */
    protected function rrmdir(
        string $path,
        $keepStartingFolder = false
    ): void {
        $dir = opendir($path);

        while (false !== ($file = readdir($dir))) {
            if (('.' !== $file) && ('..' !== $file) && ('.gitignore' !== $file)) {
                $fullPath = $path.DIRECTORY_SEPARATOR.$file;

                if (is_dir($fullPath)) {
                    $this->rrmdir($fullPath);
                } else {
                    unlink($fullPath);
                }
            }
        }

        closedir($dir);

        if (false === $keepStartingFolder) {
            rmdir($path);
        }
    }
}
