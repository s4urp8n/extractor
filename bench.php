<?php

require "vendor/autoload.php";

$benchDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'bench' . DIRECTORY_SEPARATOR;

$clear = function () use ($benchDirectory) {
    foreach (\Zver\Common::getDirectoryContentRecursive($benchDirectory) as $path) {

        clearstatcache(true);

        if (
            is_dir($path)
            ||
            !\Zver\Extractor::isFileExtensionValid($path)
        ) {
            \Zver\Common::remove($path);
        }

    }
};

$clear();

$extracted = 0;
$all = 0;

$startTime = time();

foreach (\Zver\Common::getDirectoryContentRecursive($benchDirectory) as $path) {

    if (\Zver\Extractor::isFileExtensionValid($path)) {

        $all++;
        if (\Zver\Extractor::extract($path, true)) {
            $extracted++;
        }

    }

}

$duration = time() - $startTime;

$dump = \Zver\Common::getDirectoryContentRecursive($benchDirectory);

$clear();

echo "Extracted files: " . $extracted . "\n";
echo "All files: " . $all . "\n";
echo "Dump:\n";
print_r($dump);
echo "Duration is " . gmdate('H:i:s', $duration) . "\n";
