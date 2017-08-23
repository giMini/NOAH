![BlackHat Arsenal 2017](https://img.shields.io/badge/BlackHat-Arsenal%202017-yellow.svg?style=flat)
![License](https://img.shields.io/badge/License-BSD%203-red.svg?style=flat) ![PowerShell](https://img.shields.io/badge/Language-PowerShell-blue.svg?style=flat) ![Twitter](https://img.shields.io/badge/twitter-@pabraeken-blue.svg?style=flat)

# NOAH

NOAH is  an agentless open source Incident Response framework based on PowerShell, called "No Agent Hunting" (NOAH), to help security investigation responders to gather a vast number of key artifacts without installing any agent on the endpoints saving precious time.

## Getting Started

Clone the repository or download the files from the project on a Windows computer.

### Automagic installation

1) Create a folder c:\Install
 
2) Move the scripts inside "Setup" folder to c:\Install
 
3) Run in this order : AS ADMISTRATOR (in ISE or a Powershell Console)

```
1_Download-Src.ps1
```
```    
2_Install-Modules.ps1
```    
```    
3-Install-SQL.ps1
```    
! Wait until SMS is completly installed !
 
### Now you can Reboot you computer

```    
6-Install_IISPHP.ps1
```    
```     
7-Deploy-NOAH.ps1
```    

You should be able to run the NOAH frontend from this URL: http://localhost/index.php
 
### Enjoy with your first Hunt

1) Create a CSV file with a text editor in C:\Backend\. Enter data as below:


 ServerName
 server1
 server2
 server3
 
2) In the C:\Backend\NOAH.ps1 script, replace the "POWNED user" with the one you created.

3) Run NOAH :

 ```
 cd C:\Backend
 .\NOAH.ps1 -Processor -Memory -InstalledPrograms -Netstat -AMCache -Prefetch -EnableHash -HuntDescription "This is a test"
```

## Manual installation
### Prerequisites

1) Windows computer with PowerShell installed
2) MSSQL express edition (https://www.microsoft.com/en-us/sql-server/sql-server-editions-express)
3) WAMP  (http://www.wampserver.com/en/), or LAMP if you want to install the web interface of NOAH on a Linux machine

### Installing the Database

1) On the Windows computer, install MSSQL
2) Run the Database generation scripts:
* NOAH_generation.sql
* Generate_WhiteList.sql
* Generate_VT.sql
3) Create the NOAHAdmin user and give it access to the NOAH database
4) Create the following files for the database access:
* secureKeyDatabase.key
* autoPasswordDatabase.txt

You can use the following PowerShell script to create the files:

```
$KeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
$Key = New-Object Byte[] 32   # AES encryption only supports 128-bit (16 bytes), 192-bit (24 bytes) or 256-bit key (32 bytes) 
[Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($Key)
$Key | out-file $KeyFile

$PasswordFile = "C:\temp\PoshPortal\Keys\autoPasswordDatabase.txt"
$KeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
$Key = Get-Content $KeyFile
$Password = "Spring2018" | ConvertTo-SecureString -AsPlainText -Force
$Password | ConvertFrom-SecureString -key $Key | Out-File $PasswordFile
```

### Installing the BackEnd

Copy the "BackEnd" folder on the Windows computer that has Windows PowerShell v4 installed.

Create the following files for the database access:
* secureKey.key
* autoPassword.txt

You can use the following PowerShell script to create the files:

```
$KeyFile = "C:\temp\PoshPortal\Keys\secureKey.key"
$Key = New-Object Byte[] 32   # AES encryption only supports 128-bit (16 bytes), 192-bit (24 bytes) or 256-bit key (32 bytes) 
[Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($Key)
$Key | out-file $KeyFile

$PasswordFile = "C:\temp\PoshPortal\Keys\autoPassword.txt"
$KeyFile = "C:\temp\PoshPortal\Keys\secureKey.key"
$Key = Get-Content $KeyFile
$Password = "Spring2018" | ConvertTo-SecureString -AsPlainText -Force
$Password | ConvertFrom-SecureString -key $Key | Out-File $PasswordFile
```

Configure the following variable at lines 198 and 354. You can use the same script than above:
$UserName = 'Powned\Administrator'
$user = "POWNED\Administrator"
$passwordFile = "C:\temp\PoshPortal\Keys\autoPassword.txt"
$keyFile = "C:\temp\PoshPortal\Keys\secureKey.key"

### Installing the FrontEnd

Modify the connection.php file with your user/password and the name of your database:

```
<?php 
$serverName = "SQL01\SQLEXPRESS"; //serverName\instanceName
$connectionInfo = array("Database"=>"NOAH","UID" => "Administrator","PWD" => "P@ssword3!",);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {    
}else{
     echo "La connexion n'a pu être établie.<br />";
     die(); // print_r( sqlsrv_errors(), true));
```

### Start to Hunt

To be able to hunt your endpoints, you need to use the backend with credentials that are allowed to connect and to retrieve artifacts on the endpoints. 

At the moment, you can only hunt for "All" artifacts from the web interface. If you want to choose what to hunt, do it from the Backend (PowerShell script).

### EXAMPLE: Hunting from the BackEnd

```
.\NOAH.ps1 -Processor -Memory -InstalledPrograms -Netstat -AMCache -Prefetch -EnableHash -HuntDescription "This is a test"
```

## Author

* **Pierre-Alexandre Braeken**

## License

This project is licensed under the BSD 3-clause License - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* Adam Podgorski, co-presenter at BlackHat Arsenal USA 2017
* Mark Russinovich
* Eric Zimmerman
* Nir Sofer
* Shay Levy
* CookieMonster
* David Howell
* Boe Prox
