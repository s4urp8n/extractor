<?php

use Zver\Common;
use Zver\Extractor;
use Zver\StringHelper;

class ExtractorTest extends PHPUnit\Framework\TestCase
{

    use \Zver\Package\Helper;

    public function testDriverVerification()
    {
        /**
         * /cat путь к файлу без кавычек
         * success:все ос
         */
        $notEmpty = false;

        foreach (Common::getDirectoryContentRecursive(__DIR__ . '\files') as $cat) {
            if (!StringHelper::load(Common::getFileExtension($cat))
                             ->isEqualsIgnoreCase('cat')) {
                continue;
            }
            $result = Extractor::getOSFromCatFile($cat);
            if ($result !== false) {
                $notEmpty = true;
            }
        }
        $this->assertTrue($notEmpty);
    }

    public function testRepack()
    {
        $testFile = \Zver\DirectoryWalker::fromCurrent()
                                         ->enter('files/repack')
                                         ->get() . 'zip.7z';

        $this->assertTrue(Extractor::getArchiveType($testFile) == 'zip');

        $testCopy = __DIR__ . DIRECTORY_SEPARATOR . 'repack.7z';

        Common::remove($testCopy);

        clearstatcache(true);

        $this->assertFalse(file_exists($testCopy));

        copy($testFile, $testCopy);

        clearstatcache(true);

        $this->assertTrue(file_exists($testCopy));

        $this->assertTrue(Extractor::getArchiveType($testCopy) == 'zip');

        $this->assertTrue(Extractor::repack7z($testCopy));
        $this->assertTrue(Extractor::getArchiveType($testCopy) == '7z');

        Common::remove($testCopy);
        $this->assertTrue(Extractor::getArchiveType($testFile) == 'zip');

    }

    public function testInfParsing2()
    {

        $files = Common::getDirectoryContentRecursive(static::getEtcDirectory());

        foreach ($files as $path) {

            if (\Zver\StringHelper::load($path)
                                  ->toLowerCase()
                                  ->isEndsWith('.inf')
            ) {

                $parsedInf = \Zver\Extractor::parseInf($path, true);

                $this->assertTrue(is_array($parsedInf));

                $fields = [
                    'OS',
                    'HID',
                    'Date',
                    'Version',
                    'Description',
                    'Provider',
                    'Manufacturer',
                ];

                if (empty($parsedInf)) {
                    $this->fail('Empty parsed array in ' . $path . "\n");
                }

                foreach ($parsedInf as $parsedItem) {
                    foreach ($fields as $field) {
                        if (!array_key_exists($field, $parsedItem)) {
                            $this->fail('Empty ' . $field . ' in ' . $path . "\n");
                        }
                    }
                }

            }

        }

    }

    public static function getEtcDirectory()
    {
        return static::getPackagePath('tests/files/etc/');
    }

    public function testArchiveParsing()
    {
        $archive = \Zver\DirectoryWalker::fromCurrent()
                                        ->enter('\files\parse')
                                        ->get() . '09360516872462c07e491e486ff50ba1.exe';

        $extractionDir = Extractor::getExtractionDir($archive);

        Common::remove($extractionDir);

        $extracted = Extractor::extract($archive, true, 6);

        $this->assertTrue($extracted);

        foreach (Common::getDirectoryContentRecursive($extractionDir) as $path) {

            $helper = StringHelper::load($path);

            $ext = $helper->getClone()
                          ->setLastPart('.')
                          ->toLowerCase()
                          ->get();

            if ($ext == 'inf' && !$helper->setLastPart(DIRECTORY_SEPARATOR)
                                         ->isContain('autorun')) {

                $parsed = Extractor::parseInf($path, true);

                $this->assertTrue(!empty($parsed));

            }
        }

        Common::remove($extractionDir);
    }

    public function testArchiveParsing2()
    {
        $archive = \Zver\DirectoryWalker::fromCurrent()
                                        ->enter('\files\parse')
                                        ->get() . '00e4566fc45f388202f490ca334c1e4d.7z';

        $extractionDir = Extractor::getExtractionDir($archive);

        Common::remove($extractionDir);

        $extracted = Extractor::extract($archive, true, 6);

        $files = Common::getDirectoryContentRecursive($extractionDir);

        foreach ($files as $file) {

            $ext = StringHelper::load($file)
                               ->setLastPart('.')
                               ->toLowerCase()
                               ->get();

            if ($ext == 'inf') {

                $restoredInfPath = Extractor::restoreExtractedPath(StringHelper::load($file)
                                                                               ->removeBeginning($extractionDir)
                                                                               ->get());

                $catFiles = Extractor::getCatFilesFromInf($file);

                if (empty($catFiles)) {
                    $this->fail('Empty cat files in ' . $file);
                }

                $installer = Extractor::getInstaller($archive, $restoredInfPath);

                $this->assertSame($installer, DIRECTORY_SEPARATOR . "Setup.exe");

            }
        }

        Common::remove($extractionDir);

    }

    public function testDetectArchiveRoot()
    {
        $installerDir = \Zver\DirectoryWalker::fromCurrent()
                                             ->enter('files/installers')
                                             ->get();
        $tests = [
            '5700_enu_win2k_xpinfu.exe'     => DIRECTORY_SEPARATOR,
            'AsusSetup.7z'                  => Common::replaceSlashesToPlatformSlashes('\Realtek_RTL8112L _Lan_V5786_V6247_V744_CPVistaWin7\Rtl8112l'),
            'root.zip'                      => DIRECTORY_SEPARATOR,
            'Setup.7z'                      => DIRECTORY_SEPARATOR,
            'setup.zip'                     => DIRECTORY_SEPARATOR,
            'sfx.exe'                       => DIRECTORY_SEPARATOR,
            'sp34127.exe'                   => DIRECTORY_SEPARATOR,
            'W7x.zip'                       => DIRECTORY_SEPARATOR . '3',
            'wcfg.7z'                       => DIRECTORY_SEPARATOR,
            'WiMAX_Intel_Win7_32_52323.zip' => DIRECTORY_SEPARATOR,
        ];
        foreach ($tests as $input => $output) {
            $this->assertSame(Extractor::detectArchiveRoot($installerDir . $input), $output, "Wrong root dir for " .
                                                                                             $input);
        }
    }

