program InfAnalyzer;
{$APPTYPE CONSOLE}

uses Windows,SysUtils,Classes,TntClasses,Lists,RegExpr;

var
Os5,Os6,Os7,Os8,Os81,Os10,Arch32,Arch64: boolean;
Vendors,NotVendors1,NotVendors2: TStringList;
debug: boolean;
RegExp, RegExp2: TRegExpr;

procedure AnalyzeOsInf (l: string);
begin
    debug := false;

    RegExp := TRegExpr.Create;
    RegExp2 := TRegExpr.Create;

    RegExp.Expression := '(?i)^[\s]*\;';
    if RegExp.Exec(l) then begin
        exit();
    end;

    RegExp.Expression := '(?i)(^|[\,\.\\\/\-\_ ])xp[^m]';
    RegExp2.Expression := '(?i)(^|[^n])xp([\,\.\\\/\-\_ ]|$)';

    if RegExp.Exec(l)
       or RegExp2.Exec(l)
       or (Pos('WINXP',l)>0) or (Pos('WIN_XP',l)>0)  or (Pos('WIN XP',l)>0)
       or (Pos('NT5',l)>0) or (Pos('WXP',l)>0)
    then begin
        Os5 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 33 condition;');
            WriteLn('Detected: Os5');
        end;
    end;

