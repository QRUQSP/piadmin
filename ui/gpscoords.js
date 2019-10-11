//
// This class will display the form to allow admins and tenant owners to 
// change the details of their tenant
//
function qruqsp_piadmin_gpscoords() {
    this.main = null;

    //
    // Setup the piadmin main
    //
    this.main = new M.panel('GPS Coordinates',
        'qruqsp_piadmin_gpscoords', 'main',
        'mc', 'narrow', 'sectioned', 'qruqsp.piadmin.gpscoords.main');
    this.main.sections = {
        'coords':{'label':'', 'fields':{
            'latitude':{'label':'Latitude', 'type':'text'}, 
            'longitude':{'label':'Longitude', 'type':'text'}, 
            'altitude':{'label':'Altitude', 'type':'text'}, 
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Update Coordinates', 'fn':'M.qruqsp_piadmin_gpscoords.main.save();'},
            }},
        };
    this.main.fieldValue = function(s, i, d) {
        return this.data[i];
    }
    this.main.open = function(cb) {
        M.api.getJSONCb('qruqsp.piadmin.gpsCoordsGet', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_piadmin_gpscoords.main;
            p.data = rsp;
            p.refresh();
            p.show(cb);
        });
    }
    this.main.save = function() {
        var c = this.serializeForm('yes');
        M.api.postJSONCb('qruqsp.piadmin.gpsCoordsSet', {'tnid':M.curTenantID}, c, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            M.qruqsp_piadmin_gpscoords.main.close();
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
        var appContainer = M.createContainer('mc', 'qruqsp_piadmin_gpscoords', 'yes');
        if( appContainer == null ) {
            alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
