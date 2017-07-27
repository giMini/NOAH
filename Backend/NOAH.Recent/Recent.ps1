Function Parse-RecentDocs {
<#
.SYNOPSIS
	Parses the values in the RecentDocs registry key for each user
.NOTES
	Author: David Howell
	Last Modified: 01/11/2016
OUTPUT csv
#>

$ASCIIEncoding = New-Object System.Text.ASCIIEncoding
$UnicodeEncoding = New-Object System.Text.UnicodeEncoding

# Intialize empty array for results
$ResultArray=@()

# Setup HKU:\ PSDrive for us to work with
if (!(Get-PSDrive -PSProvider Registry -Name HKU -ErrorAction SilentlyContinue)) {
	New-PSDrive -PSProvider Registry -Name HKU -Root HKEY_USERS -ErrorAction SilentlyContinue | Out-Null
}

# Get a listing of users in HKEY_USERS, then process for each one
$Users = Get-ChildItem -Path HKU:\ -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Name
ForEach ($User in $Users) {
	# Rename the root of the path so we can query with it
	$UserRoot = $User -replace "HKEY_USERS","HKU:"
	# Get some User Information to determine Username
	$UserInfo = Get-ItemProperty -Path "$($UserRoot)\Volatile Environment" -ErrorAction SilentlyContinue
	$UserName = "$($UserInfo.USERDOMAIN)\$($UserInfo.USERNAME)"
	
	# Query the RecentDocs key for this user
	if (Test-Path -Path $UserRoot\Software\Microsoft\Windows\CurrentVersion\Explorer\RecentDocs) {
		Get-Item -Path $UserRoot\Software\Microsoft\Windows\CurrentVersion\Explorer\RecentDocs | Select-Object -ExpandProperty Property | ForEach-Object {
			$TempObject = New-Object PSObject
			$BinaryValue = Get-ItemProperty -Path $UserRoot\Software\Microsoft\Windows\CurrentVersion\Explorer\RecentDocs -Name $_ | Select-Object -ExpandProperty $_
			$ASCIIValue = $ASCIIEncoding.GetString($BinaryValue)
			$HexValue = [System.BitConverter]::ToString($BinaryValue) -replace "-",""
			# Use Regex to parse the values. 
			if ($HexValue -match "(([A-F0-9]{2}0{2}(?!0000))+[A-F0-9]{2}0{2})(0000..00320+)(([A-F0-9]{2}(?!00))+[A-F0-9]{2})(([A-F0-9](?!EFBE))+[A-F0-9]EFBE0+2E0+)(([A-F0-9]{2}0{2}(?!0000))+[A-F0-9]{2}0{2})(.*)") {
			# I know this looks awful. On my system I see a unicode string, an ascii string, and another unicode string, each with values after them that are unknown.
			# I wanted to return these unknown areas in Hexadecimal format. This required me to run some awful regex against the hexadecimal string
				
				# Group 1 Match is the Unicode item name
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Name" -Value ($UnicodeEncoding.GetString(($matches[1] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))
				
				# Group 3 Match is the unknown space after the first string
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UnknownArea1" -Value $matches[3]
				
				# Group 4 match is the Ascii link file name
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ASCII_Link_Name" -Value ($ASCIIEncoding.GetString(($matches[4] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))
				
				# Group 6 match is the unknown space after the second string
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UnknownArea2" -Value $matches[6]
				
				# Group 8 match is the Unicode link file name
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Unicode_Link_Name" -Value ($UnicodeEncoding.GetString(($matches[8] -split "(..)" | Where-Object {$_} | ForEach-Object { [System.Byte]([System.Convert]::ToInt16($_, 16))})))
				
				# Group 10 match is the unknown space after the final string
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "UnknownArea3" -Value $matches[10]
			} elseif ($ASCIIValue -match "(([^\x00]\x00)+)\x00\x00.\x00\x32\x00+([^\x00]+)\x00\x00.+\x3F\x3F\x00+\x2E\x00+(([^\x00]\x00)+)") {
				# If the previous awful regex doesn't match, try this one
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Name" -Value $UnicodeEncoding.GetString($ASCIIEncoding.GetBytes($matches[1]))
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "ASCII_Link_Name" -Value $matches[3]
				Add-Member -InputObject $TempObject -MemberType NoteProperty -Name "Unicode_Link_Name" -Value $UnicodeEncoding.GetString($ASCIIEncoding.GetBytes($matches[4]))
			}
			$ResultArray += $TempObject
		}
	}
}

$ResultArray
}

Function Parse-RecentFileCache {
    Param (
        [String]$FileToParse,
        [String]$OutputCSV
    )
    # C:\Windows\AppCompat\Programs\
    $bytes = [System.IO.File]::ReadAllBytes($FileToParse)
    $binaryToString=''
    $entry=@()
    $i=0
    $breakToInsert = 0
    $offset = 0
    foreach ($b in $bytes) {
        if($b -eq 0){
            $i++
            if ($i -eq 3) {$breakToInsert = 1}
        }
        else {
            $i = 0
        }
        if($breakToInsert -eq 0 -and $offset -eq 0 -or $b -ne 0) { 
            if($b -ne 0){
                $binaryToString += [System.Text.Encoding]::ASCII.GetString($b)
            }
        }
        else {
            if($offset -eq 1) {
                $offset = 0
                $breakToInsert = 0
            }
            else {            
                $entry += $binaryToString
                $binaryToString = ''
                $breakToInsert = 0
                $offset = 1
            }
        }
    }

    $file = "Program`r`n"
    $i = 0
    foreach($t in $entry) {
        if($i -gt 4) {        
            if($($i % 2) -eq 1){
                $file += $t + "`r`n"
            }
        }
        $i++    
    }

    $file | Out-File -FilePath $OutputCSV
}