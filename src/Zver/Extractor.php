<?php

namespace Zver {

    use FG\ASN1\ASNObject;
    use Zver\Package\Helper;

    class Extractor
    {

        use Helper;

        const ARCH_32 = '32';
        const ARCH_64 = '64';

        const OS_5  = '5';
        const OS_6  = '6';
        const OS_7  = '7';
        const OS_8  = '8';
        const OS_81 = '81';
        const OS_10 = '10';

        protected static $attempted = [];

        protected static $fastMode     = false;
        protected static $sevenZipMode = false;

        public static function getDevicesTxt()
        {
            $dir = static::getExecutableDirectory();
            $content = file_get_contents($dir . 'devices.txt');

            return StringHelper::load($content)
                               ->getLinesArray();
        }

        public static function getExecutableDirectory()
        {
            return DirectoryWalker::fromCurrent()
                                  ->up(2)
                                  ->enter('files/DriverVerification')
                                  ->get();
        }

        public static function repack7z($archive, $workDir = null)
        {
            $type = static::getArchiveType($archive);

            if ($type !== false) {
                if ($type != '7z') {

                    if (is_null($workDir)) {
                        $workDir = dirname($archive);
                    }

                    $workDir = StringHelper::load($workDir)
                                           ->removeEnding('/')
                                           ->removeEnding('\\')
                                           ->ensureEndingIs(DIRECTORY_SEPARATOR)
                                           ->get();

                    $tempDir = $workDir . md5($archive) . DIRECTORY_SEPARATOR;

                    clearstatcache(true);
                    Common::remove($tempDir);

                    clearstatcache(true);
                    Common::createDirectoryIfNotExists($tempDir);

                    /**
                     * Extract origin archive
                     */
                    $command = escapeshellarg(static::get7ZipExePath()) .
                               ' x ' .
                               escapeshellarg($archive) .
                               ' -o' .
                               escapeshellarg($tempDir) .
                               ' 2>&1';

                    exec($command);

                    clearstatcache(true);
                    if (is_dir($tempDir)) {
                        if (count(Common::getDirectoryContentRecursive($tempDir)) > 0) {
                            /**
                             * Success extract
                             */
                            unlink($archive);

                            if (static::compressExtractedDirectory($tempDir, basename($archive), dirname($archive))) {

                                clearstatcache(true);
                                Common::remove($tempDir);

                                return true;
                            }

                        }
                    }

                }
            }

            return false;
        }

        public static function getArchiveType($archive)
        {
            $command = escapeshellarg(static::get7ZipExePath()) . ' l ' . escapeshellarg($archive) . ' 2>&1';

            $output = $exitcode = '';

            exec($command, $output, $exitcode);

            foreach ($output as $line) {

                $line = StringHelper::load($line)
                                    ->trimSpaces();

                if ($line->isPregMatch('#^type\s*=\s*\w+$#i')) {
                    return $line->setLastPart('=')
                                ->trimSpaces()
                                ->toLowerCase()
                                ->get();
                }

            }

            return false;
        }

        public static function get7ZipExePath()
        {
            $exe = static::getUnpackersDirectory() . '7z.exe';

            if (Common::isLinuxOS()) {

                if (static::is7zInstalled()) {
                    $exe = '7z';
                } else {
                    throw new \Exception('No 7zip!');
                }
            }

            return $exe;
        }

        public static function getUnpackersDirectory()
        {
            return DirectoryWalker::fromCurrent()
                                  ->up()
                                  ->up()
                                  ->enter('files')
                                  ->get();
        }

        public static function is7zInstalled()
        {
            $output = StringHelper::load(Common::executeInSystem('7z 2>&1'))
                                  ->trimSpaces()
                                  ->toLowerCase();

            return $output->isStartsWith('7-zip') &&
                   $output->isContain('switches') &&
                   $output->isContain('commands');
        }

        public static function compressExtractedDirectory($directoryToCompress, $archiveName, $outputDirectory)
        {

            $command = escapeshellarg(static::get7ZipExePath()) . ' a' .
                       ' -y' .
                       ' -xr!' . static::getExtractedPrefix() . '*' .
                       ' ' . escapeshellarg(StringHelper::load($outputDirectory)
                                                        ->ensureEndingIs(DIRECTORY_SEPARATOR)
                                                        ->append(StringHelper::load($archiveName)
                                                                             ->ensureEndingIs('.7z')
                                                                             ->get())
                                                        ->get()) .
                       ' ' . escapeshellarg(StringHelper::load($directoryToCompress)
                                                        ->ensureEndingIs(DIRECTORY_SEPARATOR)
                                                        ->append('*')
                                                        ->get());

            exec($command, $output, $exitCode);

            return $exitCode === 0;
        }

        public static function getExtractedPrefix()
        {
            return "_e_e___e__";
        }

        public static function getDatabaseCheckerPath()
        {
            return dirname(static::getDriverVerificationPath()) . DIRECTORY_SEPARATOR . 'DatabaseChecker.exe';
        }

        public static function getDriverVerificationPath()
        {
            return DirectoryWalker::fromCurrent()
                                  ->up(2)
                                  ->enter("/files/DriverVerification")
                                  ->get() . "DriverVerification.exe";
        }

        public static function enable7zMode()
        {
            static::$sevenZipMode = true;
        }

        public static function disable7zMode()
        {
            static::$sevenZipMode = false;
        }

        public static function enableFastMode()
        {
            static::$fastMode = true;
        }

        public static function disableFastMode()
        {
            static::$fastMode = false;
        }

        public static function getArchList()
        {
            return [
                static::ARCH_32,
                static::ARCH_64,
            ];
        }

