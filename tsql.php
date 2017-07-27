<?php

$tsqlProcessTree = "SELECT 
					fpbss.serverID, sa.serverName,[ProcessName],[processID],[parentProcessId]
					FROM [NOAH].[dbo].[FlatProcessByServerStat] fpbss, [NOAH].[dbo].[ServerAudited] sa
					WHERE hu.huntingGUID = '$huntGUID'
					AND sa.serverID = '$serverID'
					AND fpbss.serverID = sa.serverID
					ORDER by level";