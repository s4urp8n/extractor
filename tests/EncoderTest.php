<?php

use Zver\Encoder;

class EncoderTest extends PHPUnit\Framework\TestCase
{

    use \Zver\Package\Helper;

    public function testEncodeDecodeString()
    {

        $tests = [
            '/Grabbe/rsFiles2/ae77986addb/5061343df134dc3428b9.zip/downloaded/2ae77986addb5061343df134dc3428b9.exe/WLAN_WIN8/Dashboard/Autorun.inf',
            'some String____',
            'Это некоторая строка с некоторым текстом, бла, бла ,бла вапзщцуапцрдлпdfgnw984hgfhedfgds',
        ];

        foreach ($tests as $test) {
            $this->assertSame(Encoder::decodeString(Encoder::encodeString($test)), $test);
            $this->assertFalse(Encoder::encodeString($test) == $test);
        }
    }

    public function testEncodeDecodeFilename()
    {

        $tests = [
            'dfdsfsdfsdf.exe',
            '190dj2398ff23f.msi',
            '190dj2398ff23f.zip',
            '190dj2398ff23f.7z',
            'acer_190dj2398ff23f.exe',
            'amd_190dj2398ff23f.exe',
        ];

        foreach ($tests as $test) {
            $this->assertSame(Encoder::decodeFilename(Encoder::encodeFilename($test)), $test);
            $this->assertFalse(Encoder::encodeFilename($test) == $test);
        }
    }

    public function testEncodeDecodeHardid()
    {

        $tests = [
            'PCI\VEN_1002&DEV_5157&SUBSYS_0F2A1002',
            'PCI\VEN_1002&DEV_5159',
            'PCI\VEN_1002',
            'PCI\VEN_1002&DEV_5159&SUBSYS_00101545',
            'PCI\VEN_1002&DEV_5347&SUBSYS_53471002',
            'PCI\VEN_1002&DEV_5B64',
            'PCI\VEN_10EC&DEV_8139&SUBSYS_813910EC&REV_10',
            'PCI\VEN_10EC&DEV_8139&SUBSYS_813910EC&REV_20',
            'PCI\VEN_10EC&DEV_8139&SUBSYS_81391436',
            'PCI\VEN_10EC&DEV_8168&SUBSYS_07521028&REV_15',
            'PCI\VEN_10EC&DEV_8168&SUBSYS_07531025&REV_0A',
            'PCI\VEN_14E4&DEV_1677&SUBSYS_3001103C&REV0',
            'PCI\VEN_14E4&DEV_1677&SUBSYS_3001103C&REV_00',
            'PCI\VEN_14E4&DEV_1677&SUBSYS_3002103C&REV0',
            'PCI\VEN_14E4&DEV_1677&SUBSYS_3002103C&REV_00',
        ];

        foreach ($tests as $test) {
            $this->assertSame(Encoder::decodeHardid(Encoder::encodeHardid($test)), $test);
        }
    }

    public function testEncodeFilename()
    {

        $tests = [
            '22f59f4a16161a0f73919eb8d0c1d770.7z' => 'ff23c2e1a8a8a17205cac4d6b79ab007.$z',
            '27dc689e643b0138e2a2935af662e2c1.7z' => 'f0b986c48e5d7a564f1fc531288f4f9a.$z',
            '24f2393e0e92b592462ea0c83a46bd00.7z' => 'fe2f5c5474cfd3cfe8f4179651e8db77.$z',
            '8f46359dc7c16bbf65819ab1472bfd02.7z' => '62e853cb909a8dd2836ac1dae0fd2b7f.$z',
            '1f6aeaed39320b7a6a650c3347b96bf1.7z' => 'a281414b5c5f7d0181837955e0dc8d2a.$z',
            'bd71ef5bdf6c3d405107e294afef3c79.7z' => 'db0a423db2895be73a704fce1242590c.$z',
            '9fb9767ea302131326e97fbcb66457bc.7z' => 'c2dc0804157fa5a5f84c02d9d88e30d9.$z',
            '44e998c07493211ea22ef041d738d472.7z' => 'ee4cc6970ec5faa41ff427eab056be0f.$z',
            'b62bf6f063a39e781f34cb0a67ba00bc.7z' => 'd8fd28278515c406a25e9d7180d177d9.$z',
            '4859bad0679feaa7fcf9a4c0fc8bce68.7z' => 'e63cd1b780c24110292c1e97296d9486.$z',
            'rmeaudio_n0d56c3383caa.zip'          => 'rmeaudio_n7b3895565911.zip',
            'gigabyte_12536579984379.exe'         => 'gigabyte_af35830cc6e50c.exe',
            'customdrivers_ndd5ec1eac81d.exe'     => 'customdrivers_nbb349a4196ab.exe',
            'acer_nda5a34e835c5.zip'              => 'acer_nb1315e465393.zip',
        ];

        foreach ($tests as $input => $output) {
            $this->assertSame(Encoder::encodeFilename($input), $output);
            $this->assertSame(Encoder::decodeFilename($output), $input);
            $this->assertSame(Encoder::decodeFilename(Encoder::encodeFilename($input)), $input);
        }
    }

}