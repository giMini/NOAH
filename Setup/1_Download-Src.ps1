<#  
    .SYNOPSIS 
        This script download files from internet.
    .DESCRIPTION 
        Download files defined in  $AppList in this folder $DownloadFolder.
    .INPUTS
        n/a
    .OUTPUTS
        n/a
    .EXAMPLE
        .\Download-Src.ps1
    .LINK 
        n/a
    .NOTES 
        # VERSION 1.4 [OK]
        # AUTHOR: Arnaud Landry [https://github.com/arnaud-landry]
#>

### FUNCT
    . C:\Install\xFunctions.ps1

### VAR
    $DownloadFolder = "C:\Packages"
    $AppList = @()  
        # Firefox
        $AppList += ,@('Firefox-55-0-2.exe', "$DownloadFolder\Firefox", 'https://download.mozilla.org/?product=firefox-55.0.2-SSL&os=win64&lang=en-US')  
        
        # Sql
        $AppList += ,@('SQLServer2016-SSEI-Expr.exe', "$DownloadFolder\Sql", 'https://ib.adnxs.com/seg?add=1&redir=https%3A%2F%2Fgo.microsoft.com%2Ffwlink%2F%3FLinkID%3D799012')  
        $AppList += ,@('SQL2016Express-Configuration.ini', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL2016Express-Configuration.ini')  
        $AppList += ,@('SQL-ChangeSAPassword.sql', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL-ChangeSAPassword.sql')  
        $AppList += ,@('SQL-EnableSA.sql', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL-EnableSA.sql')  
        $AppList += ,@('SQL-CreateTestDb.sql', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL-CreateTestDb.sql')  
        $AppList += ,@('SQL-CreateTestUser.sql', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL-CreateTestUser.sql')  
        $AppList += ,@('TestSql.php', "$DownloadFolder\Sql", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/TestSql.php')  
        $AppList += ,@('SMS-17-2-Setup.exe', "$DownloadFolder\Sql", 'https://go.microsoft.com/fwlink/?linkid=854085')  
        

        #iis
        $AppList += ,@('index.html', "$DownloadFolder\iis", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/iis/index.html') 

        # Php
        $AppList += ,@('vc14_redist_x64.zip', "$DownloadFolder\Php", 'https://github.com/arnaud-landry/noah/raw/master/src/vc14_redist_x64.zip')  
        $AppList += ,@('php-7.0.22-nts-Win32-VC14-x64.zip', "$DownloadFolder\Php", 'https://github.com/arnaud-landry/noah/raw/master/src/php-7.0.22-nts-Win32-VC14-x64.zip')  
        $AppList += ,@('php-7.0.22-nts-Win32-VC14-x64_sqlsrv.zip', "$DownloadFolder\Php", 'https://github.com/arnaud-landry/noah/raw/master/src/php-7.0.22-nts-Win32-VC14-x64_sqlsrv.zip')  
        $AppList += ,@('php.ini', "$DownloadFolder\Php", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/php/php.ini') 
        $AppList += ,@('phpinfo.php', "$DownloadFolder\Php", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/php/phpinfo.php') 

        # Noah
        $AppList += ,@('noah-master.zip', "$DownloadFolder\Noah", 'https://github.com/giMini/NOAH/archive/master.zip')  
        $AppList += ,@('SQL-CreateNoahUser.sql', "$DownloadFolder\Noah", 'https://raw.githubusercontent.com/arnaud-landry/noah/master/sql/SQL-CreateNoahUser.sql')  

### MAIN
    foreach ($App in $AppList) {
        $AppName = $App[0]
        $AppDownloadFolder = $App[1]
        $AppUri = $App[2]
        Write-Output "$AppName, $AppDownloadFolder, $AppUri"
        xCreate-Directory -DestinationPath $AppDownloadFolder
        xDownload-File -URI $AppUri -DestinationPath $AppDownloadFolder -FileName $AppName
    }