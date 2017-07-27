# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'getTasks' - get scheduled tasks on remote server 
# ________________________________________________________________________
Function Get-Tasks {
Param(
$Schedule,
$Credential,
$Computer
)
    $out = @()
    # Get root tasks
    $allTasks = $schedule.GetFolder("\").GetTasks(0) 
    foreach($task in $allTasks) {
        $xml = [xml]$task.xml
        $actions = ""
        $arguments = ""
        $md5 = "no hash"
        $name = $task.Name
        $name = $name.replace("'","")    
        $path = $task.Path
        $path = $path.replace("'","")                
        $lastRunTime = $task.LastRunTime
        $nextRunTime = $task.NextRunTime
        $actions = ($xml.Task.Actions.Exec | % { "$($_.Command)" }) -join "`n"
        $actions = $actions.replace("""","")   
        $actions = [System.Environment]::ExpandEnvironmentVariables($actions)  
        $arguments = ($xml.Task.Actions.Exec | % { "$($_.Arguments)" })
        $runAs = ($xml.Task.Principals.principal.userID)
<#
        if($actions -and (Test-Path -Path $actions)){
            $hash = [Security.Cryptography.HashAlgorithm]::Create( "MD5" )
            $stream = ([IO.StreamReader]"$actions").BaseStream             
            $md5 = -join ($hash.ComputeHash($stream) | ForEach { "{0:x2}" -f $_ })
            $stream.Close()
        }
        #>
        if($EnableHash) {
        $md5 = Invoke-Command -ComputerName $Computer -Credential $Credential -ScriptBlock {
                param($actions)
                $fullPath = Resolve-Path $actions
                $md5h = new-object -TypeName System.Security.Cryptography.SHA256Managed
                $file = [System.IO.File]::OpenRead($fullPath)
                $hash = [System.BitConverter]::ToString($md5h.ComputeHash($file))
                $hash -replace "-", ""
                $file.Dispose()
            } -argumentlist $actions
        }
        $out += New-Object psobject -Property @{
            "Name" = $name
            "Path" = $path
            "LastRunTime" = $lastRunTime
            "NextRunTime" = $nextRunTime
            "Actions" = $actions
            "RunAs" = $runAs
            "Arguments" = $arguments
            "md5" = $md5
        }
        #Write-Output "$name,$path,$lastRunTime,$nextRunTime,$actions,$arguments,$md5,$runAs"
    }

    # Get tasks from subfolders
    $schedule.GetFolder("\").GetFolders(0) | % {
        $out += Get-Tasks -Schedule $($_.Path) -Credential $Credential -Computer $Computer
    }    
    $out
}