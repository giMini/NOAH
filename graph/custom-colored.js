var config = {
        container: "#custom-colored",
        
		rootOrientation: "WEST",

		nodeAlign: "BOTTOM",
		
        connectors: {
            type: "curve",
			style: {
				"stroke-width": 1
			}
        },
        node: {
            HTMLclass: 'nodeExample1'
        }
    },
	
	p1879 = {
		childrenDropLevel: 2,
		HTMLclass: 'nodeExample2',
        text: {
            name: "explorer.exe",                      
        },		
        image: "explorer.png"
    },
	
    p2348 = {
		parent: p1879,
		childrenDropLevel: 2,
		HTMLclass: 'nodeExample2',
        text: {
            name: "winword.exe",                      
        },
        image: "winword.png"
    },

    p2590 = {
        parent: p2348,
		childrenDropLevel: 2,
        HTMLclass: 'nodeExample1',
        text:{
            name: "powershell.exe",            
        },
		connectors: {
			style: {
				'stroke': 'red',
				'arrow-end': 'oval-wide-long'
			}
		},
        image: "powershell.png"
    },
    p2595 = {
        parent: p2348,
        childrenDropLevel: 2,
        HTMLclass: 'nodeExample1',
        text:{
            name: "cmd.exe",
            title: "PID 2595",
        },
        image: "cmd.png"
    },
    
    chart_config = [
        config,
        p1879,p2348,p2595,
		p1879,p2590,        
    ];