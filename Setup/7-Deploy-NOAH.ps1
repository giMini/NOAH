<#  
    .SYNOPSIS 
        This script deploy Noah webapp.
    .DESCRIPTION 
        Copy source to intepub and c:\Backend
        Generate Keys
        Create DB and user
        Modify connection.php
    .INPUTS
        n/a
    .OUTPUTS
        n/a
    .EXAMPLE
        .\Deploy-NOAH.ps1
    .LINK 
        n/a
    .NOTES 
        # VERSION 0.1 [WIP] 
        # AUTHOR: Arnaud Landry [https://github.com/arnaud-landry]
#>

# Unzip archive
    Write-Output "Unzip Noah Archive"
    cd C:\Packages\Noah\
    expand-archive -path 'C:\Packages\Noah\noah-master.zip' -destinationpath 'C:\Packages\Noah\'
    Pause

# create DB
    Write-Output "Create Noah Db"
    sqlcmd -S localhost\SQLEXPRESS -i C:\Packages\Noah\NOAH-master\generateDatabase\NOAH_generation.sql
    Pause
    sqlcmd -S localhost\SQLEXPRESS -i C:\Packages\Noah\NOAH-master\generateDatabase\Generate_WhiteList.sql
    Pause
    sqlcmd -S localhost\SQLEXPRESS -i C:\Packages\Noah\NOAH-master\generateDatabase\Generate_VT.sql
    Pause

# Flush generateDatabase folder
    Write-Output "Flush generateDatabase folder"    
    Remove-Item "C:\Packages\Noah\NOAH-master\generateDatabase\" -Force -Recurse
    Pause

# create Noah DB user
    Write-Output "Create Noah Db User"
    $SqlNoahDefaultPassword="SA-PWD-CHANGEME-723387667"
    $SqlNoahNewPassword = Read-Host 'What is your Noah Db password?'
    $SQLCreateNoahUser = "C:\Packages\Noah\SQL-CreateNoahUser.sql"
    (Get-Content $SQLCreateNoahUser).replace($SqlNoahDefaultPassword, $SqlNoahNewPassword) | Set-Content $SQLCreateNoahUser
    sqlcmd -S localhost\SQLEXPRESS -i $SQLCreateNoahUser 
    Pause

# Create secureKeyDatabase.key and autoPasswordDatabase.txt
    Write-Output "Create Keys"

    New-Item -Type Directory "C:\temp\PoshPortal\Keys\"

    $KeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
    $Key = New-Object Byte[] 32   # AES encryption only supports 128-bit (16 bytes), 192-bit (24 bytes) or 256-bit key (32 bytes) 
    [Security.Cryptography.RNGCryptoServiceProvider]::Create().GetBytes($Key)
    $Key | out-file $KeyFile

    $PasswordFile = "C:\temp\PoshPortal\Keys\autoPasswordDatabase.txt"
    $KeyFile = "C:\temp\PoshPortal\Keys\secureKeyDatabase.key"
    $Key = Get-Content $KeyFile
    $Password = $SqlNoahNewPassword | ConvertTo-SecureString -AsPlainText -Force
    $Password | ConvertFrom-SecureString -key $Key | Out-File $PasswordFile
    
    Pause

# Move C:\Packages\Noah\NOAH-master\Backend to c:\
    Write-Output "move backend to c:\"
    Move-Item C:\Packages\Noah\NOAH-master\Backend -Destination C:\
    Pause

# Modify connection.php
    Write-Output "modify connection.php"
    $NoahConn = "C:\Packages\Noah\NOAH-master\connection.php"
    (Get-Content $NoahConn).replace("P@ssword3!", $SqlNoahNewPassword) | Set-Content $NoahConn
    (Get-Content $NoahConn).replace("Administrator", "noah") | Set-Content $NoahConn
    (Get-Content $NoahConn).replace("SQL01", "localhost") | Set-Content $NoahConn
    Pause

# Copy source to inetpub
    Write-Output "move code to intepub"
    Move-Item C:\Packages\Noah\NOAH-master\* -Destination C:\inetpub\wwwroot\noah\ -Force
    Pause

# Change noah database user and servename
    Write-Output "Change noah database user and servename"
    
    $NoahBackend = "C:\Backend\NOAH.ps1"
    
    (Get-Content $NoahBackend).replace("NOAHAdmin", "noah") | Set-Content $NoahBackend
    # 210 : $DatabaseUserAdmin = "NOAHAdmin"
    # 369 : $DatabaseUserAdmin = "NOAHAdmin"
    
    (Get-Content $NoahBackend).replace("SQL01", "localhost") | Set-Content $NoahBackend
    # 215 : $connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
    # 255 : $connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
    # 374 : $connString = "Data Source=SQL01\SQLEXPRESS; Initial Catalog=NOAH; Integrated Security=False"
    
    Pause

# Change Powned user
    #Write-Output "modify powned user"
    # 201 : $UserName = 'Powned\Administrator'
    # 203 : $user = "POWNED\Administrator"
    # 356 : $ServerName = ''
    # 357 : $UserName = 'Powned\Administrator'    
    # 358 : $user = "POWNED\Administrator"
    #Pause

# Misc
    # 261 : $apikey = "6c6d93580478620a7b3d5c1f2255214159f2d6e327859e3d53c71d3216ba2f8e"

# Start Firefox
    Write-Output "Start Firefox" 
    & 'C:\Program Files\Mozilla Firefox\firefox.exe' http://localhost/index.php