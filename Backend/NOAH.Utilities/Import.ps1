# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Read-OpenFileDialog' - Open an open File Dialog box
# ________________________________________________________________________
Function Read-OpenFileDialog([string]$InitialDirectory, [switch]$AllowMultiSelect) {      
    [System.Reflection.Assembly]::LoadWithPartialName("System.windows.forms") | Out-Null
    $openFileDialog = New-Object System.Windows.Forms.OpenFileDialog        
    $openFileDialog.ShowHelp = $True    # http://www.sapien.com/blog/2009/02/26/primalforms-file-dialog-hangs-on-windows-vista-sp1-with-net-30-35/
    $openFileDialog.initialDirectory = $initialDirectory
    $openFileDialog.filter = "csv files (*.csv)|*.csv|All files (*.*)| *.*"
    $openFileDialog.FilterIndex = 1
    $openFileDialog.ShowDialog() | Out-Null
    return $openFileDialog.filename
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'ListFile' - get server based on a CSV file
# ________________________________________________________________________
Function ListFile {	
    $fileOpen = Read-OpenFileDialog 
    if($fileOpen -ne '') {	
		$colComputers = Import-Csv $fileOpen
    }
    $colComputers
}