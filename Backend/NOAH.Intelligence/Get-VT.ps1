
#Requires -Version 3

function Post-Http { 
    Param(
        [string]$Url,
        [string]$Parameters
    )
    $httpRequest = New-Object -ComObject Msxml2.XMLHTTP 
    $httpRequest.open("POST", $Url, $false) 
    $httpRequest.setRequestHeader("Content-type","application/x-www-form-urlencoded") 
    $httpRequest.setRequestHeader("Content-length", $Parameters.length); 
    $httpRequest.setRequestHeader("Connection", "close") 
    $httpRequest.send($Parameters) 
    return $httpRequest.responseText 
} 

function Validate-Response {        
    Param(
        $Response
    )
    $responseConverted = $Response | ConvertFrom-JSON    
    $responseObject = New-Object PSObject   
    if($responseConverted){
        foreach ($object in $responseConverted)
        {               
            #Write-Output $object.response_code
            if ($object.response_code -eq 0) {
                #Write-Log -StreamWriter $streamWriter -InfoToLog "Hash not found ($Name/$Path/$md5)"
                #[System.IO.File]::AppendAllText($StreamWriter,"Hash not found ($Name/$Path/$md5)" + ([Environment]::NewLine))
                #Write-Output "Hash not found ($Name/$Path/$md5)"                
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Result" -Value 0   
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Permalink" -Value ''             
            }
            elseif (($object.response_code -eq 1) -and ($object.positives -ne 0)) {
                #[System.IO.File]::AppendAllText($StreamWriter,"===!! Malicious !!=== ($Name/$Path/$md5) ")
                #[System.IO.File]::AppendAllText($StreamWriter,"$($object.Permalink)" + ([Environment]::NewLine))
                #Write-Output "===!! Malicious !!=== ($Name/$Path/$md5)"
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Result" -Value 1
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Permalink" -Value $($object.Permalink)                                         
            }
            elseif (($object.response_code -eq 1)) {
                #[System.IO.File]::AppendAllText($StreamWriter,"Clean ($Name/$Path/$md5)" + ([Environment]::NewLine))
                #Write-Output "Clean ($Name/$Path/$md5)"
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Result" -Value 2                
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Permalink" -Value ''
            }
            elseif ($object.response_code -eq -2) {
                #[System.IO.File]::AppendAllText($StreamWriter,"File queued for analysis ($Name/$Path/$md5) ")
                #[System.IO.File]::AppendAllText($StreamWriter,"$($object.Permalink)" + ([Environment]::NewLine))
                #Write-Output "File queued for analysis ($Name/$Path/$md5)"
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Result" -Value 3
                Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Permalink" -Value $($object.Permalink)
            }    
        }
    }
    else {
        #[System.IO.File]::AppendAllText($StreamWriter,"No response from virustotal ($Name/$Path/$md5)" + ([Environment]::NewLine))
        #Write-Output "No response from virustotal ($Name/$Path/$md5)"
        Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Response" -Value 4  
        Add-Member -InputObject $responseObject -MemberType NoteProperty -Name "Permalink" -Value ''     
    }
    return $responseObject
}