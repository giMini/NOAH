function Get-USBDevicesHistory {
[CmdletBinding()]
param (
    [string]$ComputerName, $Credential,
    [Parameter(Mandatory=$false, Position=0)]        
        [Switch] $MassStorage
)    
    $BaseKeyObject = [Microsoft.Win32.RegistryKey]::OpenBaseKey([Microsoft.Win32.RegistryHive]::LocalMachine, [Microsoft.Win32.RegistryView]::Registry32)    

    $wmi = Get-WmiObject  -List "StdRegProv" -Namespace root\default -ComputerName $ComputerName -Credential $Credential
    if($MassStorage) {
        $registryKeyToQuery = "SYSTEM\CurrentControlSet\Enum\USBSTOR"
    }
    else {
        $registryKeyToQuery = "SYSTEM\CurrentControlSet\Enum\USB"
    }

    $usbKeysObject = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE), $registryKeyToQuery)    
    $usbKeys = $usbKeysObject.sNames              
    $byVendorProductArray = @() 
    
    $files = Get-ChildItem -Recurse -Force C:\Windows\Inf -ErrorAction SilentlyContinue | Where-Object { ($_.PSIsContainer -eq $false) -and  ( $_.Name -like "*setupapi.dev*") } | Select-Object Name
    #$files.Name
    $setupAPIDev = @()
    foreach($file in $files){        
        $setupAPIDev += Get-Content "C:\Windows\Inf\$($file.Name)"
    }

    $setupAPIDev = $setupAPIDev | select-string ">>>  [Device Install (" -SimpleMatch -context 1
    
    foreach($usbKey in $usbKeys){    
        if($($usbKey.Substring(0,4)) -ne "ROOT"){                
            $tempObjectvendorProduct = New-Object PSObject
            Add-Member -InputObject $tempObjectvendorProduct -MemberType NoteProperty -Name "VendorProduct" -Value $usbKey         
            $tempObjectvendorProduct | Add-Member -MemberType NoteProperty -Name Devices -Value @()

            $deviceClassID=$registryKeyToQuery+"\"+$usbKey                
            $uniqueInstanceID = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE),"$deviceClassID")
            $uniqueInstanceIDNames = $uniqueInstanceID.sNames        
            foreach($uniqueInstanceIDName in $uniqueInstanceIDNames){  
                $capabilities = "";$classGUID = "";$friendlyName = "";$service = "";$serialNumber = "";$driver = "";
                $driverKey="";$driverDesc = "";$driverVersion = "";$providerName = "";$driverDate = "";$infPath = "";
                $programsKeys = "";$installSetupDevTimeDeviceConnected = "";$lastTimeDeviceConnected = ""
                $parentIdPrefix = "";$locationInformation="";$firstTimeDeviceConnected = "";
                if($MassStorage) {                    
                    $programsKeys=$BaseKeyObject.OpenSubKey("$deviceClassID\$uniqueInstanceIDName")
                    $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                    $installSetupDevTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                           
                }
                else {
                    <#
                    $programsKeys=$BaseKeyObject.OpenSubKey("$deviceClassID\$uniqueInstanceIDName")
                    if($programsKeys){
                        $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                        $installSetupDevTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                       
                    }
                    #>
                    foreach ($t in $setupAPIDev) {                                                  
                        if($($t.Line) -like "*$uniqueInstanceIDName*"){#if($($t.Line) -like "* - USB\$usbKey\$uniqueInstanceIDName*"){                            
                            $temp = $($t.Context.PostContext) -split ">>>  Section start"             
                            #  system local time, all data in registry are UTC
                            $tz = [System.TimeZoneInfo]::FindSystemTimeZoneById("Romance Standard Time")
                            <# How to get timezone
                            foreach ($zone in [System.TimeZoneInfo]::GetSystemTimeZones()) {
                                Write-Output $zone
                            }
                            #>
                            $installSetupDevTimeDeviceConnected = Get-Date $temp[1] -Format "yyyy-MM-dd HH:mm:ss"   
                            $installSetupDevTimeDeviceConnected = [System.TimeZoneInfo]::ConvertTimeToUtc($installSetupDevTimeDeviceConnected, $tz)
                            $installSetupDevTimeDeviceConnected = Get-Date $installSetupDevTimeDeviceConnected -Format "yyyy-MM-dd HH:mm:ss"                                                  
                        }
                    }
                                       
                }                

                $device=$deviceClassID+"\"+$uniqueInstanceIDName            
                $devicesNames = $wmi.EnumValues($($regKey.HKEY_LOCAL_MACHINE),$device).sNames         
                $serialNumber = $uniqueInstanceIDName
                foreach($value in $devicesNames){                     
                    Switch($value) {
                        "DeviceDesc" {
                        
                        }
                        "Capabilities" {
                            $capabilities = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "Capabilities" -ValueType 4 -WMI $wmi
                        }
                        "ContainerID" {
                    
                        }
                        "HardwareID" {
                    
                        }
                        "CompatibleIDs" {
                        
                        }
                        "FriendlyName" {
                            $friendlyName = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery "$device" -ValueName "FriendlyName" -ValueType 1 -WMI $wmi
                        }
                        "ClassGUID" {
                            $classGUID = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "ClassGUID" -ValueType 1 -WMI $wmi       
                            <#                     
                            if($classGUID){
                                $timeStampKeyToQuery = "SYSTEM\CurrentControlSet\Control\DeviceClasses\$($classGUID.Data)\##?#USB#$usbKey#$uniqueInstanceIDName#$($classGUID.Data)"                                                       
                                $programsKeys = $BaseKeyObject.OpenSubKey($timeStampKeyToQuery)
                                if($programsKeys) {
                                    $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                                    $firstTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                                                             
                                    $cptDate2++
                                }
                                else {
                                    $timeStampKeyToQuery = "SYSTEM\CurrentControlSet\Control\Class\$($classGUID.Data)"                                                       
                                    $programsKeys = $BaseKeyObject.OpenSubKey($timeStampKeyToQuery)
                                    if($programsKeys) {
                                        $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                                        $firstTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                                                                 
                                        $cptDate2++
                                    }
                                }
                            } 
                            #>                           
                        }
                        "Service" {
                            $service = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "Service" -ValueType 1 -WMI $wmi
                            
                            if($($service.Data) -eq "USBSTOR"){
                               #$serialNumber = $uniqueInstanceIDName
                                $usbstorRegistryKey="SYSTEM\CurrentControlSet\Enum\USBSTOR"               
                                #$usbstorRegistryKey="SYSTEM\CurrentControlSet\Enum\USB"               
                                $VendAndProd = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE),"$usbstorRegistryKey")
                                $VendAndProd = $VendAndProd.sNames        
                                foreach($vap in $VendAndProd){
                                    $usbstorVendAndProdRegistryKey="SYSTEM\CurrentControlSet\Enum\USBSTOR\"+$vap              
                                    #$usbstorVendAndProdRegistryKey="SYSTEM\CurrentControlSet\Enum\USB\"+$vap              
                                    $uniqueInstanceIDNameVendAndProd = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE),"$usbstorVendAndProdRegistryKey")
                                    $uniqueInstanceIDNameVendAndProd = $uniqueInstanceIDNameVendAndProd.sNames        
                                    foreach($uiinvap in $uniqueInstanceIDNameVendAndProd){
                                        if($($uiinvap.Substring(0,$uiinvap.Length-2)) -eq $uniqueInstanceIDName){
                                        #if($uiinvap -eq $serialNumber){
                                            $friendlyName = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery "$usbstorVendAndProdRegistryKey\$uiinvap" -ValueName "FriendlyName" -ValueType 1 -WMI $wmi                                                                                    
                                        }
                                    }
                                }                                
                            }
                            $programsKeys=$BaseKeyObject.OpenSubKey("$device")
                            $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                            $lastTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")   
                            if($($service.Data) -eq "disk"){                                                        
                            #else {
                                #$serialNumber = $uniqueInstanceIDName #$uniqueInstanceIDName.Substring(0,$uniqueInstanceIDName).Length-2)                           
                                $usbstorRegistryKey="SYSTEM\CurrentControlSet\Enum\USB"               
                                $VendAndProd = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE),"$usbstorRegistryKey")
                                $VendAndProd = $VendAndProd.sNames        
                                foreach($vap in $VendAndProd){
                                    $usbstorVendAndProdRegistryKey="SYSTEM\CurrentControlSet\Enum\USB\"+$vap              
                                    $uniqueInstanceIDNameVendAndProd = $wmi.EnumKey($($regKey.HKEY_LOCAL_MACHINE),"$usbstorVendAndProdRegistryKey")
                                    $uniqueInstanceIDNameVendAndProd = $uniqueInstanceIDNameVendAndProd.sNames        
                                    foreach($uiinvap in $uniqueInstanceIDNameVendAndProd){
                                        if($uiinvap -eq $serialNumber){
                                            $programsKeys=$BaseKeyObject.OpenSubKey("$usbstorVendAndProdRegistryKey\$uiinvap")
                                            $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                                            $lastTimeDeviceConnected = $(Get-Date ([DateTime]::FromFileTime($volumeTimestamp)) -Format "yyyy-MM-dd HH:mm:ss")                                              
                                        }
                                    }
                                }                                
                            }
                        }
                        "Driver" {
                            $driver = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "Driver" -ValueType 1 -WMI $wmi
                            $driverKey="SYSTEM\CurrentControlSet\Control\Class"+"\"+$($driver.Data)
                            $driverDesc = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "DriverDesc" -ValueType 1 -WMI $wmi
                            $driverVersion = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "DriverVersion" -ValueType 1 -WMI $wmi
                            $providerName = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "ProviderName" -ValueType 1 -WMI $wmi
                            $driverDate = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "DriverDate" -ValueType 1 -WMI $wmi
                            $infPath = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "InfPath" -ValueType 1 -WMI $wmi
                            $infSection = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $driverKey -ValueName "InfSection" -ValueType 1 -WMI $wmi

                            $programsKeys=$BaseKeyObject.OpenSubKey($deviceClassID+"\"+$uniqueInstanceIDName)
                            $volumeTimestamp = $programsKeys | Get-RegistryKeyTimestamp
                        }
                        "Mfg" {
                        
                        }
                        "ParentIdPrefix" {
                            $parentIdPrefix = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "ParentIdPrefix" -ValueType 1 -WMI $wmi
                        }      
                        "LocationInformation" {
                            $locationInformation = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery $device -ValueName "LocationInformation" -ValueType 1 -WMI $wmi
                        }           
                    }            
                }   

                #if($classGUID -eq '') {
                    $SymbolicName = Get-ValueByType -RegistryHive $($regKey.HKEY_LOCAL_MACHINE) -RegistryKeyToQuery "$device\Device Parameters" -ValueName "SymbolicName" -ValueType 1 -WMI $wmi                                        
                #}
                $infoUSBDevice = @{}
                $infoUSBDevice.Add("LocationInformation", "$($locationInformation.Data)")
                $infoUSBDevice.Add("InstanceID", "USB\$usbKey\$uniqueInstanceIDName")
                $infoUSBDevice.Add("ClassGUID", "$($classGUID.Data)")
                $infoUSBDevice.Add("SymbolicName", "$($SymbolicName.Data)")
                $infoUSBDevice.Add("SerialNumber", "$($serialNumber)")
                $infoUSBDevice.Add("Capabilities", "$($capabilities.Data)")
                $infoUSBDevice.Add("FriendlyName", "$($friendlyName.Data)")
                $infoUSBDevice.Add("firstTimeDeviceConnected", "$firstTimeDeviceConnected")
                $infoUSBDevice.Add("lastTimeDeviceConnected", "$lastTimeDeviceConnected")
                $infoUSBDevice.Add("installSetupDevTimeDeviceConnected", "$installSetupDevTimeDeviceConnected")
                $infoUSBDevice.Add("DriverDesc", "$($DriverDesc.Data)")
                $infoUSBDevice.Add("DriverVersion", "$($driverVersion.Data)")
                $infoUSBDevice.Add("ProviderName", "$($providerName.Data)")
                $infoUSBDevice.Add("DriverDate", "$($driverDate.Data)")
                $infoUSBDevice.Add("InfPath", "$($infPath.Data)")
                $infoUSBDevice.Add("InfSection", "$($infSection.Data)")
                $infoUSBDevice.Add("ParentIdPrefix", "$($parentIdPrefix.Data)")
                $infoUSBDevice.Add("Service", "$($service.Data)")
                <#$infoUSBDevice = @{
                        'LocationInformation'=$($locationInformation.Data);
                        'InstanceID'="USB\$usbKey\$uniqueInstanceIDName";
                        'ClassGUID'=$classGUID.Data;
                        'SymbolicName'=$SymbolicName.Data;
                        'SerialNumber'=$serialNumber;
                        'Capabilities'=$capabilities.Data;
                        'FriendlyName'=$friendlyName.Data;
                        'firstTimeDeviceConnected'=$firstTimeDeviceConnected;
                        'lastTimeDeviceConnected'=$lastTimeDeviceConnected;
                        'installSetupDevTimeDeviceConnected'=$installSetupDevTimeDeviceConnected;
                        'DriverDesc'=$DriverDesc.Data;
                        'DriverVersion'=$driverVersion.Data;
                        'ProviderName'=$providerName.Data;
                        'DriverDate'=$driverDate.Data;
                        'InfPath'=$infPath.Data;
                        'InfSection'=$infSection.Data;
                        'ParentIdPrefix'=$parentIdPrefix.Data;
                        'Service'=$service.Data
                        }
                        #>
                $deviceObj = New-Object -TypeName PSObject -Property $infoUSBDevice
                $tempObjectvendorProduct.Devices += $deviceObj
            }
            $byVendorProductArray += $tempObjectvendorProduct
        }
    }
    $BaseKeyObject.Close()
    $byVendorProductArray
}