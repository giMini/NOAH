function Remote-Dumping {
Param(
        $ComputerName, 
        $ScriptPath, 
        $LogDirectoryPath,
        $launchDateFiles
)

    Copy-Item -Path "$ScriptPath\NOAH.ThirdParties\procdump.exe" -Destination "\\$ComputerName\c$\windows\temp\procdump.exe"
    $dumpAProcessPath = "C:\Windows\temp\procdump.exe"
    Run-WmiRemoteProcess $ComputerName "$dumpAProcessPath -mp -n 1 -accepteula c:\windows\temp\artifact_RAM_$ComputerName.dmp"
    Start-Sleep -Seconds 15
    Copy-Item -Path "\\$ComputerName\\c$\windows\temp\artifact_RAM_$ComputerName.dmp" -Destination "$scriptPath\$launchDateFiles\artifact_RAM_$ComputerName.dmp"
    Remove-Item -Force "\\$ComputerName\c$\windows\temp\procdump.exe"
    Remove-Item -Force "\\$ComputerName\c$\windows\temp\artifact_RAM_$ComputerName.dmp"        
}