        public static function getInstaller($initialFile, $infPath = null)
        {

            $sfx = static::isSfxArchive($initialFile);

            /**
             * Executable not sfx
             */
            if (in_array(StringHelper::load(Common::getFileExtension($initialFile))
                                     ->toLowerCase()
                                     ->get(), ['exe', 'msi']) && !$sfx) {

                return -1;

            } else {

                /**
                 * Try to find setup or install file in root folder
                 */

                $guessInstaller = static::getGuessInstaller($initialFile, $infPath);

                if (!empty($guessInstaller)) {
                    return Common::replaceSlashesToPlatformSlashes($guessInstaller);
                }

                if (empty($infPath)) {
                    return false;
                }

                /**
                 * Setup file not found
                 */

                $level = static::getExtractedLevel($infPath);

                if ($level == 0) {

                    /**
                     * Setup from inf
                     */
                    return Common::replaceSlashesToPlatformSlashes($infPath);

                } else {

                    /**
                     * Setup from internal executable file
                     */
                    $parent = static::getExtractedParent($infPath);

                    if (in_array(StringHelper::load(Common::getFileExtension($parent))
                                             ->toLowerCase()
                                             ->get(), ['exe', 'msi'])) {
                        return Common::replaceSlashesToPlatformSlashes($parent);
                    }
                }

            }

            return false;
        }

        public static function isSfxArchive($archive)
        {
            $command = escapeshellarg(static::get7ZipExePath()) . ' l ' . escapeshellarg($archive) . " -r0 2>&1 ";

            $output = Common::executeInSystem($command);
            $lines = StringHelper::load($output)
                                 ->getLinesArray();
            foreach ($lines as $line) {
                if (preg_match('#^offset\s*=\s*\d+$#i', $line) === 1) {
                    return true;
                }
            }

            return false;
        }

        public static function getGuessInstaller($archiveFullPath, $relativeInfPath)
        {
            $files = static::getArchiveFilesList($archiveFullPath);

            if (!empty($files)) {
                $installers = [
                    '#setup[^.]*\.exe$#i',
                    '#livecam[^.]*\.exe$#i',
                    '#autorun[^.]*\.exe$#i',
                    '#install[^.]*\.exe$#i',
                    '#run[^.]*\.exe$#i',
                    '#install[^.]*\.exe$#i',
                    '#cfg[^.]*\.exe$#i',
                    '#whql[^.]*\.exe#i',
                ];

                //try find root installers firstly

                $root = static::detectArchiveRoot($archiveFullPath);

                if (!empty($root)) {

                    $rootFiles = [];

                    $root = StringHelper::load($root);

                    foreach ($files as $file) {

                        if ($root->isEquals(DIRECTORY_SEPARATOR)) {

                            if (!StringHelper::load($file)
                                             ->isContainSome(['/', '\\'])) {
                                $rootFiles[] = $file;
                            }

                        } else {

                            if (StringHelper::load($file)
                                            ->isStartsWith($root->getClone()
                                                                ->substring(1)
                                                                ->get())) {

                                $rootFiles[] = $file;

                            }

                        }
                    }

                    if (!empty($rootFiles)) {

                        foreach ($installers as $installerRegexp) {
                            foreach ($rootFiles as $rootFile) {
                                if (StringHelper::load($rootFile)
                                                ->isPregMatch($installerRegexp)) {
                                    return StringHelper::load($rootFile)
                                                       ->replace('[\\\\/]', '/')
                                                       ->ensureBeginningIs('/')
                                                       ->get();
                                }
                            }
                        }
                    }

                }

                //root installer not found

                $possibleDirs = [];

                if (empty($relativeInfPath)) {

                    /**
                     * Leave only root dirs
                     */
                    $minDepth = false;

                    foreach ($files as $file) {

                        $depth = count(explode(DIRECTORY_SEPARATOR, Common::replaceSlashesToPlatformSlashes($file)));

                        if ($minDepth === false) {
                            $minDepth = $depth;
                        }

                        if ($depth < $minDepth) {
                            $minDepth = $depth;
                        }
                    }

                    foreach ($files as $index => $file) {
                        $depth = count(explode(DIRECTORY_SEPARATOR, Common::replaceSlashesToPlatformSlashes($file)));

                        if ($depth > $minDepth) {
                            unset($files[$index]);
                        }

                    }

                } else {

                    /**
                     * Leave dirs only from inf's directory to the root of archive
                     */

                    $relativeInfPath = Common::replaceSlashesToPlatformSlashes($relativeInfPath);

                    $infPathParts = explode(DIRECTORY_SEPARATOR, $relativeInfPath);

                    $infPathParts = array_filter($infPathParts, function ($value) {
                        return !empty($value);
                    });

                    array_pop($infPathParts);

                    $possibleDirs[] = DIRECTORY_SEPARATOR;

                    while (count($infPathParts) > 0) {

                        $possibleDirs[] = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR,
                                                                        $infPathParts);
                        array_pop($infPathParts);
                    }

                    $files = Common::sortPathsByDepth($files, true);
                }

                /**
                 * Filtering dirs
                 */
                foreach ($files as $index => $file) {

                    $file = StringHelper::load(Common::replaceSlashesToPlatformSlashes($file))
                                        ->ensureBeginningIs(DIRECTORY_SEPARATOR)
                                        ->get();

                    $dirname = dirname($file);

                    if (!in_array($dirname, $possibleDirs)) {
                        unset($files[$index]);
                    }

                    $files[$index] = $file;

                }

                /**
                 * Search installer
                 */
                foreach ($files as $file) {
                    foreach ($installers as $installerRegexp) {
                        if (preg_match($installerRegexp, basename($file)) == 1) {
                            return StringHelper::load($file)
                                               ->replace('[\\\\/]', '/')
                                               ->get();
                        }
                    }
                }

            }

            return false;

        }

        public static function getArchiveFilesList($archivePath)
        {

            clearstatcache(true);

            if (is_file($archivePath)) {

                $command = static::get7ZipExePath() . ' l ' . escapeshellarg($archivePath) . ' -r 2>&1';

                $output = StringHelper::load(Common::executeInSystem($command))
                                      ->getLinesArray();

                $entered = false;
                $filenameStartIndex = null;

                $files = [];

                foreach ($output as $line) {

                    if (preg_match('#^[\-\s]{10,}$#', $line) == 1) {

                        if ($entered) {
                            $entered = false;
                        } else {

                            $entered = true;

                            $filenameStartIndex = StringHelper::load($line)
                                                              ->getPositionFromEnd(' ') + 1;

                            continue;
                        }

                    }

                    if ($entered) {

                        $files[] = StringHelper::load($line)
                                               ->substring($filenameStartIndex)
                                               ->trimSpacesRight()
                                               ->get();

                    }

                }

                return Common::sortFilesAndFolders($files);

            }

            return false;

        }

