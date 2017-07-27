[BlackHat Arsenal 2017](https://img.shields.io/badge/BlackHat-Arsenal%202017-yellow.svg?style=flat)
![License](https://img.shields.io/badge/License-BSD%203-red.svg?style=flat) ![PowerShell](https://img.shields.io/badge/Language-PowerShell-blue.svg?style=flat) ![Twitter](https://img.shields.io/badge/twitter-@pabraeken-blue.svg?style=flat)

# NOAH

NOAH is  an agentless open source Incident Response framework based on PowerShell, called "No Agent Hunting" (NOAH), to help security investigation responders to gather a vast number of key artifacts without installing any agent on the endpoints saving precious time.

## Getting Started

Clone the repository or download the files from the project on a Windows computer.

### Prerequisites

1) Windows computer with PowerShell installed
2) MSSQL express edition (https://www.microsoft.com/en-us/sql-server/sql-server-editions-express)
3) WAMP  (http://www.wampserver.com/en/), or LAMP if you want to install the web interface of NOAH on a Linux machine

## Installing the Database

1) On the Windows computer, install MSSQL
2) Run the Database generation scripts:
* NOAH_generation.sql
* Generate_WhiteList.sql
* Generate_VT.sql
3) Create the NOAHAdmin user and give it access to the NOAH database
4) Create the following files for the database access:
* secureKeyDatabase.key
* autoPasswordDatabase.txt
You can use the following PowerShell script to:

```
$KeyFile = "C:\temp\PoshPortal\Keys\secureKey.key"
$Key = New-Object Byte[] 32   # AES encryption only supports 128-bit (16 bytes), 192-bit (24 bytes) or 256-bit key (32 bytes) 
[Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($Key)
$Key | out-file $KeyFile

$PasswordFile = "C:\temp\PoshPortal\Keys\autoPasswordDatabase.txt"
$KeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
$Key = Get-Content $KeyFile
$Password = "Spring2018" | ConvertTo-SecureString -AsPlainText -Force
$Password | ConvertFrom-SecureString -key $Key | Out-File $PasswordFile
```

## Installing the BackEnd

Copy the "BackEnd" folder on the Windows computer that has Windows PowerShell v4 installed.

Configure the following variable at lines 198 and 354. You can use the same script than above:
$UserName = 'Powned\Administrator'
$user = "POWNED\Administrator"
$passwordFile = "C:\temp\PoshPortal\Keys\autoPassword.txt"
$keyFile = "C:\temp\PoshPortal\Keys\secureKey.key"


## Author

* **Pierre-Alexandre Braeken**

## License

This project is licensed under the BSD 3-clause License - see the [LICENSE](LICENSE) file for details

## Acknowledgments

* Adam Podgorski, co-presenter at BlackHat Arsenal USA 2017
* Mark Russinovitch
* Eric Zimmerman
* Nir Sofer
* Shay Levy
* CookieMonster
* David Howell
* Boe Prox
