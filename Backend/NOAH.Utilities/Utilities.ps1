# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Clean-CharacterChain' remove spaces, quotes and accents 
# from a string
# ________________________________________________________________________
Function Clean-CharacterChain {
param ([String]$src = [String]::Empty)
    $normalized = $src.Normalize( [Text.NormalizationForm]::FormD )
    $normalized = $normalized.replace(" ","")    
    $normalized = $normalized.replace("'","")    
    $sb = new-object Text.StringBuilder
    $normalized.ToCharArray() | % { 
        if( [Globalization.CharUnicodeInfo]::GetUnicodeCategory($_) -ne [Globalization.UnicodeCategory]::NonSpacingMark) {
            [void]$sb.Append($_)
        }
    }

    return $sb.ToString()        
}

# Run-WmiRemoteProcess
Function Run-WmiRemoteProcess
{
    Param(
        [string]$ComputerName=$env:COMPUTERNAME,
        [string]$Cmd=$(Throw "You must enter the full path to the command which will create the process."),
        [int]$TimeOut = 0,
        $Credentials
    )
 
    Write-Output "Process to create on $computername is $cmd"

    $co = new-object management.connectionoptions 
    
    $co.Username = $Credentials.UserName
    $co.SecurePassword = $Credentials.Password

    $scope = new-object management.managementscope "\\$ComputerName\root\cimv2",$co
    $scope.Connect()
    $mp = new-object management.managementpath "win32_process"
    $ogo = new-object management.objectgetoptions
    $wmiProcess = new-object management.managementclass $scope,$mp,$ogo
       
    # Exit if the object didn't get created
    if (!$wmiProcess) {return}
 
    try{        
        $remote = $wmiProcess.Create($cmd)
    }
    catch{
        $_.Exception
    }    
    if ($remote.returnvalue -eq 0) {
        Write-Debug ("Successfully launched $cmd on $computername with a process id of " + $remote.ProcessId)
        Write-Debug "Waiting completion..."
        do {
            (Write-Output "Waiting...")
            (Start-Sleep -Milliseconds 250)
        }
        while (Get-WMIobject -Class Win32_process -ComputerName $computername -Credential $Credentials | where ProcessID -eq $($remote.ProcessId))
        Write-Debug "$($remote.ProcessId) completed!"
    } else {
        Write-Debug ("Failed to launch $cmd on $computername. ReturnValue is " + $remote.ReturnValue)
    }

}

function Get-NameFromSid
{
    Param (
        [String]$currentSid
    )
 
    $objSID = $null
    $objUser = $null
 
    try {
        $sid = $currentSid.Replace("`*","")
        $objSID = New-Object System.Security.Principal.SecurityIdentifier ($sid)
        $objUser = $objSID.Translate( [System.Security.Principal.NTAccount])
        #Write-Output "SID $sid translated to $objUser.Value" 
        return "$($objUser.Value);"
    } catch {
        #Write-Output "SID $sid could not be translated" 
        return "$currentSid;"
    }
}
 
# to find the index of an element in an array
function Get-IndexOf {
    Param (
        [object[]]$array, $element
    )
 
    $line = 0..($array.length - 1) | where {$array[$_] -eq $element}
    return $line
}

 
Function Write-Log {
    [CmdletBinding()]  
    Param ([Parameter(Mandatory=$true)][string]$streamWriter, [Parameter(Mandatory=$true)][string]$infoToLog)  
    Process{    
        $global:streamWriter.WriteLine("$infoToLog")
    }
}