        public static function detectArchiveRoot($archiveFullPath)
        {
            $files = static::getArchiveFilesList($archiveFullPath);

            if (!empty($files)) {

                $partsCounts = [];

                foreach ($files as $file) {

                    $fileReplaced = Common::replaceSlashesToPlatformSlashes($file);

                    if (StringHelper::load($fileReplaced)
                                    ->isPregMatch('#\.\w+$#i')) {

                        $partsCount = count(explode(DIRECTORY_SEPARATOR, $fileReplaced));

                        if (!array_key_exists($partsCount, $partsCounts)) {
                            $partsCounts[$partsCount] = $file;
                        }

                    }
                }

                if (!empty($partsCounts)) {

                    ksort($partsCounts);

                    $rootFile = array_shift($partsCounts);

                    $root = StringHelper::load($rootFile);

                    if ($root->isContainSome(['/', '\\'])) {

                        $separator = '/';

                        if ($root->isContain('\\')) {
                            $separator = '\\';
                        }

                        $root = $root->ensureBeginningIs($separator)
                                     ->removeLastPart($separator)
                                     ->get();
                        if (empty($root)) {
                            return DIRECTORY_SEPARATOR;
                        }

                        return $root;

                    } else {
                        return DIRECTORY_SEPARATOR;
                    }

                }

            }

            return false;
        }

        public static function getExtractedLevel($path)
        {
            $level = 0;

            $path = static::restoreExtractedPath($path);

            $parts = explode(static::detectDirectorySeparator($path), $path);

            foreach ($parts as $part) {
                if (StringHelper::load($part)
                                ->isEndsWithSomeIgnoreCase(array_map(function ($value) {
                                    return '.' . $value;
                                }, static::getValidExtensions()))
                ) {
                    $level++;
                }
            }

            return $level;
        }

        public static function restoreExtractedPath($extractedPath)
        {

            $separator = static::detectDirectorySeparator($extractedPath);

            $parts = explode($separator, $extractedPath);

            $extensions = array_map(function ($value) {
                return '(' . StringHelper::getCaseInsensitiveRegexpString($value) . ')';
            }, static::getValidExtensions());

            $extensions = '_' . implode('|', $extensions);

            /**
             * Restore
             */
            foreach ($parts as $key => $part) {

                $part = StringHelper::load($part);

                if (
                    $part->isMatch('#^' . static::getExtractedPrefix() . '#') &&
                    $part->isMatch('#' . $extensions . '$#')
                ) {
                    $parts[$key] = $part
                        ->removeBeginning(static::getExtractedPrefix())
                        ->replaceCharacters(['_'], '.')
                        ->get();
                }
            }

            /**
             * Implode full path
             */
            $restored = implode($separator, $parts);

            return $restored;

        }

        public static function detectDirectorySeparator($path)
        {
            if (StringHelper::load($path)
                            ->isContain('\\')
            ) {
                return '\\';
            }

            return '/';
        }

        public static function getValidExtensions()
        {
            return [
                'msi',
                'cab',
                'exe',
                'zip',
                'rar',
                '7z',
            ];
        }

        public static function getExtractedParent($path)
        {

            $path = static::restoreExtractedPath($path);

            $parts = explode(static::detectDirectorySeparator($path), $path);

            foreach ($parts as $index => $part) {
                if (StringHelper::load($part)
                                ->isEndsWithSomeIgnoreCase(array_map(function ($value) {
                                    return '.' . $value;
                                }, static::getValidExtensions()))
                ) {

                    $parent = StringHelper::load($path);

                    return $parent
                        ->substring(0, $parent->getPosition($part))
                        ->append($part)
                        ->get();
                }
            }

            return false;
        }

        public static function isExtractedFromExtension($path, $extension)
        {
            $parts = explode(static::detectDirectorySeparator($path), $path);

            foreach ($parts as $part) {
                if (StringHelper::load($part)
                                ->isEndsWithSomeIgnoreCase(array_map(function ($value) {
                                    return '.' . $value;
                                }, static::getValidExtensions()))
                ) {

                    if (StringHelper::load($part)
                                    ->isEndsWithIgnoreCase('.' . $extension)
                    ) {
                        return true;
                    }

                    break;

                }
            }

            return false;
        }

        public static function extract($filename, $allowPartialExtraction = false, $maxDepth = null)
        {

            static::$attempted = [];

            /**
             * Check requirements
             */
            if (Common::isLinuxOS()) {
                if (
                    !static::isWineInstalled() ||
                    !static::is7zInstalled() ||
                    !static::isUnrarInstalled()
                ) {
                    throw new \Exception('Wine, 7zip, or unrar is not installed');
                }
            }

            static::doExtract($filename, $maxDepth);

            static::$attempted = [];

            $haveContent = count(Common::getDirectoryContentRecursive(static::getExtractionDir($filename))) > 0;

            $haveUnextracted = count(static::getUnExtractedFiles(static::getExtractionDir($filename))) > 0;

            /**
             * Partially extraction mode
             */
            if ($haveContent && $allowPartialExtraction) {
                return true;
            }

            /**
             * Full extraction mode
             */
            $extracted = $haveContent && !$haveUnextracted;

            /**
             * Remove directories if not extracted
             */
            if (!$extracted) {
                Common::removeDirectory(static::getExtractionDir($filename));
            }

            return $extracted;
        }

        public static function isWineInstalled()
        {
            $output = StringHelper::load(Common::executeInSystem('wine --version 2>&1'))
                                  ->trimSpaces()
                                  ->toLowerCase();

            return $output->isStartsWith('wine-');
        }

        public static function isUnrarInstalled()
        {
            $output = StringHelper::load(Common::executeInSystem('unrar 2>&1'))
                                  ->trimSpaces()
                                  ->toLowerCase();

            return $output->isStartsWith('unrar') &&
                   $output->isContain('switches') &&
                   $output->isContain('commands');
        }

