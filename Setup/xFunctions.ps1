<#  
    .SYNOPSIS 
        My functions.
    .DESCRIPTION 
        My functions.
    .INPUTS
        n/a
    .OUTPUTS
        n/a
    .EXAMPLE
        .\xFunctions.ps1
    .LINK 
        n/a
    .NOTES 
        # VERSION 1.0 [OK]
        # AUTHOR: Arnaud Landry [https://github.com/arnaud-landry]
#>

Function xDownload-File
{
    # xDownload-File -URI "http://test-debit.free.fr/4096.rnd" -DestinationPath "C:\TMP" -FileName "4096.rnd"
    [cmdletbinding()]
    param(
        [Parameter(Mandatory=$True)]
        [string] $URI,
        [Parameter(Mandatory=$True)]
        [string] $DestinationPath,
        [Parameter(Mandatory=$True)]
        [string] $FileName

    )
    try	
    {
        $output = $DestinationPath+"\"+$FileName
        (New-Object System.Net.WebClient).DownloadFile($URI,$output)
    }	
    catch
    {
        Write-Output "Invalid URI/Output"
    }
}
Function xCreate-Directory
{
    # xCreate-Directory -DestinationPath "C:\TMP"
    [cmdletbinding()]
    param(
        [Parameter(Mandatory=$True)]
        [string] $DestinationPath
    )
    try	
    {
        if (-not (test-path $DestinationPath) ) {
            New-Item -type Directory $DestinationPath |out-null
            Write-Output "$DestinationPath created"
        } else {
            Write-Output "$DestinationPath already exist"
        }
    }	
    catch
    {
        Write-Output "Invalid DestinationPath"
    }
}
Function xInstall-PackageProvider
{
    # xInstall-PackageProvider -ProviderName "xxx"
    [cmdletbinding()]
    param(
        [Parameter(Mandatory=$True)]
        [string] $ProviderName
    )
    try	
    {
        if (-not (Get-PackageProvider -Name $ProviderName ) ) {
            #Install-PackageProvider $ProviderName -ForceBootstrap -Force
            Write-Output "$ProviderName install ..."
        } else {
            Write-Output "$ProviderName already installed"
        }
    }	
    catch
    {
        Write-Output "Invalid DestinationPath"
    }
}
Function xInstall-Module
{
    [cmdletbinding()]
    param(
        [Parameter(Mandatory=$True)]
        [string] $ModuleName,
        [Parameter(Mandatory=$True)]
        [string] $ModuleVersion
    )
    try	
    {
        Install-Module $ModuleName -RequiredVersion $ModuleVersion -Force -SkipPublisherCheck
    }	
    catch
    {
        Write-Output "Invalid $ModuleName"
    }
}
Function xNew-RandomComplexPassword ($length=24)
{
    $Assembly = Add-Type -AssemblyName System.Web
    $password = [System.Web.Security.Membership]::GeneratePassword($length,2)
    return $password
}