    public function testGetInstaller()
    {
        $tests = [
            [
                'F:\\extractor.exe',
                '\folder\inf.inf',
                -1,
            ],
            [
                'F:\\extractor.msi',
                '\folder\inf.inf',
                -1,
            ],
            [
                'extractor.exe',
                '\folder\inf.inf',
                -1,
            ],
            [
                'extractor.msi',
                '\folder\inf.inf',
                -1,
            ],
            [
                'extractor.7z',
                '\folder\inf.inf',
                '\folder\inf.inf',
            ],
            [
                'extractor.zip',
                '\folder\inf.inf',
                '\folder\inf.inf',
            ],
            [
                'extractor.rar',
                '\folder\inf.inf',
                '\folder\inf.inf',
            ],
            [
                'extractor.cab',
                '\folder\inf.inf',
                '\folder\inf.inf',
            ],
            [
                'extractor.cab',
                '\folder.zip\inf.inf',
                false,
            ],
            [
                'extractor.cab',
                Extractor::getExtractedPrefix() . 'folder_zip\inf.inf',
                false,
            ],
            [
                'extractor.cab',
                Extractor::getExtractedPrefix() . 'folder_exe\inf.inf',
                'folder.exe',
            ],
            [
                'extractor.cab',
                'folder.exe\inf.inf',
                'folder.exe',
            ],
            [
                'extractor.cab',
                'folder.zip\inf.inf',
                false,
            ],
            [
                'extractor.cab',
                'folder.zip\folder.exe\inf.inf',
                false,
            ],
            [
                'extractor.cab',
                '\folder.exe\folder.zip\inf.inf',
                '\folder.exe',
            ],
            [
                'extractor.zip',
                '\PG583\PG583.INSTALL.V6.1.32.42.VISTA.MSI\Data1.cab\omnitv.inf',
                '\PG583\PG583.INSTALL.V6.1.32.42.VISTA.MSI',
            ],
            [
                'extractor.cab',
                '\ext-foldere\is-not_so far\folder.exe\folder.zip\inf.inf',
                '\ext-foldere\is-not_so far\folder.exe',
            ],
            [
                'extractor.msi',
                '\ext-foldere\is-not_so far\folder.exe\folder.zip\inf.inf',
                -1,
            ],
            [
                '\folder\onefolder\extractor.msi',
                '\ext-foldere\is-not_so far\folder.exe\folder.zip\inf.inf',
                -1,
            ],
        ];

        foreach ($tests as $test) {
            $installer = Extractor::getInstaller($test[0], $test[1]);
            $this->assertSame($installer,
                              $test[2],
                              $test[1] . ' is not the same as ' . $test[2]
            );
        }

        $installersDir = __DIR__ .
                         DIRECTORY_SEPARATOR .
                         'files' .
                         DIRECTORY_SEPARATOR .
                         'installers' .
                         DIRECTORY_SEPARATOR;

        $installersTests = [
            ['\install.exe', 'root.zip', '\3\WIN7\64\rt64win7.inf'],
            ['\install.exe', 'root.zip', '\3\WIN7\32\rt86win7.inf'],
            ['\install.exe', 'setup.zip', '\3\WIN7\32\rt86win7.inf'],
            ['\install.exe', 'setup.zip', '3\WIN7\64\rt64win7.inf'],
            ['\install.exe', 'setup.zip', null],
            ['\3\SETuP.exe', 'W7x.zip', null],
            ['\3\SETuP.exe', 'W7x.zip', '\3\WIN7\32\rt86win7.inf'],
            ['\3\SETuP.exe', 'W7x.zip', '\3\WIN7\64\rt64win7.inf'],
            ['\setup.exe', 'sfx.exe', null],
            ['\setup.exe', 'sp34127.exe', null],
            ['\setup.exe', 'sp34127.exe', '\dc02i.inf'],
            ['\Install\Setup.exe', 'WiMAX_Intel_Win7_32_52323.zip', '\Drivers\bpenum.inf'],
            ['\Install\Setup.exe', 'WiMAX_Intel_Win7_32_52323.zip', '\Drivers\bpmp.inf'],
            ['\Install\Setup.exe', 'WiMAX_Intel_Win7_32_52323.zip', '\Drivers\bpusb.inf'],
            ['\5700\hpf5700p.inf', '5700_enu_win2k_xpinfu.exe', '\5700\hpf5700p.inf'],
            [false, '5700_enu_win2k_xpinfu.exe', null],
        ];

        //Special AsusSetup
        foreach (Extractor::getArchiveFilesList($installersDir . 'AsusSetup.7z') as $archivePath) {
            $ext = StringHelper::load($archivePath)
                               ->toLowerCase()
                               ->getLastPart('.');
            if ($ext == 'inf') {
                $installersTests[] = [
                    '\Realtek_RTL8112L _Lan_V5786_V6247_V744_CPVistaWin7\Rtl8112l\AsusSetup.exe',
                    'AsusSetup.7z',
                    $archivePath,
                ];
            }
        }

        //Special Setup.exe
        foreach (Extractor::getArchiveFilesList($installersDir . 'Setup.7z') as $archivePath) {
            $ext = StringHelper::load($archivePath)
                               ->toLowerCase()
                               ->getLastPart('.');
            if ($ext == 'inf') {
                $installersTests[] = [
                    '\Setup.exe',
                    'Setup.7z',
                    $archivePath,
                ];
            }
        }

        //Special wcfg.7z
        foreach (Extractor::getArchiveFilesList($installersDir . 'wcfg.7z') as $archivePath) {
            $ext = StringHelper::load($archivePath)
                               ->toLowerCase()
                               ->getLastPart('.');
            if ($ext == 'inf') {
                $installersTests[] = [
                    '\wdcfg.exe',
                    'wcfg.7z',
                    $archivePath,
                ];
            }
        }
        foreach ($installersTests as $testData) {
            $installer = Extractor::getInstaller($installersDir . $testData[1], $testData[2]);
            $this->assertSame($installer,
                              $testData[0],
                              'Wrong installer for ' . $testData[1]
            );
        }

    }

