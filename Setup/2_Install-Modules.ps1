<#  
    .SYNOPSIS 
        This script install modules.
    .DESCRIPTION 
        Install modules listed in $ModulesList with a specfic version
    .INPUTS
        n/a
    .OUTPUTS
        n/a
    .EXAMPLE
        .\Install-Modules.ps1
    .LINK 
        https://docs.microsoft.com/en-us/powershell/module/powershellget/install-module?view=powershell-5.1
        https://docs.microsoft.com/en-us/powershell/module/packagemanagement/install-packageprovider?view=powershell-5.1
    .NOTES 
        # VERSION 1.0 [OK]
        # AUTHOR: Arnaud Landry [https://github.com/arnaud-landry]
#>

### FUNCT
    . C:\Install\xFunctions.ps1

### VAR
    $ModulesList = @()  
        $ModulesList += ,@("Pester", "4.0.6")  
        $ModulesList += ,@("PSScriptAnalyzer", "1.16.0")  
        $ModulesList += ,@("xPSDesiredStateConfiguration", "6.4.0.0")  
        $ModulesList += ,@("xWebAdministration", "1.18.0.0")  
        $ModulesList += ,@("xPhp", "1.2.0.0")  
        $ModulesList += ,@("xSQLServer", "8.0.0.0")  
        $ModulesList += ,@("InvokeBuild", "3.6.4") 

### MAIN
    xInstall-PackageProvider "Nuget"
    xInstall-PackageProvider "PowershellGet"
    Set-PSRepository -Name "PSGallery" -InstallationPolicy Trusted
    Write-Output "PSGallery Trusted"

    foreach ($Module in $ModulesList) {
        $ModuleName = $Module[0]
        $ModuleVersion = $Module[1]
        xInstall-Module -ModuleName $ModuleName -ModuleVersion $ModuleVersion
        Write-Output "$ModuleName installed"
        #$error[0]|select *
    }