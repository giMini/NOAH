<#

#requires -version 4

.SYNOPSIS         
    Name : No Agent Hunting (NOAH)        
    
    * Select list of servers from a CSV file with an OpenFileDialog
    * Get remotely Servers informations with WMI and Powershell :
    * General (Domain, role in the domain, hardware manufacturer, type and model, cpu number, memory capacity, operating system and sp level)
    * System (BIOS name, BIOS version, hardware serial number, time zone, WMI version, virtual memory file location, virtual memory current usage, virtual memory peak usage and virtual memory allocated)
    * Processor (Processor(s), processor type, family, speed in Mhz, cache size in GB and socket number)
    * Memory (Bank number, label, capacity in GB, form and type)
    * Disk (Disk type, letter, capacity in GB, free space in GB + display a chart Excel)
    * Network (Network card, DHCP enable or not, Ip address, subnet mask, default gateway, Dns servers, Dns registered or not, primary and secondary wins and wins lookup or not) 
    * Installed Programs (Display name, version, install location and publisher) 
    * Share swith NTFS rights (Share name, user account, rights, ace flags and ace type) 
    * Services (Display name, name, start by, start mode and path name)
    * Scheduled Tasks (Name, last run time, next run time and run as)
    * Printers (Locationm, name, printer state and status, share name and system name)
    * Process (Name, Path and sessionID)
    * Local Users (Groups, users)
    * ODBC Configured (dsn, Server, Port, DatabaseFile, DatabaseName, UID, PWD, Start, LastUser, Database, DefaultLibraries, DefaultPackage, DefaultPkgLibrary, System, Driver, Description)
    * ODBC Drivers Installed (Driver, DriverODBCVer, FileExtns, Setup)
    * Operating System Privileges (Strategy, SecurityParameters)   
    * MB to GB conversion
    * Display of the progress of the script
    * Add hunting ID to every tables
    * add SHA1, MD5 hash for tasks and in memory processes -> updated to SHA256
    * Netstat with binaries handle
    * Autoruns for persistence mechanisms
    * RecentFileCache, AmCache and ShimCache parsing
    * Process Tree and statistical approach in the entire network
    * Prefetch Files
    * Browser History (IE, Chrome, Firefox)
    * Collect full memory dump remotely
    * Shellbags
    * RecentDocs
    * DNSCache
    * .LNK Files
    * User profiles
    * USB Forensic
    * Explorer bar

.TODO
    * How many processes are running from critical directories with non-standard RIDs?
    * Have additional “features” been added to critical operating system files?
    * statistical approach for binaries in the entire network
    * integrate DeepBlue

.INPUT
    .csv file with servers to hunt

.OUTPUTS
    Console outputs
    Log file 
    Database
    Artifacts

.NOTES
    Version:        0.1
    Author:         Pierre-Alexandre Braeken
  
.EXAMPLE
    .\NOAH-Backend.ps1

. HELP

Microsoft created the ShimCache, or "AppCompatCache" to identify application compatibility issues. The cache data tracks file path, size, 
last modified time, and last "execution" time (depending on OS). If a file is executed with Windows "createprocess," it is logged in the ShimCache. 
While a file's presence in the ShimCache does not 100% prove file execution, it does show Windows interacted with the file. 

The Shim Cache contains references to numerous programs over an extended period of time. 
The RecentFileCache.bcf file on the other hand only contained references to programs that recently executed. 
The reason for this is because the RecentFileCache.bcf file is a temporary storage location used during the process creation. 
It appears this storage location is not used during all process creation; it's mostly used for those processes that spawned from 
executables which were recently copied or downloaded to the system.

The RecentFileCache.bcf file is another artifact that shows program execution. I have found this artifact helpful when 
investigating systems shortly after they became infected. The artifact is a quick way to locate malware - such as droppers 
and downloaders -  on the system as I briefly mentioned in the post Triaging Malware Incidents. The relevance of the executables 
listed in this artifact mean the following:

1.  The program is probably new to the system.
2.  The program executed on the system.
3.  The program executed on the system some time after the ProgramDataUpdater task was last ran. 

In the Windows 8 operating system the RecentFilceCache.bcf has been replaced by a registry hive named Amcache.hve.

< 8 : RecentFileCache.bcf 
> 7 : amcache (<DRIVE>\Windows\AppCompat\Programs\Amcache.hve)


PREFETCH

Prefetch is disabled by default on Windows Server

To enable it on Windows Server 2012:

reg add "HKEY_LOCAL_MACHINE\SYSTEM\CurrentControlSet\Control\Session Manager\Memory Management\PrefetchParameters" /v EnablePrefetcher /t REG_DWORD /d 3 /f
reg add "HKEY_LOCAL_MACHINE\Software\Microsoft\Windows NT\CurrentVersion\Prefetcher" /v MaxPrefetchFiles /t REG_DWORD /d 8192 /f
Enable-MMAgent –OperationAPI
net start sysmain

#>

Param(
[switch] $All,
[switch] $SystemToHunt,
[switch] $Processor,
[switch] $Memory,
[switch] $Disk,
[switch] $Network,
[switch] $InstalledPrograms,
[switch] $Shares,
[switch] $Services,
[switch] $ScheduledTasks,
[switch] $Printers,
[switch] $Process,
[switch] $ProcessTree,
[switch] $LocalUsers,
[switch] $ODBCConfigured,
[switch] $ODBCInstalled,
[switch] $OperatingSystemPrivileges,
[switch] $Netstat,
[switch] $DNSCache,
[switch] $LINKFile,
[switch] $ExplorerBar,
[switch] $RunMRU, # Most Recently Used
[switch] $USBHistory,
[switch] $MassStorage,
[switch] $Autoruns,
[switch] $AMCache, # > Windows 8
[switch] $EZ,
[switch] $ShimCache, # > Windows 7
[switch] $RecentFileCache, # < Windows 8
[switch] $RecentDocs,
[switch] $Prefetch,
[switch] $BrowserHistory,
[switch] $UserProfiles,
[switch] $EnableHash,
[switch] $VT,
[switch] $CollectFullMemoryDump,
[string] $HuntDescription = 'No description'
)

#---------------------------------------------------------[Initialisations]--------------------------------------------------------
$ErrorActionPreference = "SilentlyContinue"
$scriptPath = split-path -parent $MyInvocation.MyCommand.Definition
$scriptParentPath = split-path -parent $scriptPath
$scriptFile = $MyInvocation.MyCommand.Definition
$launchDate = get-date -f "yyyyMMddHHmmss"
$launchDateFiles = $launchDate
$logDirectoryPath = $scriptPath + "\NOAH.Logs\" + $launchDate
$file = "$logDirectoryPath\lsass.dmp"
$buffer = "$scriptPath\bufferCommand.txt"
$fullScriptPath = (Resolve-Path -Path $buffer).Path

$scriptPath = split-path -parent $myInvocation.MyCommand.Definition

#----------------------------------------------------------[Declarations]----------------------------------------------------------

$sScriptName = "NOAH-Backend"
$sScriptVersion = "0.1"

$launchDate = get-date -f "yyyyMMdd"
$sLogPath = $scriptPath + "\NOAH.Logs\" + $launchDate
$logDate = get-date -f "yyyyMMddHHmm"

if(!(Test-Path $logDirectoryPath)) {
    New-Item $logDirectoryPath -type directory | Out-Null
}

$logFileName = "NOAH_" + $launchDate + ".log"
$logPathName = "$logDirectoryPath\$logFileName"

$global:streamWriter = New-Object System.IO.StreamWriter $logPathName

$returnValue = ""

$loggingFunctions = "$scriptPath\NOAH.Logging\Logging.ps1"
$importFunctions = "$scriptPath\NOAH.Utilities\Import.ps1"
$parallelFunctions = "$scriptPath\NOAH.Parallel\Invoke-Parallel.ps1"
$databaseFunctions = "$scriptPath\NOAH.Database\Database.ps1"
$utilityFunctions = "$scriptPath\NOAH.Utilities\Utilities.ps1"

. $loggingFunctions
. $importFunctions
. $parallelFunctions
. $databaseFunctions
. $utilityFunctions

#-----------------------------------------------------------[Execution]------------------------------------------------------------

Start-Log -scriptName $sScriptName -scriptVersion $sScriptVersion -streamWriter $global:streamWriter

$ServerName = ''
$UserName = 'Powned\Administrator'

$user = "POWNED\Administrator"
$passwordFile = "C:\temp\PoshPortal\Keys\autoPassword.txt"
$keyFile = "C:\temp\PoshPortal\Keys\secureKey.key"
$Credential = Get-HuntCredential -User $user -PasswordFile $passwordFile -KeyFile $keyFile 

# open database connection

$DatabaseUserAdmin = "NOAHAdmin"
$DatabasePasswordFile = "C:\temp\PoshPortal\Keys\autoPasswordDatabase.txt"
$DatabaseKeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
$DatabaseCredential = Get-HuntCredential -User $DatabaseUserAdmin -PasswordFile $DatabasePasswordFile -KeyFile $DatabaseKeyFile 

$connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
$sqlConnection = Connect-Database $connString $DatabaseCredential
Write-Log -streamWriter $global:streamWriter -infoToLog "Database connection to $connString"
$sqlConnection.Open()
$sqlCommand = $sqlConnection.CreateCommand()
Write-Log -streamWriter $global:streamWriter -infoToLog "SqlCommand objec created"
$colComputers = ListFile	
$computerCount = ($colComputers | measure).Count
Write-Log -streamWriter $global:streamWriter -infoToLog "List of $computerCount computers to query collected" #`n"

$serversNotResponding = ""
$nbError = 0
$nbSuccess = 0
$nbTot = $computerCount 

$huntingGUID = [guid]::NewGuid()
$huntDateTime = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
$huntingInsert =  "INSERT INTO Hunt (huntingGUID,huntingDate,huntingState,huntingComputerNumber,huntingDescription) 
                    OUTPUT INSERTED.huntingID
                    VALUES ('$huntingGUID','$huntDateTime','0','$nbTot','$HuntDescription');"            
$nbHunt = Insert-IntoDatabaseRecordLastID $sqlCommand $huntingInsert
$sqlConnection.Close()        
Write-Log -streamWriter $global:streamWriter -infoToLog "$huntingInsert"

[System.Collections.ArrayList]$AllComputers = @()

foreach($Computer in $colComputers)
{
    $AllComputers.add($($Computer.ServerName)) | Out-Null
}

$params = @($nbHunt,$All,$SystemToHunt,$Processor,$Memory,$Disk,$Network,$InstalledPrograms,$Shares,$Services,$ScheduledTasks,$Printers,
        $Process,$ProcessTree,$LocalUsers,$ODBCConfigured,$ODBCInstalled,$OperatingSystemPrivileges,$Netstat,$DNSCache,$LINKFile,$ExplorerBar,
        $RunMRU,$USBHistory,$MassStorage,$Autoruns,$AMCache,$EZ,$ShimCache,$RecentFileCache,$RecentDocs,$Prefetch,$EnableHash,$global:streamWriter,
        $launchDateFiles,$scriptPath,$BrowserHistory,$UserProfiles,$VT,$CollectFullMemoryDump)

 if($VT) { 
    $intelligenceFunctions = "$scriptPath\NOAH.Intelligence\Get-VT.ps1"
. $intelligenceFunctions

    $connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
    $sqlConnection = Connect-Database $connString $DatabaseCredential
    Write-Log -streamWriter $global:streamWriter -infoToLog "Database connection to $connString"
    $sqlConnection.Open()
    $sqlCommand = $sqlConnection.CreateCommand()
    Write-Log -streamWriter $global:streamWriter -infoToLog "SqlCommand objec created"
    $apikey = "6c6d93580478620a7b3d5c1f2255214159f2d6e327859e3d53c71d3216ba2f8e"
    $allowedByMinute = 1 
    $queryVT = "Select hash as resource FROM [NOAH].[dbo].[ProcessTreeAudited]"
    $resVT = Select-FromDatabase($queryVT)   
    $resVT.type = 1 
    $queryVT = "Select MD5 as resource FROM [NOAH].[dbo].[AutorunAudited]"
    $resVT2 = Select-FromDatabase($queryVT)
    $queryVT = "Select hash as resource FROM [NOAH].[dbo].[ScheduledTaskAudited]"
    $resVT3 = Select-FromDatabase($queryVT)
    $queryVT = "Select SHA1 as resource FROM [NOAH].[dbo].[AmcacheAudited]"
    $resVT4 = Select-FromDatabase($queryVT)
    $resVT = $resVT + $resVT2 + $resVT3 + $resVT4    
    if($resVT){        
        $resVT = $resVT.resource | Sort-Object | Get-Unique
        $resVT = $resVT.Split('`n')           
        foreach ($md5Hash in $resVT){            
            if($md5Hash -ne ''){                
                $queryMD5 = "Select * FROM [NOAH].[dbo].[SuspiciousElement] WHERE resource LIKE '$md5Hash'"
                $resMD5 = Select-FromDatabase($queryMD5)
                if(!$resMD5){
                    #Write-Output "$($proc.Name) $($proc.md5) $($proc.Path) analyzing"
                    Write-Output "Analyzing $md5Hash"
                    $response = Post-Http -Url "https://www.virustotal.com/vtapi/v2/file/report" -Parameters "resource=$($md5Hash)&apikey=$apikey"
                    #Validate-Response -Name $($proc.Name) -Path $($proc.Path) -StreamWriter $logPathName
                    $responseObject = Validate-Response $response                
                    #$queryUpdate = "UPDATE [NOAH].[dbo].[ProcessTreeAudited] set VT = $($responseObject.Result), Permalink = '$($responseObject.Permalink)' WHERE md5 LIKE '$md5Hash'"                
                    $queryUpdate = "INSERT INTO [NOAH].[dbo].[SuspiciousElement] (resource, VT, Permalink) VALUES ('$md5Hash', $($responseObject.Result), '$($responseObject.Permalink)')"                
                    Insert-IntoDatabase $sqlCommand $queryUpdate
                    if ($allowedByMinute -eq 4) {   
                        Write-Output "Waiting 1 minute"     
                        Start-Sleep -seconds 60
                        $allowedByMinute = 0
                    }
                    $allowedByMinute++
                }
            }
        }
    }
    $sqlConnection.Close()
}
    