// Windows Vista detection
   if (Pos('_W6',l)>0) or (Pos('W6_',l)>0)
   or (Pos('-W6',l)>0) or (Pos('W6-',l)>0)
   or (Pos('/W6',l)>0) or (Pos('W6/',l)>0)
   or (Pos('\W6',l)>0) or (Pos('W6\',l)>0)
   or (Pos(',W6',l)>0) or (Pos('W6,',l)>0)
   or (Pos('.W6',l)>0) or (Pos('W6.',l)>0)
   or (Pos(' W6',l)>0) or (Pos('W6 ',l)>0)
   or ((Pos('WIN6',l)>0) And (Pos('WIN64',l)=0))
   or ((Pos('WIN_6',l)>0) And (Pos('WIN_64',l)=0))
   or ((Pos('WIN 6',l)>0) And (Pos('WIN 64',l)=0))
   or ((Pos('WINDOWS6',l)>0) And (Pos('WINDOWS64',l)=0))
   or ((Pos('WINDOWS_6',l)>0) And (Pos('WINDOWS_64',l)=0))
   or ((Pos('WINDOWS 6',l)>0) And (Pos('WINDOWS 64',l)=0))
   or (Pos(' NT6 ',l)>0) or (Pos('VISTA',l)>0) then begin
        Os6 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 52 condition;');
            WriteLn('Detected: Os6');
        end;
    end;

// Windows 7 + Windows 8 detection
    if ((Pos('_W7&8',l)>0) or (Pos('_WIN7&8',l)>0)
        or (Pos('-W7&8',l)>0) or (Pos('-WIN7&8',l)>0)
        or (Pos('/W7&8',l)>0) or (Pos('/WIN7&8',l)>0)
        or (Pos('.W7&8',l)>0) or (Pos('.WIN7&8',l)>0)
        or (Pos(',W7&8',l)>0) or (Pos(',WIN7&8',l)>0)
        or (Pos('\W7&8',l)>0) or (Pos('\WIN7&8',l)>0)
        or (Pos(' W7&8',l)>0) or (Pos(' WIN7&8',l)>0)
    ) then begin
        Os7:=True;
        Os8:=True; Os81 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 70 condition;');
            WriteLn('Detected: Os7, Os8, Os81');
        end;
    end;

// Windows 7 detection
   if (Pos('_W7',l)>0) or (Pos('W7_',l)>0)
   or (Pos('-W7',l)>0) or (Pos('W7-',l)>0)
   or (Pos('/W7',l)>0) or (Pos('W7/',l)>0)
   or (Pos('\W7',l)>0) or (Pos('W7\',l)>0)
   or (Pos(',W7',l)>0) or (Pos('W7,',l)>0)
   or (Pos('.W7',l)>0) or (Pos('W7.',l)>0)
   or (Pos(' W7',l)>0) or (Pos('W7 ',l)>0)
   or (Pos('WIN7',l)>0) or (Pos('WIN_7',l)>0) or (Pos('WIN 7',l)>0)
   or (Pos('WINDOWS7',l)>0) or (Pos('WINDOWS_7',l)>0) or (Pos('WINDOWS 7',l)>0)
   or (Pos(' NT7',l)>0) then begin
        Os7 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 71 condition;');
            WriteLn('Detected: Os7');
        end;
    end;

// Detect windows 8.1
    RegExp := TRegExpr.Create;
    RegExp.Expression := '(?i)[\_\-\/\\\,\. ]W8[\.]?1';
    if RegExp.Exec(l) then begin
       Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 84 condition;');
           WriteLn('Detected: Os81');
       end;
    end;

    RegExp := TRegExpr.Create;
    RegExp.Expression := '(?i)W8[\.]?1[\_\-\/\\\,\. ]';
    if RegExp.Exec(l) then begin
       Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 96 condition;');
           WriteLn('Detected: Os81');
       end;
    end;

    RegExp := TRegExpr.Create;
    RegExp.Expression := '(?i)WIN8[\.]?1';
    if RegExp.Exec(l) then begin
       Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 108 condition;');
           WriteLn('Detected: Os81');
       end;
    end;

    RegExp := TRegExpr.Create;
    RegExp.Expression := '(?i)WINDOWS[ \_\-]?8[\.]?1';
    if RegExp.Exec(l) then begin
       Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 120 condition;');
           WriteLn('Detected: Os81');
       end;
    end;

// Detect windows 8
   RegExp := TRegExpr.Create;
   RegExp.Expression := '(?i)[\_\-\/\\\,\. ]W8([\.][^1]|[^1]|$)';
   if RegExp.Exec(l) then begin
       Os8 := True; Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 133 condition;');
           WriteLn('Detected: Os8, Os81');
       end;
   end;

   RegExp := TRegExpr.Create;
   RegExp.Expression := '(?i)W8[\_\-\/\\\,\. ]([^1]|$)';
   if RegExp.Exec(l) then begin
       Os8 := True; Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 145 condition;');
           WriteLn('Detected: Os8, Os81');
       end;
   end;

   RegExp := TRegExpr.Create;
   RegExp.Expression := '(?i)WIN8([\.][^1]|[^1]|$)';
   if RegExp.Exec(l) then begin
       Os8 := True; Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 157 condition;');
           WriteLn('Detected: Os8, Os81');
       end;
   end;

  RegExp := TRegExpr.Create;
  RegExp.Expression := '(?i)WINDOWS[ \_\-]?8([\.][^1]|[^1]|$)';
  if RegExp.Exec(l) then begin
       Os8 := True; Os81 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 169 condition;');
           WriteLn('Detected: Os8, Os81');
       end;
  end;

// Detect windows 10
    if (Pos('_W10',l)>0) or (Pos('W10_',l)>0)
        or (Pos('-W10',l)>0) or (Pos('W10-',l)>0)
        or (Pos('/W10',l)>0) or (Pos('W10/',l)>0)
        or (Pos('\W10',l)>0) or (Pos('W10\',l)>0)
        or (Pos(',W10',l)>0) or (Pos('W10,',l)>0)
        or (Pos('.W10',l)>0) or (Pos('W10.',l)>0)
        or (Pos(' W10',l)>0) or (Pos('W10 ',l)>0)
        or (Pos('WIN10',l)>0) or (Pos('WIN_10',l)>0) or (Pos('WIN 10',l)>0)
        or (Pos('WINDOWS10',l)>0) or (Pos('WINDOWS_10',l)>0) or (Pos('WINDOWS 10',l)>0)
        or (Pos(' NT10',l)>0)
    then begin
         Os10 := True;
         if debug then begin
             WriteLn('---------------------------------------');
             WriteLn(l);
             WriteLn('Line 218 condition;');
             WriteLn('Detected: Os10');
         end;
     end;


// Additional detections
// Windows XP x32
   if (Pos('86.5.0',l)>0) or (Pos('86.5.1',l)>0) or (Pos('NT.5.1',l)>0)
   or (Pos('NT 5.1',l)>0)  or (Pos('I386',l)>0)
   then begin
        Os5 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 190 condition;');
            WriteLn('Detected: Os5, Arch32');
        end;
   end;

// Windows Vista x32
   if Pos('86.6.0',l)>0 then begin
        Os6 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 201 condition;');
            WriteLn('Detected: Os6, Arch32');
        end;
   end;

// Windows 7x32
   if Pos('86.6.1',l)>0 then begin
        Os7 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 211 condition;');
            WriteLn('Detected: Os7, Arch32');
        end;
   end;
   if Pos('86.6.2',l)>0 then begin
        Os8 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 221 condition;');
            WriteLn('Detected: Os8, Arch32');
        end;
   end;

// Windows 8.1 x32
   if Pos('86.6.3',l)>0 then begin
       Os81 := True;
       Arch32 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 231 condition;');
           WriteLn('Detected: Os81, Arch32');
       end;
   end;

// Windows 10 x32
    if Pos('86.6.4',l)>0 then begin
       Os10 := True;
       Arch32 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 286 condition;');
           WriteLn('Detected: Os10, Arch32');
       end;
   end;

// Windows Vista x64
   if Pos('64.6.0',l)>0 then begin
        Os6 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 304 condition;');
            WriteLn('Detected: Os6, Arch64');
        end;
   end;

// Windows 7 x64
   if Pos('64.6.1',l)>0 then begin
        Os7 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 316 condition;');
            WriteLn('Detected: Os7, Arch64');
        end;
   end;

// Windows 8 x64
   if Pos('64.6.2',l)>0 then begin
        Os8 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 328 condition;');
            WriteLn('Detected: Os8, Arch64');
        end;
   end;

// Windows 8.1 x64
   if Pos('64.6.3',l)>0 then begin
          Os81 := True;
          Arch64 := True;
          if debug then begin
              WriteLn('---------------------------------------');
              WriteLn(l);
              WriteLn('Line 271 condition;');
              WriteLn('Detected: Os81, Arch64');
          end;
      end;

// Windows 10 x64
   if Pos('64.6.4',l)>0 then begin
       Os10 := True;
       Arch64 := True;
       if debug then begin
           WriteLn('---------------------------------------');
           WriteLn(l);
           WriteLn('Line 352 condition;');
           WriteLn('Detected: Os10, Arch64');
       end;
   end;

   if Arch32=False then
   if (Pos('32BIT',l)>0) or (Pos('32 BIT',l)>0) or (Pos('32-BIT',l)>0) or (Pos('32_BIT',l)>0)
   or (Pos('32B.',l)>0)  or (Pos('X86',l)>0) or (Pos('X32',l)>0) or (Pos('32 PROCESSOR',l)>0)
   or (Pos('NT32',l)>0) or (Pos('NT 32',l)>0) or (Pos('NT_32',l)>0) or (Pos('NT-32',l)>0)
   or (Pos('AMD32',l)>0) or (Pos('AMD 32',l)>0) or (Pos('AMD_32',l)>0) or (Pos('AMD-32',l)>0)
   or (Pos('CPU32',l)>0) or (Pos('CPU 32',l)>0) or (Pos('CPU_32',l)>0) or (Pos('CPU-32',l)>0)
   or (Pos('_32',l)>0) or (Pos('/32/',l)>0) or (Pos('WIN32',l)>0) or (Pos('DRIVERS32',l)>0)
   or (Pos('COINSTALLERS32',l)>0) or (Pos('INST32',l)>0) or (Pos('INSTALL32',l)>0)
   or (Pos('RUNDLL32',l)>0) or (Pos('SYS32COPY',l)>0) or (Pos('SYSTEM32',l)>0)
   or (Pos('NTX86',l)>0) or (Pos('.X86',l)>0) then begin
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 290 condition;');
            WriteLn('Detected: Arch32');
        end;
   end;

   if Arch64=False then
   if (Pos('64BIT',l)>0) or (Pos('64 BIT',l)>0) or (Pos('64-BIT',l)>0) or (Pos('64_BIT',l)>0)
   or (Pos('64B.',l)>0) or (Pos('X64',l)>0)  or (Pos('64 PROCESSOR',l)>0)
   or (Pos('CPU64',l)>0) or (Pos('CPU 64',l)>0) or (Pos('CPU_64',l)>0) or (Pos('CPU-64',l)>0)
   or (Pos('AMD64',l)>0) or (Pos('AMD 64',l)>0) or (Pos('AMD_64',l)>0) or (Pos('AMD-64',l)>0)
   or (Pos('ATHLON64',l)>0) or (Pos('ATHLON 64',l)>0) or (Pos('ATHLON_64',l)>0) or (Pos('ATHLON-64',l)>0)
   or (Pos('TURION64',l)>0) or (Pos('TURION 64',l)>0) or (Pos('TURION_64',l)>0) or (Pos('TURION-64',l)>0)
   or (Pos('SEMPRON64',l)>0) or (Pos('SEMPRON 64',l)>0) or (Pos('SEMPRON_64',l)>0) or (Pos('SEMPRON-64',l)>0)
   or (Pos('NT64',l)>0) or (Pos('NT 64',l)>0) or (Pos('NTI64',l)>0) or (Pos('NTIA64',l)>0)
   or ((Pos('_64',l)>0) and (Pos('SUBSYS_64',l)<1))or (Pos('/64/',l)>0) or (Pos('WIN64',l)>0) // or (Pos('SYSTEM64',l)>0)
   or (Pos('64_REG',l)>0) or (Pos('INST64',l)>0) or (Pos('INSTALL64',l)>0) then begin
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 310 condition;');
            WriteLn('Detected: Arch64');
        end;
   end;

// Additional Windows Vista & Windows 7 detection
   RegExp.Expression := '(?i)(\s|win|windows)[\s]*vista,[\s]*7';
   if (RegExp.Exec(l)) then begin
        Os6 := True;
        Os7 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 322 condition;');
            WriteLn('Detected: Os6, Os7');
        end;
   end;

   if (Arch32=False) or (Arch64=False) then
   if (Pos('32.64',l)>0) or (Pos('32/64',l)>0)
   or (Pos('32 / 64',l)>0) or (Pos('32-64',l)>0)
   or (Pos('86.64',l)>0) or (Pos('86/64',l)>0)
   or (Pos('86 / 64',l)>0) or (Pos('86-64',l)>0)
   or (Pos('86&32',l)>0) or (Pos('86 & 32',l)>0)
   or (Pos('86&64',l)>0) or (Pos('86 & 64',l)>0)
   then begin
        Arch32 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 340 condition;');
            WriteLn('Detected: Arch32, Arch64');
        end;
   end;

   if (Os5=False) or (Arch32=False) then
   if (Pos('5_32',l)>0) or (Pos('5/32',l)>0)
   or (Pos('XP_32',l)>0) or (Pos('XP-32',l)>0)
   or (Pos('XP32',l)>0) or (Pos('XP 32',l)>0) 
   or (Pos('XPX32',l)>0) or (Pos('XPX86',l)>0)
   then begin
        Os5 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 356 condition;');
            WriteLn('Detected: Os5, Arch32');
        end;
   end;

   if (Os6=False) or (Arch32=False) then
   if  //(Pos('6_32',l)>0) or (Pos('6-32',l)>0) or (Pos('6/32',l)>0) or
   (Pos('VISTA/32',l)>0) or (Pos('VISTA32',l)>0) or (Pos('VISTA-32',l)>0)
   or (Pos('VISTA 32',l)>0) or (Pos('VISTA_32',l)>0) or (Pos('VISTAX32',l)>0)
   or (Pos('VI32',l)>0)
   then begin
        Os6 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 372 condition;');
            WriteLn('Detected: Os6, Arch32');
        end;
   end;

   if (Os7=False) or (Arch32=False) then
   if (Pos('7_32',l)>0) or (Pos('7-32',l)>0) or (Pos('7/32',l)>0)
   or (Pos('WIN7 32',l)>0) or (Pos('WIN7/32',l)>0) or (Pos('WIN7X32',l)>0)
   then begin
        Os7 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 386 condition;');
            WriteLn('Detected: Os7, Arch32');
        end;
   end;

   if (Os8=False) or (Os81=False) or (Arch32=False) then
      if (Pos('8_32',l)>0) or (Pos('8-32',l)>0) or (Pos('8/32',l)>0)
      or (Pos('WIN8 32',l)>0) or (Pos('WIN8/32',l)>0) or (Pos('WIN8X32',l)>0)
      then begin
           Os8 := True;
           Arch32 := True;
           if debug then begin
               WriteLn('---------------------------------------');
               WriteLn(l);
               WriteLn('Line 470 condition;');
               WriteLn('Detected: Os8, Arch32');
           end;
      end;

   if (Os81=False) or (Arch32=False) then
        if (Pos('81_32',l)>0) or (Pos('8.1_32',l)>0)
        or (Pos('81-32',l)>0) or (Pos('8.1-32',l)>0)
        or (Pos('81/32',l)>0) or (Pos('8.1/32',l)>0)
        or (Pos('WIN81 32',l)>0) or (Pos('WIN8.1 32',l)>0)
        or (Pos('WIN81/32',l)>0) or (Pos('WIN8.1/32',l)>0)
        or (Pos('WIN81X32',l)>0) or (Pos('WIN8.1X32',l)>0)
        then begin
            Os81 := True;
            Arch32 := True;
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn(l);
                WriteLn('Line 418 condition;');
                WriteLn('Detected: Os81, Arch32');
            end;
        end;

   if (Os10=False) or (Arch32=False) then
        if (Pos('10_32',l)>0)
        or (Pos('10-32',l)>0)
        or (Pos('10/32',l)>0)
        or (Pos('WIN10 32',l)>0)
        or (Pos('WIN10/32',l)>0)
        or (Pos('WIN10X32',l)>0)
        then begin
            Os10 := True;
            Arch32 := True;
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn(l);
                WriteLn('Line 518 condition;');
                WriteLn('Detected: Os10, Arch32');
            end;
        end;

   if (Os5=False) or (Arch64=False) then
   if (Pos('5_64',l)>0) or (Pos('XP_64',l)>0) or (Pos('XP-64',l)>0)
   or (Pos('XP 64',l)>0) or (Pos('64.5.1',l)>0) or (Pos('XPX64',l)>0)
   then begin
        Os5 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 532 condition;');
            WriteLn('Detected: Os5, Arch64');
        end;
   end;

   if (Os6=False) or (Arch64=False) then
   if //(Pos('6_64',l)>0) or (Pos('6-64',l)>0) or (Pos('6/64',l)>0) or
   (Pos('VISTA/64',l)>0) or (Pos('VISTA64',l)>0) or (Pos('VISTA-64',l)>0)
   or (Pos('VISTA 64',l)>0) or (Pos('VISTA_64',l)>0) or (Pos('VISTAX64',l)>0)
   or (Pos('VI64',l)>0) or (Pos('64.6.0',l)>0)
   then begin
        Os6 := True;
        Arch64 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 548 condition;');
            WriteLn('Detected: Os6, Arch64');
        end;
   end;

   RegExp.Expression := '(?i)(\s|win)7[\-\_]64';
   if (Os7=False) or (Arch64=False) then
   if (Pos('7_64',l)>0) or (RegExp.Exec(l)) or (Pos('7/64',l)>0)
   or (Pos('WIN7 64',l)>0) or (Pos('WIN7X64',l)>0) or (Pos('64.6.1',l)>0)
   then begin
        Os7 := True;
        Arch64 := True;
        if debug then begin
             WriteLn('---------------------------------------');
             WriteLn(l);
             WriteLn('Line 563 condition;');
             WriteLn('Detected: Os7, Arch64');
         end;
   end;

    if (Os8=False) or (Os81=False) or (Arch64=False) then
        if (Pos('8_64',l)>0) or (Pos('8-64',l)>0) or (Pos('8/64',l)>0)
            or (Pos('WIN8 64',l)>0) or (Pos('WIN8X64',l)>0) or (Pos('64.6.2',l)>0)
        then begin
            Os8 := True;
            Arch64 := True;
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn(l);
                WriteLn('Line 577 condition;');
                WriteLn('Detected: Os8, Arch64');
            end;
        end;

    if (Os81=False) or (Arch64=False) then
        if (Pos('81_64',l)>0) or (Pos('8.1_64',l)>0)
            or (Pos('81-64',l)>0) or (Pos('8.1-64',l)>0)
            or (Pos('81/64',l)>0) or (Pos('8.1/64',l)>0)
            or (Pos('WIN81 64',l)>0) or (Pos('WIN8.1 64',l)>0)
            or (Pos('WIN81/64',l)>0) or (Pos('WIN8.1/64',l)>0)
            or (Pos('WIN81X64',l)>0) or (Pos('WIN8.1X64',l)>0)
        then begin
             Os81 := True;
             Arch64 := True;
             if debug then begin
                 WriteLn('---------------------------------------');
                 WriteLn(l);
                 WriteLn('Line 595 condition;');
                 WriteLn('Detected: Os81, Arch64');
             end;
        end;

    if (Os10=False) or (Arch64=False) then
        if (Pos('10_64',l)>0)
            or (Pos('10-64',l)>0)
            or (Pos('10/64',l)>0)
            or (Pos('WIN10 64',l)>0)
            or (Pos('WIN10/64',l)>0)
            or (Pos('WIN10X64',l)>0)
        then begin
             Os10 := True;
             Arch64 := True;
             if debug then begin
                 WriteLn('---------------------------------------');
                 WriteLn(l);
                 WriteLn('Line 613 condition;');
                 WriteLn('Detected: Os10, Arch64');
             end;
        end;

   if Os6=False then
   if (Pos('5219.',l)>0) or (Pos('5270.',l)>0) or (Pos('5308.',l)>0)
   or (Pos('5381.',l)>0) or (Pos('5384.',l)>0) or (Pos('5472.',l)>0)
   or (Pos('5600.',l)>0) or (Pos('5728.',l)>0) or (Pos('5744.',l)>0)
   then begin
        Os6 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 509 condition;');
            WriteLn('Detected: Os6');
        end;
   end;

   if Os7=False then
   if (Pos('6.1.7600',l)>0) or (Pos('6.1.7601',l)>0) then begin
        Os7 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn(l);
            WriteLn('Line 638 condition;');
            WriteLn('Detected: Os7');
        end;
   end;

   if (Os8=False) or (Os81=False) then
       if (Pos('6.2.9200',l)>0) or (Pos('6.2.9201',l)>0) then begin
            Os8 := True; Os81 := True;
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn(l);
                WriteLn('Line 649 condition;');
                WriteLn('Detected: Os8, Os81');
            end;
       end;

   if (Os81=False) then
        if (Pos('6.3.9600',l)>0) or (Pos('6.3.9601',l)>0) then begin
            Os81 := True;
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn(l);
                WriteLn('Line 660 condition;');
                WriteLn('Detected: Os81');
            end;
        end;
end;

function AnalyzeVendor(l: string): string;
var
i,k: byte;
j: integer;
s: string;
Found: boolean;
debug: boolean;
begin
   l := Trim(l); l := AnsiUpperCase(l);

   debug := false;

// MessageBox(GetActiveWindow,PChar(l),'0',Mb_Ok);

   if Pos('N-TRIG',l)>0 then begin Result := 'NTRIG'; Exit; end;
   if (Pos('DLINK',l)>0) or (Pos('D-LINK',l)>0) or (Pos('RALINK',l)>0)
      or (Pos('DISPLAYLINK',l)>0) then begin Result := 'DLINK'; Exit; end;
   if Pos('C-MEDIA',l)>0 then begin Result := 'CMEDIA'; Exit; end;
   if (Pos('E-MU',l)>0) or (Pos('CREA',l)=1)
      then begin Result := 'CREATIVE'; Exit; end;
   if (Pos('Y-E',l)>0) or (Pos('YEDATA',l)>0) or (l='YED') then
      begin Result := 'YEDATA'; Exit; end;

   if (Pos('STANDARD',l) > 0) and (Pos('MICRO',l) > 0) then begin Result := 'SMC'; Exit; end;

   if (Pos('(STANDARD',l)>0) or (Pos('(ENHANCED',l)>0)
      then begin Result := 'MICROSOFT'; Exit; end;
   if (Pos('ADVANCED MICRO',l)>0) or (Pos('PCIE',l)>0) or (Pos('AMD',l)=1)
      then begin Result := 'AMD'; Exit; end;
   if (Pos('ANALOG',l)>0) and (Pos('DEVICES',l)>0) then begin Result := 'ANALOGDEVICES'; Exit; end;
   if ((Pos('SAM',l)>0) and (Pos('SUNG',l)>0)) or (Pos('SSF',l)>0) or (l='SENS')
      or (l='CMC7XX') or (l='C7XX') or (l='SSUD') 
      then begin Result := 'SAMSUNG'; Exit; end;
   if (Pos('HEWLETT',l)>0) or (Pos('HP_',l)>0) or (Pos('HP=',l)>0) or
      (Pos('"HP"',l)>0) or (Pos('%HP%',l)>0) or (Pos('HHP',l)>0) or
      (Pos('MFP',l)>0)
      then begin Result := 'HP'; Exit; end;
   if (Pos('EGO',l)=1) or (Pos('M8UDEV',l)>0) or (Pos('XPMATEDEV',l)>0)
      then begin Result := 'EGO'; Exit; end;
   if Pos('SOC PC-CAMERA',l)>0 then begin Result := 'SOC'; Exit; end;
   if Pos('ATK',l)>0 then begin Result := 'ATK'; Exit; end;
   if (Pos('INFOPRINT',l)>0) or (s='THERMAL') then begin Result := 'INFOPRINT'; Exit; end; { InfoPrint Solutions Company 6700-008 }

   if (Pos('MEGACHIPS',l) > 0) then begin Result := 'MEGACHIPS'; Exit; end;
   if (Pos('BUSLOGIC',l) > 0) then begin Result := 'BUSLOGIC'; Exit; end;
   if (Pos('PACKARD',l) > 0) and (Pos('BELL',l) > 0) then begin Result := 'PACKARDBELL'; Exit; end;
   if (Pos('SMART',l) > 0) and (Pos('LINK',l) > 0) then begin Result := 'SMARTLINK'; Exit; end;
   if (Pos('RAMBUS',l) > 0) then begin Result := 'RAMBUS'; Exit; end;
   if (Pos('BUSTECH',l) > 0) then begin Result := 'BUSTECH'; Exit; end;
   if (Pos('ENGINEERING',l) > 0) and (Pos('DESIGN',l) > 0) then begin Result := 'EDT'; Exit; end;
   if (Pos('SANDISK',l) > 0) then begin Result := 'SANDISK'; Exit; end;
   if (Pos('SMART',l) > 0) and (Pos('LINK',l) > 0) then begin Result := 'SMARTLINK'; Exit; end;
   if ((Pos('BROADCOM',l) > 0) or (Pos('WIDCOMM',l) > 0)) then begin Result := 'BROADCOM'; Exit; end;
   if (Pos('MIPS',l) > 0) then begin Result := 'MIPS'; Exit; end;
   if (Pos('MYNAME',l) > 0) then begin Result := 'MYNAME'; Exit; end;
   if ((Pos('QUALCOMM',l)>0) or (Pos('GOBI',l)>0)) then begin Result := 'QUALCOMM'; Exit; end;
   if (Pos('IMAGING SOURCE',l)>0) then begin Result := 'IMAGINGSOURCE'; Exit; end;

   if Pos('=',l)=0 then begin Result := ''; Exit; end;

   While Pos('"',l)>0 do
        Delete(l,Pos('"',l), 1);

   Delete(l,1,Pos('=',l));
   s := Trim(l);

   {if debug then begin
     if Length(s)>0 then begin
        WriteLn('---------------------------------------');
        WriteLn('[LINE]: ' + l);
        WriteLn('[DETECTED]: ' + s);
     end;
   end;}

   s := StringReplace(s,'(',' ',[rfReplaceAll]);
   s := StringReplace(s,')',' ',[rfReplaceAll]);
   s := StringReplace(s,'{',' ',[rfReplaceAll]);
   s := StringReplace(s,'}',' ',[rfReplaceAll]);
   s := StringReplace(s,'MFG.','',[rfReplaceAll]);

   if Pos(' ',s)>0 then s := Copy(s,1,Pos(' ',s)-1);
   if Pos('.',s)>0 then s := Copy(s,1,Pos('.',s)-1);
   if Pos(',',s)>0 then s := Copy(s,1,Pos(',',s)-1);
   if Pos('-',s)>0 then s := Copy(s,1,Pos('-',s)-1);
   if Pos('_',s)>0 then s := Copy(s,1,Pos('_',s)-1);
   if Pos(';',s)>0 then s := Copy(s,1,Pos(';',s)-1);
   if Pos('=',s)>0 then s := Copy(s,1,Pos('=',s)-1);
   if Pos('&',s)>0 then s := Copy(s,1,Pos('&',s)-1);
   s := Trim(s);

    if (s='DEFAULT') then begin
        Result := 'MICROSOFT';
        Exit;
    end;
    if ((Pos('HP',s)=1)) then begin
        Result := 'HP';
        Exit;
    end;

// MessageBox(GetActiveWindow,PChar(s),'1',Mb_Ok);

   if Pos('MFG',s)>0 then s := StringReplace(s,'MFG','',[rfReplaceAll]);
   if Pos('MFC',s)>0 then s := StringReplace(s,'MFC','',[rfReplaceAll]);
   if Pos('DEVICE',s)>0 then s := StringReplace(s,'DEVICE','',[rfReplaceAll]);
   if Pos('FILTER',s)>0 then s := StringReplace(s,'FILTER','',[rfReplaceAll]);
   if Pos('MODELS',s)>0 then s := StringReplace(s,'MODELS','',[rfReplaceAll]);
   if Pos('MODEL',s)>0 then s := StringReplace(s,'MODEL','',[rfReplaceAll]);
   if Pos('MODE',s)>0 then s := StringReplace(s,'MODE','',[rfReplaceAll]);

   if Length(s)=0 then begin Result := ''; Exit; end;

// MessageBox(GetActiveWindow,PChar(s),'2',Mb_Ok);

   Found := False; j := -1;
   if NotVendors1.Count>0 then begin
   Repeat Inc(j);
   if Pos(NotVendors1[j],s)>0 then Found := True;
   Until (Found) or (j=NotVendors1.Count-1);
   end;

   if Found then begin Result := ''; Exit; end;

   Found := False; j := -1;
   if NotVendors2.Count>0 then begin
   Repeat Inc(j);
   if NotVendors2[j]=s then Found := True;
   Until (Found) or (j=NotVendors2.Count-1);
   end;

   if Found then begin Result := ''; Exit; end;

   k := 0; for i := 0 to 9 do if Pos(IntToStr(i),s)>0 then Inc(k);
   if k>1 then begin Result := ''; Exit; end;

// MessageBox(GetActiveWindow,PChar(s),'3',Mb_Ok);

   if (s='MSFT') or (s='MS') or (Pos('MICROSOFT',s)>0)
   or (s='STD') or (Pos('OEM',s)>0) or (s='GENERIC') then s := 'MICROSOFT';
   if (Pos('CREATIVE',s)>0) or (s='EMU') then s := 'CREATIVE';
   if Pos('ASUS',s)>0 then s := 'ASUS';
   if Pos('RICOH',s)>0 then s := 'RICOH';
   if (Pos('QUALCOMM',s)>0) or (Pos('GOBI',s)>0) then s := 'QUALCOMM';
   if Pos('WINBOND',s)>0 then s := 'WINBOND';
   if (Pos('ERIC',s)>0) or (s='EMPF3') or (s='SE') then s := 'ERICSSON';
   if (Pos('TEXAS',s)>0) or (s='TI') then s := 'TEXAS';
   if Pos('BISON',s)>0 then s := 'BISON';
   if Pos('ALLIED',s)>0 then s := 'ALLIED';
   if Pos('NOVATEK',s)>0 then s := 'NOVATEK';
   if Pos('ANDROID',s)>0 then s := 'GOOGLE';
   if Pos('NETGEAR',s)>0 then s := 'NETGEAR';
   if Pos('GIGA',s)>0 then s := 'GIGABYTE';
   if Pos('EGALAX',s)>0 then s := 'EGALAX';
   if (s='O2') or (Pos('O2',s)=1) or (s='OZSCR') then s := 'O2MICRO';
   if Pos('SRS',s)=1 then s := 'SRS';
   if (Pos('OPTI',s)=1) or (s='GLOBETROTTER') then s := 'OPTION';
   if Pos('LSI',s)>0 then s := 'LSI';
   if Pos('ARGOSY',s)>0 then s := 'ARGOSY';
   if (Pos('VIA',s)>0) or (s='VNT') or (s='ICE') or (s='ENVY')
   or (Pos('DELTA',s)>0) or (s='VINYL') then s := 'VIA';
   if (Pos('AUDIOTRACK',s)>0) or (Pos('AUDIOTR',s)>0)
   or (s='PRODIGY') then s := 'AUDIOTRACK';
   if Pos('IBM',s)>0 then s := 'IBM';
   if Pos('LG',s)>0 then s := 'LG';
   if Pos('ATK',s)>0 then s := 'ATK';
   if Pos('JVS',s)>0 then s := 'JVS';
   if Pos('CFOS',s)>0 then s := 'CFOS';
   if (Pos('GENI',s)>0) or (s='SCROLL') then s := 'GENIOUS';
   if Pos('SONY',s)>0 then s := 'SONY';
   if Pos('QCOMSERIAL',s)>0 then s := 'QUALCOMM';
   if (Pos('QLOGIC',s)>0) or (s='NETXEN') then s := 'QLOGIC';
   if (Pos('DELL',s)>0) or (s='GTEKO') then s := 'DELL';
   if (Pos('NEC',s)=1) or (s='FXI') then s := 'NEC';
   if Pos('LIVE',s)=1 then s := 'AIRLIVE';
   if (Pos('SAI',s)=1) or (s='MAGICMOUSE') then s := 'SAITEK';
   if Pos('ITE',s)=1 then s := 'ITE';
   if (Pos('FUJ',s)>0) or (s='FSC') then s := 'FUJITSU';
   if Pos('ZTE',s)>0 then s := 'ZTE';
   if Pos('SIS',s)>0 then s := 'SIS';
   if Pos('TRID',s)>0 then s := 'TRIDENT';
   if Pos('S3',s)>0 then s := 'S3';
   if Pos('FXI',s)>0 then s := 'FXI';
   if Pos('ANGEL',s)>0 then s := 'ANGEL';
   if Pos('SYNTEK',s)>0 then s := 'SYNTEK';
   if Pos('ALCOR',s)>0 then s := 'ALCOR';
   if Pos('ASMEDIA',s)>0 then s := 'ASMEDIA';
   if Pos('ETRON',s)=1 then s := 'ETRON';
   if Pos('CERTANCE',s)=1 then s := 'CERTANCE';
   if Pos('FRACTAL',s)=1 then s := 'FRACTAL';
   if Pos('FEIXUN',s)>0 then s := 'FEIXUN';
   if Pos('FRESCO',s)=1 then s := 'FRESCO';
   if Pos('GENESYS',s)=1 then s := 'GENESYS';
   if Pos('NUVOTON',s)=1 then s := 'NUVOTON';
   if Pos('PLANEX',s)=1 then s := 'PLANEX';
   if Pos('RENESAS',s)=1 then s := 'RENESAS';
   if Pos('TERRATEC',s)=1 then s := 'TERRATEC';
   if Pos('SIERRA',s)=1 then s := 'SIERRA';
   if (Pos('TREND',s)=1) then s := 'TRENDNET';
   if (Pos('XAVI',s)=1) then s := 'XAVI';
   if (Pos('MLNX',s)>0) or (Pos('MLX',s)>0) or (s='HCA') or (s='MTL') then s := 'MELLANOX';
   if (Pos('TOSHIBA',s)>0) or (s='TELI') then s := 'TOSHIBA';
   if (Pos('MATROX',s)>0) or (Pos('MXE',s)>0) then s := 'MATROX';
   if (Pos('SUNPLUS',s)>0) or (Pos('SPL',s)>0) then s := 'SUNPLUS';
   if (s='INTL') or (s='INTC') or (s='MEI') or (s='ISCT') or (s='IMC')
      or (Pos('INTEL',s)>0) or (Pos('ESG',s)>0) or (s='NETEFFECT') then s := 'INTEL';
   if (Pos('LX',s)=1) or (Pos('LEXMARK',s)>0) or (s='MIT') then s := 'LEXMARK';
   if Pos('BRCD',s)>0 then s := 'BROCADE';
   if (s='YA') or (s='IIYAMA') then s := 'YAMAHA';
   if (s='JM') or (s='JMR') or (s='JMCR') or (Pos('JMICRON',s)=1) then s := 'JMICRON';
   if (s='LOGICOOL') or (s='LOGITEC') or (s='SETPOINT') then s := 'LOGITECH';
   if (s='BCOM') or (s='BRCM') or (s='BCM') or (Pos('BROADCOM',s)=1) then s := 'BROADCOM';
   if (s='SYN') or (s='SN') then s := 'SYNAPTICS';
   if (Pos('ACTIONTEC',s) >0) or (s='ACCTON') then s := 'ACTION';
   if (s='VMCAM') or (s='UVCPCC') or (s='RICHPOWER') then s := 'VIMICRO';
   if s='ACR' then s := 'ACER';
   if s='AICHARGERPLUS' then s := 'ASUS';
   if s='AL' then s := 'AIRLINK';
   if s='AST' then s := 'ASPEED';
   if (s='BF') or (s='BAFO') then s := 'BIGFOOT';
   if s='BR' then s := 'BROTHER';
   if s='CAS' then s := 'CHICONY';
   if s='CP' then s := 'CANON';
   if (s='CPL') or (s='EPL') or (s='ZPL') then s := 'ZEBRA';
   if s='MTM' then s := 'MITSUMI';
   if s='IO' then s := 'ECHO';
   if s='FXI' then s := 'XEROX';
   if s='JL' then s := 'JEILIN';
   if s='MSI' then s := 'MICROSTAR';
   if s='NWT' then s := 'NOVATEL';
   if s='CY' then s := 'CYGNETRON';
   if s='LITE' then s := 'LITEON';
   if s='MAD' then s := 'MADCATZ';
   if s='MMMTS' then s := 'MMT';
   if s='CCDDIRECT' then s := 'UNIBRAIN';
   if s='PCL6' then s := 'HP';
   if (s='TC') or (s='TOUCHCHIP') or (s='UPEK') then s := 'AUTHENTEC';
   if (s='TOUCHSENSE') then s := 'SAITEK';
   if s='WACMT' then s := 'WACOM';
   if s='WINFAST' then s := 'LEADTEK';
   if s='RNDISS' then s := 'VIA';
   if (s='NVDA') or (s='AGEIA') then s := 'NVIDIA';
   if s='ENECIR' then s := 'ENE';
   if (s='CXT') or (s='CAM') or (s='ICOMP') then s := 'CONEXANT';
   if s='SYMC' then s := 'SYMANTEC';
   if (s='RTK') or (s='RTKHANDSFREE') or (s='RTKAVRCP') or (Pos('RTL',s)>0)
      or (Pos('HDA',s)>0) or (Pos('REALT',s)=1) then s := 'REALTEK';
   if s='CSR' then s := 'CAMBRIDGE';
   if s='ADPT' then s := 'ADAPTEC';
   if (s='KB') or (s='SEIKO') or (Pos('SII',s)=1) // Seiko --> Epson
      or (Pos('EPSON',s)>0) then s := 'EPSON';
   if (s='HTC') or (s='HT') then s := 'HITACHI';
   if (Pos('KM',s)=1) or (Pos('KYOCERA',s)>0) then s := 'KYOCERA';
   if s='HSF' then s := 'CONEXANT';
   if s='XYZBTH' then s := 'ATHEROS';
   if (s='BST') or (Pos('BIOS',s)>0) then s := 'BIOSTAR';
   if (s='SM') or (s='SMI') or (s='SIMG')
      or (Pos('SILICON',s)>0) then s := 'SILICON';
   if s='MUSE' then s := 'HERCULES';
   if s='MONSTERTV' then s := 'VIDZMEDIA';
   if (s='M8U') or (s='ESU22') or (s='MAYA44') or (s='JULI@')
      or (s='DUAFIRE') then s := 'ESI';
   if (Pos('MOT',s)=1) or (s='M3AD') then s := 'MOTOROLA';
   if (s='EMA') or (Pos('EMACHIN',s)>0) then s := 'EMACHINES';
   if s='RAWETHER' then s := 'PCAUSA';
   if s='SRP' then s := 'BIXOLON';
   if s='TD' then s := 'TANDBERG';
   if s='GWY' then s := 'GATEWAY';
   if (s='LNVO') or (s='DTM') then s := 'LENOVO';
   if s='TIUMP' then s := 'HUAWEI';
   if s='SMSC' then s := 'SMC';
   if s='STM' then s := 'ST';
   if s='UTC' then s := 'TOUCHUTILITY';
   if s='HCWHDPVR' then s := 'HAUPPAUGE';
   if (s='SYSKONNECT') or (Pos('MARVELL',s)>0) then s := 'MARVELL';
   if s='ZYAIR' then s := 'ZYXEL';
   if (s='ALICOM') or (s='ALICAM') or (s='ALISDIF') then s := 'ALI';
   if (s='CISS') or (Pos('COMPAQ',s)>0) then s := 'COMPAQ';
   if (s='ATITECHNOLOGIES') or (s='SEC') then s := 'ATI';
   if s='SPOC' then s := 'INFINEON';
   if (s='KILLER') or (s='BFOOT') then s := 'BIGFOOT';
   if (s='WAVEDVBT') or (s='WAVEATSC') then s := 'LUMANATE';
   if s='SF' then s := 'SOFTFOUNDRY';
   if Pos('GLOBESPAN',s)>0 then s := 'GLOBESPAN';
   if Pos('COREGA',s)>0 then s := 'COREGA';
   if Pos('SAGEM',s)=1 then s := 'SAGEM';
   if s='MAUIIIIG' then s := 'EMUSED';
   if s='XTREME' then s := 'DIAMOND';
   if s='ADVANCED' then s := 'AMD';
   if s='ASUSTEK' then s := 'ASUS';
   if (s='BROADCOMM') or (s='WIDCOMM') then s := 'BROADCOM';
   if s='BROTHERMFC' then s := 'BROTHER';
   if (s='CREATIVECC') or (s='CREATIVELABS') then s := 'CREATIVE';
   if (s='DELLWIRELESS') then s := 'DELL';
   if (s='ELANTECH') then s := 'ELAN';
   if (s='ERICSSONSSNDIS') then s := 'ERICSSON';
   if (s='JMCRMODELS') then s := 'JMICRON';
   if (s='LXCZVS') then s := 'LEXMARK';
   if (s='NEC-MITSUBISHI') or (s='NECEL') then s := 'NEC';
   if (s='ULI') then s := 'NVIDIA';
   if (s='QCOMSERIALPORT') then s := 'QUALCOMM';
   if (s='ROBOTICS') then s := 'USR';
   if (s='SINDORICOH') then s := 'RICOH';
   if (s='STANDARD') or (s='(STANDARD') then s := '';
   if (s='U.S.') then s := 'USR';
   if (s='VIATECH') then s := 'VIA';
   if (s='ASMBSW') then s := 'ASUS';
   if (s='CSJ') then s := 'CITIZEN';
   if (s='HSW') then s := 'INTEL';
   if (s='ICI') then s := 'CITIZEN';
   if (s='ZBOARD') then s := 'IZONE';
   if (s='RCUVCAVS') then s := 'RICOH';
   if (s='SD') then s := 'VIEWSONIC'; { ViewSonic SD-Z225 }
   if (s='THUNDERBOLT') then s := 'INTEL';
   if Pos('PHILIPS',s)>0 then s := 'PHILIPS';
   if Pos('BLUECHERRY',s)>0 then s := 'BLUECHERRY';
   if (s='QCOM') then s := 'QUALCOMM';
   if (s='IZONE') then s := 'STEELSERIES';
   if (s='SIBEAM') then s := 'DELL';
   if (s='ARCTOSA') then s := 'RAZER';
   if (s='NXPSEMICONDUCTOR') then s := 'NXP';
   if (s='PERC') then s := 'DELL';
   if (s='CREAF') then s := 'CREATIVE';
   if (s='VAIO') then s := 'SONY';
   if (s='JETFAX') then s := 'HP';
   if (s='BEARPAW') then s := 'MUSTEK';
   if (s='LYCOSA') then s := 'RAZER';
   if (s='AIRLINKS') then s := 'AIRLINK';
   if (s='VIDPID') then s := 'IBALL';
   if (s='ORITE') then s := 'IBALL';
   if (s='PP') then s := 'KENSINGTON';
   if (s='SQ') then s := 'KENSINGTON';
   if (s='ATTANSIC') then s := 'ATHEROS';
   if (s='TALLYDASCOM') then s := 'TALLY';
   if Length(s)<2 then s := '';

   Found := False;
   if Length(s)>0 then
     for i := 1 to Length(s) do
        if Ord(s[i])>122 then Found := True;
   if Found then s := '';

   {if debug then begin
      if Length(s)>0 then begin
        WriteLn('---------------------------------------');
        WriteLn('[LINE]: ' + l);
        WriteLn('[DETECTED]: ' + s);
      end;
    end;}

// MessageBox(GetActiveWindow,PChar(l+#13+s),'Manufacturer',Mb_Ok);
   Result := s;
end;

var
//CurrentDir: string;
List1: TTntStringList;
i,j: integer;
Year: integer;
l,l1: string;
FName,Manufacturers: string;
DriverVer_Found,{REG_EXPAND_FOUND,}SYSWOW_FOUND,SYSWOW64_FOUND: boolean;
label 1;
begin
    debug := false;

   //CurrentDir := GetCurrentDir;
   //CurrentDir := ExtractFilePath(ParamStr(0));
   
   FName := ParamStr(1);
// MessageBox(GetActiveWindow,PChar(FName),'Path to Inf',Mb_Ok);

   if Length(FName)=0 then Exit;

   Os5 := False; Os6 := False; Os7 := False; Os8 := False;
   Arch32 := False; Arch64 := False;
   DriverVer_Found := False; {REG_EXPAND_FOUND := False;}
   SYSWOW_FOUND := False; SYSWOW64_FOUND := False;
   Year := 0; Manufacturers := '';

   AnalyzeOsInf(AnsiUpperCase(FName)); // Анализ имени файла

   List1 := TTntStringList.Create;

   if Length(ParamStr(2))>0 then goto 1; // Т.е. не Inf
   if FileExists(FName)=False then Exit;

   Vendors := TStringList.Create;
   NotVendors1 := TStringList.Create;
   NotVendors2 := TStringList.Create;

   //if FileExists(CurrentDir+'Vendors.txt')
   //then Vendors.LoadFromFile(CurrentDir+'Vendors.txt');
   Vendors.Text := VendorsString;

   //if FileExists(CurrentDir+'NotVendors1.txt')
   //then NotVendors1.LoadFromFile(CurrentDir+'NotVendors1.txt');
   NotVendors1.Text := NotVendors1String;

   //if FileExists(CurrentDir+'NotVendors2.txt')
   //then NotVendors2.LoadFromFile(CurrentDir+'NotVendors2.txt');
   NotVendors2.Text := NotVendors2String;

   List1.LoadFromFile(FName); // Анализ содержимого

   for i := 0 to List1.Count-1 do begin

   l := List1.AnsiStrings[i];
   l := Trim(l);
   if Length(l)<3 then Continue;

   if DriverVer_Found=False then
   if (Pos('DriverVer',l)>0) or (l='[Version]') then begin // Определяем год
      DriverVer_Found := True; l1 := l;
      Delete(l1,1,Pos('/',l1)); Delete(l1,1,Pos('/',l1));
      l1 := Copy(l1,1,4); TryStrToInt(l1,Year);
   end;

   l := AnsiUpperCase(l);

//   if REG_EXPAND_FOUND=False then if Pos('REG_EXPAND_SZ',l)>0 then REG_EXPAND_FOUND := True;
   if SYSWOW_FOUND=False then if (Pos('SYSWOW',l)>0) then SYSWOW_FOUND := True;
   if SYSWOW64_FOUND=False then if (Pos('SYSWOW64',l)>0) or (Pos('_W64',l)>0) then SYSWOW64_FOUND := True;

   AnalyzeOsInf(l);

// MessageBox(GetActiveWindow,PChar(l),'Current string',Mb_Ok);

   if Vendors.Count>0 then
   for j := 0 to Vendors.Count-1 do begin
     if (Pos(Vendors[j]+' ',l)>0) or (Pos(' '+Vendors[j],l)>0)
     or (Pos(Vendors[j]+'=',l)>0) or (Pos('='+Vendors[j],l)>0)
     then
     if (Pos(Vendors[j],Manufacturers)=0) then Manufacturers := Manufacturers+','+Vendors[j];
   end;

   if l='[MANUFACTURER]' then begin
      j := 0; if i<List1.Count-1 then
      Repeat
      Inc(j); l := List1.AnsiStrings[i+j]; l1 := '';
      if (Length(l)>2) and (l[1]<>';') then l1 := AnalyzeVendor(l);
//    MessageBox(GetActiveWindow,PChar(l+#13+l1),'Manufacturer',Mb_Ok);
      if (Length(l1)>1) and (Pos(l1,Manufacturers)=0)
      then Manufacturers := Manufacturers+','+l1;
      if Length(l)=0 then l := ' ';
      Until (l[1]=' ') or (l[1]='[') or (l[1]='=') or (i+j=List1.Count-1);
   end;

   if ((Pos('VENDOR',l)=1) or (Pos('PROVIDER',l)=1) or (Pos('S_PROVIDER',l)=1) or
       (Pos('MANUF',l)=1) or (Pos('DRIVERMFG',l)=1) or (Pos('VER_VENDOR',l)=1) or
       (Pos('COMPANY_NAME',l)=1) or
       (Pos('MFG',l)=1) or (Pos('STDMFG',l)=1) or (Pos('MFGNAME',l)=1) or
       (Pos('_MFG',l)>0) or (Pos('SOURCEDISKNAME',l)=1) or (Pos('INSTDISK',l)=1) or
       (Pos('DISKNAME',l)=1) or (Pos('DISK_NAME',l)=1) or (Pos('DISKSNAME',l)=1) or
       (Pos('DRIVERDISK',l)=1) or (Pos('DISK1',l)=1) or (Pos('DISKID',l)=1) or
       (Pos('DISKDESCRIPTION',l)=1) or (Pos('SOURCEDISK1',l)=1)) and
       ( (Pos('%',l)=0) and (Pos('.DLL',l)=0) and (Pos('.EXE',l)=0) and
         (Pos('=',l)>0) and (Pos('"',l)>0) )
       then begin
       l := AnalyzeVendor(l);
//     MessageBox(GetActiveWindow,PChar(l),'Manufacturer',Mb_Ok);
       if (Length(l)>0) and (Pos(l,Manufacturers)=0) then begin
       if Length(Manufacturers)=0 then Manufacturers := l else Manufacturers := Manufacturers+','+l;
       end;
    end;

   j := Pos('MANUFACTURER:',l);
   if j>0 then begin Delete(l,1,j+13); l := Trim(l);
      j := Pos(' ',l); if j>0 then l := Copy(l,1,j-1);
      if (Length(l)>0) and (Pos(l,Manufacturers)=0) then Manufacturers := Manufacturers+','+l;
   end;

// if Length(Manufacturers)>0 then   
// MessageBox(GetActiveWindow,PChar(l+#13+Manufacturers),'Manufacturers',Mb_Ok);

   if Year=0 then begin // Если год не определился по DriverVer
   if (Pos(' 2001',l)>0) or (Pos('/2001',l)>0) then Year := 2001;
   if (Pos(' 2002',l)>0) or (Pos('/2002',l)>0) then Year := 2002;
   if (Pos(' 2003',l)>0) or (Pos('/2003',l)>0) then Year := 2003;
   if (Pos(' 2004',l)>0) or (Pos('/2004',l)>0) then Year := 2004;
   if (Pos(' 2005',l)>0) or (Pos('/2005',l)>0) then Year := 2005;
   if (Pos(' 2006',l)>0) or (Pos('/2006',l)>0) then Year := 2006;
   if (Pos(' 2007',l)>0) or (Pos('/2007',l)>0) then Year := 2007;
   if (Pos(' 2008',l)>0) or (Pos('/2008',l)>0) then Year := 2008;
   if (Pos(' 2009',l)>0) or (Pos('/2009',l)>0) then Year := 2009;
   if (Pos(' 2010',l)>0) or (Pos('/2010',l)>0) then Year := 2010;
   if (Pos(' 2011',l)>0) or (Pos('/2011',l)>0) then Year := 2011;
   if (Pos(' 2012',l)>0) or (Pos('/2012',l)>0) then Year := 2012;
   end;

   end; // for i := 0 to List1.Count

// MessageBox(GetActiveWindow,PChar(IntToStr(Year)),'Info',Mb_Ok);

   // Натяжка по архитектуре
   if (Os5=True) and (Arch64=False) then begin
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn('Line 816 condition;');
            WriteLn('Detected: Arch32');
        end;
   end;

   // Натяжка по датам
   if (Os5=False) and (Os6=False) and (Os7=False) and (Os8=False) and (Os81=False) and (Os10=False) and (Year>0)
   then begin
     if Year<2006 then begin
        Os5 := True;
        Arch32 := True;
        if debug then begin
            WriteLn('---------------------------------------');
            WriteLn('Line 837 condition;');
            WriteLn('Detected: Os5, Arch32');
        end;
     end;
   end;

   Vendors.Free; NotVendors1.Free; NotVendors2.Free;

1: l := '';
   if (Os5=True) and (Arch32=True) then l := l+'5_32,';
   if (Os6=True) and (Arch32=True) then l := l+'6_32,';
   if (Os7=True) and (Arch32=True) then l := l+'7_32,';
   if (Os8=True) and (Arch32=True) then l := l+'8_32,';
   if (Os81=True) and (Arch32=True) then l := l+'81_32,';
   if (Os10=True) and (Arch32=True) then l := l+'10_32,';

   if (Os5=True) and (Arch64=True) then l := l+'5_64,';
   if (Os6=True) and (Arch64=True) then l := l+'6_64,';
   if (Os7=True) and (Arch64=True) then l := l+'7_64,';
   if (Os8=True) and (Arch64=True) then l := l+'8_64,';
   if (Os81=True) and (Arch64=True) then l := l+'81_64,';
   if (Os10=True) and (Arch64=True) then l := l+'10_64,';

   // If arch found and OS not found - print XP + Vista + 7 + 8 + 8.1
   if Length(l)<3 then begin
       if (Arch32=True) and (Os5=False) and (Os6=False) and (Os7=False) and (Os8=False) and (Os81=False) and (Os10=False)
       then begin
            l := '5_32,6_32,7_32,8_32,81_32,';
            if debug then begin
                WriteLn('---------------------------------------');
                WriteLn('Line 1206 condition;');
                WriteLn('Detected: Os5,Os6,Os7,Os8,Os81,Arch32');
            end;
       end;
       if (Arch64=True) and (Os5=False) and (Os6=False) and (Os7=False) and (Os8=False) and (Os81=False) and (Os10=False)
       then begin
          l := l+'5_64,6_64,7_64,8_64,81_64,';
          if debug then begin
              WriteLn('---------------------------------------');
              WriteLn('Line 1215 condition;');
              WriteLn('Detected: Os5,Os6,Os7,Os8,Os81,Arch64');
          end;
       end;
   end;

   if Length(l)>0 then Delete(l,Length(l),1); // Убираем последнюю запятую

   if Length(Manufacturers)>0 then
   if Manufacturers[1]=',' then Delete(Manufacturers,1,1); // Убираем пeрвую запятую

   if Length(ParamStr(2))=0 then l := l+';'+Manufacturers;

   WriteLn(l);

   List1.Free;
end.
