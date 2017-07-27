<?php 

switch ($pageSwitch) {
	case 'browserhistory':
        $title = "Broswer History";
		$var = "bha";
		$table = "[NOAH].[dbo].[BrowserHistoryAudited]";
		$req = ",[BrowserHistoryAuditedID],[BrowserType],[UserName],[URL]";
		$id = "BrowserHistoryAuditedID";
		$orderBy = "BrowserHistoryAuditedID";
		$th = "<th>Browser</th><th>UserName</th><th>URL</th>";
		$stat = ", URL";	
		$thStat = "<th>URL</th><th>Total</th>";
        break;
	case 'dnscache':
        $title = "DNS Cache";
		$var = "dnscache";
		$table = "[NOAH].[dbo].[DNSCacheAudited]";
		$req = ", sa.serverID, DNSCacheAuditedID, [RecordName]";
		$id = "DNSCacheAuditedID";
		$orderBy = "DNSCacheAuditedID";
		$th = "<th>Record Name</th>";
		$stat = ", RecordName";	
		$thStat = "<th>RecordName</th><th>Total</th>";
        break;
	case 'explorerbar':
        $title = "Explorer Bar";
		$var = "explorerbar";
		$table = "[NOAH].[dbo].[ExplorerBarAudited]";
		$req = ", sa.serverID, ExplorerBarAuditedID, [UserName] ,[URL]";
		$id = "ExplorerBarAuditedID";
		$orderBy = "ExplorerBarAuditedID";
		$th = "<th>User Name</th><th>URL</th>";
		$stat = ", URL";	
		$thStat = "<th>URL</th><th>Total</th>";
        break;
	case 'linkfile':
        $title = "Link Files";
		$var = "linkfile";
		$table = "[NOAH].[dbo].[LinkFilesAudited]";
		$req = ",sa.[serverID],LinkFilesAuditedID,[FileName],[Caption],[CreationDate],[LastAccessed],[LastModified],[Target],[Hidden]";
		$id = "LinkFilesAuditedID";
		$orderBy = "LinkFilesAuditedID";
		$th = "<th>File Name</th><th>Caption</th><th>Creation Date</th><th>Last Accessed</th><th>Last Modified</th><th>Target</th><th>Hidden</th>";
		$stat = ", Caption";	
		$thStat = "<th>Caption</th><th>Total</th>";
        break;		
    case 'shimcache':
        $title = "Shim Cache";
		$var = "shimcache";
		$table = "[NOAH].[dbo].[ShimCacheAudited]";
		$req = ",[shimCacheAuditedID],[ProgramName],[LastModified]";
		$stat = ", ProgramName";		
		$id = "shimCacheAuditedID";
		$orderBy = "shimCacheAuditedID";
		$th = "<th>Program Name</th> <th>Last Modified</th>";
		$thStat = "<th>Program Name</th><th>Total</th>";
        break;
    case 'amcache':
        $title = "AM Cache";
		$var = "amcache";
		$table = "[NOAH].[dbo].[AmcacheAudited]";
		$req = ",[amcacheAuditedID],[Associated],[ProgramName],[ProgramID],[VolumeID],[VolumeIDLastWriteTimestamp],[FileID],[FileIDLastWriteTimestamp],[SHA1],[FullPath],[FileExtension],[MFTEntryNumber],[MFTSequenceNumber],[FileSize],[FileVersionString],[FileVersionNumber],[FileDescription],[PEHeaderSize],[PEHeaderHash],[PEHeaderChecksum],[Created],[LastModified],[LastModified2],[CompileTime],[LanguageID],[CompanyName]";
		$id = "amcacheAuditedID";
		$orderBy = "amcacheAuditedID";
		$th = "<th>Associated</th><th>ProgramName</th><th>ProgramID</th><th>Volume ID</th><th>VolumeIDLastWriteTimestamp</th><th>FileID</th><th>FileIDLastWriteTimestamp</th><th>SHA1</th><th>FullPath</th><th>FileExtension</th><th>MFTEntryNumber</th><th>MFTSequenceNumber</th><th>FileSize</th><th>FileVersionString</th><th>FileVersionNumber</th><th>FileDescription</th><th>PEHeaderSize</th><th>PEHeaderHash</th><th>PEHeaderChecksum</th><th>Created</th><th>LastModified</th><th>LastModified2</th><th>CompileTime</th><th>LanguageID</th><th>CompanyName</th>";
		$stat = ", SHA1";	
		$thStat = "<th>Hash</th><th>Total</th>";
		break;
	case 'runmru':
        $title = "Most Recent Used list";
		$var = "runmru";
		$table = "[NOAH].[dbo].[RunMRUsAudited]";
		$req = ", sa.serverID, RunMRUsAuditedID, [UserName] ,[MRU]";
		$id = "RunMRUsAuditedID";
		$orderBy = "RunMRUsAuditedID";
		$th = "<th>User Name</th><th>MRU Entries</th>";
		$stat = ", MRU";	
		$thStat = "<th>MRU</th><th>Total</th>";
        break;		
	case 'netstat':
        $title = "Network Flows";
		$var = "netstat";
		$table = "[NOAH].[dbo].[NetStatAudited]";
		$req = ", sa.serverID, NetstatID, [Protocol],[LocalAddress],[LocalPort],[RemoteAddress],[RemotePort],[State],[ProcessName],[PID]";
		$id = "NetstatID";
		$orderBy = "NetstatID";
		$th = "<th>Protocol</th><th>Local Address</th><th>Local Port</th><th>Remote Address</th><th>Remote Port</th><th>State</th><th>Process Name</th><th>PID</th>";
		$stat = ", RemoteAddress";	
		$thStat = "<th>RemoteAddress</th><th>Total</th>";
        break;	
    case 'persistence':
        $title = "Persistence";
		$var = "persistence";
		$table = "[NOAH].[dbo].[AutorunAudited]";
		$req = ", autorunAuditedID, Suspicious, [SHA-256], LaunchString, EntryLocation, [Entry], Signer, Category";
		$id = "autorunAuditedID";
		$orderBy = "Suspicious DESC";
		$th = "<th>SHA-256</th><th>Category</th><th>Launch String</th><th>Entry</th><th>Signer</th> ";
		$stat = ", [SHA-256]";	
		$thStat = "<th>SHA256</th><th>Total</th>";
        break;
	case 'processtreebyserver':
        $title = "Process Tree";
		$var = "processtreebyserver";
		$table = "[NOAH].[dbo].[ProcessTreeAudited]";
		$req = ",sa.serverID,ProcessTreeAuditedID,[name],[processID],[parentProcessId],[sessionID],[handles],[creationDate],[location],[CommandLine],[Decoded],[Suspicious],[Description],[hash],[username],tableInf.[domain],[VT],[permalink]";
		$stat = ", [hash], name";		
		$id = "ProcessTreeAuditedID";
		$orderBy = "ProcessTreeAuditedID";		
		$th = "<th>Parent Process ID</th><th>Parent Process Name</th><th>Process ID</th><th>Process Name</th><th>sessionID</th><th>handles</th><th>creationDate</th><th>location</th><th>CommandLine</th><th>Decoded</th><th>Suspicious</th><th>Description</th><th>Hash</th><th>username</th><th>domain</th><th>VT</th><th>permalink</th>";
		$thStat = "<th>Hash</th><th>Process Name</th><th>Total</th>";
        break;
	case 'recentdocs':
        $title = "Recent Documents";
		$var = "recentdocs";
		$table = "[NOAH].[dbo].[RecentDocsAudited]";
		$req = ", sa.serverID, [recentDocsAuditedID],[UserName],[Unicode_Link_Name]";
		$id = "recentDocsAuditedID";
		$orderBy = "recentDocsAuditedID";
		$th = "<th>User Name</th> <th>Unicode_Link_Name</th>";
		$stat = ", [Unicode_Link_Name]";	
		$thStat = "<th>Unicode_Link_Nam</th><th>Total</th>";
        break;	
	case 'scheduledtask':
        $title = "Scheduled Tasks";
		$var = "scheduledtask";
		$table = "[NOAH].[dbo].[ScheduledTaskAudited]";
		$req = ", sa.serverID, sa.serverName, scheduledTaskAuditedID,[name],[runAs],[scheduledAction],[nextRunTime],[lastRunTime],[pathName],[arguments],[hash],[Suspicious]";
		$id = "scheduledTaskAuditedID";
		$orderBy = "Suspicious DESC";
		$th = "<th>Name</th><th>Run As</th><th>Arguments</th><th>Scheduled Action</th><th>Path Name</th><th>Last Run Time</th><th>Next Run Time</th><th>Hash</th<th>Suspicious</th>";
		$stat = ", hash";	
		$thStat = "<th>Hash</th><th>Total</th>";
        break;
	case 'service':
        $title = "Services";
		$var = "service";
		$table = "[NOAH].[dbo].[ServiceAudited]";
		$req = ", sa.serverID, sa.serverName, serviceAuditedID, [displayName],[name],[startName],[startMode],[servicePathName],[serviceDescription]";
		$id = "serviceAuditedID";
		$orderBy = "serviceAuditedID";
		$th = "<th>Display Name</th><th>Name</th><th>Start Name</th><th>Start Mode</th><th>Service Path Name</th><th>Service Description</th>";
		$stat = ", displayName";	
		$thStat = "<th>Display Name</th><th>Total</th>";
        break;		
	case 'software':
        $title = "Software Installed";
		$var = "softwareinstalled";
		$table = "[NOAH].[dbo].[InstalledProgramAudited]";
		$req = ", sa.serverID, [installedProgramID], [displayName],[displayVersion],[installLocation],[publisher],[displayicon]";
		$id = "installedProgramID";
		$orderBy = "installedProgramID";
		$th = "<th>Program Name</th> <th>Version</th><th>Location</th><th>Publisher</th><th>Icon</th>";
		$stat = ", displayName";	
		$thStat = "<th>Display Name</th><th>Total</th>";
        break;
	case 'usbhistory':
        $title = "USB History";
		$var = "usbhistory";
		$table = "[NOAH].[dbo].[USBHistoryAudited]";
		$req = ", sa.serverID,USBHistoryAuditedID,[DeviceName],[FriendlyName],[InstanceID],[ClassGUID],[SymbolicName],[SerialNumber],
		[LastTimeDeviceConnected],[InstallSetupDevTimeDeviceConnected],[DriverDesc],[DriverVersion],[ProviderName],
		[DriverDate],[InfPath],[InfSection],[ParentIdPrefix],[Service]";
		$id = "USBHistoryAuditedID";
		$orderBy = "USBHistoryAuditedID";
		$th = "<th>DeviceName</th><th>FriendlyName</th><th>InstanceID</th><th>ClassGUID</th><th>SymbolicName</th><th>SerialNumber</th><th>LastTimeDeviceConnected</th><th>InstallSetupDevTimeDeviceConnected</th><th>DriverDesc</th><th>DriverVersion</th><th>ProviderName</th><th>DriverDate</th><th>InfPath</th><th>InfSection</th><th>ParentIdPrefix</th><th>Service</th>";
		$stat = ", DeviceName";	
		$thStat = "<th>Device Name</th><th>Total</th>";
        break;
	case 'useraccess':
        $title = "User Access";
		$var = "useraccess";
		$table = "[NOAH].[dbo].[InstalledProgramAudited]";
		$req = ", sa.serverID, [installedProgramID], [displayName],[displayVersion],[installLocation],[publisher],[displayicon]";
		$id = "installedProgramID";
		$orderBy = "installedProgramID";
		$th = "<th>Program Name</th> <th>Version</th><th>Location</th><th>Publisher</th><th>Icon</th>";
        break;	
    default:
       $stop = 1;
}