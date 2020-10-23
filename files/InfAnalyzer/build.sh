#!/bin/sh

rm -f InfAnalyzer_unix*
rm -f *.o
rm -f *.ppu
patch InfAnalyzer.dpr -i unix.patch -o InfAnalyzer_unix.dpr
fpc -Mdelphi -O3 InfAnalyzer_unix.dpr
