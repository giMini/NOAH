# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Translate-AccessMask' - Translate integer value in string
# ________________________________________________________________________
Function Translate-AccessMask($val) {
    Switch ($val)
    {
        2032127 {"FullControl"; break}
        1179785 {"Read"; break}
        1180063 {"Read, Write"; break}
        1179817 {"ReadAndExecute"; break}
        -1610612736 {"ReadAndExecuteExtended"; break}
        1245631 {"ReadAndExecute, Modify, Write"; break}
        1180095 {"ReadAndExecute, Write"; break}
        268435456 {"FullControl (Sub Only)"; break}
        default {$AccessMask = $val; break}
    }
}
# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Translate-AceType' - Translate integer value in string
# ________________________________________________________________________
Function Translate-AceType($val) {
    Switch ($val)
    {
        0 {"Allow"; break}
        1 {"Deny"; break}
        2 {"Audit"; break}
    }
}
# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Translate-AceFlagse' - Translate integer value in string
# ________________________________________________________________________
<#  OBJECT_INHERIT_ACE
    1 (0x1)
    Noncontainer child objects inherit the ACE as an effective ACE.
    For child objects that are containers, the ACE is inherited as an inherit-only ACE unless the NO_PROPAGATE_INHERIT_ACE bit flag is also set.
    CONTAINER_INHERIT_ACE
    2 (0x2)
    Child objects that are containers, such as directories, inherit the ACE as an effective ACE. The inherited ACE is inheritable unless the NO_PROPAGATE_INHERIT_ACE bit flag is also set.
    NO_PROPAGATE_INHERIT_ACE
    4 (0x4)
    If the ACE is inherited by a child object, the system clears the OBJECT_INHERIT_ACE and CONTAINER_INHERIT_ACE flags in the inherited ACE. This prevents the ACE from being inherited by subsequent generations of objects.
    INHERIT_ONLY_ACE
    8 (0x8)
    Indicates an inherit-only ACE which does not control access to the object to which it is attached. If this flag is not set, the ACE is an effective ACE which controls access to the object to which it is attached.
    Both effective and inherit-only ACEs can be inherited depending on the state of the other inheritance flags.
    INHERITED_ACE
    16 (0x10)
    The system sets this bit when it propagates an inherited ACE to a child object.
    Access these the same way. You can break them out using the bitwise AND operator or just test for the totals #>
Function Translate-AceFlags($val) {
    Switch ($val)
    {
        0 {"0"}
        1 {"Noncontainer child objects inherit"; break}
        2 {"Containers will inherit and pass on"; break}
        3 {"Containers AND Non-containers will inherit and pass on"; break}       
    }
}
# ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
# Function Name 'Get-NtfsRights' - Enumerates NTFS rights of a folder
# ________________________________________________________________________
Function Get-NtfsRights($name,$path,$comp,$Credential) {
	$path = [regex]::Escape($path)
	$share = "\\$comp\\$name"
	$wmi = gwmi Win32_LogicalFileSecuritySetting -filter "path='$path'" -ComputerName $comp -Credential $Credential
	$wmi.GetSecurityDescriptor().Descriptor.DACL | where {$_.AccessMask -as [Security.AccessControl.FileSystemRights]} |select `
                @{name="ShareName";Expression={$share}},
				@{name="Principal";Expression={"{0}\{1}" -f $_.Trustee.Domain,$_.Trustee.name}},
				@{name="Rights";Expression={Translate-AccessMask $_.AccessMask }},
				@{name="AceFlags";Expression={Translate-AceFlags $_.AceFlags }},
				@{name="AceType";Expression={Translate-AceType $_.AceType }}
				
}