        protected static function doExtract($filename, $maxDepth)
        {

            if (static::isInFastMode()) {
                $maxDepth = 1;
            }

            static::$attempted[] = $filename;

            clearstatcache(true);

            if (
                file_exists($filename)
                &&
                static::isFileExtensionValid($filename)
                &&
                (
                    is_null($maxDepth)
                    ||
                    (
                        !is_null($maxDepth)
                        &&
                        static::getExtractedLevel($filename) <= $maxDepth
                    )
                )
            ) {

                $extractionDir = StringHelper::load(Common::replaceSlashesToPlatformSlashes(static::getExtractionDir($filename)))
                                             ->ensureEndingIs(DIRECTORY_SEPARATOR)
                                             ->get();

                if (is_dir($extractionDir)) {
                    Common::removeDirectory($extractionDir);
                }

                $methods = static::getExtractionMethods($filename);

                if (!empty($methods)) {

                    foreach ($methods as $methodName => $method) {

                        if (is_dir($extractionDir . $methodName)) {
                            Common::removeDirectory($extractionDir . $methodName);
                        }

                        Common::createDirectoryIfNotExists($extractionDir . $methodName);

                        $method($filename, $extractionDir . $methodName);
                    }

                    /**
                     * All methods done
                     * Finding usefull from it
                     */
                    $usefullMethod = ArrayHelper::load($methods)
                                                ->getFirstKey();

                    $usefullCount = count(Common::getDirectoryContentRecursive($extractionDir . $usefullMethod));

                    foreach ($methods as $methodName => $method) {

                        if ($methodName != $usefullMethod) {

                            $count = count(Common::getDirectoryContentRecursive($extractionDir . $methodName));

                            if ($count > $usefullCount) {

                                Common::removeDirectory($extractionDir . $usefullMethod);

                                $usefullCount = $count;
                                $usefullMethod = $methodName;
                            } else {
                                Common::removeDirectory($extractionDir . $methodName);
                            }
                        }
                    }

                    /**
                     * Rename
                     */
                    foreach (Common::getDirectoryContent($extractionDir . $usefullMethod) as $path) {

                        $baseName = basename($path);

                        $newName = StringHelper::load($path)
                                               ->removeEnding($baseName)
                                               ->removeEnding(DIRECTORY_SEPARATOR)
                                               ->removeEnding($usefullMethod)
                                               ->ensureEndingIs(DIRECTORY_SEPARATOR)
                                               ->append($baseName)
                                               ->get();

                        if ($path != $newName) {
                            Common::move($path, $newName);
                        }
                    }

                    Common::removeDirectory($extractionDir . $usefullMethod);

                    /**
                     * Remove if directory is empty
                     */
                    if (count(Common::getDirectoryContentRecursive($extractionDir)) == 0) {
                        Common::removeDirectory($extractionDir);
                    }

                    /**
                     * Extraction done.
                     * Now recursive extract nested files
                     */
                    while (!empty($unExtracted = static::getUnExtractedFiles($extractionDir))) {
                        foreach ($unExtracted as $forExtraction) {
                            static::doExtract($forExtraction, $maxDepth);
                        }
                    }
                }
            }
        }

        public static function isInFastMode()
        {
            return static::$fastMode === true;
        }

        public static function isFileExtensionValid($filename)
        {
            return in_array(StringHelper::load($filename)
                                        ->toLowerCase()
                                        ->getLastPart('.'), static::getValidExtensions());
        }

        public static function getExtractionDir($filename)
        {
            $directory = StringHelper::load(dirname($filename))
                                     ->ensureEndingIs(DIRECTORY_SEPARATOR);

            $name = StringHelper::load(basename($filename))
                                ->prepend(static::getExtractedPrefix())
                                ->replaceCharacters(['.'], '_');

            return $directory->append($name)
                             ->get();
        }

        public static function getExtractionMethods($filename = null)
        {

            $methods = [
                'inno'  => function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . 'innounp.exe';

                    $cmd = escapeshellarg($exe) . ' -x -q -b -y -d' . escapeshellarg($to) . ' ' . escapeshellarg($path);

                    return static::executeCommand($cmd, false);
                },
                'wise'  => function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . 'E_WISE_W.exe';

                    $cmd = escapeshellarg($exe) . ' ' . escapeshellarg($path) . ' ' . escapeshellarg($to);

                    return static::executeCommand($cmd, false);
                },
                '7z'    => function ($path, $to) {

                    $exe = static::get7ZipExePath();

                    $cmd = escapeshellarg($exe) . ' x -y ' . escapeshellarg($path) . ' -p123 -o' . escapeshellarg($to);

                    return static::executeCommand($cmd);
                },
                'unrar' => function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . 'UnRAR.exe';

                    if (Common::isLinuxOs()) {
                        $exe = 'unrar';
                    }

                    $cmd = escapeshellarg($exe) . ' x -y -o+ -p- ' . escapeshellarg($path) . ' ' . escapeshellarg($to);

