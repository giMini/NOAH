
Function List-ODBCConfigured {
Param(
        [string]$Key,
        [string]$ComputerName,
        $Credential
    )
    $array = @()
         
    $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $ComputerName -Credential $Credential
    
    $subkeys = $wmi.EnumKey(2147483650,$Key)
    if($subkeys) {
        $remoteSubkeysNames = $subkeys.sNames           
        foreach($subkey in $remoteSubkeysNames){                              
            $thisSubKey=$Key+"\"+$subkey         
            $psObject = New-Object PSObject        
            $psObject | Add-Member -MemberType NoteProperty -Name "ComputerName" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"$computername")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DSN" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"dsn")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Server" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Server")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Port" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Port")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DatabaseFile" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DatabaseFile")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DatabaseName" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DatabaseName")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "UID" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"UID")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "PWD" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"PWD")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Start" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Start")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "LastUser" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"LastUser")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Database" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Database")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DefaultLibraries" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DefaultLibraries")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DefaultPackage" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DefaultPackage")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DefaultPkgLibrary" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DefaultPkgLibrary")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "System" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"System")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Driver" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Driver")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Description" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Description")).sValue)

            $array += $psObject            
        }             
    }

    $array   
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'listODBCInstalled' - get ODBC connections installed 
# ________________________________________________________________________
Function listODBCInstalled ($odbcDriversInstalled) {
    $computername = $strComputer 
    $arrayInstalled = @()       
    $remoteBaseKeyObject = [microsoft.win32.registrykey]::OpenRemoteBaseKey('LocalMachine',$computername)     
    $remoteBaseKey = $remoteBaseKeyObject.OpenSubKey($odbcDriversInstalled)             
    $subKeys = $remoteBaseKey.GetSubKeyNames()            
    foreach($key in $subKeys){            
        $thisKey=$odbcDriversInstalled+"\\"+$key          
        $thisSubKey=$remoteBaseKeyObject.OpenSubKey($thisKey)         
        $psObjectInstalled = New-Object PSObject
        $psObjectInstalled | Add-Member -MemberType NoteProperty -Name "ComputerName" -Value $computername
        $psObjectInstalled | Add-Member -MemberType NoteProperty -Name "Driver" -Value $($thisSubKey.GetValue("Driver"))
        $psObjectInstalled | Add-Member -MemberType NoteProperty -Name "DriverODBCVer" -Value $($thisSubKey.GetValue("DriverODBCVer"))
        $psObjectInstalled | Add-Member -MemberType NoteProperty -Name "FileExtns" -Value $($thisSubKey.GetValue("FileExtns"))
        $psObjectInstalled | Add-Member -MemberType NoteProperty -Name "Setup" -Value $($thisSubKey.GetValue("Setup"))
        $arrayInstalled += $psObjectInstalled
    }           
    $arrayInstalled    
}