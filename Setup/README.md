Tested on Windows 2016 Full Desktop (on Azure) , Powershell 5.1 , Basic A3 (4 vcpus, 7 GB memory)

- Create a folder c:\Install
    * New-Item -Type Directory "c:\Install"

- Download all script under Setup folder (more part will move to DSC soon)

- Run in this order : AS ADMISTRATOR (in ISE or a Powershell Console)
    * 1_Download-Src.ps1 -» Powershell
        =» Download source in C:\Packages
    
    * 2_Install-Modules.ps1 -» Powershell
        =» install some modules

    * 3-Install-SQL.ps1 -» Powershell
        =» Install SQL, create a testdb/testuser and SMS
        =» Wait until SMS is completly installed. (it takes some time))

    * REBOOT

    * 6-Install_IISPHP.ps1 -» DSC + Powershell
        =» you can check your config with : 
        =» http://localhost/
        =» http://localhost/phpinfo.php -» to check your php configuration and mssql extension
        =» http://localhost/testsql.php -» to perform a simple db connection on the testdb

    * 7-Deploy-NOAH.ps1 -» Powershell
        =» Noah (Frontend) is here :) http://localhost/index.php

- Enjoy with your first Hunt : 
    - Create your CSV file in C:\Backend\ like that : 
        ServerName
        localhost
    - Update the "POWNED user" information in C:\Backend\NOAH.ps1
    - Run NOAH : 
        cd C:\Backend
        .\NOAH.ps1 -Processor -Memory -InstalledPrograms -Netstat -AMCache -Prefetch -EnableHash -HuntDescription "This is a test"