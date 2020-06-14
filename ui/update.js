//
//
function qruqsp_piadmin_update() {
    this.main = null;

    //
    // Setup the main
    //
    this.main = new M.panel('System Update',
        'qruqsp_piadmin_update', 'main',
        'mc', 'narrow', 'sectioned', 'qruqsp.piadmin.update.main');
    this.main.data = {
        'help':"This will check for updates from qruqsp.org and automatically apply them.",
        }
    this.main.timer = null;
    this.main.start_ts = 0;
    this.main.sections = {
        'help':{'label':'', 'type':'html'},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Upgrade Now', 'fn':'M.qruqsp_piadmin_update.main.upgradeCode();'},
            }},
        };
    this.main.fieldValue = function(s, i, d) {
        return '';
    }
    this.main.open = function(cb) {
        this.show(cb);
    }
    this.main.upgradeCode = function() {
        M.api.getJSONCb('ciniki.sysadmin.cinikiUpdateCode', {}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.alert('done');
        });
    };
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
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_update', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