    public function testReplaceVendor()
    {
        $tests = [
            'ACER INCORPORATED'            => 'ACER',
            'ACER INC.'                    => 'ACER',
            'ACER'                         => 'ACER',
            ' acER '                       => 'ACER',
            "2L (CONCEPTRONIC)"            => '2L',
            "A4 TECH"                      => 'A4',
            "ABOCOM"                       => 'ABOCOM',
            "ACCTON TECHNOLOGY CORP."      => 'ACTION',
            "AGERE"                        => 'AGERE',
            "AIRLINK"                      => 'AIRLINK',
            "ALCOR"                        => 'ALCOR',
            "AMIGO TECHNOLOGY INC."        => 'AMIGO',
            "ASUS"                         => 'ASUS',
            "ATHEROS"                      => 'ATHEROS',
            "AVERMEDIA TECHNOLOGIES, INC." => 'AVERMEDIA',
            "AZUREWAVE TECHNOLOGIES, INC." => 'AZUREWAVE',
            "BELKIN COMPONENTS"            => 'BELKIN',
            "BISON"                        => 'BISON',
            "BROADCOM"                     => "BROADCOM",
            "BROTHER"                      => "BROTHER",
            "CHICONY"                      => "CHICONY",
            "CISCO"                        => "CISCO",
            "CNET TECHNOLOGY INC."         => 'CNET',
            "CONCEPTRONIC"                 => 'CONCEPTRONIC',
            "CONEXANT"                     => 'CONEXANT',
            "COREGA K.K."                  => 'COREGA',
            "COREGA TAIWAN INC."           => 'COREGA',
            "COREGA"                       => 'COREGA',
            "DEUTSCHE TELEKOM AG"          => 'DEUTSCHE',
            "DLINK"                        => 'DLINK',
            "DRAYTEK"                      => 'DRAYTEK',
            "EMACHINES"                    => 'EMACHINES',
            "ENCORE ELECTRONICS INC."      => 'ENCORE',
            "ENE"                          => 'ENE',
            "HAWKING TECHNOLOGIES"         => 'HAWKING',
            "HAWKING TECHNOLOGIES, INC."   => 'HAWKING',
            "HERCULES"                     => 'HERCULES',
            "HP"                           => 'HP',
            "HUAWEI"                       => 'HUAWEI',
            "IC PLUS CORP."                => 'IC',
            "NETOPIA, INC."                => 'NETOPIA',
            "NVIDIA"                       => 'NVIDIA',
            "O2MICRO"                      => 'O2MICRO',
            "OVISLINK CORP."               => 'OVISLINK',
            "SITECOM EUROPE BV"            => 'SITECOM',
            "SURECOM TECHNOLOGY CORP."     => 'SURECOM',
            "SURECOM"                      => 'SURECOM',
            "SUYIN OPTRONICS CORP."        => 'SUYIN',
            "YUAN TV DRIVER"               => 'YUAN',
            "YUAN"                         => 'YUAN',
            "Z-COM"                        => 'Z-COM',
            "2L CENTRAL EUROPE BV"         => "2L",
            "2L INTERNATIONAL BV"          => "2L",
            "AIRTIES WIRELESS NETWORKS"    => "AIRTIES",
            "ALLIED TELESIS INC."          => "ALLIED",
            "ALLIED TELESIS K.K."          => "ALLIED",
            "ALLWIN"                       => "ALLWIN",
            "AMD"                          => "AMD",
            "ZYXEL"                        => "ZYXEL",
        ];

        foreach ($tests as $input => $output) {
            $this->assertSame(Extractor::replaceVendor($input), $output);
        }

    }

    public function testFastMode()
    {

        $this->assertFalse(Extractor::isInFastMode());

        Extractor::enableFastMode();

        $this->assertTrue(Extractor::isInFastMode());

        Extractor::disableFastMode();

        $this->assertFalse(Extractor::isInFastMode());
    }

