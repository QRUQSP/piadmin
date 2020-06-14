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
        'interfaces':{'label':'Network Interfaces', 'type':'simplegrid', 'num_cols':3,
            'headerValues':['Interface', 'IP/Netmask', ''],
            'cellClasses':['multiline', 'multiline', ''],
            },
        };
    this.main.fieldValue = function(s, i, d) {
        return this.data[i];
    }
    this.main.cellValue = function(s, i, j, d) {
        switch(j) {
            case 0: return '<span class="maintext">' + d.type + '</span>'
                + '<span class="subtext">Name:' + d.name + '</span>'
                + '<span class="subtext">MAC:' + d.mac + '</span>';
            case 1: return '<span class="maintext">Status: ' + d.status + '</span>'
                + '<span class="subtext">IP: ' + d.ip + '</span>'
                + '<span class="subtext">Netmask: ' + d.netmask + '</span>';
            case 2: return (d.wireless == 'yes' ? '<button onclick="M.qruqsp_piadmin_networking.main.wifiEdit(\'' + d.name + '\');">Configure</button>' : '');
//            case 1: return d.ip;
//            case 2: return d.netmask;
//            case 3: return d.mac;
        }
    }
    this.main.wifiEdit = function(name) {
        M.qruqsp_piadmin_networking.wifi.open('M.qruqsp_piadmin_networking.main.open();',name);
    }
    this.main.open = function(cb) {
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

    //
    // Setup the piadmin main
    //
    this.wifi = new M.panel('Networking - Wifi',
        'qruqsp_piadmin_networking', 'wifi',
        'mc', 'medium', 'sectioned', 'qruqsp.piadmin.networking.wifi');
    this.wifi.iface = 'hotspot';
    this.wifi.sections = {
        '_tabs':{'label':'', 'type':'paneltabs', 'selected':'hotspot', 'tabs':{
            'hotspot':{'label':'Hotspot', 'fn':'M.qruqsp_piadmin_networking.wifi.switchMode("hotspot");'},
            'dhcp':{'label':'DHCP', 'fn':'M.qruqsp_piadmin_networking.wifi.switchMode("dhcp");'},
//            'static':{'label':'Static', 'fn':'M.qruqsp_piadmin_networking.wifi.switchMode("static");'},
            'off':{'label':'Off', 'fn':'M.qruqsp_piadmin_networking.wifi.switchMode("off");'},
            }},
        'hotspot':{'label':'Hotspot Settings', 
            'visible':function() { return (M.qruqsp_piadmin_networking.wifi.sections._tabs.selected == 'hotspot' ? 'yes' : 'hidden');},
            'fields':{
                'hostapd_ssid':{'label':'SSID', 'type':'text'},
                'hostapd_channel':{'label':'Channel', 'type':'text'},
                'hostapd_password':{'label':'Password', 'type':'text'},
            }},
        'dhcp':{'label':'DHCP Setting', 
            'visible':function() { return (M.qruqsp_piadmin_networking.wifi.sections._tabs.selected == 'dhcp' ? 'yes' : 'hidden');},
            'fields':{
                'wifi_ssid':{'label':'SSID', 'type':'select', 'options':{}},
                'wifi_psk':{'label':'WiFi Password', 'type':'text'},
            }},
//        'static':{'label':'Static IP', 
//            'fields':{
//                'static_ip':{'label':'IP', 'type':'text'},
//                'static_netmask':{'label':'Netmask', 'type':'text'},
//                'static_gateway':{'label':'Gateway', 'type':'text'},
//                'static_dns':{'label':'DNS', 'type':'text'},
//            }},
//        'off':{'label':'', 'fields':{
//            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.qruqsp_piadmin_networking.wifi.save();'},
            }},
        };
    this.wifi.fieldValue = function(s, i, d) {
        return this.data[i];
    }
    this.wifi.switchMode = function(t) {
        this.sections._tabs.selected = t;
        this.refreshSection('_tabs');
        this.showHideSection('hotspot');
        this.showHideSection('dhcp');
    }
    this.wifi.open = function(cb, iface) {
        if( iface != null ) { this.iface = iface; }
        M.api.getJSONCb('qruqsp.piadmin.networking', {'tnid':M.curTenantID, 'iface':this.iface}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.qruqsp_piadmin_networking.wifi;
            p.data = rsp;
            p.sections.dhcp.fields.wifi_ssid.options = {};
            if( rsp.ssids != null ) {
                for(var i in rsp.ssids) {
                    p.sections.dhcp.fields.wifi_ssid.options[rsp.ssids[i]] = rsp.ssids[i];
                }
            }
            console.log(rsp);
            p.refresh();
            p.show(cb);
        });
    }
    this.wifi.save = function() {
        console.log('save');
    }
    this.wifi.addClose('Back');


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
            M.alert('App Error');
            return false;
        } 
    
        this.main.open(cb);
    }
}
