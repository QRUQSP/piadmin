//
// This class will display the form to allow admins and tenant owners to 
// change the details of their tenant
//
function qruqsp_piadmin_networking() {
    this.main = null;

    //
    // Setup the piadmin main
    //
    this.main = new M.panel('Networking',
        'qruqsp_piadmin_networking', 'main',
        'mc', 'medium', 'sectioned', 'qruqsp.piadmin.networking.main');
    this.main.sections = {
        'interfaces':{'label':'Network Interfaces', 'type':'simplegrid', 'num_cols':4,
            'headerValues':['Interface', 'IP', 'Netmask', 'MAC'],
            },
        };
    this.main.fieldValue = function(s, i, d) {
        return this.data[i];
    }
    this.main.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return d.name;
            case 1: return d.ip;
            case 2: return d.netmask;
            case 3: return d.mac;
        }
    }
    this.main.open = function(cb) {
        console.log('testing');
        M.api.getJSONCb('qruqsp.piadmin.networking', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_piadmin_networking.main;
            p.data = rsp;
            console.log(rsp);
            p.refresh();
            p.show(cb);
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
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_networking', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
