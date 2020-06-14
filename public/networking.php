<?php
//
// Description
// -----------
// This method will return the current network details for the Pi.
// 
// Arguments
// ---------
// ciniki: 
// tnid:            The ID of the current tenant.
// 
// Returns
// ---------
// 
function qruqsp_piadmin_networking(&$ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'iface'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Interface'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'checkAccess');
    $rc = qruqsp_piadmin_checkAccess($ciniki, $args['tnid'], 'qruqsp.piadmin.networking');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load the interfaces
    //
    ciniki_core_loadMethod($ciniki, 'qruqsp', 'piadmin', 'private', 'networkingLoad');
    $rc = qruqsp_piadmin_networkingLoad($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'qruqsp.piadmin.7', 'msg'=>'Unable to load interface details', 'err'=>$rc['err']));
    }
    $rsp = $rc;
    $interfaces = $rc['interfaces'];

    //
    // Load the list of SSID's found on the wifi
    //
    if( isset($args['iface']) && $args['iface'] != '' ) {
        $rsp['ssids'] = array(); 
        foreach($interfaces as $interface) {
            if( strncmp($interface['name'], 'wlan', 4) == 0 ) {
                //
                // Get the list of available wifi 
                //
                exec('sudo iwlist ' . $interface['name'] . ' scan', $lines);
                foreach($lines as $line) {
                    if( preg_match('/ESSID:"(.*)"/', $line, $m) && $m[1] != '' ) {
                        $rsp['ssids'][] = $m[1];
                    }
                }
                //
                // Read the hostapd conf
                //
                if( file_exists('/etc/hostapd/hostapd.conf') ) {
                    $lines = file('/etc/hostapd/hostapd.conf');
                    foreach($lines as $line) {
                        if( preg_match("/^\s*ssid\s*=\s*(.*)/", $line, $m) ) {
                            $rsp['hostapd_ssid'] = $m[1];
                        } elseif( preg_match("/^\s*channel\s*=\s*(.*)/", $line, $m) ) {
                            $rsp['hostapd_channel'] = $m[1];
                        } elseif( preg_match("/^\s*wpa_passphrase\s*=\s*(.*)/", $line, $m) ) {
                            $rsp['hostapd_password'] = $m[1];
                        }
                    }
                } else {
                    $rsp['hostapd_ssid'] = '';
                    $rsp['hostapd_channel'] = '';
                    $rsp['hostapd_password'] = '';
                }
            }
        }
    }

    return $rsp;
}
?>
