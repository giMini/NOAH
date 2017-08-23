<#  
    .SYNOPSIS 
        This script generate a DSC configuration for IIS/PHP.
    .DESCRIPTION 
        Install and configure IIS, PHP (with MSSQL Ext) and create a new website.
        Reboot requiered : VC14 !
    .INPUTS
        n/a
    .OUTPUTS
        n/a
    .EXAMPLE
        .\Install_IISPHP.ps1
    .LINK 
        n/a
    .NOTES 
        # VERSION 1.1 [OK] 
        # AUTHOR: Arnaud Landry [https://github.com/arnaud-landry]
#>

configuration IISPHP 
{ 
    param 
    ( 
        # Target nodes to apply the configuration 
            [string]$NodeName = 'localhost', 
        # Name of the website to create 
            [Parameter(Mandatory)] 
            [ValidateNotNullOrEmpty()] 
            [String]$WebSiteName,  
        # Destination path for Website content 
            [Parameter(Mandatory)] 
            [ValidateNotNullOrEmpty()] 
            [String]$WebsitePath,
        # Package Folder
            [Parameter(Mandatory = $true)]
            [string] $PackageFolder,
        <#
            # xphp , requierements : VC14 Visual C++ 2015 Redist Package (x64)
            # MSVC++ 14.0 _MSC_VER == 1900 (Visual Studio 2015)
                [Parameter(Mandatory = $true)]
                [string] $Vc14DownloadUri,
            # xphp , VC14 x64 Non Thread Safe from http://windows.php.net/download/ 
                [Parameter(Mandatory = $true)]
                [string] $Php7DownloadUri,
            # xphp , sqlsrv ext for php7 VC14 x64 Non Thread Safe
                [Parameter(Mandatory = $true)]
                [string] $Php7ExtDownloadUri,
        #>
            # xphp , destination aka c:\php
                [Parameter(Mandatory = $true)]
                [String] $Php7DestinationPath
        <#
            # xphp , php.ini URI
                [Parameter(Mandatory = $true)]
                [string] $Php7ConfigurationUri,
            # Sql server
                [Parameter(Mandatory = $true)]
                [string] $SqlServerExpress2017Uri,
                [Parameter(Mandatory = $true)]
                [string] $SqlServerExpress2017ConfigurationURI,
                [Parameter(Mandatory = $true)]
                [string] $SqlServerExpress2017SMSUri,
            # WebApp
                [Parameter(Mandatory = $true)]
                [string] $WebAppUri
        #>
    ) 
 
    # Import Resources
        Import-DscResource -ModuleName "PSDesiredStateConfiguration"
        Import-DscResource -ModuleName "xPSDesiredStateConfiguration" -moduleVersion "6.4.0.0"
        Import-DscResource -ModuleName "xWebAdministration" -moduleVersion "1.18.0.0"
        Import-DscResource -ModuleName "xphp" -moduleVersion "1.2.0.0"
        Import-DscResource -ModuleName "xSQLServer" -moduleVersion "8.0.0.0"
    
    # Configuration
    Node $NodeName 
    { 
        # Install the IIS role 
            WindowsFeature IIS 
            { 
                Ensure          = "Present" 
                Name            = "Web-Server" 
            } 
            foreach ($Feature in @("Web-Mgmt-Tools","web-Default-Doc", `
                    "Web-Dir-Browsing","Web-Http-Errors","Web-Static-Content",`
                    "Web-Http-Logging","web-Stat-Compression","web-Filtering",`
                    "web-CGI","web-ISAPI-Ext","web-ISAPI-Filter","Web-Asp-Net45","Web-Mgmt-Service","Web-Mgmt-Console"))
            {
                WindowsFeature "$Feature$Number"
                {
                    Ensure       = "Present"
                    Name         = $Feature
                    DependsOn    = "[WindowsFeature]IIS" 
                }
            }
        
        # Stop the default website 
            xWebsite StopDefaultSite  
            { 
                Ensure          = "Present" 
                Name            = "Default Web Site" 
                State           = "Stopped" 
                PhysicalPath    = "C:\inetpub\wwwroot" 
                DependsOn       = "[WindowsFeature]IIS" 
            }

        # Create WebSiteName Path and Default files (index.html, phpinfo.php and testsql.php)
            File index
            {
                Ensure = "Present"  
                Type = "File" 
                SourcePath = "C:\Packages\iis\index.html"
                DestinationPath = "$WebsitePath\index.html"
            }

            File phpinfo
            {
                Ensure = "Present"  
                Type = "File" 
                SourcePath = "C:\Packages\Php\phpinfo.php"
                DestinationPath = "$WebsitePath\phpinfo.php"
            }
            File testsql
            {
                Ensure = "Present"  
                Type = "File" 
                SourcePath = "C:\Packages\Sql\TestSql.php"
                DestinationPath = "$WebsitePath\TestSql.php"
            }
        # Create the new Website 
            xWebsite NewWebsite
            { 
                Ensure          = "Present" 
                Name            = $WebSiteName 
                State           = "Started" 
                PhysicalPath    = $WebsitePath 
                BindingInfo     = MSFT_xWebBindingInformation 
                                { 
                                Protocol              = "HTTP" 
                                Port                  = 80 
                                } 
                DependsOn       = "[File]index" 
            }
        # Install VC14
            $Vc14Zip = Join-Path $PackageFolder "\php\vc14_redist_x64.zip"
            $Vc14Unzip = Join-Path $PackageFolder "\php\"
            <# =» download with "Download-Src.ps1"
                xRemoteFile Vc14Archive
                {
                    uri = $Vc14DownloadUri
                    DestinationPath = $Vc14Zip
                }
            #>
            Archive Vc14Unzip
            {
                Path = $Vc14Zip
                Destination  = $Vc14Unzip
                #DependsOn = [xRemoteFile]Vc14Archive
            }
            
            $Vc14Exe = Join-Path $PackageFolder "\php\vc_redist_x64.exe"
            Package Vc14Exe
            {
                Ensure = "Present"
                Name = "Microsoft Visual C++ 2015 Redistributable (x64) - 14.0.24212"
                Path = $Vc14Exe
                ProductId = ''
                Arguments = '/install /passive /norestart' # silent mode
            }
            
        # Install PHP
            # Install php 7
                $Php7Zip = Join-Path $PackageFolder "\php\php-7.0.22-nts-Win32-VC14-x64.zip"
                <# =» download with "Download-Src.ps1"
                    xRemoteFile Php7Archive
                    {
                        uri = $Php7DownloadUri
                        DestinationPath = $Php7Zip
                    }
                #>

                Archive Php7Unzip
                {
                    Path = $Php7Zip
                    Destination  = $Php7DestinationPath
                    # DependsOn = [xRemoteFile]Php7Archive
                }

            # Install php 7 Ext mssql
                $Php7ExtZip = Join-Path $PackageFolder "\php\php-7.0.22-nts-Win32-VC14-x64_sqlsrv.zip"
                <# =» download with "Download-Src.ps1"
                    xRemoteFile Php7ExtArchive
                    {
                        uri = $Php7ExtDownloadUri
                        DestinationPath = $Php7ExtZip
                    }
                #>
                Archive Php7ExtUnzip
                {
                    Path = $Php7ExtZip
                    Destination  = "$($Php7DestinationPath)\ext\"
                    #DependsOn = [xRemoteFile]Php7ExtArchive
                }

            # Make sure the php.ini is in the Php folder
                $Php7Configuration = Join-Path $PackageFolder "\php\php.ini"
                <# =» download with "Download-Src.ps1"
                    xRemoteFile Php7IniSrc
                    {
                        uri = $Php7ConfigurationUri
                        DestinationPath = $Php7Configuration
                    }
                #>
                File Php7Ini
                {
                    Ensure = "Present" 
                    Type = "File" 
                    SourcePath = $Php7Configuration
                    DestinationPath = "$($Php7DestinationPath)\php.ini"  
                }

            # Make sure the php cgi module is registered with IIS
                Script FastCGI-IIS
                {
                    SetScript = 
                    { 
                        $PhpCgi = 'C:\php\php-cgi.exe'
                        New-WebHandler -Name "PHP-FastCGI" -Path "*.php" -Verb "*" -Modules "FastCgiModule" -ScriptProcessor $PhpCgi -ResourceType File
                        $configPath = get-webconfiguration 'system.webServer/fastcgi/application' | where-object { $_.fullPath -eq $PhpCgi }
                        if (!$pool) {
                            add-webconfiguration 'system.webserver/fastcgi' -value @{'fullPath' = $PhpCgi }
                        }                
                    }
                    TestScript = { 
                    $result = Get-WebHandler -Name "*php*"
                        if([string]::IsNullOrEmpty($result))
                        {
                            return $false
                        }
                        else{
                            return $true
                        }
                    
                    }
                    GetScript = { @{ Result = (Get-WebHandler -Name "*php*") } }          
                }
            # Make sure the php binary folder is in the path
                Environment PathPhp
                {
                    Name = "Path"
                    Value = ";$($Php7DestinationPath)"
                    Ensure = "Present"
                    Path = $true
                }
    } 
}

cd C:\Install

Write-Output "Build Configuration"
IISPHP -nodename "localhost" `
    -WebSiteName "noah" `
    -WebsitePath "C:\inetpub\wwwroot\noah" `
    -PackageFolder "C:\Packages" `
    -Php7DestinationPath "C:\php"
Pause

Write-Output "Create Checksum" #Pull mode only
New-DscCheckSum -Path ".\IISPHP\" -Force
Pause

Write-Output "Apply Configuration"
Start-DscConfiguration -Path .\IISPHP -Verbose -Wait -Force
Pause

Write-Output "Install Firefox ... wait for it :)"
#https://wiki.mozilla.org/Installer:Command_Line_Arguments
C:\Packages\Firefox\Firefox-55-0-2.exe /S
Pause

Write-Output "Start Firefox" 
& 'C:\Program Files\Mozilla Firefox\firefox.exe' http://localhost http://localhost/phpinfo.php http://localhost/testsql.php