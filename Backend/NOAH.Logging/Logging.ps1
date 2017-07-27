Function Start-Log {    
    [CmdletBinding()]  
    Param ([Parameter(Mandatory=$true)][string]$scriptName, [Parameter(Mandatory=$true)][string]$scriptVersion, 
        [Parameter(Mandatory=$true)][string]$streamWriter)
    Process{                  
        $global:streamWriter.WriteLine("================================================================================================")
        $global:streamWriter.WriteLine("[$ScriptName] version [$ScriptVersion] started at $([DateTime]::Now)")
        $global:streamWriter.WriteLine("================================================================================================`n")       
    }
}


Function End-Log { 
    [CmdletBinding()]  
    Param ([Parameter(Mandatory=$true)][string]$streamWriter)  
    Process{    
        $global:streamWriter.WriteLine("`n================================================================================================")
        $global:streamWriter.WriteLine("Script ended at $([DateTime]::Now)")
        $global:streamWriter.WriteLine("================================================================================================")
  
        $global:streamWriter.Close()   
    }
}