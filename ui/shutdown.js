//
//
function qruqsp_piadmin_shutdown() {
    this.main = null;

    //
    // Setup the main
    //
    this.main = new M.panel('Shutdown',
        'qruqsp_piadmin_shutdown', 'main',
        'mc', 'narrow', 'sectioned', 'qruqsp.piadmin.shutdown.main');
    this.main.data = {
        'help':"This will power off your pi and you will need to physically remove and add the power to turn it back on.",
        }
    this.main.timer = null;
    this.main.start_ts = 0;
    this.main.sections = {
        'help':{'label':'', 'type':'html'},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Power Off Now', 'fn':'M.qruqsp_piadmin_shutdown.main.save();'},
            }},
        };
    this.main.fieldValue = function(s, i, d) {
        return '';
    }
    this.main.open = function(cb) {
        this.show(cb);
    }
    this.main.save = function() {
        if( confirm('Are you sure you want to power off now?') ) {
            M.api.getJSONCb('qruqsp.piadmin.shutdown', {'tnid':M.curTenantID}, function(rsp) {
                M.alert('Your system will now be powering off. You may now close this window.');
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
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_shutdown', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