                    return static::executeCommand($cmd);
                },
                'i5'    => function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . 'i5comp.exe';

                    $cmd = escapeshellarg($exe) . ' x -r -o ' . escapeshellarg($path) . ' "*" ' . escapeshellarg($to);

                    return static::executeCommand($cmd, false);
                },
                'msix'  => function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . 'MsiX.exe';

                    if (Common::isLinuxOS()) {

                        $path = Common::executeInSystem('wine winepath -w ' . escapeshellarg($path) . ' 2>/dev/null');
                        $to = Common::executeInSystem('wine winepath -w ' . escapeshellarg($to) . ' 2>/dev/null');

                        $path = StringHelper::load($path)
                                            ->trimSpacesRight()
                                            ->get();
                        $to = StringHelper::load($to)
                                          ->trimSpacesRight()
                                          ->get();
                    }

                    $cmd = escapeshellarg($exe) . ' ' . escapeshellarg($path) . ' /out ' . escapeshellarg($to);

                    return static::executeCommand($cmd, false);
                },
            ];

            if (static::isIn7zMode()) {
                $methods = ['7z' => $methods['7z']];
            }

            if (Common::isLinuxOS()) {
                $methods['7zWine'] = function ($path, $to) {

                    $exe = static::getUnpackersDirectory() . '7z.exe';

                    $cmd = escapeshellarg($exe) .
                           ' x -y ' .
                           escapeshellarg($path) .
                           ' -o' .
                           escapeshellarg($to) .
                           ' 2>/dev/null';

                    return static::executeCommand($cmd, false);
                };
            }

            return $methods;
        }

        public static function executeCommand($command, $noWine = true)
        {
            if (Common::isLinuxOS()) {
                if (!$noWine) {
                    $command = 'wine ' . $command;
                }
            }

            $output = $exitcode = null;

            @exec($command . ' 2>&1', $output, $exitcode);

            return $exitcode;
        }

        public static function isIn7zMode()
        {
            return static::$sevenZipMode === true;
        }

        protected static function getUnExtractedFiles($directory)
        {
            $unextracted = Common::getDirectoryContentRecursive($directory);

            foreach ($unextracted as $index => $path) {

                if (
                    is_dir($path) ||
                    !static::isFileExtensionValid($path) ||
                    is_dir(static::getExtractionDir($path)) ||
                    in_array($path, static::$attempted)
                ) {
                    unset($unextracted[$index]);
                    continue;
                }
            }

            return array_values($unextracted);
        }

        public static function parseInf($file, $installedFromExe, &$outputParsed = null, &$outputOS = null)
        {

            clearstatcache(true);

            $return = [];
            $command = $output = $exitcode = '';

            $installedFlag = '/inf';

            if ($installedFromExe) {
                $installedFlag = '/exe';
            }

            $parser = DirectoryWalker::fromCurrent()
                                     ->upUntil('src')
                                     ->up()
                                     ->enter('files/InfParser')
                                     ->get() . 'InfParser.exe';

            if (file_exists($file)) {

                $catFiles = static::getCatFilesFromInf($file);

                if (!empty($catFiles)) {
                    /**
                     * Verify files
                     */
                    foreach ($catFiles as $index => $catFile) {
                        $fullcat = static::findCatFile($catFile, dirname($file));
                        if (!static::verifyInfCat($file, $fullcat)) {
                            unset($catFiles[$index]);
                        }
                    }

                    if (!empty($catFiles)) {
                        foreach ($catFiles as $catFile) {
                            $fullcat = static::findCatFile($catFile, dirname($file));
                            $osFromCat = static::getOSFromCatFile($fullcat);
                            if (!empty($osFromCat)) {

                                $command = escapeshellarg($parser) .
                                           ' ' .
                                           $installedFlag .
                                           ' ' .
                                           escapeshellarg($file) .
                                           ' ' .
                                           escapeshellarg($osFromCat) .
                                           ' 2>&1';

                                @exec($command, $output, $exitcode);

                                if (!is_null($outputOS)) {
                                    $outputOS = $osFromCat;
                                }

                                if (!is_null($outputParsed)) {
                                    $outputParsed = $output;
                                }

                                $count = count($output);

                                if ($count > 3) {

                                    $last = $output[$count - 1];
                                    $preLast = $output[$count - 2];

                                    if ($last == 'success' && $preLast == '[FINISH]') {

                                        foreach ($output as $index => $line) {

                                            $line = StringHelper::load($line);

                                            if ($line->isPregMatch('#^\[\d+\]$#')
                                                &&
                                                array_key_exists($index + 7, $output)) {

                                                $return[] = [
                                                    'OS'           => StringHelper::load($output[$index + 1])
                                                                                  ->removeFirstPart('=')
                                                                                  ->get(),
                                                    'HID'          => StringHelper::load($output[$index + 2])
                                                                                  ->removeFirstPart('=')
                                                                                  ->get(),
                                                    'Date'         => StringHelper::load($output[$index + 3])
                                                                                  ->removeFirstPart('=')
                                                                                  ->get(),
                                                    'Version'      => StringHelper::load($output[$index + 4])
                                                                                  ->removeFirstPart('=')
                                                                                  ->get(),
                                                    'Description'  => StringHelper::load($output[$index + 5])
                                                                                  ->removeFirstPart('=')
                                                                                  ->get(),
                                                    'Provider'     => static::replaceVendor(StringHelper::load($output[$index +
                                                                                                                       6])
                                                                                                        ->removeFirstPart('=')
                                                                                                        ->get()),
                                                    'Manufacturer' => static::replaceVendor(StringHelper::load($output[$index +
                                                                                                                       7])
                                                                                                        ->removeFirstPart('=')
                                                                                                        ->get()),
                                                ];

                                            }

                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }

            return $return;
        }

        public static function getCatFilesFromInf($file)
        {

            $files = [];

            $content = static::detectEncoding(file_get_contents($file));

            $content = static::removeComments($content);

            $content = static::getStringsReplacedContent($content);

            $lines = StringHelper::load($content)
                                 ->getLinesArray();

            foreach ($lines as $line) {

                $line = StringHelper::load($line)
                                    ->remove('#\s+#');

                if ($line->getClone()
                         ->toLowerCase()
                         ->isMatch('#^cat(alog)?(file|name)([^=])*=[^\.]+\.cat$#i')) {
                    $files[] = $line->getLastPart('=');
                }

            }

            return $files;

        }

        public static function detectEncoding($content)
        {
            $reservedEncodings = [
                'UTF-16LE',
                'UTF-32',
                'UTF-32BE',
                'UTF-32LE',
                'UTF-16',
                'ISO-8859-1',
                'UTF-16BE',
                'ASCII',
                'Windows-1252',
            ];

            $condition = function ($content) {
                return stripos($content, '[version]') !== false;
            };

            if (!$condition($content)) {
                //Find usable encoding
                foreach ($reservedEncodings as $encoding) {
                    $converted = mb_convert_encoding($content, Common::getDefaultEncoding(), $encoding);
                    if ($condition($converted)) {
                        return $converted;
                    }
                }
            }

            return $content;
        }

        public static function removeComments($content, $commentSign = ';')
        {
            return preg_replace('#' . $commentSign . '.*#', '', $content);
        }

        public static function getStringsReplacedContent($content, $parsed = null)
        {

            if (empty($parsed)) {
                $parsed = static::infToArray($content);
            }

            if (!empty($parsed)) {

                $replaces = [];

                foreach ($parsed as $section => $sectionValues) {

                    if (StringHelper::load($section)
                                    ->toLowerCase()
                                    ->isMatch('#^strings$#i')
                    ) {

                        foreach ($sectionValues as $value) {

                            $stringValue = StringHelper::load($value)
                                                       ->setFirstPart(';');

                            if ($stringValue->isContainIgnoreCase('=') && $stringValue->getLength() > 3) {

                                $key = $stringValue->getClone()
                                                   ->setFirstPart('=')
                                                   ->trimSpacesRight()
                                                   ->trimSpacesLeft()
                                                   ->get();

                                $value = $stringValue->getClone()
                                                     ->removeFirstPart('=')
                                                     ->trimSpacesRight()
                                                     ->trimSpacesLeft();

                                if ($value->isWrappedBy('"')) {
                                    $value->unwrap('"');
                                }

                                if ($value->isWrappedBy('\'')) {
                                    $value->unwrap('\'');
                                }

                                $replaces[$key] = $value->get();

                            }

                        }
                    }
                }

                if (!empty($replaces)) {

                    foreach ($replaces as $key => $value) {

                        $pattern = StringHelper::load(preg_quote($key))
                                               ->replace('/#/', '\#')
                                               ->prepend('#%')
                                               ->append('%#')//fix escape
                                               ->get();

                        $content = preg_replace($pattern, $value, $content);
                    }

                }

            }

            return $content;

        }

        public static function infToArray($content)
        {

            $parsed = [];

            $content = static::detectEncoding($content);

            $lines = StringHelper::load($content)
                                 ->getLinesArray();

            $section = '';

            foreach ($lines as $line) {

                /**
                 * Read none comment line
                 */
                $line = StringHelper::load($line)
                                    ->setFirstPart(';')
                                    ->trimSpacesLeft()
                                    ->trimSpacesRight()
                                    ->remove('#<[^>]*>#');

                if ($line->getLength() > 0) {

                    if ($line->isStartsWith('[')) {

                        /**
                         * Section line
                         */
                        $section = $line->removeEnding(']')
                                        ->removeBeginning('[')
                                        ->trimSpacesRight()
                                        ->trimSpacesLeft()
                                        ->get();

                    } else {

                        /**
                         * None section line
                         */
                        if ($line->isContain('=')) {

                            /**
                             * Replace spaces around equals sign
                             */
                            $line->replace('#\s*=\s*#', '=');
                        }

                        if (!empty($section)) {
                            $parsed[$section][] = $line->get();
                        }
                    }

                }
            }

            return $parsed;
        }

        public static function findCatFile($file, $directory)
        {
            foreach (Common::getDirectoryContent($directory) as $path) {

                $helper = StringHelper::load($path);

                if ($helper->getClone()
                           ->setLastPart(DIRECTORY_SEPARATOR)
                           ->isEqualsIgnoreCase($file)) {
                    return $path;
                }

            }

            return false;
        }

        public static function verifyInfCat($inf, $cat)
        {
            try {

                $tempDir = __DIR__ . DIRECTORY_SEPARATOR . md5($inf . ' ' . $cat) . DIRECTORY_SEPARATOR;

                Common::remove($tempDir);

                Common::createDirectoryIfNotExists($tempDir);

                clearstatcache(true);

                if (!is_dir($tempDir)) {
                    throw new \Exception('Cant create temp dir');
                }

                if (!copy($inf, $tempDir . basename($inf))
                    ||
                    !copy($cat, $tempDir . basename($cat))) {
                    throw new \Exception('Cant copy file');
                }

                $command = escapeshellarg(static::getDriverVerificationPath()) . ' /verify ' . $tempDir;

                $output = $exitcode = '';

                exec($command, $output, $exitcode);

                Common::remove($tempDir);

                if (is_array($output) && count($output) == 1 && $output[0] == 'success') {
                    return true;
                }
            }
            catch (\Throwable $e) {
                return false;
            }

            return false;

        }

        public static function getOSFromCatFile($cat)
        {
            $command = escapeshellarg(static::getDriverVerificationPath()) . ' /cat ' . $cat . " 2>&1";
            $output = $exitcode = '';
            exec($command, $output, $exitcode);
            if (is_array($output) && count($output) == 1) {
                $string = StringHelper::load($output[0]);
                if ($string->isStartsWith('success:')) {
                    return $string->removeBeginning('success:')
                                  ->get();
                }
            }

            return false;
        }

        public static function replaceVendor($vendor)
        {

            $vendor = StringHelper::load($vendor)
                                  ->toUpperCase()
                                  ->trimSpaces()
                                  ->get();

            $vendorReplacer = DirectoryWalker::fromCurrent()
                                             ->upUntil('src')
                                             ->up()
                                             ->enter('files/VendorReplacer')
                                             ->get() . 'VendorReplacer.exe';

            $command = $vendorReplacer . ' "' . $vendor . '" 2>' . Common::getNullDevice();

            if (Common::isLinuxOS()) {
                $command = 'wine ' . $command;
            }

            $output = [];
            $exitcode = 0;

            @exec($command, $output, $exitcode);

            if (!empty($output) && $exitcode == 0) {
                $outputVendor = StringHelper::load($output)
                                            ->trimSpaces()
                                            ->toUpperCase()
                                            ->get();

                if (!empty($outputVendor)) {
                    return $outputVendor;
                }
            }

            return $vendor;
        }

        public static function getExcludedArchesFromPath($path)
        {
            $arches = [
                static::ARCH_32 => static::get32ArchRegexps(),
                static::ARCH_64 => static::get64ArchRegexps(),
            ];

            $path = Common::replaceSlashesToPlatformSlashes($path);

            $pathParts = explode(DIRECTORY_SEPARATOR, $path);

            /**
             * From the end
             */
            $pathParts = array_reverse($pathParts);

            $archMatch = false;

            foreach ($pathParts as $pathPart) {
                foreach ($arches as $archKey => $archValue) {
                    foreach ($archValue as $archRegexp) {
                        if (preg_match('#' . $archRegexp . '#i', $pathPart) == 1) {
                            $archMatch = $archKey;
                            break 3;
                        }
                    }
                }
            }

            if ($archMatch === false) {
                return [];
            }

            unset($arches[$archMatch]);

            return array_keys($arches);

        }

        public static function get32ArchRegexps()
        {
            return [
                'x?' . static::getSeparatorsRegexp() . '(i386|86|32)' . static::getSeparatorsRegexp() . '(b(it)?)?',
                'sp2',
                'sp3',
            ];
        }

        protected static function getSeparatorsRegexp()
        {
            return '([ \._,\-\/&]*)?';
        }

        public static function get64ArchRegexps()
        {
            return [
                '(x|amd|nt|i|nti)?' .
                static::getSeparatorsRegexp() .
                '64' .
                static::getSeparatorsRegexp() .
                '(b(it)?)?',
            ];
        }

        public static function getValidHardId($hardid)
        {
            $badFile = DirectoryWalker::fromCurrent()
                                      ->upUntil('src')
                                      ->up()
                                      ->enter('files')
                                      ->get() . 'BadHardidsList.php';

            $bad = include($badFile);

            if (
                array_key_exists($hardid, $bad)
                ||
                StringHelper::load($hardid)
                            ->trimSpaces()
                            ->isEmpty()
            ) {
                return false;
            }

            return $hardid;

        }

        public static function removeFromArchive($archivePath, $removePath)
        {

            clearstatcache(true);

            if (is_file($archivePath)) {

                $command = static::get7ZipExePath() .
                           ' d ' .
                           escapeshellarg($archivePath) .
                           ' ' .
                           escapeshellarg($removePath) .
                           ' -r0 2>&1';

                $output = $exitcode = null;

                @exec($command, $output, $exitcode);

                return $exitcode == 0;

            }

            return false;

        }

        public static function getOSFromString($string)
        {
            $oses = [];
            $parts = StringHelper::load(preg_replace('/[^\x00-\x7F]+/', '', $string))
                                 ->replace('#<[^>]*br[^>]*>#', "\n")
                                 ->replace('#%#', '\%')
                                 ->replace('#\s+(and|&|or)\s+#', ' / ')
                                 ->replace('#\s+((\d+\.){2,}\d)\s+#', ' ')
                                 ->replace(
                                     '#' .
                                     static::getSeparatorsRegexp() .
                                     '2\d{3}' .
                                     static::getSeparatorsRegexp() .
                                     '#',
                                     ' '
                                 )
                                 ->replace('#\(#', ' (')
                                 ->replace('#[\[\]]+#', ' ')
                                 ->replace('#\((c|r|tm)\)#', ' ')
                                 ->getLinesArray();

            /**
             * Special cases:
             * Windows 10/7 (32 & 64-bit)
             * 10/7 (32 & 64-bit)
             * 10/7/8 (32 & 64-bit)
             * win 10/ win 7/ windows 8 (32 & 64-bit)
             */

            foreach ($parts as $index => $part) {

                $part = StringHelper::load($part)
                                    ->trimSpaces()
                                    ->get();

                $versionPattern = '(' .
                                  static::getWindowsRegexp() .
                                  static::getSeparatorsRegexp() .
                                  '(xp|vista|7|8|8' .
                                  static::getSeparatorsRegexp() .
                                  '1|10)' .
                                  static::getOSVersionsRegexp() .
                                  ')';

                $versionSeparators = '[\/&,; ]';

                $pattern = '#((' .
                           $versionSeparators .
                           '*' .
                           $versionPattern .
                           $versionSeparators .
                           '*' .
                           '){2,})\s+.+$#iu';

                $matches = [];

                if (preg_match($pattern, $part, $matches) === 1) {

                    if (!empty($matches)) {

                        $tail = ' ' . StringHelper::load($matches[0])
                                                  ->removeFirstChars(StringHelper::load($matches[1])
                                                                                 ->getLength())
                                                  ->get();

                        $matches = preg_split('#' . $versionSeparators . '+#', $matches[1]);

                        if (!empty($matches)) {

                            foreach ($matches as $match) {
                                $parts[] = StringHelper::load($match . $tail)
                                                       ->trimSpaces()
                                                       ->get();
                            }
                        }
                    }

                }
            }

            /**
             * Complete parts if have one arch but haven't other
             */
            if (count($parts) > 1) {

                $tied = array_map(function ($value) {

                    $string = StringHelper::load($value);

                    foreach (static::getOSVersionStrings() as $versionString) {
                        $string = $string->replace('#' . preg_quote($versionString, '#') . '#i', ' ');
                    }

                    return $string
                        ->trimSpaces()
                        ->get();

                }, $parts);

                $tied = array_filter($tied, function ($value) {

                    foreach (static::getBadOSRegexps() as $badRegexp) {
                        if (preg_match($badRegexp, $value) === 1) {
                            return false;
                        }
                    }

                    return true;
                });

                /**
                 * Search arch
                 */
                foreach ($tied as $item) {

                    $regexps = [
                        static::ARCH_32 => static::get32ArchRegexps(),
                        static::ARCH_64 => static::get64ArchRegexps(),
                    ];

                    foreach ($regexps as $arch => $regexpArch) {

                        foreach ($regexpArch as $regexp) {

                            if (preg_match('#' . $regexp . '#iu', $item) === 1) {

                                $otherArch = $arch == static::ARCH_32 ? static::ARCH_64 : static::ARCH_32;
                                $otherArchOS = StringHelper::load($item)
                                                           ->removeLastPart(' ')
                                                           ->get();

                                $foundOther = array_search($otherArchOS, $tied);

                                if ($foundOther !== false) {

                                    $foundOther = $tied[$foundOther] . ' ' . $otherArch . '-bit';

                                    $parts[] = $foundOther;
                                }
                            }
                        }

                    }

                }

            }

            /**
             * All parts ready
             */
            foreach ($parts as $part) {

                $clear = StringHelper::load($part)
                                     ->replace('#<[^>]+>#', ' ')
                                     ->replace('#[^\w\.\- \/\\\\&_]+#', ' ')
                                     ->trimSpaces()
                                     ->get();

                $oses = array_merge($oses, static::getOSFromStringValue($clear));
            }

            $oses = array_unique($oses);

            sort($oses);

            return $oses;
        }

        protected static function getWindowsRegexp()
        {
            return '(w(in(dows)?)?)?';
        }

        protected static function getOSVersionsRegexp()
        {
            return '(( |' . implode('|', static::getOSVersionStrings()) . ')+)?';
        }

        protected static function getOSVersionStrings()
        {
            return [
                'business',
                'home',
                'ultimate',
                'professional',
                'pro',
                'premium',
                'edition',
                'basic',
                'maximum',
                'enterprise',
                'starter',
            ];
        }

        protected static function getBadOSRegexps()
        {
            return [
                '#^\d+$#iu',
                '#macos|linux|unix#iu',
                '#^\W+$#iu',
            ];
        }

        protected static function getOSFromStringValue($string)
        {
            $oses = [];

            $string = StringHelper::load($string)
                                  ->trimSpaces()
                                  ->get();

            foreach (static::getBadOSRegexps() as $badRegexp) {
                if (preg_match($badRegexp, $string) === 1) {
                    return [];
                }
            }
            foreach (static::getOSList() as $os => $osData) {

                $regexps = [];

                if ($osData['arch'] == static::ARCH_32) {
                    $regexps = array_merge(static::get32ArchRegexps(), static::get3264ArchRegexps());
                } elseif ($osData['arch'] == static::ARCH_64) {
                    $regexps = array_merge(static::get64ArchRegexps(), static::get3264ArchRegexps());
                }
                foreach ($regexps as $regexp) {
                    $matchRegexp = sprintf($osData['humanRegexp'], $regexp);
                    if (preg_match($matchRegexp, $string) === 1) {
                        $oses[] = $os;
                        break;
                    }
                }
            }

            return $oses;
        }

        public static function getOSList()
        {
            return [
                static::getFullOSName(static::OS_5, static::ARCH_32)  => [
                    'name'        => 'Windows XP 32-bit',
                    'value'       => static::OS_5,
                    'osRegexp'    => '#\b5\.1\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        'xp' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'complete'    => 1,//This flag means that regexp match get arch and platform
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_5, static::ARCH_64)  => [
                    'name'        => 'Windows XP 64-bit',
                    'value'       => static::OS_5,
                    'osRegexp'    => '#\b5\.2\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        'xp' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'complete'    => 1,//This flag means that regexp match get arch and platform
                    'arch'        => static::ARCH_64,
                ],
                static::getFullOSName(static::OS_6, static::ARCH_32)  => [
                    'name'        => 'Windows Vista 32-bit',
                    'value'       => static::OS_6,
                    'osRegexp'    => '#\b6\.0\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        'vista' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_6, static::ARCH_64)  => [
                    'name'        => 'Windows Vista 64-bit',
                    'value'       => static::OS_6,
                    'osRegexp'    => '#\b6\.0\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        'vista' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_64,
                ],
                static::getFullOSName(static::OS_7, static::ARCH_32)  => [
                    'name'        => 'Windows 7 32-bit',
                    'value'       => static::OS_7,
                    'osRegexp'    => '#\b6\.1\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '7' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_7, static::ARCH_64)  => [
                    'name'        => 'Windows 7 64-bit',
                    'value'       => static::OS_7,
                    'osRegexp'    => '#\b6\.1\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '7' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_64,
                ],
                static::getFullOSName(static::OS_8, static::ARCH_32)  => [
                    'name'        => 'Windows 8 32-bit',
                    'value'       => static::OS_8,
                    'osRegexp'    => '#\b6\.2\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '8' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_8, static::ARCH_64)  => [
                    'name'        => 'Windows 8 64-bit',
                    'value'       => static::OS_8,
                    'osRegexp'    => '#\b6\.2\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '8' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_64,
                ],
                static::getFullOSName(static::OS_81, static::ARCH_32) => [
                    'name'        => 'Windows 8.1 32-bit',
                    'value'       => static::OS_81,
                    'osRegexp'    => '#\b6\.3\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '8' .
                        static::getSeparatorsRegexp() .
                        '1' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_81, static::ARCH_64) => [
                    'name'        => 'Windows 8.1 64-bit',
                    'value'       => static::OS_81,
                    'osRegexp'    => '#\b6\.3\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '8' .
                        static::getSeparatorsRegexp() .
                        '1' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_64,
                ],
                static::getFullOSName(static::OS_10, static::ARCH_32) => [
                    'name'        => 'Windows 10 32-bit',
                    'value'       => static::OS_10,
                    'osRegexp'    => '#\b10\.0\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '10' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_32,
                ],
                static::getFullOSName(static::OS_10, static::ARCH_64) => [
                    'name'        => 'Windows 10 64-bit',
                    'value'       => static::OS_10,
                    'osRegexp'    => '#\b10\.0\b#',
                    'humanRegexp' => sprintf(
                        '#%s#iu',
                        static::getWindowsRegexp() .
                        static::getSeparatorsRegexp() .
                        '10' .
                        static::getOSVersionsRegexp() .
                        static::getSeparatorsRegexp() .
                        '%s'
                    ),
                    'arch'        => static::ARCH_64,
                ],
            ];
        }

        public static function getFullOSName($os, $arch)
        {
            return $os . '_' . $arch;
        }

        public static function get3264ArchRegexps()
        {
            $regexps = [
                '\s*all\s+versions?\s*',
            ];

            foreach (static::get32ArchRegexps() as $arch32) {
                foreach (static::get64ArchRegexps() as $arch64) {
                    $regexps[] = $arch32 . static::getSeparatorsRegexp() . $arch64;
                }
            }

            return $regexps;
        }

    }
}
