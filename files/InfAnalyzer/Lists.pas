unit Lists;

interface

const
  VendorsString =
    'ACER' + sLineBreak +
    'ADAPTEC' + sLineBreak +
    'ASMEDIA' + sLineBreak +
    'ASIX' + sLineBreak +
    'ASUS' + sLineBreak +
    'ATHEROS' + sLineBreak +
    'AVERMEDIA' + sLineBreak +
    'BIXOLON' + sLineBreak +
    'BROADCOM' + sLineBreak +
    'BROTHER' + sLineBreak +
    'CANON' + sLineBreak +
    'COMPAQ' + sLineBreak +
    'CREATIVE' + sLineBreak +
    'DELL' + sLineBreak +
    'DIGITECH' + sLineBreak +
    'EGISTEC' + sLineBreak +
    'ELAN' + sLineBreak +
    'EPSON' + sLineBreak +
    'FOXLINK' + sLineBreak +
    'FTDI' + sLineBreak +
    'FUJITSU' + sLineBreak +
    'HUAWEI' + sLineBreak +
    'KYOCERA' + sLineBreak +
    'LEADCORE' + sLineBreak +
    'LENOVO' + sLineBreak +
    'LEXMARK' + sLineBreak +
    'LOGITECH' + sLineBreak +
    'MATROX' + sLineBreak +
    'MOTOROLA' + sLineBreak +
    'NOVATEL' + sLineBreak +
    'NVIDIA' + sLineBreak +
    'O2MICRO' + sLineBreak +
    'OKI' + sLineBreak +
    'OXFORD' + sLineBreak +
    'PANASONIC' + sLineBreak +
    'PHILIPS' + sLineBreak +
    'PROLIFIC' + sLineBreak +
    'QISDA' + sLineBreak +
    'QUALCOMM' + sLineBreak +
    'QUARTICS' + sLineBreak +
    'REALTEK' + sLineBreak +
    'RICOH' + sLineBreak +
    'SONY' + sLineBreak +
    'SAMSUNG' + sLineBreak +
    'SCM' + sLineBreak +
    'SILICON' + sLineBreak +
    'SMSC' + sLineBreak +
    'STEELSERIES' + sLineBreak +
    'TANDBERG' + sLineBreak +
    'TOSHIBA' + sLineBreak +
    'WIDCOMM' + sLineBreak +
    'WINBOND' + sLineBreak +
    'ZYXEL';

  //check substrings
  NotVendors1String =
    ' AND' + sLineBreak +
    ' LAN' + sLineBreak +
    '?' + sLineBreak +
    'ACCELEROMETER' + sLineBreak +
    'ACPI' + sLineBreak +
    'ADAPTER' + sLineBreak +
    'ALLWIN' + sLineBreak +
    'ANALOG' + sLineBreak +
    'AVCTVFM' + sLineBreak +
    'AVCUWIL2' + sLineBreak +
    'AWAY' + sLineBreak +
    'BDT' + sLineBreak +
    'BENCHMARK' + sLineBreak +
    'BLUE' + sLineBreak +
    'BTHAVRCP' + sLineBreak +
    'BTNKEY' + sLineBreak +
    'BTPORT' + sLineBreak +
    'BUS' + sLineBreak +
    'BUTTON' + sLineBreak +
    'CAMERA' + sLineBreak +
    'CAMM' + sLineBreak +
    'COLORMASK' + sLineBreak +
    'COMPANY' + sLineBreak +
    'CONTROLADOR' + sLineBreak +
    'DISC' + sLineBreak +
    'DISK' + sLineBreak +
    'DISQ' + sLineBreak +
    'DRIVER' + sLineBreak +
    'DRIVRU' + sLineBreak +
    'DRV' + sLineBreak +
    'DTV' + sLineBreak +
    'DYSK' + sLineBreak +
    'EDESKTOP' + sLineBreak +
    'EDISTYNEET' + sLineBreak +
    'EEE' + sLineBreak +
    'ENTRY' + sLineBreak +
    'EXPRESS' + sLineBreak +
    'FORALL' + sLineBreak +
    'FORWARD' + sLineBreak +
    'GFX' + sLineBreak +
    'GONILNIK' + sLineBreak +
    'GPRS' + sLineBreak +
    'HANDHELDS' + sLineBreak +
    'HID' + sLineBreak +
    'HUB' + sLineBreak +
    'INSTALL' + sLineBreak +
    'IPS' + sLineBreak +
    'ISDN' + sLineBreak +
    'KBD' + sLineBreak +
    'KEYBOARD' + sLineBreak +
    'LAUNCH' + sLineBreak +
    'LECTEUR' + sLineBreak +
    'LINUX' + sLineBreak +
    'LIST' + sLineBreak +
    'MANUF' + sLineBreak +
    'MIRROR' + sLineBreak +
    'NAME' + sLineBreak +
    'NETWORK' + sLineBreak +
    'NULL' + sLineBreak +
    'OVRL' + sLineBreak +
    'PCI' + sLineBreak +
    'PENMOUNT' + sLineBreak +
    'PILOTE' + sLineBreak +
    'PLAYER' + sLineBreak +
    'PMOUSE' + sLineBreak +
    'PRINT' + sLineBreak +
    'REMOTE' + sLineBreak +
    'REPRODUCTOR' + sLineBreak +
    'RING' + sLineBreak +
    'SALPHAM' + sLineBreak +
    'SERPORT' + sLineBreak +
    'SMBL' + sLineBreak +
    'SOURCE' + sLineBreak +
    'STEROWNIK' + sLineBreak +
    'STUURP' + sLineBreak +
    'SUPPORT' + sLineBreak +
    'SYMBOL' + sLineBreak +
    'TABLET' + sLineBreak +
    'TOASTER' + sLineBreak +
    'USB' + sLineBreak +
    'VDISP' + sLineBreak +
    'VENDOR' + sLineBreak +
    'VIRTUAL' + sLineBreak +
    'VISTA' + sLineBreak +
    'WEBCAM' + sLineBreak +
    'WIND' + sLineBreak +
    'XPTWOPORT';

  //strict check
  NotVendors2String =
    '3M' + sLineBreak +
    '4DMOULF' + sLineBreak +
    'ACF' + sLineBreak +
    'ACI' + sLineBreak +
    'ACTIVE' + sLineBreak +
    'ADD' + sLineBreak +
    'AGP' + sLineBreak +
    'AHCI' + sLineBreak +
    'AICHARGER' + sLineBreak +
    'AUDIO' + sLineBreak +
    'AUREON' + sLineBreak +
    'AVDIO' + sLineBreak +
    'AZ' + sLineBreak +
    'BIP' + sLineBreak +
    'BVRP' + sLineBreak +
    'CARDPHONE' + sLineBreak +
    'CASTOR' + sLineBreak +
    'CCID' + sLineBreak +
    'CD' + sLineBreak +
    'CHANGER' + sLineBreak +
    'CI' + sLineBreak +
    'CMD' + sLineBreak +
    'COMPARE' + sLineBreak +
    'CPO' + sLineBreak +
    'CS' + sLineBreak +
    'CYUMP' + sLineBreak +
    'DC3D' + sLineBreak +
    'DDK' + sLineBreak +
    'DF' + sLineBreak +
    'DSI' + sLineBreak +
    'DT' + sLineBreak +
    'DUPLEX' + sLineBreak +
    'DVXPLORE' + sLineBreak +
    'EASY' + sLineBreak +
    'EPVT' + sLineBreak +
    'ELX' + sLineBreak +
    'ET' + sLineBreak +
    'EUMUS' + sLineBreak +
    'FTDIHW' + sLineBreak +
    'FTS' + sLineBreak +
    'GAMING' + sLineBreak +
    'GP' + sLineBreak +
    'GPSCOMPORT' + sLineBreak +
    'GRABSTER' + sLineBreak +
    'HARDWARE' + sLineBreak +
    'HCF' + sLineBreak +
    'HCSWITCH' + sLineBreak +
    'HEADSET' + sLineBreak +
    'HIGH' + sLineBreak +
    'HS' + sLineBreak +
    'HSX' + sLineBreak +
    'HTO' + sLineBreak +
    'IASTORA' + sLineBreak +
    'IASTORS' + sLineBreak +
    'IIRSP' + sLineBreak +
    'IOFFICEWORKS' + sLineBreak +
    'JUMPSTART' + sLineBreak +
    'KLFT' + sLineBreak +
    'LAN' + sLineBreak +
    'LAYERWALKER' + sLineBreak +
    'LCD' + sLineBreak +
    'LINK' + sLineBreak +
    'LTT' + sLineBreak +
    'MIDI' + sLineBreak +
    'MOBILE' + sLineBreak +
    'MP3' + sLineBreak +
    'MTC' + sLineBreak +
    'MY' + sLineBreak +
    'MYCORP' + sLineBreak +
    'NB' + sLineBreak +
    'NDS' + sLineBreak +
    'NEWISYS' + sLineBreak +
    'NMD' + sLineBreak +
    'NT' + sLineBreak +
    'NTKR' + sLineBreak +
    'NUID' + sLineBreak +
    'OAR' + sLineBreak +
    'OM' + sLineBreak +
    'OVT' + sLineBreak +
    'PARA' + sLineBreak +
    'PC' + sLineBreak +
    'PLEASE' + sLineBreak +
    'PNPXTEST' + sLineBreak +
    'PRO' + sLineBreak +
    'PRODUCT' + sLineBreak +
    'PROVIDER' + sLineBreak +
    'PSEUDO' + sLineBreak +
    'PU' + sLineBreak +
    'QCV' + sLineBreak +
    'QSOUND' + sLineBreak +
    'QUARTICS' + sLineBreak +
    'RAC' + sLineBreak +
    'RSTE' + sLineBreak +
    'RTKBTH' + sLineBreak +
    'SCANNER' + sLineBreak +
    'SCHIJF' + sLineBreak +
    'SCM' + sLineBreak +
    'SECURITY' + sLineBreak +
    'SEE' + sLineBreak +
    'SENSIBLE' + sLineBreak +
    'SMART' + sLineBreak +
    'SMARTCARD' + sLineBreak +
    'SOUND' + sLineBreak +
    'SRTSP' + sLineBreak +
    'STCII' + sLineBreak +
    'STE' + sLineBreak +
    'STORPORT' + sLineBreak +
    'SUPER' + sLineBreak +
    'SWI' + sLineBreak +
    'SYMREDIR' + sLineBreak +
    'TAPE' + sLineBreak +
    'TARGET' + sLineBreak +
    'TMIC' + sLineBreak +
    'TOUCHWARE' + sLineBreak +
    'TP' + sLineBreak +
    'TRIGGER' + sLineBreak +
    'TV' + sLineBreak +
    'TVFM' + sLineBreak +
    'TVMASTER' + sLineBreak +
    'UAA' + sLineBreak +
    'UNKNOWN' + sLineBreak +
    'UPGRADE' + sLineBreak +
    'UVC' + sLineBreak +
    'VEN' + sLineBreak +
    'VFX' + sLineBreak +
    'VLAN' + sLineBreak +
    'VM' + sLineBreak +
    'WB' + sLineBreak +
    'WC' + sLineBreak +
    'WDT' + sLineBreak +
    'WHQL' + sLineBreak +
    'WIRELESS' + sLineBreak +
    'WLAN' + sLineBreak +
    'WPSNUIO' + sLineBreak +
    'WS' + sLineBreak +
    'WUS' + sLineBreak +
    'X00' + sLineBreak +
    'XPSVCOM' + sLineBreak +
    'XVGA' + sLineBreak;
implementation

end.