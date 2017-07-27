Function Parse-Prefetch {


<#
.SYNOPSIS
	Parses the data within a Prefetch file (.pf)

.NOTES
	Author: David Howell
	Last Modified:12/18/2015
	
	Info regarding Prefetch data structures was pulled from the following articles:
	http://www.forensicswiki.org/wiki/Windows_Prefetch_File_Format
	https://github.com/libyal/libscca/blob/master/documentation/Windows%20Prefetch%20File%20(PF)%20format.asciidoc
    Thanks to Yogesh Khatri for this info.
	http://www.swiftforensics.com/2010/04/the-windows-prefetchfile.html
	http://www.swiftforensics.com/2013/10/windows-prefetch-pf-files.html
#>

[CmdletBinding()]Param(
	[Parameter(Mandatory=$True)][String]$FilePath
)

$ASCIIEncoding = New-Object System.Text.ASCIIEncoding
$UnicodeEncoding = New-Object System.Text.UnicodeEncoding

if (Test-Path -Path $FilePath) {
	# Open a FileStream to read the file, and a BinaryReader so we can read chunks and parse the data
	$FileStream = New-Object System.IO.FileStream -ArgumentList ($FilePath, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read)
	$BinReader = New-Object System.IO.BinaryReader $FileStream
	
	# Create a Custom Object to store prefetch info
	$TempObject = "" | Select-Object -Property Name, Hash, LastExecutionTime, NumberOfExecutions
	
	##################################
	# Parse File Information Section #
	##################################
	
	# 4 Bytes - Version Indicator
	$Version = [System.BitConverter]::ToString($BinReader.ReadBytes(4)) -replace "-",""
	# 4 Bytes - "SCCA" Signature
	$ASCIIEncoding.GetString($BinReader.ReadBytes(4)) | Out-Null
	# 4 Bytes - unknown purpose
	# Value is 0x0F000000 for WinXP or 0x11000000 for Win7/8
	[System.BitConverter]::ToString($BinReader.ReadBytes(4)) -replace "-","" | Out-Null
	# 4 Bytes - size of the Prefetch file
	$TempObject | Add-Member -MemberType NoteProperty -Name "PrefetchSize" -Value ([System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0))
	# 60 bytes - Unicode encoded executable name
	$TempObject.Name = $UnicodeEncoding.GetString($BinReader.ReadBytes(60))
	# 4 bytes - the prefetch hash
	$TempObject.Hash = [System.BitConverter]::ToString($BinReader.ReadBytes(4)) -replace "-",""
	# 4 bytes - unknown purpose
	$BinReader.ReadBytes(4) | Out-Null
	
	# Use Version Indicator to determine prefetch structure type and switch to the appropriate processing
	switch ($Version) {
		# Windows XP Structure
		"11000000" {
			##################################
			# Parse File Information Section #
			##################################
			
			# 4 bytes - Offset to Metrics Array
			$MetricsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Metrics Array
			$MetricsArrayEntries  = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Number of Entries in Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Offset to Filename Strings Array
			$FilenamesArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Filename Strings Array
			$FilenamesArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Volume Information Array
			$VolumeInfoArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Volume Information Array
			$VolumeInfoArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Volume Information Array
			$VolumeInfoArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 8 bytes - Last Execution Time
			$TempObject.LastExecutionTime = [DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G")
			# 16 bytes - Unknown
			$BinReader.ReadBytes(16) | Out-Null
			# 4 bytes - Execution Count
			$TempObject.NumberOfExecutions = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null			
			
			#######################
			# Parse Metrics Array #
			#######################
			
			# Loop through the metrics array and parse the information
            $fileNames = ''
			for ($i=1; $i -le $MetricsArrayEntries; $i++) {
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Filename String Offset
				$FileNameOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Filename String length in unicode characters
				$FileNameLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown Flags
				$BinReader.ReadBytes(4) | Out-Null
				
				# Read the File Name out of Filename Strings array using the Offset and Length we just parsed
				
				# Store Current Location in Section A so we can come back
				$CurrentLocation = $FileStream.Position
				# Change File Stream Position to File Name Offset
				$FileStream.Position = $FilenamesArrayOffset + $FileNameOffset
				# Read the File Name
				#$TempObject | Add-Member -MemberType NoteProperty -Name "Filename$($i)" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
                if($i -ne 1) { $fileNames += ';'}
                $fileNames += ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
				# Change back to location in the metrics array
				$FileStream.Position = $CurrentLocation
				
				Remove-Variable -Name FileNameOffset
				Remove-Variable -Name FileNameLength
				Remove-Variable -Name CurrentLocation
			}
            $TempObject | Add-Member -MemberType NoteProperty -Name "Filenames" -Value $fileNames
			Remove-Variable -Name i
			
			##################################
			# Parse Volume Information Array #
			##################################
			
			$FileStream.Position = $VolumeInfoArrayOffset
			for ($i=1; $i -le $VolumeInfoArrayEntries; $i++) {
				# 4 bytes - Volume path name offset
				$VolumePathOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Volume path name length
				$VolumePathLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 8 bytes - Volume creation time
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_CreationTime" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
				# 4 bytes - Volume serial number
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_SerialNumber" -Value ([System.BitConverter]::ToString($BinReader.ReadBytes(4),0) -replace "-","")
				# 4 bytes - Offset to NTFS File References array
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Length of NTFS File References array
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Directory Strings array offset
				$DirectoryStringsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Directory Strings array number of entries
				$DirectoryStringsArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				
				# Read the Volume Path String using the offset and length we parsed
				$FileStream.Position = $VolumeInfoArrayOffset + $VolumePathOffset
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Path" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($VolumePathLength * 2)))
				
				# Move to the Directory Strings Array and read the strings
				$FileStream.Position = $VolumeInfoArrayOffset + $DirectoryStringsArrayOffset
				for ($j=1; $j -le $DirectoryStringsArrayEntries; $j++) {
					# 2 bytes - directory string length
					$DirectoryStringLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)
					$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Directory$($j)" -Value $UnicodeEncoding.GetString($BinReader.ReadBytes($DirectoryStringLength * 2 + 2))
					Remove-Variable -Name DirectoryStringLength
				}
				Remove-Variable -Name VolumePathOffset
				Remove-Variable -Name VolumePathLength
				Remove-Variable -Name DirectoryStringsArrayOffset
				Remove-Variable -Name DirectoryStringsArrayEntries
				Remove-Variable -Name j
			}
		}
		
		# Windows Vista / 7 Structure
		"17000000" {
			##################################
			# Parse File Information Section #
			##################################
	
			# 4 bytes - Offset to Metrics Array
			$MetricsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Metrics Array
			$MetricsArrayEntries  = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Number of Entries in Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Offset to Filename Strings Array
			$FilenamesArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Filename Strings Array
			$FilenamesArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Volume Information Array
			$VolumeInfoArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Volume Information Array
			$VolumeInfoArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Volume Information Array
			$VolumeInfoArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 8 bytes - Unknown
			$BinReader.ReadBytes(8) | Out-Null
			# 8 bytes - Last Execution Time
			$TempObject.LastExecutionTime = [DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G")
			# 16 bytes - Unknown
			$BinReader.ReadBytes(16) | Out-Null
			# 4 bytes - Execution Count
			$TempObject.NumberOfExecutions = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null
			# 80 bytes - Unknown
			$BinReader.ReadBytes(80) | Out-Null
			
			#######################
			# Parse Metrics Array #
			#######################
			
			# Loop through the metrics array and parse the information
            $fileNames = ''
			for ($i=1; $i -le $MetricsArrayEntries; $i++) {
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Filename string offset
				$FileNameOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Filename string length in Unicode characters
				$FileNameLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown Flags
				$BinReader.ReadBytes(4) | Out-Null
				# 8 bytes - NTFS File Reference
				$BinReader.ReadBytes(8) | Out-Null
				
				# Read the File Name out of the Filename Strings array using the Offset and Length we just parsed
				
				# Store Current Location in Section A so we can come back
				$CurrentLocation = $FileStream.Position
				# Change File Stream Position to File Name Offset
				$FileStream.Position = $FilenamesArrayOffset + $FileNameOffset
				# Read the File Name
				#$TempObject | Add-Member -MemberType NoteProperty -Name "Filename$($i)" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
                if($i -ne 1) { $fileNames += ';'}
                $fileNames += ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
				# Change back to location in the metrics array
				$FileStream.Position = $CurrentLocation
				
				Remove-Variable -Name FileNameOffset
				Remove-Variable -Name FileNameLength
				Remove-Variable -Name CurrentLocation
			}
            $TempObject | Add-Member -MemberType NoteProperty -Name "Filenames" -Value $fileNames
			Remove-Variable -Name i
			
			# Loop through the Volume Information array and parse entries
			$FileStream.Position = $VolumeInfoArrayOffset
			for ($i=1; $i -le $VolumeInfoArrayEntries; $i++) {
				# 4 bytes - Volume name path offset
				$VolumePathOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Volume name path length
				$VolumePathLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 8 bytes - Volume Creation time
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_CreationTime" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
				# 4 bytes - Volume Serial Number
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_SerialNumber" -Value ([System.BitConverter]::ToString($BinReader.ReadBytes(4),0) -replace "-","")
				# 4 bytes - Offset to NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Length of NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Directory Strings array ofset
				$DirectoryStringsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Directory strings array number of entries
				$DirectoryStringsArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 28 bytes - Unknown
				$BinReader.ReadBytes(28) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 28 bytes - Unknown
				$BinReader.ReadBytes(28) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null

				# Read the Volume Path String
				$FileStream.Position = $VolumeInfoArrayOffset + $VolumePathOffset
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Path" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($VolumePathLength * 2)))
				
				# Move to the Directory Strings Array and read the strings
				$FileStream.Position = $VolumeInfoArrayOffset + $DirectoryStringsArrayOffset
				for ($j=1; $j -le $DirectoryStringsArrayEntries; $j++) {
					$DirectoryStringLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)
					$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Directory$($j)" -Value $UnicodeEncoding.GetString($BinReader.ReadBytes($DirectoryStringLength * 2 + 2))
					Remove-Variable -Name DirectoryStringLength
				}
				Remove-Variable -Name VolumePathOffset
				Remove-Variable -Name VolumePathLength
				Remove-Variable -Name DirectoryStringsArrayOffset
				Remove-Variable -Name DirectoryStringsArrayEntries
				Remove-Variable -Name j
			}
		}
		
		# Windows 8 Structure
		"1A000000" {
			# Remove LastExecutionTime since there are 8 instead of 1
			$TempObject.PSObject.Properties.Remove("LastExecutionTime")
			
			##################################
			# Parse File Information Section #
			##################################
			
			# 4 bytes - Offset to Metrics Array
			$MetricsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Metrics Array
			$MetricsArrayEntries  = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Number of Entries in Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Offset to Filename Strings Array
			$FilenamesArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Filename Strings Array
			$FilenamesArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Volume Information Array
			$VolumeInfoArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Volume Information Array
			$VolumeInfoArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Volume Information Array
			$VolumeInfoArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 8 bytes - Unknown
			$BinReader.ReadBytes(8) | Out-Null
			# 8 bytes - 1st Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_1" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 2nd Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_2" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 3rd Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_3" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 4th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_4" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 5th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_5" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 6th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_6" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 7th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_7" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 8th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_8" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 16 bytes - Unknown
			$BinReader.ReadBytes(16) | Out-Null
			# 4 bytes - Execution Count
			$TempObject.NumberOfExecutions = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null
			# 88 bytes - Unknown
			$BinReader.ReadBytes(84) | Out-Null
			
			#######################
			# Parse Metrics Array #
			#######################
			$FileStream.Position = $MetricsArrayOffset
			# Loop through the metrics array and parse the information
            $fileNames = ''
			for ($i=1; $i -le $MetricsArrayEntries; $i++) {
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Filename string offset
				$FileNameOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Filename string length in Unicode characters
				$FileNameLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown Flags
				$BinReader.ReadBytes(4) | Out-Null
				# 8 bytes - NTFS File Reference
				$BinReader.ReadBytes(8) | Out-Null
				
				# Read the File Name out of the Filename Strings array using the Offset and Length we just parsed
				
				# Store Current Location in Section A so we can come back
				$CurrentLocation = $FileStream.Position
				# Change File Stream Position to File Name Offset
				$FileStream.Position = $FilenamesArrayOffset + $FileNameOffset
				# Read the File Name
				#$TempObject | Add-Member -MemberType NoteProperty -Name "Filename$($i)" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
                if($i -ne 1) { $fileNames += ';'}
                $fileNames += ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
				# Change back to location in the metrics array
				$FileStream.Position = $CurrentLocation
				
				Remove-Variable -Name FileNameOffset
				Remove-Variable -Name FileNameLength
				Remove-Variable -Name CurrentLocation
			}
            $TempObject | Add-Member -MemberType NoteProperty -Name "Filenames" -Value $fileNames
			Remove-Variable -Name i
			
			# Loop through the Volume Information array and parse entries
			$FileStream.Position = $VolumeInfoArrayOffset
			for ($i=1; $i -le $VolumeInfoArrayEntries; $i++) {
				# 4 bytes - Volume name path offset
				$VolumePathOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Volume name path length
				$VolumePathLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 8 bytes - Volume Creation time
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_CreationTime" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
				# 4 bytes - Volume Serial Number
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_SerialNumber" -Value ([System.BitConverter]::ToString($BinReader.ReadBytes(4),0) -replace "-","")
				# 4 bytes - Offset to NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Length of NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Directory Strings array ofset
				$DirectoryStringsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Directory strings array number of entries
				$DirectoryStringsArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 28 bytes - Unknown
				$BinReader.ReadBytes(28) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 28 bytes - Unknown
				$BinReader.ReadBytes(28) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null

				# Read the Volume Path String
				$FileStream.Position = $VolumeInfoArrayOffset + $VolumePathOffset
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Path" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($VolumePathLength * 2)))
				
				# Move to the Directory Strings Array and read the strings
				$FileStream.Position = $VolumeInfoArrayOffset + $DirectoryStringsArrayOffset
				for ($j=1; $j -le $DirectoryStringsArrayEntries; $j++) {
					$DirectoryStringLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)
					$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Directory$($j)" -Value $UnicodeEncoding.GetString($BinReader.ReadBytes($DirectoryStringLength * 2 + 2))
					Remove-Variable -Name DirectoryStringLength
				}
				Remove-Variable -Name VolumePathOffset
				Remove-Variable -Name VolumePathLength
				Remove-Variable -Name DirectoryStringsArrayOffset
				Remove-Variable -Name DirectoryStringsArrayEntries
				Remove-Variable -Name j
			}
		}
		
		# Windows 10 Structure
		"1E000000" {
			# Remove LastExecutionTime since there are 8 instead of 1
			$TempObject.PSObject.Properties.Remove("LastExecutionTime")
			
			##################################
			# Parse File Information Section #
			##################################
			
			# 4 bytes - Offset to Metrics Array
			$MetricsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Metrics Array
			$MetricsArrayEntries  = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Number of Entries in Trace Chains Array
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Offset to Filename Strings Array
			$FilenamesArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Filename Strings Array
			$FilenamesArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Offset to Volume Information Array
			$VolumeInfoArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Number of Entries in Volume Information Array
			$VolumeInfoArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Length of Volume Information Array
			$VolumeInfoArrayLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 8 bytes - Unknown
			$BinReader.ReadBytes(8) | Out-Null
			# 8 bytes - 1st Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_1" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 2nd Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_2" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 3rd Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_3" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 4th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_4" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 5th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_5" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 6th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_6" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 7th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_7" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 8 bytes - 8th Last Execution Time
			$TempObject | Add-Member -MemberType NoteProperty -Name "LastExecutionTime_8" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
			# 16 bytes - Unknown
			$BinReader.ReadBytes(16) | Out-Null
			# 4 bytes - Execution Count
			$TempObject.NumberOfExecutions = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null
			# 4 bytes - Unknown
			$BinReader.ReadBytes(4) | Out-Null
			# 88 bytes - Unknown
			$BinReader.ReadBytes(84) | Out-Null
			
			#######################
			# Parse Metrics Array #
			#######################
			$FileStream.Position = $MetricsArrayOffset
			# Loop through the metrics array and parse the information
            $fileNames = ''
			for ($i=1; $i -le $MetricsArrayEntries; $i++) {
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 4 bytes - Filename string offset
				$FileNameOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Filename string length in Unicode characters
				$FileNameLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown Flags
				$BinReader.ReadBytes(4) | Out-Null
				# 8 bytes - NTFS File Reference
				$BinReader.ReadBytes(8) | Out-Null
				
				# Read the File Name out of the Filename Strings array using the Offset and Length we just parsed
				
				# Store Current Location in Section A so we can come back
				$CurrentLocation = $FileStream.Position
				# Change File Stream Position to File Name Offset
				$FileStream.Position = $FilenamesArrayOffset + $FileNameOffset
				# Read the File Name
				#$TempObject | Add-Member -MemberType NoteProperty -Name "Filename$($i)" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
                if($i -ne 1) { $fileNames += ';'}
                $fileNames += ($UnicodeEncoding.GetString($BinReader.ReadBytes($FileNameLength * 2)))
				# Change back to location in the metrics array
				$FileStream.Position = $CurrentLocation
				
				Remove-Variable -Name FileNameOffset
				Remove-Variable -Name FileNameLength
				Remove-Variable -Name CurrentLocation
			}
            $TempObject | Add-Member -MemberType NoteProperty -Name "Filenames" -Value $fileNames
			Remove-Variable -Name i
			
			# Loop through the Volume Information array and parse entries
			$FileStream.Position = $VolumeInfoArrayOffset
			for ($i=1; $i -le $VolumeInfoArrayEntries; $i++) {
				# 4 bytes - Volume name path offset
				$VolumePathOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Volume name path length
				$VolumePathLength = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 8 bytes - Volume Creation time
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_CreationTime" -Value ([DateTime]::FromFileTime([System.BitConverter]::ToUInt64($BinReader.ReadBytes(8),0)).ToString("G"))
				# 4 bytes - Volume Serial Number
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_SerialNumber" -Value ([System.BitConverter]::ToString($BinReader.ReadBytes(4),0) -replace "-","")
				# 4 bytes - Offset to NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Length of NTFS File References
				[System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0) | Out-Null
				# 4 bytes - Directory Strings array ofset
				$DirectoryStringsArrayOffset = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Directory strings array number of entries
				$DirectoryStringsArrayEntries = [System.BitConverter]::ToUInt32($BinReader.ReadBytes(4),0)
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 24 bytes - Unknown
				$BinReader.ReadBytes(24) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null
				# 24 bytes - Unknown
				$BinReader.ReadBytes(24) | Out-Null
				# 4 bytes - Unknown
				$BinReader.ReadBytes(4) | Out-Null

				# Read the Volume Path String
				$FileStream.Position = $VolumeInfoArrayOffset + $VolumePathOffset
				$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Path" -Value ($UnicodeEncoding.GetString($BinReader.ReadBytes($VolumePathLength * 2)))
				
				# Move to the Directory Strings Array and read the strings
				$FileStream.Position = $VolumeInfoArrayOffset + $DirectoryStringsArrayOffset
				for ($j=1; $j -le $DirectoryStringsArrayEntries; $j++) {
					$DirectoryStringLength = [System.BitConverter]::ToUInt16($BinReader.ReadBytes(2),0)
					$TempObject | Add-Member -MemberType NoteProperty -Name "Volume$($i)_Directory$($j)" -Value $UnicodeEncoding.GetString($BinReader.ReadBytes($DirectoryStringLength * 2 + 2))
					Remove-Variable -Name DirectoryStringLength
				}
				Remove-Variable -Name VolumePathOffset
				Remove-Variable -Name VolumePathLength
				Remove-Variable -Name DirectoryStringsArrayOffset
				Remove-Variable -Name DirectoryStringsArrayEntries
				Remove-Variable -Name j
			}
		}
		
		
	}
	
	$TempObject
}
}