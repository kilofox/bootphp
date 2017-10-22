<?php

namespace Bootphp;

use Bootphp\Core;

/**
 * File helper class.
 *
 * @author      Tinsh <kilofox2000@gmail.com>
 * @copyright   (C) 2013-2017 Kilofox Studio
 * @license     http://kilofox.net/bootphp/license
 */
class Filesystem
{
    /**
     * @var  array   Include paths that are used to find files
     */
    protected static $_paths = [APP_PATH, VEN_PATH];

    /**
     * Searches for a file in the [Cascading Filesystem](bootphp/files), and
     * returns the path to the file that has the highest precedence, so that it
     * can be included.
     *
     * When searching the "config", "messages", or "i18n" directories, or when
     * the `$array` flag is set to true, an array of all the files that match
     * that path in the [Cascading Filesystem](bootphp/files) will be returned.
     * These files will return arrays which must be merged together.
     *
     * If no extension is given, the default extension `.php` will be used.
     *
     *     // Returns an absolute path to views/template.php
     *     Filesystem::findFile('views', 'template');
     *
     *     // Returns an absolute path to media/css/style.css
     *     Filesystem::findFile('media', 'css/style', 'css');
     *
     *     // Returns an array of all the "mimes" configuration files
     *     Filesystem::findFile('config', 'mimes');
     *
     * @param   string  $dir    directory name (views, i18n, classes, extensions, etc.)
     * @param   string  $file   filename with subdirectory
     * @param   string  $ext    extension to search for
     * @param   boolean $array  return an array of files?
     * @return  array   a list of files when $array is true
     * @return  string  single file path
     */
    public static function findFile($dir, $file, $ext = null, $array = false)
    {
        if ($ext === null) {
            // Use the default extension
            $ext = '.php';
        } elseif ($ext) {
            // Prefix the extension with a period
            $ext = ".{$ext}";
        } else {
            // Use no extension
            $ext = '';
        }

        // Create a partial path of the filename
        $path = $dir . DIRECTORY_SEPARATOR . $file . $ext;

        if (Core::$caching === true and isset(self::$_files[$path . ($array ? '_array' : '_path')])) {
            // This path has been cached
            return self::$_files[$path . ($array ? '_array' : '_path')];
        }

        if (Core::$profiling === true and class_exists('Profiler', false)) {
            // Start a new benchmark
            $benchmark = Profiler::start('Bootphp', __FUNCTION__);
        }

        if ($array or $dir === 'config' or $dir === 'i18n' or $dir === 'messages') {
            // Include paths must be searched in reverse
            $paths = array_reverse(self::$_paths);

            // Array of files that have been found
            $found = [];

            foreach ($paths as $dir) {
                if (is_file($dir . $path)) {
                    // This path has a file, add it to the list
                    $found[] = $dir . $path;
                }
            }
        } else {
            // The file has not been found yet
            $found = false;

            foreach (self::$_paths as $dir) {
                $file = $dir . DIRECTORY_SEPARATOR . $path;
                if (is_file($file)) {
                    // A path has been found
                    $found = $file;

                    // Stop searching
                    break;
                }
            }
        }

        if (Core::$caching === true) {
            // Add the path to the cache
            self::$_files[$path . ($array ? '_array' : '_path')] = $found;

            // Files have been changed
            self::$_files_changed = true;
        }

        if (isset($benchmark)) {
            // Stop the benchmark
            Profiler::stop($benchmark);
        }

        return $found;
    }

    /**
     * Recursively finds all of the files in the specified directory at any
     * location in the [Cascading Filesystem](bootphp/files), and returns an
     * array of all the files found, sorted alphabetically.
     *
     *     // Find all view files.
     *     $views = Core::list_files('views');
     *
     * @param   string  $directory  directory name
     * @param   array   $paths      list of paths to search
     * @return  array
     */
    public static function list_files($directory = null, array $paths = null)
    {
        if ($directory !== null) {
            // Add the directory separator
            $directory .= DIRECTORY_SEPARATOR;
        }

        if ($paths === null) {
            // Use the default paths
            $paths = self::$_paths;
        }

        // Create an array for the files
        $found = [];

        foreach ($paths as $path) {
            if (is_dir($path . $directory)) {
                // Create a new directory iterator
                $dir = new DirectoryIterator($path . $directory);

                foreach ($dir as $file) {
                    // Get the file name
                    $filename = $file->getFilename();

                    if ($filename[0] === '.' or $filename[strlen($filename) - 1] === '~') {
                        // Skip all hidden files and UNIX backup files
                        continue;
                    }

                    // Relative filename is the array key
                    $key = $directory . $filename;

                    if ($file->isDir()) {
                        if ($sub_dir = self::list_files($key, $paths)) {
                            if (isset($found[$key])) {
                                // Append the sub-directory list
                                $found[$key] += $sub_dir;
                            } else {
                                // Create a new sub-directory list
                                $found[$key] = $sub_dir;
                            }
                        }
                    } else {
                        if (!isset($found[$key])) {
                            // Add new files to the list
                            $found[$key] = realpath($file->getPathName());
                        }
                    }
                }
            }
        }

        // Sort the results alphabetically
        ksort($found);

        return $found;
    }

}
