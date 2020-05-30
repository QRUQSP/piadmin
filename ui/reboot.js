//
//
function qruqsp_piadmin_reboot() {
    this.main = null;

    //
    // Setup the main
    //
    this.main = new M.panel('Reboot',
        'qruqsp_piadmin_reboot', 'main',
        'mc', 'narrow', 'sectioned', 'qruqsp.piadmin.reboot.main');
    this.main.data = {
        'help':"Rebooting the Pi will run a clean shutdown and startup. When ready, click the Reboot now button."
            + "<br/><br/>This will take 30-60 seconds.",
        }
    this.main.timer = null;
    this.main.start_ts = 0;
    this.main.sections = {
        'help':{'label':'', 'type':'html'},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Reboot Now', 'fn':'M.qruqsp_piadmin_reboot.main.save();'},
            }},
        };
    this.main.fieldValue = function(s, i, d) {
        return '';
    }
    this.main.open = function(cb) {
        this.show(cb);
    }
    this.main.save = function() {
        if( confirm('Are you sure you want to reboot now?') ) {
            this.start_ts = Date.now();
            M.startLoad();
            this.timer = setTimeout('M.qruqsp_piadmin_reboot.checkSystemUp();', 15000);
            M.api.getJSONCb('qruqsp.piadmin.reboot', {'tnid':M.curTenantID}, function(rsp) {
                console.log('issued reboot');
            });
        }
    }
    this.main.addClose('Back');

    this.start = function(cb, ap, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_reboot', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }

    this.checkSystemUp = function() {
        if( this.timer != null ) {
            clearTimeout(this.timer);
        }
        console.log('checking if pi is back');
        var u = M.api.url + '?method=ciniki.core.echoTest&api_key=' + M.api.key + '&auth_token=' + M.api.token;
        var x = M.xmlHttpCreate();
        try {
            x.open("GET", u, false);
            x.send(null);
        } catch(e) {
            // Check if reboot is taking longer than 120 seconds (2 minutes)
            if( Date.now() > this.main.start_ts + 120000 ) {
                M.alert("The system has not come back online, this could because the IP address changed, "
                    + "the WiFi or Network settings where changed prior to reboot. "
                    + "If nothings was changed, you may need to plug in a monitor to find out the issue.");
            }
            this.timer = setTimeout('M.qruqsp_piadmin_reboot.checkSystemUp();', 10000);
        }
        if( x.status == 200 ) {
            console.log('rebooted');
            // Wait another 5 seconds for database to come online
            setTimeout('M.qruqsp_piadmin_reboot.systemBack();', 5000);
        }
    }
    this.systemBack = function() {
        M.stopLoad();
        this.main.close();
    }
}
