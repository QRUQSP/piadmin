//
//
function qruqsp_piadmin_sshpasswd() {
    this.main = null;

    //
    // Setup the main
    //
    this.main = new M.panel('UTC Date and Time',
        'qruqsp_piadmin_sshpasswd', 'main',
        'mc', 'narrow', 'sectioned', 'qruqsp.piadmin.sshpasswd.main');
    this.main.sections = {
        '_pass':{'label':'', 'fields':{
            'newpassword':{'label':'New Password', 'type':'password'}, 
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Set SSH Password', 'fn':'M.qruqsp_piadmin_sshpasswd.main.save();'},
            }},
        };
    this.main.fieldValue = function(s, i, d) {
        return '';
    }
    this.main.open = function(cb) {
        this.show(cb);
    }
    this.main.save = function() {
        var c = this.serializeForm('yes');
        M.api.postJSONCb('qruqsp.piadmin.sshPasswordSet', {'tnid':M.curTenantID}, c, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.qruqsp_piadmin_sshpasswd.main.close();
        });
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
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_sshpasswd', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