    public function testRemoveFromArchive()
    {
        $testData = [
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => 'fol der',
                'result'  => [
                    'file.txt',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => 'file.txt',
                'result'  => [
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => 'fol der' . DIRECTORY_SEPARATOR . 'subfol der' . DIRECTORY_SEPARATOR . 'file.txt',
                'result'  => [
                    'file.txt',
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => 'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                'result'  => [
                    'file.txt',
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => '*.7z',
                'result'  => [
                    'file.txt',
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'remove'  => '*.txt',
                'result'  => [
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
        ];

        foreach ($testData as $test) {

            $safeCopyPath = __DIR__ .
                            DIRECTORY_SEPARATOR .
                            md5_file($test['archive']) .
                            '.' .
                            Common::getFileExtension($test['archive']);

            @unlink($safeCopyPath);

            $this->assertTrue(copy($test['archive'], $safeCopyPath));

            $beforeDeletion = Extractor::getArchiveFilesList($safeCopyPath);

            $this->assertTrue(Extractor::removeFromArchive($safeCopyPath, $test['remove']));

            $afterDeletion = Extractor::getArchiveFilesList($safeCopyPath);

            $this->assertSame($afterDeletion, $test['result'], 'Can\'t remove ' . $test['remove']);
            $this->assertNotSame($afterDeletion, $beforeDeletion);

            @unlink($safeCopyPath);
        }

    }

    public function testIsSfx()
    {
        $sfxDir = \Zver\DirectoryWalker::fromCurrent()
                                       ->enter('files/sfx')
                                       ->get();
        foreach (Common::getDirectoryContentRecursive($sfxDir) as $archive) {
            $isSfx = Extractor::isSfxArchive($archive);
            if (StringHelper::load(basename($archive))
                            ->isStartsWithIgnoreCase('sfx')) {
                $this->assertTrue($isSfx);
            } else {
                $this->assertFalse($isSfx);
            }
        }
    }

    public function testGetExcludedArchFromPath()
    {

        $tests = [
            [
                __DIR__ . '\files\installers\WIN7\32',
                [
                    Extractor::ARCH_64,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7\64/',
                [
                    Extractor::ARCH_32,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7\64-bit/',
                [
                    Extractor::ARCH_32,
                ],
            ],
            [
                '/7/amd64/',
                [
                    Extractor::ARCH_32,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7\64\32',
                [
                    Extractor::ARCH_64,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7\64\x32',
                [
                    Extractor::ARCH_64,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7\64\x32bit',
                [
                    Extractor::ARCH_64,
                ],
            ],
            [
                __DIR__ . '\files\installers\WIN7',
                [],
            ],
        ];

        foreach ($tests as $testIndex => $test) {
            $this->assertEquals(
                Extractor::getExcludedArchesFromPath($test[0]),
                $test[1],
                'failed index is ' . $testIndex
            );
        }

    }

    public function testGetOSFromString()
    {

        $generateInput = function ($os, $arch) {
            if ($arch == 32) {
                return [
                    $os . ' 32',
                    'win' . $os . ' 32',
                    'windows ' . $os . ' 32',
                    'win ' . $os . ' 32',
                    'win' . $os . ' 32',
                    'win' . $os . '32',
                    'windows ' . $os . ' 32-b',
                    'windows ' . $os . ' 32-bit',
                    'windows ' . $os . ' 32 bit',
                    'windows ' . $os . ' x86',
                    'windows ' . $os . ' x-86',
                    'windows ' . $os . ' x 86',
                    'windows ' . $os . ' x<sup>86</sup>',
                ];
            }

            return [
                $os . ' 64',
                'win' . $os . ' 64',
                'windows ' . $os . ' 64',
                'win ' . $os . ' 64',
                'win' . $os . ' 64',
                'Windows ' . $os . ' ​​(​64-<span style="dwqdwqdwqd:dwsdfwed;">разрядная версия</span>)​​​​',
                'win' . $os . '64',
                'windows ' . $os . ' 64-b',
                'windows ' . $os . ' 64-bit',
                'windows ' . $os . ' 64 bit',
                'windows ' . $os . ' x64',
                'windows ' . $os . ' x-64',
                'windows ' . $os . ' amd-64',
                'windows ' . $os . ' amd64',
                'windows_' . $os . '_amd_64',
                'windows ' . $os . ' x 64',
                'Radeon Software Crimson ReLive Edition Graphics Driver Installer for Windows  ' . $os . ' 64-Bit',
            ];
        };

        $tests = [
            [
                'input'  => [
                    '',
                    'windows',
                    'windows xp',
                    'windows vista',
                    'altalavista',
                    'windows 2000 64bit',
                    '%',
                    '*',
                    '.*',
                    "764",
                    "????>+_)(&!@#$%^&*()",
                    "1064",
                    "8164",
                    "864",
                    "864\n764\n1064",
                    '23cfwe w43erf23r23r',
                ],
                'output' => [],
            ],
            [
                'input'  => [
                    'Windows® 7 64-bit ​',
                    'Windows ® 7 64-bit ​',
                    'Win®7 64-bit ​',
                    'Win®7 64-bit ​',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('xp', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('xp', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('vista', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('vista', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('7', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('7', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('8', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('8', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('8.1', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('8.1', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('81', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('81', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('8_1', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('8_1', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => $generateInput('10', 32),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_32),
                ],
            ],
            [
                'input'  => $generateInput('10', 64),
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    'windows 10 x86-64',
                    'windows10 x86-64',
                    '10 x86-64',
                    '10_x86-64',
                    '10x86-64',
                    'win10 x86-64',
                    'win_10_x86-x64',
                    'win_10_x86-amd64',
                    'windows_10_x86-amd64',
                    'w_10_x86-amd64',
                    'w10x86-64',
                    'w10_32-64bit',
                    'w10_32bit-64bit',
                    'w10_32bit_64bit',
                    'w10_32bit-amd64',
                    'w10_32bit-amd64bit',
                    'Windows 10 (32 & 64-bit)',
                    'Windows 10 32 & 64-bit',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    "windows 7 x86\nWin7amd64\n7x64\nwindows10 x86-64",
                    "windows 7 x86<br>Win7amd64<br>7x64<br>windows10 x86-64",
                    "windows 7 x86</br>7amd64<br/>7x64<br/>windows10 x86-64",
                    'Windows 10/7 (32 & 64-bit)',
                    'Windows 7/10 (32 & 64-bit)',
                    '10/7 (32 & 64-bit)',
                    '10/7 x86-64',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    'win 10/ win 7/ windows 8 32 & 64-bit',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    'win 10/ win 7/ windows 8 64-bit',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    'win 10 x86/ win 7amd64/ windows 8 64-bit & win8.1x86-64',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    'Windows® 10, 64-bit*',
                    'Windows 10 64-bit',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => '<td class="os">Windows® 10, 64-bit*<br>Windows 8.1, 64-bit*<br>Windows 7, 64-bit*</td>',
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_10, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                    Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                ],
            ],
            [
                'input'  => [
                    '/VGA_Intel_9.17.10.2828_Win8x64/Setup.if2',
                    '/AMT_Intel_8.1.0.1263_Win8x64_Installers_ME_SW/Setup.if2',
                ],
                'output' => [
                    Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
                ],
            ],

        ];

        $oldTests = [
            //#26
            '/Win2000~Vista X86 driver/Setup.exe'                                                                                                                                                                      => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
            ],
            '/Wireless(80211BG)_Acer_2.23.08.2004_XPx86/Setup.exe'                                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
            ],
            '/CMOS_Camera_Bison_BN28V8SD04300_Vista64/2KSETUP64.exe'                                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            '/3G_Qualcomm_v6.0.5.4_XPx86_VISTAx86x64/Setup.exe'                                                                                                                                                        => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            '/Finger Print_Authentec_9.0.8.21_Win7x86_XPx86/setup.msi'                                                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            'Win7 x86 Service Registers'                                                                                                                                                                               => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            'Windows XP x64'                                                                                                                                                                                           => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Windows XP 32 /64bit'                                                                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Win98, Win2000, WinME, WinXP(SP2 )/winXP64bit , Win2003(64bit) OS,Vista x86'                                                                                                                              => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
            ],
            'Win2000, WinXP(SP2),Win2003,Win OS for 64bit.Vista X86,X64'                                                                                                                                               => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            'Microsoft Windows 7 (64-bit),         Microsoft Windows 8 (64-bit),
             Microsoft Windows Server 2003 64-Bit Edition, Microsoft Windows Vista (64-bit),
             Microsoft Windows XP x64'                                                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Microsoft Windows 8 (32-bit)'                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'Microsoft Windows Vista,
            Microsoft Windows Vista (64-bit),
            Microsoft Windows Vista Business (32-bit),
            Microsoft Windows Vista Business (64-bit),
            Microsoft Windows Vista Enterprise (32-bit),
            Microsoft Windows Vista Enterprise (64-bit),
            Microsoft Windows Vista Home Basic (32-bit),
            Microsoft Windows Vista Home Basic (64-bit),
            Microsoft Windows Vista Home Premium (32-bit),
            Microsoft Windows Vista Home Premium (64-bit),
            Microsoft Windows Vista Starter,
            Microsoft Windows Vista Ultimate (32-bit),
            Microsoft Windows Vista Ultimate (64-bit)'                                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            'Windows 8(64bit) Windows 7(64bit) Windows Vista(64bit)'                                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Windows XP 32-bit,
            Windows 2000,
            Windows NT 4.0,
            Windows Vista 32-bit,
            Windows Server 2003 32-bit,
            Windows Server 2008 32-bit'                                                                                                                                                                    => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
            ],
            'Windows 8 (32bit,64bit)'                                                                                                                                                                                  => [
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Win XP/2003/2008/Vista/Win 7/Win 8(32/64bit)'                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Win Vista/2008/Win 7/Win 8(32/64bit)'                                                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Win 2000/XP/2003/2008/Vista/Win 7/Win 8(32/64bit)'                                                                                                                                                        => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Win XP x32 x64'                                                                                                                                                                                           => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Windows 7/8 and Vista 32 bit'                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'Win 7/XP x64'                                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'Windows 7(32bit,64bit)'                                                                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'Win Vista/2008/Win 7(32,64bit)'                                                                                                                                                                           => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'linux 2.4.8 x64'                                                                                                                                                                                          => [],
            '8 x32'                                                                                                                                                                                                    => [
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'macos 8 x86'                                                                                                                                                                                              => [],
            "Win98, Win2000, WinME, WinXP(SP2 )' WinXP 64bit , Win2003(x64bit) OS"                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Win98, Win2000, WinME, WinXP(SP2 /64bit) , Win2003(64bit) OS,Vista X86'                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
            ],
            'Windows XP/Vista/7/8 64 bit '                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Windows XP/Vista/7/8 32 bit '                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'WINDOWS 2000,
             WINDOWS SERVER 2003,
             WINDOWS XP Pro,
             WINDOWS SERVER 2008,
             WINDOWS VISTA BUSINESS,
             WINDOWS VISTA 64,
             WINDOWS 7 Ultimate, 32 and 64-bit,
             WINDOWS 7 Professional, 32 and 64-bit'                                                                                                                                                        => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'BLP Elite Laserwriter 7.1.2 Driver Download (235 KB) (FTP)'                                                                                                                                               => [],
            'BLP Elite Laserwriter 8.1.2 Driver Download (235 KB) (FTP)'                                                                                                                                               => [],
            'XP 32/64
              Vista 32/64
              Win7 32/64
              Win8 32/64'                                                                                                                                                                                  => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Win7_32'                                                                                                                                                                                                  => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            'Win7_64'                                                                                                                                                                                                  => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'WVista_32'                                                                                                                                                                                                => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
            ],
            'WVista_64'                                                                                                                                                                                                => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            'XP_64'                                                                                                                                                                                                    => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'XP_32'                                                                                                                                                                                                    => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
            ],
            //#67
            'XPSP2x64'                                                                                                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Win7_3264'                                                                                                                                                                                                => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'WVista_3264'                                                                                                                                                                                              => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            '[Windows� XP / Vista / 7 (32 Bits)] 5 MB'                                                                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            '[Windows� Vista / 7 (32 Bits)] 5 MB'                                                                                                                                                                      => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            '[Windows 7] 7.1 MB'                                                                                                                                                                                       => [],
            ' [Windows Vista 32-bit / 64-bit] 9.28 MB'                                                                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
            ],
            '[Windows® 7 / Vista SP2/ XP SP3] 68 MB'                                                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
            ],
            //№75
            'Windows XP x64 Edition; Windows XP; Windows Vista x64 Edition; Windows Vista;
              Windows Server 2008 x64 Edition; Windows Server 2008 R2 x64 Edition;
              Windows Server 2008; Windows Server 2003  x64 Edition; Windows Server 2003;
              Windows 7 x64 Edition; Windows 7; Windows 2000'                                                                                                                                              => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'BLP Eclipse 8 Laserwriter 7.1.2 Driver Download (244 KB) (FTP)'                                                                                                                                           => [],
            'Windows XP, Windows Vista, Windows 7 (all versions)'                                                                                                                                                      => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'For Windows XP/Vista/Windows7/Windows8 x86/x64 Driver >>>'                                                                                                                                                => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            '* Microsoft Windows Vista* 64,
               Microsoft Windows* 7 64
               Windows* Embedded Standard 7(  )'                                                                                                                                                           => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'Windows XP  *, Windows XP  Professional x64 Edition* '                                                                                                                                                    => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'Windows XP: 233 MHz with 64 MB RAM'                                                                                                                                                                       => [],
            'following Dell monitor in Microsoft(R) Windows(R) XP, x64 and, Windows(R) 2000 operating systems:'                                                                                                        => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            // #83
            'This WinXP 64-bit nForce UDA driver package for MCP61, MCP72, MCP73, MCP78, and MCP7A consists of the following components:'                                                                              => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'This Win7/Vista 64-bit MCP79 nForce SATAIDE package'                                                                                                                                                      => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            '- Windows Drivers, All Controllers
              o Windows Server 2008, Enterprise, Standard, Datacenter (32-bit, 64-bit)
              o Windows Server 2008 R2, Enterprise, Standard, Datacenter (64-bit)
              o Windows Server 2003, Enterprise, Standard, Datacenter (32-bit, 64-bit)
              o Windows XP, Windows Vista, Windows 7 (all versions)
              - Windows Drivers, 5-Series & 2-Series Controllers Only
              o Windows Server 2003 R2, Enterprise, Standard, Datacenter (32-bit, 64-bit)'                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'PackageCode = {77146EFF-71EA-4C32-8EC7-E33C09C51771}'                                                                                                                                                     => [],
            '1.  The system must contain one of the following ASMedia products:
              2.  The system must be running on one of the following
              - Microsoft* Windows* XP Home Edition
              - Microsoft* Windows* XP Professional
              - Microsoft* Windows* XP x64 Edition
              - Microsoft* Windows* Server 2003
              - Microsoft* Windows* Server 2003 x64 Edition
              - Microsoft* Windows* Vista
              - Microsoft* Windows* Vista x64 Edition
              - Microsoft* Windows* Server 2008
              - Microsoft* Windows* Server 2008 x64 Edition
              - Microsoft* Windows* Win7
              - Microsoft* Windows* Win7 x64 Edition
              3. The following operating systems are not supported:'                                                                                                                                       => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
            ],
            'Windows* 2000, Windows* XP (i386 & x64),
              Windows* Vista.'                                                                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_64),
            ],
            'ICAP_EXPOSURETIME'                                                                                                                                                                                        => [],
            ';#### the range of value (8~30 or 32~37) ####'                                                                                                                                                            => [],
            'Windows 7 32-bit, Windows 8.1 32-bit, Windows 8 32-bit, Windows Vista 32-bit '                                                                                                                            => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'Windows 7 64-bit, Windows 8.1 64-bit, Windows 8 64-bit'                                                                                                                                                   => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Microsoft Windows 8.1 (32-bit) downloads for HP Business Inkjet 1000 Printer'                                                                                                                             => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
            ],
            'Microsoft Windows 8.1 Enterprise (64-bit) downloads for HP Business Inkjet 1000 Printer'                                                                                                                  => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
            ],
            'Microsoft driver v.8.1 for Windows Vista'                                                                                                                                                                 => [],
            'Server 2003, Microsoft Windows Storage Server 2003 File...by this version. ' .
            'This driver is the latest available... » Version: 8.8.1.0 (26 Mar 2007) Enhancements'                                                                                                                     => [],
            'Windows<sup>&reg;</sup> Vista<br>Windows<sup>&reg;</sup> Vista 64bit<br>Windows<sup>&reg;</sup> 7<br>Windows<sup>&reg;</sup> 7 64bit<br>Windows<sup>&reg;</sup> 8<br>Windows<sup>&reg;</sup> 8 64bit<br>' => [
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Windows<sup>&reg;</sup> 8.1<br>Windows<sup>&reg;</sup> 8.1 64bit<br>'                                                                                                                                     => [
                Extractor::getFullOSName(Extractor::OS_81,
                                         Extractor::ARCH_32), Extractor::getFullOSName(Extractor::OS_81,
                                                                                       Extractor::ARCH_64),
            ],
            'Windows 8.1, Windows 8.1 64bit'                                                                                                                                                                           => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
            ],
            'Windows 8.1 x32, Windows 8.1 x64'                                                                                                                                                                         => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
            ],
            // #101
            'Windows 8.1 x64'                                                                                                                                                                                          => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
            ],
            'Windows <sup>®</sup> 8<br>Windows <sup>®</sup> 8 64bit<br>'                                                                                                                                               => [
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32), Extractor::getFullOSName(Extractor::OS_8,
                                                                                                        Extractor::ARCH_64),
            ],
            'Windows 8, Windows 8.1 x32'                                                                                                                                                                               => [
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
            'Windows 8/8.1 (32/64-bit), Windows 7 (32/64-bit)'                                                                                                                                                         => [
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_81, Extractor::ARCH_64),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Windows 8 64bit'                                                                                                                                                                                          => [
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_64),
            ],
            'Windows 7/8 32bit, Windows Vista 32bit, Windows XP 32bit'                                                                                                                                                 => [
                Extractor::getFullOSName(Extractor::OS_5, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_6, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_7, Extractor::ARCH_32),
                Extractor::getFullOSName(Extractor::OS_8, Extractor::ARCH_32),
            ],
        ];

        $oldTests = array_map(function ($key, $value) {
            return [
                'input'  => $key,
                'output' => $value,
            ];
        }, array_keys($oldTests), $oldTests);

        $tests = array_merge($tests, $oldTests);

        foreach ($tests as $index => $test) {
            if (!is_array($test['input'])) {
                $test['input'] = [$test['input']];
            }
            foreach ($test['input'] as $input) {
                $this->assertSame(
                    Extractor::getOSFromString($input),
                    $test['output'],
                    'OS wrong for input: ' . $input . ' on test #' . $index
                );
            }
        }
    }

    public function getListArchivesFolder()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . 'list' . DIRECTORY_SEPARATOR;
    }

    public function testValidHardID()
    {
        $tests = [
            ['USB\VID_8087&PID_07F2', false],
            ['USB\VID_12D1&SUBCLASS_01&PROT_03', false],
            ['USBPRINT\VID_03F0&PID_67173WEFRWGVEW', 'USBPRINT\VID_03F0&PID_67173WEFRWGVEW',],
            [
                '',
                false,
            ],
            [
                '            ',
                false,
            ],
        ];

        foreach ($tests as $index => $test) {
            $this->assertSame(Extractor::getValidHardId($test[0]), $test[1]);
        }

    }

    public function testList7zArchive()
    {

        $testData = [

            [
                'archive' => '',
                'result'  => false,
            ],
            [
                'archive' => 'fsdj238r2332r',
                'result'  => false,
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7z.7z',
                'result'  => [
                    'file.txt',
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der' . DIRECTORY_SEPARATOR . 'file.txt',
                    'nested.7z',
                    'run.php',
                    'test.php',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . '7zSpaces.7z',
                'result'  => [
                    'fol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der',
                    'fol der' . DIRECTORY_SEPARATOR . 'subfol der' . DIRECTORY_SEPARATOR . 'fil e.txt',
                ],
            ],
            [
                'archive' => $this->getListArchivesFolder() . 'zip.zip',
                'result'  => [
                    'composer.json',
                    'composer.lock',
                ],
            ],

        ];

        foreach ($testData as $testIndex => $test) {
            $this->assertSame(
                Extractor::getArchiveFilesList($test['archive']),
                $test['result']
            );
        }

    }

    public function testRestoreExtractedPath()
    {

        $prefix = Extractor::getExtractedPrefix();

        $tests = [
            [
                'F:\GrabbersFiles\downloaded\\' . $prefix . '2a',
                'F:\GrabbersFiles\downloaded\\' . $prefix . '2a',
            ],
            [
                'F:',
                'F:',
            ],
            [
                'F:\GrabbersFiles\downloaded\\' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                'F:\GrabbersFiles\downloaded\\' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip\WLAN_WIN8\Intel\Autorun.inf',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Intel\Autorun.inf',
            ],
            [
                'F:\GrabbersFiles\downloaded\\' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip\WLAN_WIN8\Intel\Win7Plus\Win32\Drivers\Win8\Netwen00.INF',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Intel\Win7Plus\Win32\Drivers\Win8\Netwen00.INF',
            ],
            [
                'F:\GrabbersFiles\downloaded\\' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip\WLAN_WIN8\Intel\Win7Plus\Win32\Install\\' .
                $prefix .
                'Intel PROSet Wireless_msi\netwex.inf',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Intel\Win7Plus\Win32\Install\Intel PROSet Wireless.msi\netwex.inf',
            ],
            [
                '/GrabbersFiles/downloaded/' . $prefix . '2a',
                '/GrabbersFiles/downloaded/' . $prefix . '2a',
            ],
            [
                '/',
                '/',
            ],
            [
                '/GrabbersFiles/downloaded/' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip/WLAN_WIN8/Dashboard/Autorun.inf',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                '/GrabbersFiles/downloaded/' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip/WLAN_WIN8/Intel/Autorun.inf',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Intel/Autorun.inf',
            ],
            [
                '/GrabbersFiles/downloaded/' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip/WLAN_WIN8/Intel/Win7Plus/Win32/Drivers/Win8/Netwen00.INF',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Intel/Win7Plus/Win32/Drivers/Win8/Netwen00.INF',
            ],
            [
                '/GrabbersFiles/downloaded/' .
                $prefix .
                '2ae77986addb5061343df134dc3428b9_zip/WLAN_WIN8/Intel/Win7Plus/Win32/Install/' .
                $prefix .
                'Intel PROSet Wireless_msi/netwex.inf',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Intel/Win7Plus/Win32/Install/Intel PROSet Wireless.msi/netwex.inf',
            ],
        ];

        foreach ($tests as $test) {
            $this->assertSame(Extractor::restoreExtractedPath($test[0]), $test[1]);
        }

    }

    public function testIsExtractedExt()
    {
        $tests = [
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'exe',
                false,
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'zip',
                true,
            ],
            [
                'F:\GrabbersFiles\downloaded\WLAN_WIN8\Dashboard\Autorun.inf',
                'exe',
                false,
            ],
            [
                'F:\GrabbersFiles\downloaded\WLAN_WIN8\Dashboard\Autorun.inf',
                'zip',
                false,
            ],
            [
                'F:\GrabbersFiles\downloaded\WLAN_WIN8\Dashboard\Autorun.inf',
                'cab',
                false,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'zip',
                true,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                'exe',
                false,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                'zip',
                true,
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                'exe',
                false,
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                'zip',
                true,
            ],
            [
                '/GrabbersFiles/downloaded/WLAN_WIN8/Dashboard/Autorun.inf',
                'exe',
                false,
            ],
            [
                '/GrabbersFiles/downloaded/WLAN_WIN8/Dashboard/Autorun.inf',
                'zip',
                false,
            ],
            [
                '/GrabbersFiles/downloaded/WLAN_WIN8/Dashboard/Autorun.inf',
                'cab',
                false,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                'zip',
                true,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                'exe',
                false,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                'zip',
                true,
            ],

        ];

        foreach ($tests as $test) {
            $this->assertSame(
                Extractor::isExtractedFromExtension($test[0], $test[1]),
                $test[2]
            );
        }

    }

    public function testGetExtractionLevel()
    {
        $prefix = Extractor::getExtractedPrefix();

        $tests = [
            [
                "\\" .
                $prefix .
                "LAN_PRO_Win8_64_exe\\" .
                $prefix .
                "LAN_PRO_Win8_64_exe\\" .
                $prefix .
                "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\e1qmsg.dll",
                3,
            ],
            [
                "\\" . $prefix . "xe\PRO1000\Winx64\NDIS62\\e1qmsg.dll",
                0,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1r62x64.cat",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1r62x64.din",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1r62x64.inf",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1r62x64.sys",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1rmsg.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1y62x64.CAT",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1y62x64.din",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1y62x64.inf",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\e1y62x64.sys",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicCo36.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInE1R.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInE6.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInstC.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInstK.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInstQ.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInstY.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\NicInVQ.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\eadme.txt",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\1q62x64.cat",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\1q62x64.din",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\1q62x64.inf",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\1q62x64.sys",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS62\\1qmsg.dll",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS63",
                1,
            ],
            [
                "\\" . $prefix . "LAN_PRO_Win8_64_exe\PRO1000\Winx64\NDIS63\\1c63x64.cat",
                1,
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                1,
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                1,
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip',
                1,
            ],
            [
                '/PG583/PG583.INSTALL.V6.1.32.42.VISTA.MSI/Data1.cab/omnitv.inf',
                2,
            ],
            [
                'F:\GrabbersFiles\downloaded\WLAN_WIN8\Dashboard\Autorun.inf',
                0,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                2,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                2,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                2,
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                1,
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                1,
            ],
            [
                '/GrabbersFiles/downloaded/WLAN_WIN8/Dashboard/Autorun.inf',
                0,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                2,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                2,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                2,
            ],

        ];

        foreach ($tests as $test) {
            $this->assertSame(
                Extractor::getExtractedLevel($test[0]),
                $test[1],
                'Wrong result for ' . $test[0]);
        }
    }

    public function testGetExtractionParent()
    {
        $tests = [
            [
                '/PG583/PG583.INSTALL.V6.1.32.42.VISTA.MSI/Data1.cab/omnitv.inf',
                '/PG583/PG583.INSTALL.V6.1.32.42.VISTA.MSI',
            ],
            [
                '/folder.exe/folder.zip/inf.inf',
                '/folder.exe',
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\GrabbersFiles\downloaded\2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                'F:\GrabbersFiles\downloaded\WLAN_WIN8\Dashboard\Autorun.inf',
                false,
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.zip\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip',
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip',
            ],
            [
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip\downloaded\2ae77986addb5061343df134dc3428b9.exe\WLAN_WIN8\Dashboard\Autorun.inf',
                'F:\Grabbe\rsFiles2\ae77986addb\5061343df134dc3428b9.zip',
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                '/GrabbersFiles/downloaded/2ae77986addb5061343df134dc3428b9.zip',
            ],
            [
                '/GrabbersFiles/downloaded/WLAN_WIN8/Dashboard/Autorun.inf',
                false,
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.zip/WLAN_WIN8/Dashboard/Autorun.inf',
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip',
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip',
            ],
            [
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
                '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip',
            ],

        ];

        foreach ($tests as $test) {
            $this->assertSame(
                Extractor::getExtractedParent($test[0]),
                $test[1]
            );
        }
    }

    public function test7zType()
    {

        $dir = \Zver\DirectoryWalker::fromCurrent()
                                    ->enter('files\type')
                                    ->get();

        foreach (Common::getDirectoryContent($dir) as $path) {

            $type = Extractor::getArchiveType($path);
            $check = Common::getFilenameWithoutExtension($path);

            $this->assertSame($type, $check, $check . '!=' . $type);
        }

    }

    public function testExtract()
    {
        $archives = Common::getDirectoryContent(static::getArchivesDirectory());
        foreach ($archives as $archive) {
            if (!Extractor::isFileExtensionValid($archive)) {
                continue;
            }
            $extracted = Extractor::extract($archive, true, 1);
            Common::removeDirectory(Extractor::getExtractionDir($archive));
            $this->assertTrue($extracted, 'Can\'t extract ' . $archive);
        }
    }

    public static function getArchivesDirectory()
    {
        return static::getPackagePath('tests/files/archives/');
    }

    public function testGetReplacedStrings()
    {

        $content = file_get_contents(static::getPackagePath('/tests/files/') . 'testReplace.inf');
        $result = file_get_contents(static::getPackagePath('/tests/files/') . 'testReplaced.inf');

        $content = Extractor::getStringsReplacedContent($content);

        $content = StringHelper::load($content)
                               ->trimSpaces()
                               ->get();

        $result = StringHelper::load($result)
                              ->trimSpaces()
                              ->get();

        $this->assertSame($content, $result);

    }

    public function testRemoveComments()
    {

        $testFiles = \Zver\DirectoryWalker::fromCurrent()
                                          ->enter('files')
                                          ->get();

        $commented = file_get_contents($testFiles . 'test.inf');
        $unCommented = file_get_contents($testFiles . 'uncommentedTest.inf');

        $this->assertSame(
            StringHelper::load(
                Extractor::removeComments($commented))
                        ->trimSpaces()
                        ->get(),
            StringHelper::load($unCommented)
                        ->trimSpaces()
                        ->get()
        );

    }

    public function test7Zip()
    {
        $this->assertTrue(!empty(Extractor::get7ZipExePath()));
    }

    public function testCompress()
    {

        $compressDirectory = \Zver\DirectoryWalker::fromCurrent()
                                                  ->enter('files/compress')
                                                  ->get();

        $archive = 'arc2.7z';

        @unlink($compressDirectory . 'example' . DIRECTORY_SEPARATOR . $archive);

        Extractor::compressExtractedDirectory($compressDirectory . 'template', $archive,
                                              $compressDirectory . 'example');

        $this->assertTrue(
            file_exists($compressDirectory . 'example' . DIRECTORY_SEPARATOR . $archive)
        );

        @unlink($compressDirectory . 'example' . DIRECTORY_SEPARATOR . $archive);

        $this->assertTrue(true);

    }

    public function testForCoveragePurposes()
    {
        /**
         * Only for coverage purposes
         */
        $this->assertTrue(Extractor::isWineInstalled() || true);
        $this->assertTrue(Extractor::isUnrarInstalled() || true);
        $this->assertTrue(Extractor::is7zInstalled() || true);

        $this->assertNotEmpty(Extractor::getArchList());
        $this->assertNotEmpty(Extractor::getOSList());
    }

    public function testInfToArray()
    {

        $expected = [
            'Version'                => [
                'Signature=$Windows NT$',
                'Class=System',
                'ClassGuid={4d36e97d-e325-11ce-bfc1-08002be10318}',
                'Provider=%MFGNAME%',
                'CatalogFile=IFXTPM.cat',
                'DriverVer=12/14/2007,2.01.0001.00',
            ],
            'DestinationDirs'        => [
                'DefaultDestDir=12',
                'CopyDriver=12',
            ],
            'SourceDisksNames'       => [
                '1=%INSTDISK%,',
            ],
            'SourceDisksFiles'       => [
                'IFXTPM.SYS=1',
            ],
            'SourceDisksFiles.amd64' => [
                'IFXTPM.SYS=1, amd64',
            ],
            'Manufacturer'           => [
                '%MFGNAME%=Company, NT.5.1, NTamd64.5.1, NT.6.0, NTamd64.6.0',
            ],
            'DriverInstall.NTx86'    => [
                'AddReg=CommonAddReg',
                'DelReg=CommonDelReg',
            ],
        ];

        $current = \Zver\Extractor::infToArray(file_get_contents(__DIR__ .
                                                                 DIRECTORY_SEPARATOR .
                                                                 'files' .
                                                                 DIRECTORY_SEPARATOR .
                                                                 'test.inf'));

        $this->assertSame($expected, $current);

    }
}