Function Write-Error {
    [CmdletBinding()]  
    Param ([Parameter(Mandatory=$true)][string]$streamWriter, [Parameter(Mandatory=$true)][string]$errorCaught, [Parameter(Mandatory=$true)][boolean]$forceExit)  
    Process{
        $global:streamWriter.WriteLine("Error: [$errorCaught]")        
        if ($forceExit -eq $true){
            End-Log -streamWriter $global:streamWriter
            break;
        }
    }
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Get-LocalUsersInGroup' - get local users in groups 
# ________________________________________________________________________
function Get-LocalUsersInGroup {
Param(
        [string]$ComputerName,
        $Credential
    )
    if($saveIntDomainRole -le 3) {
        $user = $Credential.UserName
        $pass = $($Credential.GetNetworkCredential().password)

        # Create an ADSI Search    
        $serverADSIObject = New-Object System.DirectoryServices.DirectoryEntry("WinNT://$ComputerName",$user,$pass)
        # Limit the output to 50 objects
        $serverADSIObject.SizeLimit = '50'

        # Add the Domain to the search
        # $serverADSIObject.SearchRoot = $DomainEntry

        # Execute the Search
        $serverADSIObject.FindAll()

        $serverADSIObject = [ADSI]"WinNT://$ComputerName,computer"
        $localUserinGroups=@()
        $serverADSIObject.psbase.children | Where { $_.psbase.schemaClassName -eq 'group' } |`
            foreach {
                $group =[ADSI]$_.psbase.Path
                $group.psbase.Invoke("Members") | `
                foreach {$localUserinGroups += New-Object psobject -property @{Group = $group.Name;User=(($_.GetType().InvokeMember("Adspath", 'GetProperty', $null, $_, $null)) -replace "WinNT://","")}}
            }
    }
    else {
        $localUserinGroups = @()
    }
    $localUserinGroups
}
 
# Parse the text file from the secdump and outputs an array of policies
function Parse-SecdumpFileToObject {
    Param (
        [String]$file
    )
 
    # The array that will be returned
    $policies = @()
 
    # put the text file to an array
    $fileContent = Get-Content $file
 
    # Find the delimitations of the security policies
    $start = IndexOf $fileContent "[Privilege Rights]"
    $end = IndexOf $fileContent "[Version]"
    if($end -lt $start) {
        $end = $fileContent.Length
    }
 
    # Extract the security policies between those delimitations
    For ($i = $start+1; $i -lt $end; $i++) {
        $policy = New-Object Object
        $line = $fileContent[$i].split(" =")
 
        # Add policy name to the policy
        Add-Member -memberType NoteProperty -name name -value $line[0] -inputObject $policy
        # Extract array of members, translate the SIDs, and add the members array to the policy
        $members = $line[3].split(",")        
        For ($j = 0; $j -lt $members.Count; $j++) {
            if ($members[$j] -like "``**") {
                $members[$j] = Get-NameFromSid $members[$j]                           
            }
            else {
               $members[$j] = "$($members[$j]);"
            }
        }
        Add-Member -memberType NoteProperty -name members -value $members -inputObject $policy
 
        # Add the policy to the "policies" array
        $policies += $policy
    }
    return $policies
}

function Get-HuntCredential {
<#  
    .SYNOPSIS  
        Get the credential to connect to servers to Hunt
    .DESCRIPTION              
        
    .EXAMPLE          
#> 
    Param (
        [String]$User,
        [String]$PasswordFile,
        [String]$KeyFile
    )
    $key = Get-Content $KeyFile        
    $deploymentCredential = New-Object -TypeName System.Management.Automation.PSCredential -ArgumentList $User, (Get-Content $passwordFile | ConvertTo-SecureString -Key $key)
    return $deploymentCredential
}

function Set-RegistryKey {
<#
.SYNOPSIS
    Set a setting in the registry
    Author: Pierre-Alexandre Braeken (@pabraeken)
    License: BSD 3-Clause
    Required Dependencies: None
    Optional Dependencies: None 

.DESCRIPTION
    Set-RegistryKey allows for the configuration of a registry setting

.PARAMETER computername

.PARAMETER parentKey

.PARAMETER nameRegistryKey

.PARAMETER valueRegistryKey
    
.EXAMPLE
    C:\PS> Set-RegistryKey "Server1" "SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Policies\\System" "LocalAccountTokenFilterPolicy" "1"
#>
    Param (
       $computername, 
       $parentKey, 
       $nameRegistryKey, 
       $valueRegistryKey
    )
    try{    
        $remoteBaseKeyObject = [microsoft.win32.registrykey]::OpenRemoteBaseKey('LocalMachine',$computername)     
        $regKey = $remoteBaseKeyObject.OpenSubKey($parentKey,$true)
        $regKey.Setvalue("$nameRegistryKey", "$valueRegistryKey", [Microsoft.Win32.RegistryValueKind]::ExpandString) 
        $remoteBaseKeyObject.close()
    }
    catch {
        $_.Exception
    }
}