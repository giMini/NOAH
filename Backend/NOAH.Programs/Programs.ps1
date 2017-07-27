
# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'List-ProgramsInstalled' - get info by WMI request 
# ________________________________________________________________________
Function List-ProgramsInstalled {
Param(
        [string]$UninstallKey,
        [string]$ComputerName,
        $Credential
    )
    $array = @()
         
    $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $ComputerName -Credential $Credential
    
    $subkeys = $wmi.EnumKey(2147483650,$UninstallKey)
    if($subkeys) {
        $remoteSubkeysNames = $subkeys.sNames           
        foreach($key in $remoteSubkeysNames){                              
            $thisSubKey=$UninstallKey+"\"+$key         
            $psObject = New-Object PSObject        
            $psObject | Add-Member -MemberType NoteProperty -Name "DisplayName" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DisplayName")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DisplayVersion" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DisplayVersion")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "InstallLocation" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"InstallLocation")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "Publisher" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"Publisher")).sValue)
            $psObject | Add-Member -MemberType NoteProperty -Name "DisplayIcon" -Value $(($wmi.GetStringValue(2147483650,$thisSubKey,"DisplayIcon")).sValue)
            $array += $psObject            
        }             
    }

    $array   
}