$splat = @{
    Throttle = 100
    RunspaceTimeout = 1000
    InputObject = $AllComputers   
    parameter = $params
    NoCloseOnTimeout = $True
}

#$colComputers | Invoke-Parallel -Throttle 100 -in {
Invoke-Parallel -Quiet @splat -ScriptBlock {
    #foreach ($strComputer in $colComputers){  
    #Set Error Action to Silently Continue
    $ErrorActionPreference = "SilentlyContinue"

    $global:streamWriter = $parameter[33]    
    $launchDateFiles = $parameter[34]    
    $scriptPath = $parameter[35]    

    $tc = [System.Management.ManagementDateTimeconverter] 

    $start =$tc::ToDmtfDateTime((Get-Date).AddDays(-1).Date) 

    # https://msdn.microsoft.com/en-us/library/aa390440(v=vs.85).aspx
    $regKey = @{"HKEY_CLASSES_ROOT" = 2147483648; "HKEY_CURRENT_USER" = 2147483649; "HKEY_LOCAL_MACHINE" = 2147483650; "HKEY_USERS" = 2147483651; "HKEY_CURRENT_CONFIG" = 2147483653;}
    $regType = @{"REG_SZ" = 1; "REG_EXPAND_SZ" = 2; "REG_BINARY" = 3; "REG_DWORD" = 4; "REG_MULTI_SZ" = 7; "REG_QWORD" = 11;}

    #-----------------------------------------------------------[Functions]------------------------------------------------------------

    $databaseFunctions = "$scriptPath\NOAH.Database\Database.ps1"
    $prefetchFunctions = "$scriptPath\NOAH.Prefetch\Prefetch.ps1"
    $processFunctions = "$scriptPath\NOAH.Process\ProcessTree.ps1"
    $registryFunctions = "$scriptPath\NOAH.Registry\Registry.ps1"
    $utilityFunctions = "$scriptPath\NOAH.Utilities\Utilities.ps1"
    $USBForensicFunctions = "$scriptPath\NOAH.USB\USBForensic.ps1"
    $ODBCFunctions = "$scriptPath\NOAH.ODBC\ODBC.ps1"
    $NTFSFunctions = "$scriptPath\NOAH.NTFS\NTFS.ps1"
    $NetworkFunctions = "$scriptPath\NOAH.Network\NetworkStatistics.ps1"
    $recentFunctions = "$scriptPath\NOAH.Recent\Recent.ps1"
    $programsFunctions = "$scriptPath\NOAH.Programs\Programs.ps1"
    $tasksFunctions = "$scriptPath\NOAH.Tasks\Tasks.ps1"    

. $databaseFunctions
. $prefetchFunctions
. $processFunctions
. $registryFunctions
. $utilityFunctions
. $USBForensicFunctions
. $ODBCFunctions
. $NTFSFunctions
. $NetworkFunctions
. $recentFunctions
. $programsFunctions
. $tasksFunctions

    $ServerName = ''
    $UserName = 'Powned\Administrator'    
    $user = "POWNED\Administrator"
    $passwordFile = "C:\temp\PoshPortal\Keys\autoPassword.txt"
    $keyFile = "C:\temp\PoshPortal\Keys\secureKey.key"
    $Credential = Get-HuntCredential -User $user -PasswordFile $passwordFile -KeyFile $keyFile 

    $UserNameSchedTasks = 'Administrator'
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($Credential.Password)
    $UnsecurePassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    $passwordSchedTasks = $UnsecurePassword
    $domainName = 'powned.com'

    $DatabaseUserAdmin = "NOAHAdmin"
    $DatabasePasswordFile = "C:\temp\PoshPortal\Keys\autoPasswordDatabase.txt"
    $DatabaseKeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
    $DatabaseCredential = Get-HuntCredential -User $DatabaseUserAdmin -PasswordFile $DatabasePasswordFile -KeyFile $DatabaseKeyFile     

    $connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
    $sqlConnection = Connect-Database $connString $DatabaseCredential
    Write-Log -streamWriter $global:streamWriter -infoToLog "Database connection to $connString"
    $sqlConnection.Open()
    $sqlCommand = $sqlConnection.CreateCommand()
    Write-Log -streamWriter $global:streamWriter -infoToLog "SqlCommand objec created"

    $strComputer = $_.trim()    
    $items = ""
    $queryText = "Select count(*) as recordCount FROM ServerAudited"
    $nbServer = Count-Record($queryText)

   # $queryText = "Select count(*) as recordCount FROM Hunt"
   # $nbHunt = Count-Record($queryText)
    $nbHunt = $parameter[0]
    $All = $parameter[1]    
    $SystemToHunt = $parameter[2]
    $Processor = $parameter[3]
    $Memory = $parameter[4]
    $Disk = $parameter[5]
    $Network = $parameter[6]
    $InstalledPrograms = $parameter[7]
    $Shares = $parameter[8]
    $Services = $parameter[9]
    $ScheduledTasks = $parameter[10]
    $Printers = $parameter[11]
    $Process = $parameter[12]
    $ProcessTree = $parameter[13]
    $LocalUsers = $parameter[14]
    $ODBCConfigured = $parameter[15]
    $ODBCInstalled = $parameter[16]
    $OperatingSystemPrivileges = $parameter[17]
    $Netstat = $parameter[18]
    $DNSCache = $parameter[19]
    $LINKFile = $parameter[20]
    $ExplorerBar = $parameter[21]
    $RunMRU = $parameter[22]
    $USBHistory = $parameter[23]
    $MassStorage = $parameter[24]
    $Autoruns = $parameter[25]
    $AMCache = $parameter[26]
    $EZ = $parameter[27]
    $ShimCache = $parameter[28]
    $RecentFileCache = $parameter[29]
    $RecentDocs = $parameter[30]
    $Prefetch = $parameter[31]
    $EnableHash = $parameter[32]
    $BrowserHistory = $parameter[36]
    $UserProfiles = $parameter[37]    
    $CollectFullMemoryDump = $parameter[39]
       
    $serverName = $strComputer
    Write-Output "Getting general information ($strComputer)<br>"                 
    $items = gwmi Win32_ComputerSystem -Comp $strComputer -Credential $Credential | Select-Object Domain, DomainRole, Manufacturer, Model, SystemType, NumberOfProcessors, TotalPhysicalMemory 
    if($items) {
        $domain = $items.Domain
        $domainRole = $items.DomainRole
        $manufacturer = $items.Manufacturer
        $model = $items.Model
        $systemType = $items.SystemType
        $numberOfProcessors = $items.NumberOfProcessors
        $totalPhysicalMemory = [math]::round(($items.TotalPhysicalMemory)/1024/1024/1024, 0)    
        Write-Output "Getting systems information ($strComputer)<br>"
        $items = ""
        $items = gwmi Win32_OperatingSystem -Comp $strComputer -Credential $Credential | Select-Object Caption, csdversion   
        $operatingSystem = $items.Caption
        $servicePackLevel = $items.csdversion
        $items = ""
        $items = gwmi Win32_BIOS -Comp $strComputer -Credential $Credential | Select-Object Name, SMBIOSbiosVersion, SerialNumber
        $biosName = $items.Name
        $biosVersion = $items.SMBIOSbiosVersion
        $hardwareSerial = $items.SerialNumber
        $items = ""
        $items = gwmi Win32_TimeZone -Comp $strComputer -Credential $Credential | Select-Object Caption
        $timeZone = $items.Caption
        $items = ""
        $items = gwmi Win32_WmiSetting -Comp $strComputer -Credential $Credential | Select-Object BuildVersion    
        $wmiVersion = $items.BuildVersion             	      
        $items = ""
        $items = gwmi Win32_PageFileUsage -Comp $strComputer -Credential $Credential | Select-Object Name, CurrentUsage, PeakUsage, AllocatedBaseSize    
        $virtualMemoryName = $items.Name
        $virtualMemoryCurrentUsage = $items.CurrentUsage
        $virtualMermoryPeakUsage = $items.PeakUsage
        $virtualMemoryAllocatedBaseSize = $items.AllocatedBaseSize

        $saveIntDomainRole = $domainRole

        Switch($domainRole) {
            0{$domainRole = "Stand Alone Workstation"}
            1{$domainRole = "Member Workstation"}
            2{$domainRole = "Stand Alone Server"}
            3{$domainRole = "Member Server"}
            4{$domainRole = "Back-up Domain Controller"}
            5{$domainRole = "Primary Domain Controller"}
            default{"Undetermined"}
        }        
     
        $serverQueryInsert = "INSERT INTO ServerAudited (huntingID,serverName,domain,role,HW_Make,HW_Model,HW_Type,cpuCount,memoryGB,operatingSystem,servicePackLevel,
                    biosName,biosVersion,hardwareSerial,timeZone,wmiVersion,virtualMemoryName,virtualMemoryCurrentUsage,virtualMermoryPeakUsage,
                    virtualMemoryAllocatedBaseSize) 
                    OUTPUT INSERTED.serverID
                    VALUES('$nbHunt','$serverName','$domain','$domainRole','$manufacturer','$model','$systemType','$numberOfProcessors',
                    '$totalPhysicalMemory','$operatingSystem','$servicePackLevel','$biosName','$biosVersion','$hardwareSerial','$timeZone','$wmiVersion','$virtualMemoryName',
                    '$virtualMemoryCurrentUsage','$virtualMermoryPeakUsage','$virtualMemoryAllocatedBaseSize'); SELECT SCOPE_IDENTITY()"
        Write-Output "Inserting server information ($strComputer)<br>"          
        $nbServer = Insert-IntoDatabaseRecordLastID $sqlCommand $serverQueryInsert
        Write-Log -streamWriter $global:streamWriter -infoToLog "$serverQueryInsert"

        New-PSDrive -Name Y -PSProvider filesystem -Root "\\$strComputer\c$" -Credential $Credential | Out-Null

        if($Processor -or $All) {
            Write-Output "Getting processor information ($strComputer)<br>"     
            $items = ""
            $items = gwmi Win32_Processor -Comp $strComputer -Credential $Credential | Select-Object DeviceID, Name, Description, family, currentClockSpeed, l2cacheSize, UpgradeMethod, SocketDesignation
            Write-Output "Inserting processor information ($strComputer)<br>"  
            foreach($item in $items) {
                $deviceLocator = $item.DeviceID
                $processorName = $item.Name
                $processorDescription = $item.Description
                $processorFamily = $item.family
                $currentClockSpeed = $item.currentClockSpeed
                $l2cacheSize = $item.l2cacheSize
                $upgradeMethod = $item.UpgradeMethod
                $socketDesignation = $item.SocketDesignation
                $processorQueryInsert =  "INSERT INTO ProcessorAudited (serverID,Name,TypeP,Family,Speed,CacheSize,Interface,SocketNumber) VALUES
                                    ('$nbServer','$deviceLocator','$processorName','$processorFamily','$currentClockSpeed','$l2cacheSize','$upgradeMethod','$socketDesignation')"            
                Insert-IntoDatabase $sqlCommand $processorQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$processorQueryInsert"            
            }
        }

        if($Memory -or $All) {  
            Write-Output "Getting memory information ($strComputer)<br>"
            $items = ""
            $items = gwmi Win32_PhysicalMemory -Comp $strComputer -Credential $Credential | Select-Object DeviceLocator, Capacity, FormFactor, TypeDetail
            Write-Output "Inserting memory information ($strComputer)<br>"
            foreach($item in $items) {
                $deviceLocator = $item.DeviceLocator
                $capacity = [math]::round(($item.Capacity)/1024/1024/1024, 0)
                $formFactor = $item.FormFactor
                $typeDetail = $item.TypeDetail
                $memoryQueryInsert = "INSERT INTO MemoryAudited (serverID,Label,Capacity,Form,TypeM) VALUES ('$nbServer','$deviceLocator','$capacity','$formFactor','$typeDetail')"
                Insert-IntoDatabase $sqlCommand $memoryQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$memoryQueryInsert"
            }
        }

        if($Disk -or $All) {
            Write-Output "Getting disks information ($strComputer)<br>"      
            $items = ""       
            $items = gwmi Win32_LogicalDisk -Comp $strComputer -Credential $Credential | Select-Object DriveType, DeviceID, Size, FreeSpace
            Write-Output "Inserting disk information ($strComputer)<br>"  
            foreach($item in $items) {
                $driveType = $item.DriveType
                $deviceID = $item.DeviceID
                $size = [math]::round(($item.Size)/1024/1024/1024, 0)   
                $freeSpace = [math]::round(($item.FreeSpace)/1024/1024/1024, 0)    
    
                Switch($driveType) {
                    2{$driveType = "Floppy"}
                    3{$driveType = "Fixed Disk"}
                    5{$driveType = "Removable Media"}
                    default{"Undetermined"}
                }
    
                $diskQueryInsert = "INSERT INTO DriveAudited (serverID,diskType,driveLetter,capacity,freeSpace) VALUES ('$nbServer','$driveType','$deviceID','$size','$freeSpace')"
                Insert-IntoDatabase $sqlCommand $diskQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$diskQueryInsert"
            }
        }

        if($Network -or $All) {
            Write-Output "Getting network information ($strComputer)<br>" 
            $items = ""
            $items = gwmi Win32_NetworkAdapterConfiguration -Comp $strComputer -Credential $Credential | Where{$_.IPEnabled -eq "True"} | Select-Object Caption, DHCPEnabled, IPAddress, IPSubnet, DefaultIPGateway, DNSServerSearchOrder, FullDNSRegistrationEnabled, WINSPrimaryServer, WINSSecondaryServer, WINSEnableLMHostsLookup
            Write-Output "Inserting network information ($strComputer)<br>"  
            foreach($item in $items) {
                $caption = $item.Caption
                $dhcpEnabled = $item.DHCPEnabled
                $ipAddress = $item.IPAddress
                $ipSubnet = $item.IPSubnet
                $defaultIPGateway = $item.DefaultIPGateway
                $dnsServerSearchOrder = $item.DNSServerSearchOrder
                $fullDNSRegistrationEnabled = $item.FullDNSRegistrationEnabled
                $winsPrimaryServer = $item.WINSPrimaryServer
                $winsSecondaryServer = $item.WINSSecondaryServer
                $winsEnableLMHostsLookup = $item.WINSEnableLMHostsLookup
                $networkQueryInsert = "INSERT INTO NetworkAudited (serverID,networkCard,dhcpEnabled,ipAddress,subnetMask,defaultGateway,dnsServers,dnsReg,primaryWins,secondaryWins,winsLookup) 
                VALUES ('$nbServer','$caption','$dhcpEnabled','$ipAddress','$ipSubnet','$defaultIPGateway','$dnsServerSearchOrder','$fullDNSRegistrationEnabled',
                '$winsPrimaryServer','$winsSecondaryServer','$winsEnableLMHostsLookup')"
                Insert-IntoDatabase $sqlCommand $networkQueryInsert    
                Write-Log -streamWriter $global:streamWriter -infoToLog "$networkQueryInsert"
            }
        }

        if($InstalledPrograms -or $All) {
            Write-Output "Getting programs installed information ($strComputer)<br>"       
            # Populate Installed Programs           
            #$arrayprogramsInstalled = listProgramsInstalled -UninstallKey "SOFTWARE\\Microsoft\\Windows\\CurrentVersion\\Uninstall" -ComputerName $strComputer     
            #$arrayprogramsInstalled2 = listProgramsInstalled -Key "SOFTWARE\\Wow6432Node\\Microsoft\\Windows\\CurrentVersion\\Uninstall" -ComputerName $strComputer     

            $arrayprogramsInstalled = List-ProgramsInstalled -UninstallKey "SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall" -ComputerName $strComputer -Credential $Credential
            $arrayprogramsInstalled2 = List-ProgramsInstalled -UninstallKey "SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall" -ComputerName $strComputer -Credential $Credential

            $items = ""      
            $items = $arrayprogramsInstalled + $arrayprogramsInstalled2      
            Write-Output "Inserting installed programs information ($strComputer)<br>"  
            foreach($item in $items) {
                $displayName = $item.DisplayName
                $displayVersion = $item.DisplayVersion
                $installLocation = $item.InstallLocation
                $publisher = $item.Publisher    
                $displayicon = $item.displayicon 
                if(!([string]::IsNullOrEmpty($displayName))) {    
                    $installedProgramQueryInsert = "INSERT INTO InstalledProgramAudited (serverID,displayName,displayVersion,installLocation,publisher,displayicon) VALUES ('$nbServer','$displayName','$displayVersion','$installLocation','$publisher','$displayicon')"
                    Insert-IntoDatabase $sqlCommand $installedProgramQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$installedProgramQueryInsert"
                }
            }
        }

        if($Shares -or $All) {
            # Populate Shares 
            Write-Output "Getting shares information ($strComputer)<br>"             
            if ($shares = Get-WmiObject Win32_Share -ComputerName $strComputer -Credential $Credential) {        
                $items = @() 
	            $shares | Foreach {$items += Get-NtfsRights $_.Name $_.Path $_.__Server $Credential}
            }
            else {$shares = "Failed to get share information from {0}." -f $($_.ToUpper())}            
            Write-Output "Inserting shares information  ($strComputer)<br>"
            $shareName = ""
            $shareNameSave = ""
            foreach ($item in $items) { 
                $shareName = $item.ShareName
                if($shareName -ne $shareNameSave) {
                    $sharesQueryInsert = "INSERT INTO ShareAudited (serverID,shareName) VALUES ('$nbServer','$shareName')"        
                    Insert-IntoDatabase $sqlCommand $sharesQueryInsert
                    $shareNameSave = $shareName
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$sharesQueryInsert"
                }
                $principal = $item.Principal
                $rights = $item.Rights
                $aceFlags = $item.AceFlags
                $aceType = $item.AceType
    
                $queryText = "Select shareAuditedID as shareAuditedID FROM ShareAudited WHERE shareName LIKE '$shareName'"
                $recordReturned = Select-FromDatabase($queryText)
                $shareAuditedID = $recordReturned.shareAuditedID
                $sharesRightsQueryInsert = "INSERT INTO ShareRightsAudited (shareAuditedID,account,rights,aceFlags,aceType) VALUES ('$shareAuditedID','$principal','$rights','$aceFlags','$aceType')"        
                Insert-IntoDatabase $sqlCommand $sharesRightsQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$sharesRightsQueryInsert"
            } 
        }

        if($Services -or $All) {
            # Populate Services   
            Write-Output "Getting services information ($strComputer)<br>" 	
            $items = ""
            $items = Get-WmiObject win32_service -Comp $strComputer -Credential $Credential | Select-Object DisplayName, Name, StartName, StartMode, PathName, Description                 
            Write-Output "Inserting services information ($strComputer)<br>"
            foreach ($item in $items) { 
                $displayName = $item.DisplayName
                $name = $item.Name
                $startName = $item.StartName
                $startMode = $item.StartMode
                $pathName = $item.PathName
                $description = $item.Description
                $description = $description.replace("'","")    

                $servicesQueryInsert = "INSERT INTO ServiceAudited (serverID,displayName,name,startName,startMode,servicePathName,serviceDescription) VALUES ('$nbServer','$displayName','$name','$startName','$startMode','$pathName','$description')"    
                Insert-IntoDatabase $sqlCommand $servicesQueryInsert     
                Write-Log -streamWriter $global:streamWriter -infoToLog "$servicesQueryInsert"   
            } 
        }

        if($ScheduledTasks -or $All) {
            # Populate Scheduled Tasks       
            Write-Output "Getting tasks information ($strComputer)<br>"     
            $items = @()        
            try { 
                $schedule = new-object -comobject "Schedule.Service" ;                  
                $schedule.Connect($strComputer, $UserNameSchedTasks, $domainName, $passwordSchedTasks)                  
            }
            catch [System.Management.Automation.PSArgumentException] { 
                Write-Output $_ 
            }          
            $items += Get-Tasks -Schedule $schedule -Credential $Credential -Computer $strComputer
            # Close com
            [System.Runtime.Interopservices.Marshal]::ReleaseComObject($schedule) | Out-Null
            Remove-Variable schedule        
            Write-Output "Inserting Scheduled Tasks information ($strComputer)<br>"     
            foreach ($item in $items) { 
                $name = $item.Name            
                $path = $item.Path
                $lastRunTime = $item.LastRunTime
                $nextRunTime = $item.NextRunTime
                $actions = $item.Actions
                $arguments = $item.Arguments
                $runAs = $item.RunAs
                $md5 = $item.md5

                $suspicious = 0
                if($arguments -match 'hidden' -or $arguments -match 'nop' -or $arguments -match 'noprofile' -or $arguments -match 'Enc' -or $arguments -match 'EncodedCommand' -or $arguments -match 'bypass' -or $arguments -match 'NonI' -or $arguments -match 'iex' -or $arguments -match 'FromBase64String'){
                    $suspicious = 1 
                }

                $scheduledTasksQueryInsert = "INSERT INTO ScheduledTaskAudited (serverID,name,pathName,arguments,lastRunTime,nextRunTime,scheduledAction,runAs,hash,Suspicious) VALUES ('$nbServer','$name','$path','$arguments','$lastRunTime','$nextRunTime','$actions','$runAs','$md5','$suspicious')"
                Insert-IntoDatabase $sqlCommand $scheduledTasksQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$scheduledTasksQueryInsert" 
            }
        }

        if($Printers -or $All) {
            # Populate Printers     
            Write-Output "Getting printers information ($strComputer)<br>"
            $items = ""
            $items = gwmi Win32_Printer -Comp $strComputer -Credential $Credential | Select-Object Location, Name, PrinterState, PrinterStatus, ShareName, SystemName           
            Write-Output "Inserting Printers information ($strComputer)<br>"  
            foreach ($item in $items) {  
                $name = $item.Name
                $location = $item.Location
                $printerState = $item.PrinterState
                $printerStatus = $item.PrinterStatus
                $shareName = $item.ShareName
                $systemName = $item.SystemName

                $printerQueryInsert = "INSERT INTO PrinterAudited (serverID,name,location,printerState,printerStatus,shareName,systemName) VALUES ('$nbServer','$name','$location','$printerState','$printerStatus','$shareName','$systemName')"
                Insert-IntoDatabase $sqlCommand $printerQueryInsert    
                Write-Log -streamWriter $global:streamWriter -infoToLog "$printerQueryInsert" 
            }   
        }
        
        if($Process -or $All) {                  
            # Populate Process       
            Write-Output "Getting process information ($strComputer)<br>"     
            $items = ""
            $items = gwmi win32_process -ComputerName $strComputer -Credential $Credential | select-object Name, Path, SessionId, Handles, CreationDate, CommandLine, ProcessId
            Write-Output "Inserting Process information ($strComputer)<br>"  
            foreach ($item in $items) {
                $md5 = ''
                $processID = $item.ProcessId
                $name = $item.Name            
                $location = $item.Path
                $sessionID = $item.sessionID
                $Handles = $item.Handles
                $CreationDate = $item.CreationDate
                $CommandLine = $item.CommandLine
                if($EnableHash) {
                    $md5 = Invoke-Command -ComputerName $strComputer -Credential $Credential -ScriptBlock {
                        param($location)
                        $fullPath = Resolve-Path $location
                        $md5h = new-object -TypeName System.Security.Cryptography.SHA256Managed
                        $file = [System.IO.File]::OpenRead($fullPath)
                        $hash = [System.BitConverter]::ToString($md5h.ComputeHash($file))
                        $hash -replace "-", ""
                        $file.Dispose()
                    } -argumentlist $location
                }
                $CommandLine = $CommandLine.ToLower()
                $processQueryInsert = "INSERT INTO ProcessAudited (serverID,processID,name,location,sessionID,Handles,CommandLine,hash) VALUES ('$nbServer','$processID','$name','$location','$sessionID','$Handles','$CommandLine','$md5')"
                Insert-IntoDatabase $sqlCommand $processQueryInsert    
                Write-Log -streamWriter $global:streamWriter -infoToLog "$processQueryInsert" 
            } 
        }

        if($ProcessTree -or $All) {
            Write-Output "Getting process tree information ($strComputer)<br>"     
            $items = ""
            
            $ProcessTreeArray = @()
            $ProcessTreeRetrieved = @()
            #$Depth,$_.ProcessId,$_.ParentProcessId,$_.Name,$_.sessionID,$_.Handles,$_.CreationDate,$_.Path,$_.CommandLine,$_.Description
            $ProcessTreeRetrieved = Get-ProcessTree -ComputerName $strComputer -Credential $Credential #| select Level, Id, ParentId, Name, SessionID, Handles, CreationDate, Path, CommandLine, Description, UserName, DomainName          
            foreach($pt in $ProcessTreeRetrieved) {
                $suspiciousCommand = 0
                $decodedText = ''
                $processChain = $($pt.CommandLine) -replace """",""
                $processChain = $($pt.CommandLine) -replace ";",""
                $commandArray = $processChain.Split(' ')
                $argumentsCount = $commandArray.Count
                for($i = 0; $i -lt $argumentsCount ;$i++) {
                    if ($commandArray[$i] -match "^(?:[A-Za-z0-9+/]{4})*(?:[A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=|[A-Za-z0-9+/]{4})$") {
                        $decodedText = [System.Text.Encoding]::Unicode.GetString([System.Convert]::FromBase64String($($commandArray[$i])))                      
                        $checkAscii = $decodedText.ToCharArray()
                        $nonBase64 = 0
                        foreach($ca in $checkAscii) {                        
                            $isAcii = [int[]][char[]]$ca
                            if($isAcii -gt 127) {
                                $decodedText = ''
                                $nonBase64 = 1
                            }
                        }
                        if($nonBase64 -eq 0){
                            $decodedText = $decodedText -Replace ';', ''
                            $suspiciousCommand = 1
                        }
                    }
                }                                
                if($processChain -match 'hidden' -or $processChain -match 'nop' -or $processChain -match 'noprofile' -or $processChain -match 'Enc' -or $processChain -match 'EncodedCommand' -or $processChain -match 'bypass' -or $processChain -match 'NonI' -or $processChain -match 'iex' -or $processChain -match 'FromBase64String'){
                    $suspiciousCommand = 1 
                }
                $ProcessTreeArray += "$($pt.Level);$($pt.Id);$($pt.ParentId);$($pt.Name);$($pt.SessionID);$($pt.Handles);$($pt.CreationDate);$($pt.Path);$processChain;$($pt.Description);$decodedText;$suspiciousCommand;$($pt.GetOwner().User);$($pt.GetOwner().Domain)"
               
            }
            Write-Output "Inserting Process Tree information ($strComputer)<br>"  
            foreach ($processTreeToInsert in $ProcessTreeArray){            
                $processTreeToInsertExploded = $processTreeToInsert.Split(';')
                $level = $processTreeToInsertExploded[0]
                $processID = $processTreeToInsertExploded[1]
                $parentProcessId = $processTreeToInsertExploded[2]
                $name = $processTreeToInsertExploded[3]
                $sessionID = $processTreeToInsertExploded[4]
                $handles = $processTreeToInsertExploded[5]
                $creationDate = $processTreeToInsertExploded[6]
                $location = $processTreeToInsertExploded[7]
                $commandLine = $processTreeToInsertExploded[8]
                $description = $processTreeToInsertExploded[9]                       
                $decodedText = $processTreeToInsertExploded[10]                
                $decodedText = $decodedText -Replace "'", "''"                
                $suspiciousCommand = $processTreeToInsertExploded[11]
                $userName = $processTreeToInsertExploded[12]
                $domain = $processTreeToInsertExploded[13]                
                $md5 = ""                              
                if($EnableHash) {
                    $md5 = Invoke-Command -ComputerName $strComputer -Credential $Credential -ScriptBlock {
                        param($location)
                        $fullPath = Resolve-Path $location
                        $md5h = new-object -TypeName System.Security.Cryptography.SHA256Managed
                        $file = [System.IO.File]::OpenRead($fullPath)
                        $hash = [System.BitConverter]::ToString($md5h.ComputeHash($file))
                        $hash -replace "-", ""
                        $file.Dispose()
                    } -argumentlist $location
                }
                $processQueryInsert = "INSERT INTO ProcessTreeAudited (serverID,level,processID,parentProcessId,name,sessionID,handles,creationDate,location,commandLine,description,hash,Decoded,Suspicious,username,domain) 
                VALUES ('$nbServer','$level','$processID','$parentProcessId','$name','$sessionID','$Handles','$creationDate','$location','$CommandLine','$description','$md5','$decodedText','$suspiciousCommand','$userName','$domain')"
                Insert-IntoDatabase $sqlCommand $processQueryInsert    
                Write-Log -streamWriter $global:streamWriter -infoToLog "$processQueryInsert"
            }          
            # update stats
            Write-Output "Updating process tree statistics ($strComputer)<br>"     
            $serverID = "$nbServer"

            $query = "
                WITH Parent AS
            (
                SELECT
                    ProcessID,
                    [parentProcessId],
                    [Name] AS ProcessName,
		            serverID, level
                FROM
                    [NOAH].[dbo].[ProcessTreeAudited]
                WHERE
                    serverID = $serverID
	            AND level LIKE 0

                UNION ALL

                SELECT
                    TH.ProcessID,
                    TH.[parentProcessId],
                    CONVERT(varchar(100), Parent.ProcessName + '/' + TH.Name) AS ProcessName,
		            TH.serverID, TH.level
                FROM
                    [NOAH].[dbo].[ProcessTreeAudited] TH
                INNER JOIN
                    Parent
                ON
                    Parent.processID = TH.[parentProcessId]
	            AND Parent.serverID = TH.serverID
            )
            SELECT distinct(ProcessName), parentProcessId, processID, level FROM Parent
            ORDER BY ProcessName 

            "
            $result = Select-FromDatabase($query)
            foreach($res in $result){
                $query2 = "Select * FROM [NOAH].[dbo].[FlatProcessStat] WHERE ProcessName LIKE '$($res.ProcessName)'"
                $res2 = Select-FromDatabase($query2)
                if($res2){
                    $queryUpdate = "UPDATE [NOAH].[dbo].[FlatProcessStat] set Count = Count + 1 WHERE FlatProcessID LIKE $($res2.FlatProcessID)"
                    Insert-IntoDatabase $sqlCommand $queryUpdate
                }
                else {
                    $queryInsert = "INSERT INTO [NOAH].[dbo].[FlatProcessStat] (Count, ProcessName) VALUES (1, '$($res.ProcessName)')"
                    Insert-IntoDatabase $sqlCommand $queryInsert
                }
                $queryInsert = "INSERT INTO [NOAH].[dbo].[FlatProcessByServerStat] (serverID, ProcessName, parentProcessId, ProcessID, level) VALUES ('$serverID', '$($res.ProcessName)', '$($res.parentProcessId)', '$($res.ProcessID)', '$($res.level)')"
                Insert-IntoDatabase $sqlCommand $queryInsert
            }
        }

        if($Netstat -or $All) {
            Write-Output "Getting Netstat information ($strComputer)<br>"     
            $netstatResult = Get-NetworkStatistics -ComputerName $strComputer -Credentials $Credential
            Write-Output "Inserting Netstat information ($strComputer)<br>" 
            foreach($t in $netstatResult){
                $netStatAuditedQueryInsert = "INSERT INTO NetStatAudited (serverID,Protocol,LocalAddress,LocalPort,RemoteAddress,RemotePort,State,ProcessName,PID) 
                VALUES ('$nbServer','$($t.Protocol)','$($t.LocalAddress)','$($t.LocalPort)','$($t.RemoteAddress)','$($t.RemotePort)','$($t.State)','$($t.ProcessName)','$($t.PID)')"    
                # $netStatAuditedQueryInsert                
                Insert-IntoDatabase $sqlCommand $netStatAuditedQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$netStatAuditedQueryInsert"
            } 
        }  

        if($ODBCConfigured -or $All) {  
            # Populate ODBC Configured 
            Write-Output "Getting ODBC connections Configured ($strComputer)<br>"   
            if($systemType -eq "x86-based PC") {
                $odbcConfigured = "SOFTWARE\wow6432Node\odbc\odbc.ini"
                $odbcDriversInstalled = "SOFTWARE\wow6432Node\odbc\odbcinst.ini"
            }
            else {
                $odbcConfigured = "SOFTWARE\odbc\odbc.ini"
                $odbcDriversInstalled = "SOFTWARE\odbc\odbcinst.ini"
            }     
            Write-Output "Inserting ODBC connections Configured ($strComputer)<br>" 
            $items = ""  
            $items = List-ODBCConfigured -Key $odbcConfigured -ComputerName $strComputer -Credential $Credential     
            foreach ($item in $items) {  
                $dsn = $item.dsn
                $serverName = $item.server
                $port = $item.port
                $dataBaseFile = $item.dataBaseFile
                $dataBaseName = $item.dataBaseName
                $odbcUID = $item.UID
                $odbcPWD = $item.PWD
                $start = $item.start
                $lastUser = $item.lastUser
                $odbcDatabase = $item.Database
                $defaultLibraries = $item.defaultLibraries
                $defaultPackage = $item.defaultPackage
                $defaultPkgLibrary = $item.defaultPkgLibrary
                $odbcSystem = $item.System
                $driver = $item.driver
                $odbcDescription = $item.Description

                $odbcConfiguredQueryInsert = "INSERT INTO ODBCConfiguredAudited (serverID,dsn,serverName,port,dataBaseFile,dataBaseName,odbcUID,odbcPWD,start,lastUser,odbcDatabase,defaultLibraries,defaultPackage,defaultPkgLibrary,odbcSystem,driver,odbcDescription) 
                VALUES ('$nbServer','$dsn','$serverName','$port','$dataBaseFile','$dataBaseName','$odbcUID','$odbcPWD','$start','$lastUser','$odbcDatabase','$defaultLibraries','$defaultPackage','$defaultPkgLibrary','$odbcSystem','$driver','$odbcDescription')"    
                Insert-IntoDatabase $sqlCommand $odbcConfiguredQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$odbcConfiguredQueryInsert"
            } 
        }
        
        if($ODBCInstalled -or $All) {                   
            # Populate ODBC Drivers Installed               
            Write-Output "Getting ODBC Drivers Installed ($strComputer)<br>" 
            $items = ""
            $items = listODBCInstalled $odbcDriversInstalled   
            Write-Output "Inserting ODBC Drivers Installed ($strComputer)<br>" 
            foreach ($item in $items) {      
                $driver = $item.Driver
                $driverODBCVer = $item.DriverODBCVer
                $fileExtns = $item.FileExtns
                $setup = $item.Setup

                $odbcInstalledQueryInsert = "INSERT INTO ODBCInstalledAudited (serverID,driver,driverODBCVer,fileExtns,setup) VALUES ('$nbServer','$driver','$driverODBCVer','$fileExtns','$setup')"
                Insert-IntoDatabase $sqlCommand $odbcInstalledQueryInsert    
                Write-Log -streamWriter $global:streamWriter -infoToLog "$odbcInstalledQueryInsert"   
            }   
        }

        if($LocalUsers -or $All) { 
            Write-Output "Getting local users information ($strComputer)<br>"                 
            $items = ""
            $items = Get-LocalUsersInGroup -ComputerName $strComputer -Credential $Credential
            Write-Output "Inserting local users information ($strComputer)<br>"  
            foreach($item in $items) {
                $group = $item.Group
                $user = $item.User
                $localUsersQueryInsert = "INSERT INTO LocalGroupAudited (serverID,localGroup,userNested) VALUES ('$nbServer','$group','$user')"
                Insert-IntoDatabase $sqlCommand $localUsersQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$localUsersQueryInsert"
            }
        }

        if($OperatingSystemPrivileges -or $All) { 
            Write-Output "Getting OS Privileges information ($strComputer)<br>"   
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd 'secedit.exe /export /areas USER_RIGHTS /cfg c:\secdump.txt' -Credentials $Credential | Wait-Process
            #Start-Sleep -Seconds 3  # wait for file to be created
            <#
            [string]$strScriptPath = Split-Path $MyInvocation.MyCommand.Path
            $file = ($strScriptPath + "\secdump.txt")
            try {
                #$fileTocopy = "\\$strComputer\c$\secdump.txt"
                #Copy-Item $fileTocopy $file               
                Copy-Item -Path "y:\secdump.txt" -Destination $file
            }
            catch{
                $_.Exception
            }#>
            Write-Output "Parsing OS Privileges information ($strComputer)<br>"   
            $dumpResult = Parse-SecdumpFileToObject "\\$strComputer\c$\secdump.txt"
            #Start-Sleep -Seconds 1

            Remove-Item "\\$strComputer\c$\secdump.txt"
            #Remove-Item $file

            # convert the dump to XML to a test file        
            $XMLDump = $dumpResult | ConvertTo-XML -NoTypeInformation
            # Save Dump Data in the Output File
            $XMLDump.Save("$scriptPath\secdump.xml")

            $xmlPath = "$scriptPath\secdump.xml"
            $nodes = ""
            $nodes = Select-Xml -Path $xmlPath -XPath "//Property" | Select-Object -ExpandProperty Node

            $arrayPrivilege = @{}

            $nbNode = 0
            $nodes | ForEach-Object {
                $name = ""
                $name = $_.Name   
                if($name -eq "name") {
                    $privilegeName = $_ | Select '#text'
                    $privilegeName = $privilegeName.'#text'
                }
                if($name -eq "members") {
                    $members = $_ | Select 'Property'        
                    $members = $members.property
                    $arrayPrivilege.Add($privilegeName, $members) 
                }    
         
                $nbNode++
            }
            Write-Output "Inserting OS Privileges information ($strComputer)<br>"
            foreach($privilege in $arrayPrivilege.keys) {
                $strategy = $privilege
                $securityParameters = $arrayPrivilege.item($privilege)        
                $OSPrivilegeQueryInsert = "INSERT INTO OSPrivilegeAudited (serverID,strategy,securityParameter) VALUES ('$nbServer','$strategy','$securityParameters')"              
                Insert-IntoDatabase $sqlCommand $OSPrivilegeQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$localUsersQueryInsert"
            }
        }

        if($Autoruns -or $All) { 
            Write-Output "Getting Autoruns information ($strComputer)<br>"      
            Copy-Item -Path "$scriptPath\NOAH.ThirdParties\autorunsc.exe" -Destination "\\$strComputer\c$\Windows\temp\autorunsc.exe"
            $autorunPath = "C:\Windows\temp\autorunsc.exe"
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd "cmd /c $autorunPath -a * -s -m -h -t -c -accepteula -nobanner > c:\windows\temp\auto.csv" -Credentials $Credential | Wait-Process            
            Copy-Item -Path "\\$strComputer\c$\Windows\temp\auto.csv" -Destination "$scriptPath\auto_$strComputer.csv"
            Remove-Item -Force "\\$strComputer\c$\Windows\temp\autorunsc.exe"
            Remove-Item -Force "\\$strComputer\c$\Windows\temp\auto.csv"
            $autorunImport = Import-Csv "$scriptPath\auto_$strComputer.csv"
            Write-Output "Inserting Autoruns information ($strComputer)<br>" 
            foreach ($ai in $autorunImport) {
                $suspicious = 0
                $launchString = $($ai.'Launch String')
                if($launchString -match 'hidden' -or $launchString -match 'Enc' -or $launchString -match 'EncodedCommand' -or $launchString -match 'bypass' -or $launchString -match 'NonI' -or $launchString -match 'iex' -or $launchString -match 'FromBase64String'){
                    $suspicious = 1 
                }
                $autorunAuditedQueryInsert = "INSERT INTO AutorunAudited ([serverID]
                    ,[Time]
                    ,[EntryLocation]
                    ,[Entry]
                    ,[Enabled]
                    ,[Category]
                    ,[Profile]
                    ,[Description]
                    ,[Signer]
                    ,[Company]
                    ,[ImagePath]
                    ,[Version]
                    ,[LaunchString]
                    ,[MD5]
                    ,[SHA-1]
                    ,[PESHA-1]
                    ,[PESHA-256]
                    ,[SHA-256]
                    ,[IMP]
                    ,Suspicious) 
                        VALUES ('$nbServer','$($ai.Time)','$($ai.'Entry Location')','$($ai.Entry)','$($ai.Enabled)','$($ai.Category)','$($ai.Profile)',
                        '$($ai.Description)','$($ai.Signer)','$($ai.Company)','$($ai.ImagePath)',
                        '$($ai.Version)','$($ai.'Launch String')','$($ai.MD5)','$($ai.'SHA-1')','$($ai.'PESHA-1')','$($ai.'PESHA-256')','$($ai.'SHA-256')',
                        '$($ai.IMP)','$suspicious')"    
                Insert-IntoDatabase $sqlCommand $autorunAuditedQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$autorunAuditedQueryInsert"                  
            }
        }
        
        if($AMCache -or $All) { 
            # Corriger probleme path quand plusieurs fichiers present dans le repertoire
            Write-Output "Getting AMCache information ($strComputer)<br>"   
            Write-Output "Ninja copy ($strComputer)"   
            Copy-Item -Path "$scriptPath\NOAH.ThirdParties\Invoke-NinjaCopy.exe" -Destination "\\$strComputer\c$\Windows\temp\Invoke-NinjaCopy.exe"            
            $autorunPath = "C:\Windows\temp\Invoke-NinjaCopy.exe"
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd "cmd /c $autorunPath" -Credentials $Credential | Out-Null
            #Start-Sleep -Seconds 30
            Copy-Item -Path "\\$strComputer\c$\amcache.hve" -Destination "$scriptPath\NOAH.Logs\$launchDateFiles\amcache_$strComputer.hve"
            Remove-Item -Force "\\$strComputer\c$\Windows\temp\Invoke-NinjaCopy.exe"
            Remove-Item -Force "\\$strComputer\C$\amcache.hve"            
            if($EZ -or $All) {               
                Write-Output "Parsing amcache ($strComputer)<br>"
                $exe = "$scriptPath\NOAH.ThirdParties\AmcacheParser.exe"
                &$exe -f "$scriptPath\NOAH.Logs\$launchDateFiles\amcache_$strComputer.hve" -s "$scriptPath\NOAH.Logs\$launchDateFiles\$strComputer"
                $amcacheToParse = gci "$scriptPath\NOAH.Logs\$launchDateFiles\$strComputer" | Where-Object {$_.Extension -eq ".tsv"}
                $amcacheImport =  Import-Csv -Delimiter "`t" -Path "$scriptPath\NOAH.Logs\$launchDateFiles\$strComputer\$amcacheToParse"
                Write-Output "Inserting AMCache information ($strComputer)<br>"
                foreach ($ac in $amcacheImport) {                 
                    $VolumeIDLastWriteTimestamp = Get-Date $ac.VolumeIDLastWriteTimestamp -Format "yyyy-MM-dd HH:mm:ss"   
                    $FileIDLastWriteTimestamp = Get-Date $ac.FileIDLastWriteTimestamp -Format "yyyy-MM-dd HH:mm:ss"
                    $Created = Get-Date $ac.Created -Format "yyyy-MM-dd HH:mm:ss"
                    $LastModified = Get-Date $ac.LastModified -Format "yyyy-MM-dd HH:mm:ss"
                    $LastModified2 = Get-Date $ac.LastModified2 -Format "yyyy-MM-dd HH:mm:ss"
                    $compileTime = Get-Date $ac.CompileTime -Format "yyyy-MM-dd HH:mm:ss"
                    $amcacheAuditedQueryInsert = "INSERT INTO AmcacheAudited
                        ([serverID]
                        ,[ProgramName]
                        ,[ProgramID]
                        ,[VolumeID]
                        ,[VolumeIDLastWriteTimestamp]
                        ,[FileID]
                        ,[FileIDLastWriteTimestamp]
                        ,[SHA1]
                        ,[FullPath]
                        ,[FileExtension]
                        ,[MFTEntryNumber]
                        ,[MFTSequenceNumber]
                        ,[FileSize]
                        ,[FileVersionString]
                        ,[FileVersionNumber]
                        ,[FileDescription]
                        ,[PEHeaderSize]
                        ,[PEHeaderHash]
                        ,[PEHeaderChecksum]
                        ,[Created]
                        ,[LastModified]
                        ,[LastModified2]
                        ,[CompileTime]
                        ,[LanguageID])
                        VALUES ('$nbServer','$($ac.ProgramName)','$($ac.'ProgramID')','$($ac.VolumeID)','$($VolumeIDLastWriteTimestamp)','$($ac.FileID)','$($FileIDLastWriteTimestamp)',
                            '$($ac.SHA1)','$($ac.FullPath)','$($ac.FileExtension)','$($ac.MFTEntryNumber)',
                            '$($ac.MFTSequenceNumber)','$($ac.'FileSize')','$($ac.FileVersionString)','$($ac.FileVersionNumber)','$($ac.FileDescription)','$($ac.PEHeaderSize)','$($ac.PEHeaderHash)',
                            '$($ac.PEHeaderChecksum)','$($Created)','$($LastModified)','$($LastModified2)','$($CompileTime)','$($ac.LanguageID)')"    
                    Insert-IntoDatabase $sqlCommand $amcacheAuditedQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$amcacheAuditedQueryInsert" 
                }
            }
            else {
                Write-Output "Parsing and inserting amcache ($strComputer)<br>"
                $hive = "$scriptPath\NOAH.Logs\$launchDateFiles\amcache_$strComputer.hve"

                Import-RegistryHive -File $hive -Key "HKLM\$strComputer$launchDateFiles" -Name TempHive

                $BaseKeyObject = [Microsoft.Win32.RegistryKey]::OpenBaseKey([Microsoft.Win32.RegistryHive]::LocalMachine, [Microsoft.Win32.RegistryView]::Registry32)
                $tempHive = "$strComputer$launchDateFiles\Root\File"
                $BaseKey = $BaseKeyObject.OpenSubKey($tempHive)
                $volumeGUID = $BaseKey.GetSubKeyNames() 
                $amCacheArray = @()          
                $dataCSV = "" 
                foreach($volume in $volumeGUID){ 
                    $programsKey=$tempHive+"\"+$volume          
                    $programsKeys=$BaseKeyObject.OpenSubKey($programsKey)
                    $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                    $volumeLastWriteTime =  $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")
                    $programs = $programsKeys.GetSubKeyNames()            
                    foreach($program in $programs){
                        $productName = "";$companyName = "";$fileVersionNumberOnly = "";$languageCode = "";$switchBackContext = "";$fileVersion = "";$fileSize = ""
                        $PEHeaderFieldSizeOfImage = "";$hashOfPEHeader = "";$PEHeaderFieldChecksum = "";$fileDescription = "";$majorAndMinorOSVersion = ""
                        $compileTimestamp = "";$lastModifiedTimestamp = "";$createdTimestamp = "";$fullPathToFile = "";$lastModifiedTimestamp2 = "";$programID = ""
                        $SHA1HashOfFile = "";$unknown1 = "";$unknown2 = "";$unknown3 = "";$unknown4 = "";$associated = 0        
                        $tempPSObject = New-Object PSObject            
                        $tempPSObject = Get-ItemProperty "hklm:\$tempHive\$volume\$program" 
                        $programKey=$BaseKeyObject.OpenSubKey("$tempHive\$volume\$program")   
                        $programTimestamp = $programKey | Get-RegistryKeyTimestamp
                        $programLastWriteTime =  $(Get-Date ([DateTime]::FromFileTime($programTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                            
                        $programKey.Close()
                        $tempKey = $program.PadLeft(8, '0');

                        $seq1 = $tempKey.Substring(0, 4);
                        $seq2 = $tempKey.Substring(2, 2);
                        $seq = $seq1.TrimEnd('0');

                        if ($seq.Length -eq 0)
                        {
                            $seq = "0";
                        }

                        $MFTSequenceNumber = [System.Convert]::ToInt32($seq, 16);
                        $ent = $tempKey.Substring(4);
                        $MFTEntryNumber = [System.Convert]::ToInt32($ent, 16);

                        if(!($($tempPSObject.0))){            
                            $tempPSObjectPrograms = New-Object PSObject            
                            $tempPSObjectPrograms = Get-ItemProperty "hklm:\$strComputer$launchDateFiles\Root\Programs\$($tempPSObject.100)" -EA SilentlyContinue
                            if($($tempPSObjectPrograms.0)){
                                $productName = $tempPSObjectPrograms.0
                                $associated = 1
                            }
                        }
                        else {
                            $productName = $tempPSObject.0
                        }
                        if($($tempPSObject.1)){
                            $companyName = $tempPSObject.1            
                        }
                        if($($tempPSObject.2)){
                            $fileVersionNumberOnly = $tempPSObject.2            
                        }
                        if($($tempPSObject.3)){
                            $languageCode = $tempPSObject.3                                                     
                        }
                        if($($tempPSObject.4)){
                            $switchBackContext = $tempPSObject.4                                                        
                        }
                        if($($tempPSObject.5)){
                            $fileVersion = $tempPSObject.5                                                        
                        }
                        if($($tempPSObject.6)){
                            $fileSize = $tempPSObject.6                                                        
                        }
                        if($($tempPSObject.7)){
                            $PEHeaderFieldSizeOfImage = $tempPSObject.7                                                          
                        }
                        if($($tempPSObject.8)){
                            $hashOfPEHeader = $tempPSObject.8                                                                               
                        }
                        if($($tempPSObject.9)){
                            $PEHeaderFieldChecksum = $tempPSObject.9                                                        
                        }
                        if($($tempPSObject.a)){
                            $unknown1 = $tempPSObject.a                                                     
                        }
                        if($($tempPSObject.b)){
                            $unknown2 = $tempPSObject.b                                                       
                        }
                        if($($tempPSObject.c)){
                            $fileDescription = $tempPSObject.c                                                                               
                        }
                        if($($tempPSObject.d)){
                            $majorAndMinorOSVersion = $tempPSObject.d                                                        
                        }
                        if($($tempPSObject.f)){
                            $compileTimestamp = [timezone]::CurrentTimeZone.ToLocalTime(([datetime]'1/1/1970').AddSeconds($($tempPSObject.f)))
                        }
                        if($($tempPSObject.10)){
                            $unknown3 = $tempPSObject.10                                                        
                        }
                        if($($tempPSObject.11)){
                            $lastModifiedTimestamp = $(Get-Date ([DateTime]::FromFileTime($($tempPSObject.11))) -Format "yyyy-MM-dd HH:mm:ss")                                
                        }
                        if($($tempPSObject.12)){
                            $createdTimestamp = $(Get-Date ([DateTime]::FromFileTime($($tempPSObject.12))) -Format "yyyy-MM-dd HH:mm:ss")                                                      
                        }
                        if($($tempPSObject.15)){                         
                            $fullPathToFile = $tempPSObject.15                                                      
                        }
                        if($($tempPSObject.16)){
                            $unknown4 = $tempPSObject.16                                                        
                        }
                        if($($tempPSObject.17)){
                            $lastModifiedTimestamp2 = $(Get-Date ([DateTime]::FromFileTime($($tempPSObject.17))) -Format "yyyy-MM-dd HH:mm:ss")                                                         
                        }
                        if($($tempPSObject.100)){
                            $programID = $tempPSObject.100                                                       
                        }
                        if($($tempPSObject.101)){
                            $SHA1HashOfFile = $tempPSObject.101
                            $SHA1HashOfFileTrimed = $SHA1HashOfFile.TrimStart(" ", "0")
                        }                     
                                        
                        $amcacheAuditedQueryInsert = "INSERT INTO AmcacheAudited
                                    ([serverID]
                                    ,[Associated]
                                    ,[ProgramName]
                                    ,[ProgramID]
                                    ,[VolumeID]
                                    ,[VolumeIDLastWriteTimestamp]
                                    ,[FileID]
                                    ,[FileIDLastWriteTimestamp]
                                    ,[SHA1]
                                    ,[FullPath]
                                    ,[FileExtension]
                                    ,[MFTEntryNumber]
                                    ,[MFTSequenceNumber]
                                    ,[FileSize]
                                    ,[FileVersionString]
                                    ,[FileVersionNumber]
                                    ,[FileDescription]
                                    ,[PEHeaderSize]
                                    ,[PEHeaderHash]
                                    ,[PEHeaderChecksum]
                                    ,[Created]
                                    ,[LastModified]
                                    ,[LastModified2]
                                    ,[CompileTime]
                                    ,[LanguageID]
                                    ,CompanyName)
                                    VALUES ('$nbServer','$associated','$productName','$programID','$volume','$volumeLastWriteTime','$program','$programLastWriteTime',
                                        '$SHA1HashOfFileTrimed','$fullPathToFile','$([System.IO.Path]::GetExtension($fullPathToFile))','$MFTEntryNumber',
                                        '$MFTSequenceNumber','$fileSize','$fileVersion','$fileVersionNumberOnly','$fileDescription','$PEHeaderFieldSizeOfImage','$hashOfPEHeader',
                                        '$PEHeaderFieldChecksum','$createdTimestamp','$lastModifiedTimestamp','$lastModifiedTimestamp2','$compileTimestamp','$languageCode','$companyName')"    
                        $result = Insert-IntoDatabase $sqlCommand $amcacheAuditedQueryInsert                        
                        Write-Log -streamWriter $global:streamWriter -infoToLog "$amcacheAuditedQueryInsert $result" 
                    }
                    $programsKeys.Close()
                }

                $programsKeys.Close()

                $BaseKey.Close()
                $BaseKeyObject.Close()
                Remove-RegistryHive -Name TempHive
            }                       
        }

        if($RecentFileCache -or $All) { 
            Write-Output "Getting RecentFileCache information ($strComputer)<br>"   
            Write-Output "RecentFileCache copy ($strComputer)<br>"   
            $recentFileCacheToParse = "$scriptPath\$launchDateFiles\RecentFileCache_$strComputer.bcf"
            try{
                Copy-Item -Path "\\$strComputer\c$\Windows\AppCompat\Programs\RecentFileCache.bcf" -Destination "$scriptPath\$launchDateFiles\RecentFileCache_$strComputer.bcf"
            }
            catch{
                $_
            }

            Write-Output "Parsing RecentFileCache ($strComputer)<br>"
            Parse-RecentFileCache -FileToParse $recentFileCacheToParse -OutputCSV "$scriptPath\$launchDateFiles\RecentFileCache_$strComputer.csv"
            $recentFileCacheImport =  Import-Csv -Path "$scriptPath\$launchDateFiles\RecentFileCache_$strComputer.csv"
            Write-Output "Inserting RecentFileCache information ($strComputer)<br>"            
            foreach ($rf in $recentFileCacheImport) {                           
                $recentFileCacheAuditedQueryInsert = "INSERT INTO RecentFileCacheAudited
                    ([serverID]
                    ,[Program])                   
                    VALUES ('$nbServer','$($rf.Program)')"    
                Insert-IntoDatabase $sqlCommand $recentFileCacheAuditedQueryInsert
                Write-Log -streamWriter $global:streamWriter -infoToLog "$recentFileCacheAuditedQueryInsert"                 
            }            
        }

        if($RecentDocs -or $All) { 
            Write-Output "Getting Recent Docs information ($strComputer)<br>"      
            $ASCIIEncoding = New-Object System.Text.ASCIIEncoding
            $UnicodeEncoding = New-Object System.Text.UnicodeEncoding
            $recentDocsByUser=@()

            $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $strComputer -Credential $Credential
            $subkeys = $wmi.EnumKey(2147483651,"")

            $test = $subkeys.sNames
            $i = 0
            foreach($t in $test){
                $userName = $(($wmi.GetStringValue(2147483651,"$t\Volatile Environment","USERNAME")).sValue)
                $subkeysByUser = $wmi.EnumKey(2147483651,"$t\Software\Microsoft\Windows\CurrentVersion\Explorer\RecentDocs")
                $remoteSubkeysNames = $subkeysByUser.sNames              
                foreach($key in $remoteSubkeysNames){
                    $thisSubKey="$t\Software\Microsoft\Windows\CurrentVersion\Explorer\RecentDocs"+"\"+$key            
                    $sNames = $wmi.EnumValues(2147483651,$thisSubKey).sNames
                    $TempObject = ""
                    $binaryValue = ""
                    $ASCIIValue = ""
                    $HexValue = ""
                    foreach($value in $sNames){
                        $TempObject = New-Object PSObject
                        $binaryValue = $(($wmi.GetBinaryValue(2147483651,$thisSubKey,$value)).uValue)
                        $ASCIIValue = $ASCIIEncoding.GetString($binaryValue)
                        $HexValue = [System.BitConverter]::ToString($BinaryValue) -replace "-",""			    
			            if ($HexValue -match "(([A-F0-9]{2}0{2}(?!0000))+[A-F0-9]{2}0{2})(0000..00320+)(([A-F0-9]{2}(?!00))+[A-F0-9]{2})(([A-F0-9](?!EFBE))+[A-F0-9]EFBE0+2E0+)(([A-F0-9]{2}0{2}(?!0000))+[A-F0-9]{2}0{2})(.*)") {			    								    
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName
				            # Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Name" -Value ($UnicodeEncoding.GetString(($matches[1] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))								    				            								    
				            # Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ASCII_Link_Name" -Value ($ASCIIEncoding.GetString(($matches[4] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))								    				            								    
				            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Unicode_Link_Name" -Value ($UnicodeEncoding.GetString(($matches[8] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))								    				            
			            } elseif ($ASCIIValue -match "(([^\x00]\x00)+)\x00\x00.\x00\x32\x00+([^\x00]+)\x00\x00.+\x3F\x3F\x00+\x2E\x00+(([^\x00]\x00)+)") {
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName				    
				            # Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Name" -Value $UnicodeEncoding.GetString($ASCIIEncoding.GetBytes($matches[1]))
				            # Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ASCII_Link_Name" -Value $matches[3]
				            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Unicode_Link_Name" -Value $UnicodeEncoding.GetString($ASCIIEncoding.GetBytes($matches[4]))
			            }
                        else {
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Unicode_Link_Name" -Value $UnicodeEncoding.GetString($binaryValue)
                        }
			            $recentDocsByUser += $TempObject
                    }
                }
            }           
            Write-Output "Inserting Recent Docs information ($strComputer)<br>"                 
            foreach ($rcdby in $recentDocsByUser) {
                if(($rcdby.UserName) -and ($rcdby.'Unicode_Link_Name')){
                    $recentDocsAuditedQueryInsert = "INSERT INTO RecentDocsAudited ([serverID]
                        ,[UserName]
                        ,[Unicode_Link_Name])                   
                            VALUES ('$nbServer','$($rcdby.UserName)','$($rcdby.'Unicode_Link_Name')')"    
                    Insert-IntoDatabase $sqlCommand $recentDocsAuditedQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$recentDocsAuditedQueryInsert"  
                }      
            }
        }

        if($ShimCache -or $All) { 
            $ASCIIEncoding = New-Object System.Text.ASCIIEncoding
            $UnicodeEncoding = New-Object System.Text.UnicodeEncoding

            $wmi = Get-WmiObject -List "StdRegProv" -Namespace root\default -ComputerName $strComputer -Credential $Credential

            $subkey = "SOFTWARE\Microsoft\Windows NT\CurrentVersion"
            $currentMejorVersionNumber = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $subkey -ValueName "CurrentMajorVersionNumber" -ValueType 4 -WMI $wmi
            if($currentMejorVersionNumber) {
                $versionOS = $currentMejorVersionNumber.Data    
            }
            else {
                $subkey = "SOFTWARE\Microsoft\Windows NT\CurrentVersion"
                $currentVersion = Get-ValueByType  -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $subkey -ValueName "CurrentVersion" -ValueType 1 -WMI $wmi
                $versionOS = $currentVersion.Data
            }

            $subkey = "SYSTEM\CurrentControlSet\Control\Session Manager\Environment"
            $architecture = Get-ValueByType  -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $subkey -ValueName "PROCESSOR_ARCHITECTURE" -ValueType 1 -WMI $wmi
            if($($architecture.Data) -eq "x86") {
                $OSarchitecture = "32-bit"    
            }
            else {
                $OSarchitecture = "64-bit"  
            }

            switch ($versionOS) {
                "6.1" {
                    $headerOffset = 120        
                }
                "6.3" {
                    $headerOffset = 128        
                }
                "10" {
                    $headerOffset = 48        
                }
            }

            $subkey = "SYSTEM\CurrentControlSet\Control\Session Manager\AppCompatCache"
            $valueInTheSubkey = Get-ValueByType  -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $subkey -ValueName "AppCompatCache" -ValueType 3 -WMI $wmi

            $bytes = $valueInTheSubkey.Data  

            $stream = New-Object System.IO.MemoryStream
            $binWriter = New-Object System.IO.BinaryWriter $stream
            $binWriter.Write($bytes)
            $BinReader = New-Object System.IO.BinaryReader $binWriter.BaseStream
            $BinReader.BaseStream.Position = 0
            $numberOfEntries = 0
            if($versionOS -eq "6.1"){
                $Version = [System.BitConverter]::ToString($BinReader.ReadBytes(4)) -replace "-","" 
                If($Version -eq "EE0FDCBA"){ # BadC0fee
                    $numberOfEntries = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(4),0)
                }
            }

            $BinReader.ReadBytes($headerOffset) | Out-Null
            $i = 1            
            $shimCacheArray=@()            
            while ($BinReader.BaseStream.Position -ne $BinReader.BaseStream.Length) {  
                $TempObject = New-Object PSObject              
                if($versionOS -eq "6.1"){
                    $entryLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0) 
                    $pathLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)  
                    if($OSarchitecture -eq "32-bit") {
                        $offsetToPathName = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)                                           
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ProgramName" -Value $($UnicodeEncoding.GetString($bytes,$offsetToPathName,$entryLength).Replace("\??\", ""))
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "LastModified" -Value $(Get-Date ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G")) -Format "yyyy-MM-dd HH:mm:ss")
                        $BinReader.ReadBytes(16) | Out-Null
                        $shimCacheArray += $TempObject
                    }
                    else {
                        $offsetToPathName = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(8),0)                   
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ProgramName" -Value $($UnicodeEncoding.GetString($bytes,$offsetToPathName,$entryLength).Replace("\??\", ""))
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "LastModified" -Value $(Get-Date ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G")) -Format "yyyy-MM-dd HH:mm:ss")
                        $BinReader.ReadBytes(16) | Out-Null
                        $shimCacheArray += $TempObject
                    }
                    if ($i -ge $numberOfEntries) {break;}
                }
                if($versionOS -eq "6.3" -or $versionOS -eq "10"){
                    $Version = [System.BitConverter]::ToString($BinReader.ReadBytes(4)) -replace "-","" 
                    switch ($Version) {
                        # Windows 2012
                        "30307473" { 
            
                        }
	                    # Windows 2012R2 and 2016 Structure
	                    "31307473" { # 0 to 4
                            # 4 bytes - unknown purpose
                            $BinReader.ReadBytes(4) | Out-Null # 4 to 8
                            $entryLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) # 8 to 12
                            $pathLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)  # 12 to 14           
                            $programName = $UnicodeEncoding.GetString($BinReader.ReadBytes($pathLength))
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ProgramName" -Value $programName
                            $deviationOffset = 0
                            if($versionOS -eq "6.3") {
                                $deviationOffset = 10
                                $fixedOffset = 6
                                $BinReader.ReadBytes(10) | Out-Null
                            }
                            if($versionOS -eq "10") {
                                $fixedOffset = 22
                            }
                            $lastModifiedExecution = Get-Date ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G")) -Format "yyyy-MM-dd HH:mm:ss"
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "LastModified" -Value $lastModifiedExecution
                            $nextEntry = $entryLength - ($pathLength + $deviationOffset + $fixedOffset)
                            $BinReader.ReadBytes($nextEntry) | Out-Null 
                            $shimCacheArray += $TempObject                           
                        }
                    }
                }                
                $i++
            }
            Write-Output "Inserting ShimCache information ($strComputer)<br>"      
            foreach ($sca in $shimCacheArray) {
                if(($sca.ProgramName) -and ($sca.LastModified)){
                    $shimCacheAuditedQueryInsert = "INSERT INTO [ShimCacheAudited] ([serverID]
                        ,[ProgramName]
                        ,[LastModified])                   
                            VALUES ('$nbServer','$($sca.ProgramName)', CAST ('$($sca.LastModified)' AS DATETIME2(0)))"    
                    Insert-IntoDatabase $sqlCommand $shimCacheAuditedQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$shimCacheAuditedQueryInsert"  
                }      
            }
        }

        if($Prefetch -or $All) { 
            Write-Output "Getting Prefetch information ($strComputer)<br>"
            if (Test-Path -Path "\\$strComputer\c$\Windows\Prefetch") {
                if(!(Test-Path "$scriptPath\NOAH.Logs\$launchDateFiles\prefetch\$strComputer")) {
                    New-Item "$scriptPath\NOAH.Logs\$launchDateFiles\prefetch\$strComputer" -type directory | Out-Null
                }
                try{
                    Copy-Item -Path "\\$strComputer\c$\Windows\Prefetch\*" -Destination "$scriptPath\NOAH.Logs\$launchDateFiles\prefetch\$strComputer" -include "*.pf" 
                }
                catch{
                    Write-Output $_
                }
                Write-Output "Inserting Prefetch information ($strComputer)<br>"      
                $prefetchFiles = Get-ChildItem "$scriptPath\$launchDateFiles\$strComputer" -Filter *.pf
                foreach ($pf in $prefetchFiles) {
                    $fileParsed = Parse-Prefetch $($pf.FullName)
                    $lastExecutionTime_1 = Get-Date $fileParsed.LastExecutionTime_1 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_1 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_1 -eq '1600-12-31 4:00:00') {$lastExecutionTime_1 = ''}
                    $lastExecutionTime_2 = Get-Date $fileParsed.LastExecutionTime_2 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_2 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_2 -eq '1600-12-31 4:00:00') {$lastExecutionTime_2 = ''}
                    $lastExecutionTime_3 = Get-Date $fileParsed.LastExecutionTime_3 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_3 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_3 -eq '1600-12-31 4:00:00') {$lastExecutionTime_3 = ''}
                    $lastExecutionTime_4 = Get-Date $fileParsed.LastExecutionTime_4 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_4 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_4 -eq '1600-12-31 4:00:00') {$lastExecutionTime_4 = ''}
                    $lastExecutionTime_5 = Get-Date $fileParsed.LastExecutionTime_5 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_5 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_5 -eq '1600-12-31 4:00:00') {$lastExecutionTime_5 = ''}
                    $lastExecutionTime_6 = Get-Date $fileParsed.LastExecutionTime_6 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_6 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_6 -eq '1600-12-31 4:00:00') {$lastExecutionTime_6 = ''}
                    $lastExecutionTime_7 = Get-Date $fileParsed.LastExecutionTime_7 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_7 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_7 -eq '1600-12-31 4:00:00') {$lastExecutionTime_7 = ''}
                    $lastExecutionTime_8 = Get-Date $fileParsed.LastExecutionTime_8 -Format "yyyy-MM-dd HH:mm:ss"
                    if($lastExecutionTime_8 -eq '1600-12-31 16:00:00' -or $lastExecutionTime_8 -eq '1600-12-31 4:00:00') {$lastExecutionTime_8 = ''}
                    $prefetchAuditedQueryInsert = "INSERT INTO PrefetchAudited
                    ([serverID]
                    ,[ProgramName]
                    ,[Hash] 
                    ,[NumberOfExecutions]
                    ,[PrefetchSize]
                    ,[LastExecutionTime_1]
                    ,[LastExecutionTime_2]
                    ,[LastExecutionTime_3]
                    ,[LastExecutionTime_4]
                    ,[LastExecutionTime_5]
                    ,[LastExecutionTime_6]
                    ,[LastExecutionTime_7]
                    ,[LastExecutionTime_8])
                    OUTPUT INSERTED.prefetchAuditedID
                    VALUES ('$nbServer','$($fileParsed.Name)','$($fileParsed.'Hash')','$($fileParsed.NumberOfExecutions)','$($fileParsed.PrefetchSize)', CAST ('$($lastExecutionTime_1)' AS DATETIME2(0)),
                    CAST ('$($lastExecutionTime_2)' AS DATETIME2(0)), CAST ('$($lastExecutionTime_3)' AS DATETIME2(0)), CAST ('$($lastExecutionTime_4)' AS DATETIME2(0)), CAST ('$($lastExecutionTime_5)' AS DATETIME2(0)),
                    CAST ('$($lastExecutionTime_6)' AS DATETIME2(0)), CAST ('$($lastExecutionTime_7)' AS DATETIME2(0)), CAST ('$($lastExecutionTime_8)' AS DATETIME2(0)))"
                    #    '$($lastExecutionTime_2)','$($lastExecutionTime_3)','$($lastExecutionTime_4)','$($lastExecutionTime_5)',
                    #    '$($lastExecutionTime_6)','$($lastExecutionTime_7)','$($lastExecutionTime_8)')"    
                    $nbPrefetch = Insert-IntoDatabaseRecordLastID $sqlCommand $prefetchAuditedQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$prefetchAuditedQueryInsert"
                    $fileNames = $($fileParsed.FileNames) -split ';'
                    foreach($file in $fileNames) {                
                        $file = $file -replace "'",""         
                        $prefetchFilesAssociatedAuditedQueryInsert = "INSERT INTO PrefetchFilesAssociatedAudited (prefetchAuditedID, FileAssociated) VALUES ('$nbPrefetch','$file')"        
                        Insert-IntoDatabaseRecordLastID $sqlCommand $prefetchFilesAssociatedAuditedQueryInsert
                        Write-Log -streamWriter $global:streamWriter -infoToLog "$prefetchFilesAssociatedAuditedQueryInsert"                        
                    }
                }
            }
        }

        if($DNSCache -or $All) { 
            Write-Output "Getting DNS cache information ($strComputer)<br>"                 
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd 'powershell.exe "ipconfig /displaydns | select-string ''(Record Name)'' | Sort | Unique | Out-File C:\dnscache.txt"' -Credentials $Credential | Wait-Process
            $items = Get-Content "\\$strComputer\c$\dnscache.txt"            
            Remove-Item "\\$strComputer\c$\dnscache.txt"                                 
            Write-Output "Inserting DNS cache information ($strComputer)<br>"  
            foreach($item in $items) {
                if($item -ne ""){
                    $item = $item -replace "    Record Name . . . . . : ", ""                                
                    $DNSCacheQueryInsert = "INSERT INTO DNSCacheAudited (serverID,RecordName) VALUES ('$nbServer','$item')"
                    Insert-IntoDatabase $sqlCommand $DNSCacheQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$DNSCacheQueryInsert"
                }
            }
        }

        if($LINKFile -or $All) { 
            Write-Output "Getting LINK File information ($strComputer)<br>"                 
            $items = gwmi -ea 0 Win32_ShortcutFile -ComputerName $strComputer -Credential $Credential | select FileName,caption,@{NAME='CreationDate';EXPRESSION={$_.ConvertToDateTime($_.CreationDate)}},@{NAME=’LastAccessed’;EXPRESSION={$_.ConvertToDateTime($_.LastAccessed)}},@{NAME=’LastModified’;EXPRESSION={$_.ConvertToDateTime($_.LastModified)}},Target,Hidden | sort LastModified -Descending                                      
            Write-Output "Inserting LINK File information ($strComputer)<br>"  
            foreach($item in $items) {
                if($item -ne ""){
                    $filename = $($item.FileName) -replace "'", ""
                    $caption = $($item.caption) -replace "'", ""
                    $target = $($item.Target) -replace "'", ""
                    $LINKFilesQueryInsert = "INSERT INTO LinkFilesAudited (serverID,FileName,Caption,CreationDate,LastAccessed,LastModified,Target,Hidden) VALUES ('$nbServer','$filename','$caption','$($item.CreationDate)','$($item.LastAccessed)','$($item.LastModified)','$target','$($item.Hidden)')"
                    Insert-IntoDatabase $sqlCommand $LINKFilesQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$LINKFilesQueryInsert"
                }
            }
        }        

        if($ExplorerBar -or $All) { 
            Write-Output "Getting Explorer address bar history information ($strComputer)<br>"                 
            $typedURLs=@()
            $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $strComputer -Credential $Credential
            $subkeys = $wmi.EnumKey(2147483651,"")

            $test = $subkeys.sNames
            $i = 0
            foreach($t in $test){
                $userName = $(($wmi.GetStringValue(2147483651,"$t\Volatile Environment","USERNAME")).sValue)
                $thisSubKey = "$t\Software\Microsoft\Windows\CurrentVersion\Explorer\TypedPaths"
                $sNames = $wmi.EnumValues(2147483651,$thisSubKey).sNames               
                $TempObject = ""                
                foreach($value in $sNames){
                    $TempObject = New-Object PSObject
                    $stringValue = $(($wmi.GetStringValue(2147483651,$thisSubKey,$value)).sValue)        				
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "URL" -Value $stringValue	
                    $typedURLs += $TempObject
                }
            }            
            Write-Output "Inserting Explorer address bar history information ($strComputer)<br>"  
            foreach($typedURL in $typedURLs) {
                if($typedURL -ne ""){
                    $ExplorerBarQueryInsert = "INSERT INTO ExplorerBarAudited (serverID,UserName,URL) VALUES ('$nbServer','$($typedURL.UserName)','$($typedURL.URL)')"
                    Insert-IntoDatabase $sqlCommand $ExplorerBarQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$ExplorerBarQueryInsert"
                }
            }
        }

        if($RunMRU -or $All) { 
            Write-Output "Getting Run MRU history information ($strComputer)<br>"                 
            $runMRUs=@()
            $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $strComputer -Credential $Credential
            $subkeys = $wmi.EnumKey(2147483651,"")

            $test = $subkeys.sNames
            $i = 0
            foreach($t in $test){
                $userName = $(($wmi.GetStringValue(2147483651,"$t\Volatile Environment","USERNAME")).sValue)
                $thisSubKey = "$t\Software\Microsoft\Windows\CurrentVersion\explorer\RunMru"
                $sNames = $wmi.EnumValues(2147483651,$thisSubKey).sNames               
                $TempObject = ""                
                foreach($value in $sNames){
                    $TempObject = New-Object PSObject
                    $stringValue = $(($wmi.GetStringValue(2147483651,$thisSubKey,$value)).sValue)        				
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "MRU" -Value $stringValue	
                    $runMRUs += $TempObject
                }
            }            
            Write-Output "Inserting Run MRU history information ($strComputer)<br>"  
            foreach($MRU in $runMRUs) {
                if($MRU -ne ""){
                    $RunMRUsQueryInsert = "INSERT INTO RunMRUsAudited (serverID,UserName,MRU) VALUES ('$nbServer','$($MRU.UserName)','$($MRU.MRU)')"
                    Insert-IntoDatabase $sqlCommand $RunMRUsQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$RunMRUsQueryInsert"
                }
            }
        }

        if($USBHistory -or $All) { 
            Write-Output "Getting USB History information ($strComputer)<br>"
            if($MassStorage) {               
                $items = Get-USBDevicesHistory -ComputerName $strComputer -Credential $Credential -MassStorage
            }
            else {
                $items = Get-USBDevicesHistory -ComputerName $strComputer -Credential $Credential
            }
            Write-Output "Inserting USB History information ($strComputer)<br>"  
            foreach ($byVendorProduct in $items) {
                Write-Output "Device class ID: $($byVendorProduct.VendorProduct)<br>"
                $devices = $byVendorProduct.Devices
                foreach ($device in $($byVendorProduct.Devices)) {                                             
                    $USBHistoryQueryInsert = "INSERT INTO [dbo].[USBHistoryAudited]
                        ([serverID]
                        ,[DeviceName]
                        ,[FriendlyName]
                        ,[InstanceID]
                        ,[ClassGUID]
                        ,[SymbolicName]
                        ,[SerialNumber]
                        ,[LastTimeDeviceConnected]
                        ,[InstallSetupDevTimeDeviceConnected]
                        ,[DriverDesc]
                        ,[DriverVersion]
                        ,[ProviderName]
                        ,[DriverDate]
                        ,[InfPath]
                        ,[InfSection]
                        ,[ParentIdPrefix]
                        ,[Service])
                        VALUES ('$nbServer','$($device.LocationInformation)','$($device.FriendlyName)','$($device.InstanceID)',
                        '$($device.ClassGUID)','$($device.SymbolicName)','$($device.SerialNumber)','$($device.lastTimeDeviceConnected)','$($device.installSetupDevTimeDeviceConnected)'
                        ,'$($device.DriverDesc)','$($device.DriverVersion)','$($device.ProviderName)','$($device.DriverDate)','$($device.InfPath)'
                        ,'$($device.InfSection)','$($device.ParentIdPrefix)','$($device.Service)')"
                    Insert-IntoDatabase $sqlCommand $USBHistoryQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$USBHistoryQueryInsert"
                }
            }           
        }

        if($BrowserHistory -or $All) { 
            Write-Output "Getting Browser History information ($strComputer)<br>"
            $hives = @{"HKEY_CLASSES_ROOT" = 2147483648; "HKEY_CURRENT_USER" = 2147483649; "HKEY_LOCAL_MACHINE" = 2147483650; "HKEY_USERS" = 2147483651; "HKEY_CURRENT_CONFIG" = 2147483653;}
            $TypedURLs=@()
            $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $strComputer -Credential $Credential
            $subkeys = $wmi.EnumKey($($hives['HKEY_USERS']),"")

            $paths = $subkeys.sNames
            $i = 0
            foreach($path in $paths){
                $userName = $(($wmi.GetStringValue($($hives['HKEY_USERS']),"$path\Volatile Environment","USERNAME")).sValue)
                if($userName -eq $null) {
                    $userName = $path
                }
                $thisSubKey = "$path\Software\Microsoft\Internet Explorer\TypedURLs"
                $sNames = $wmi.EnumValues($($hives['HKEY_USERS']),$thisSubKey).sNames               
                $TempObject = ""
                $binaryValue = ""
                $ASCIIValue = ""
                $HexValue = ""
                foreach($value in $sNames){
                    $TempObject = New-Object PSObject
                    $stringValue = $(($wmi.GetStringValue($($hives['HKEY_USERS']),$thisSubKey,$value)).sValue)    
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Browser" -Value 1    				
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $userName
                    Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "URL" -Value $stringValue	
                    $TypedURLs += $TempObject
                }
            }

            $userProfiles = gwmi win32_userprofile -ComputerName $strComputer -Credential $Credential | select @{LABEL=”last used”;EXPRESSION={$_.ConvertToDateTime($_.lastusetime)}}, LocalPath, SID

            # chrome
            foreach ($up in $userProfiles) {
                $localPath = $up.LocalPath.Replace(":","$")
                $Path = "\\$strComputer\$localPath\AppData\Local\Google\Chrome\User Data\Default\History"    
    
                if ((Test-Path -Path $Path)) {      
                    $fileContent = Get-Content $Path  
                    if($fileContent) {
                        $Regex = '((http|https))://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)*?'
                        #$Regex = '(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:''".,<>?«»“”‘’]))'
                        $Value = Get-Content -Path $Path |Select-String -AllMatches $regex |% {($_.Matches).Value} |Sort -Unique
                        $Value | ForEach-Object {
                            $url = $_                
                            $url = $url -replace '[\x00]+', ' '                                
                            if($($url.IndexOf(' ')) -ne -1){
                                $url = $url.Substring(0, $url.IndexOf(' '))                                
                            }                                   
                            $TempObject = New-Object PSObject                
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Browser" -Value 2
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $($up.LocalPath)
                            Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "URL" -Value $url
                            $TypedURLs += $TempObject
                        }   
                    } 
                }
            }

            # Firefox
            foreach ($up in $userProfiles) {
                $localPath = $up.LocalPath.Replace(":","$")    
                $Path = "\\$strComputer\$localPath\AppData\Roaming\Mozilla\Firefox\Profiles\"
                if (-not (Test-Path -Path $Path)) {
                    Write-Verbose "[!] Could not find FireFox History for username: $localPath<br>"
                }
                else {
                    $Profiles = Get-ChildItem -Path "$Path\*.default\" -ErrorAction SilentlyContinue
                    #$Regex = '(htt(p|s))://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)*?'
                    $Regex = '((http|https))://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)*?'
                    #$Regex = '(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:''".,<>?«»“”‘’]))'
                    $fileContent = Get-Content $Profiles\places.sqlite
                    $Value = $fileContent | Select-String -Pattern $Regex -AllMatches | Select-Object -ExpandProperty Matches | Sort -Unique        
                    $Value.Value |ForEach-Object {
                        $url = $_                
                        $url = $url -replace '[\x00]+', ' '             
                        if($($url.IndexOf(' ')) -ne -1){
                            $url = $url.Substring(0, $url.IndexOf(' '))                                
                        }
                        $TempObject = New-Object PSObject                
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Browser" -Value 3
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UserName" -Value $($up.LocalPath)
                        Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "URL" -Value $url
                        $TypedURLs += $TempObject
                    }
                }
            }
            #$TypedURLs
            Write-Output "Inserting Browser History information ($strComputer)<br>"  
            foreach($TypedURL in $TypedURLs) {
                if($TypedURL -ne ""){
                    $TypedURLQueryInsert = "INSERT INTO BrowserHistoryAudited (serverID,BrowserType,UserName,URL) VALUES ('$nbServer','$($TypedURL.Browser)','$($TypedURL.UserName)','$($TypedURL.URL)')"
                    Insert-IntoDatabase $sqlCommand $TypedURLQueryInsert
                    Write-Log -streamWriter $global:streamWriter -infoToLog "$TypedURLQueryInsert"
                }
            }

        }

        if($UserProfiles -or $All) { 
            Write-Output "Getting User Profiles information ($strComputer)<br>"
            $userProfiles = gwmi win32_userprofile -ComputerName $strComputer -Credential $Credential | select @{LABEL=”last used”;EXPRESSION={$_.ConvertToDateTime($_.lastusetime)}}, LocalPath, SID | ft -a
            $userProfiles
        }
        
        if($CollectFullMemoryDump) {                
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd 'setx _NT_SYMBOL_PATH "srv*c:\Symbols*http://msdl.microsoft.com/download/symbols' -Credentials $Credential | Wait-Process
            $from = "C:\temp\PoshPortal\NOAH\NOAH.ThirdParties\x64\*"
            Get-ChildItem $from -recurse | Copy-Item -Destination "\\$strComputer\c$\windows\temp\x64"    
            $dumpAProcessPath = "C:\windows\temp\x64\livekd.exe"                        
            Run-WmiRemoteProcess -ComputerName $strComputer -Cmd "$dumpAProcessPath -o c:\windows\temp\artifact_RAM_$strComputer.dmp -accepteula" -Credentials $Credential | Wait-Process
            Copy-Item -Path "\\$strComputer\\c$\windows\temp\artifact_RAM_$strComputer.dmp" -Destination "$scriptPath\NOAH.Logs\$launchDateFiles\artifact_RAM_$strComputer.dmp"
            Remove-Item -Force "\\$strComputer\c$\windows\temp\x64\*" -Recurse -Confirm:$false
            Remove-Item -Force "\\$strComputer\c$\windows\temp\artifact_RAM_$strComputer.dmp"
        }

        $nbSuccess++           
    }
    else {

        Write-Log -streamWriter $global:streamWriter -infoToLog "WMI connection to $strComputer failed`n"        

        $serversNotResponding += "$strComputer `r`n"
        $nbError++
    }            
    $sqlConnection.Close()
}

if($nbError -gt 0) {
    $printErrorEncountered = "`r`n The script encountered $nbError error `r`n $serversNotResponding"       
}


$sqlConnection = Connect-Database $connString $DatabaseCredential
$sqlConnection.Open()
$sqlCommand = $sqlConnection.CreateCommand()
$huntingUpdate =  "UPDATE Hunt SET huntingState=1 WHERE huntingGUID = '$huntingGUID'"                    
Insert-IntoDatabase $sqlCommand $huntingUpdate
$sqlConnection.Close()
Write-Log -streamWriter $global:streamWriter -infoToLog "`r`n****************************************************** $printErrorEncountered `r`n $nbSuccess / $nbTot server(s) answer WMI requests `r`n $nbError / $nbTot server(s) NOT answer WMI requests `r`n ****************************************************** `n "

Write-Log -streamWriter $global:streamWriter -infoToLog "Database connection closed"

End-Log -streamWriter $global:streamWriter