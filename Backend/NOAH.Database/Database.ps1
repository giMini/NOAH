# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Connect-Database' - connect to a SQL database
# ________________________________________________________________________
Function Connect-Database{
[CmdletBinding()]  
    Param (
        [Parameter(Mandatory=$true)]$ConnString,
        $Credential
    ) 
    $sqlConnection = new-object System.Data.SqlClient.SqlConnection
    $sqlConnection.ConnectionString = $connString  
    $userName = $Credential.UserName  
    $password = $Credential.Password
    $password.MakeReadOnly()
    $sqlConnection.Credential = New-Object System.Data.SqlClient.SqlCredential($userName, $password);    
    return $sqlConnection
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Count-Record' - count number of record about a query
# ________________________________________________________________________
Function Count-Record {
[CmdletBinding()]  
    Param (
        [Parameter(Mandatory=$true)]$Query
    ) 
    $queryText = $query
    $sqlCommand = $sqlConnection.CreateCommand()
    $sqlCommand.CommandText = $QueryText
    $dataAdapter = new-object System.Data.SqlClient.SqlDataAdapter $sqlCommand
    $dataset = new-object System.Data.Dataset
    $dataAdapter.Fill($dataset) | Out-Null
    $nbRecord = ($dataset.Tables[0].recordCount)
    return $nbRecord
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Select-FromDatabase' - select query
# ________________________________________________________________________
Function Select-FromDatabase {
[CmdletBinding()]  
    Param (
        [Parameter(Mandatory=$true)]$Query
    )   
    $queryText = $query
    $sqlCommand = $sqlConnection.CreateCommand()
    $sqlCommand.CommandText = $QueryText
    $dataAdapter = new-object System.Data.SqlClient.SqlDataAdapter $sqlCommand
    $dataset = new-object System.Data.Dataset
    $dataAdapter.Fill($dataset) | Out-Null
    $record = ($dataset.Tables[0])
    return $record
}

# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Insert-IntoDatabase' - insert record in a SQL table
# ________________________________________________________________________
Function Insert-IntoDatabase{   
[CmdletBinding()]  
    Param (
        [Parameter(Mandatory=$true)]$SQLCommand,
        [Parameter(Mandatory=$true)]$Query
    )       
    $sqlCommand.CommandText = $query
    try{
        $sqlCommand.executenonquery() | Out-Null
    }
    catch {
        Write-Output $Query
        Write-Output $_.Exception
    }
}

Function Insert-IntoDatabaseRecordLastID{  
[CmdletBinding()]  
    Param (
        [Parameter(Mandatory=$true)]$SQLCommand,
        [Parameter(Mandatory=$true)]$Query
    )        

    $SQLCommand.CommandText = $Query
    try{
        $newId = $SQLCommand.ExecuteScalar();
        return $newID
    }
    catch {
        Write-Output $Query
        Write-Output $_.Exception
    }
}