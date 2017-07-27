Function Show-ProcessTree {
[CmdletBinding()]
Param(
	[Parameter(Position = 0, Mandatory = $true)]
	$ListOfProcess
)
    $tabResult = @()
    function Get-ProcessChildren($tabResult, $P,$Depth=1) {
        $procs | Where-Object {$_.ParentProcessId -eq $p.ProcessID -and $_.ParentProcessId -ne 0} | ForEach-Object {            
            "{0};{1};{2};{3};{4};{5};{6};{7};{8};{9};{10};{11}" -f $Depth,$_.ProcessId,$_.ParentProcessId,$_.Name,$_.sessionID,$_.Handles,$_.CreationDate,$_.Path,$_.CommandLine,$_.Description,$($_.GetOwner().User),$($_.GetOwner().Domain)
            Get-ProcessChildren $tabResult $_ (++$Depth)
            $Depth--
        }
    }
    $filter = {-not (Get-Process -Id $_.ParentProcessId -ErrorAction SilentlyContinue) -or $_.ParentProcessId -eq 0}
    $procs = $ListOfProcess    
    $top = $procs | Where-Object $filter | Sort-Object ProcessID
    foreach ($p in $top) {        
        $tabResult += "0;$($p.ProcessId);$($p.ParentProcessId);$($p.Name);$($p.sessionID);$($p.Handles);$($p.CreationDate);$($p.Path);$($p.CommandLine);$($p.Description);$($p.GetOwner().User);$($p.GetOwner().Domain)"
        $tabResult += Get-ProcessChildren $tabResult $p
    }
    $tabResult   
}

function Get-ProcessTree {
    [CmdletBinding()]
    param([string]$ComputerName, $Credential, [int]$IndentSize = 2)
    
    $indentSize   = [Math]::Max(1, [Math]::Min(12, $indentSize))
    $computerName = ($computerName, ".")[[String]::IsNullOrEmpty($computerName)]
    $processes    = Get-WmiObject Win32_Process -Credential $Credential -ComputerName $computerName
    $pids         = $processes | select -ExpandProperty ProcessId
    $parents      = $processes | select -ExpandProperty ParentProcessId -Unique
    $liveParents  = $parents | ? { $pids -contains $_ }
    $deadParents  = Compare-Object -ReferenceObject $parents -DifferenceObject $liveParents `
                  | select -ExpandProperty InputObject
    $processByParent = $processes | Group-Object -AsHashTable ParentProcessId
    #$Depth,$_.ProcessId,$_.ParentProcessId,$_.Name,$_.sessionID,$_.Handles,$_.CreationDate,$_.Path,$_.CommandLine,$_.Description
    function Write-ProcessTree($process, [int]$level = 0) {
        $id = $process.ProcessId
        $parentProcessId = $process.ParentProcessId
        $process `
        | Add-Member NoteProperty Id $id -PassThru `
        | Add-Member NoteProperty ParentId $parentProcessId -PassThru `
        | Add-Member NoteProperty Level $level -PassThru
        $processByParent.Item($id) `
        | ? { $_ } `
        | % { Write-ProcessTree $_ ($level + 1) }
    }

    $processes `
    | ? { $_.ProcessId -ne 0 -and ($_.ProcessId -eq $_.ParentProcessId -or $deadParents -contains $_.ParentProcessId) } `
    | % { Write-ProcessTree $_ }
}
<#
$test = Get-ProcessTree -ComputerName $strComputer -Credential $Credential | select Level, Id, ParentId, Name, SessionID, Handles, CreationDate, Path, CommandLine, Description, UserName, DomainName           
foreach ($t in $test) {

$($t.GetOwner().User)
}
#>