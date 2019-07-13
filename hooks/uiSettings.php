<?php
//
// Description
// -----------
// This function returns the settings for the module and the main menu items and settings menu items
//
// Arguments
// ---------
// ciniki:
// tnid:
// args: The arguments for the hook
//
// Returns
// -------
//
function qruqsp_piadmin_hooks_uiSettings(&$ciniki, $tnid, $args) {
    //
    // Setup the default response
    //
    $rsp = array('stat'=>'ok', 'menu_items'=>array(), 'settings_menu_items'=>array());

    //
    // Check permissions for what menu items should be available
    //
    if( isset($ciniki['tenant']['modules']['qruqsp.piadmin'])
        && (isset($args['permissions']['owners'])
            || isset($args['permissions']['employees'])
            || isset($args['permissions']['resellers'])
            || ($ciniki['session']['user']['perms']&0x01) == 0x01
            )
        ) {
        $rsp['settings_menu_items'][] = array(
            'priority'=>950,
            'label'=>'Set SSH Password',
            'edit'=>array('app'=>'qruqsp.piadmin.sshpasswd'),
            );
/*        $rsp['settings_menu_items'][] = array(
            'priority'=>941,
            'label'=>'Wifi Setup',
            'edit'=>array('app'=>'qruqsp.piadmin.wifi'),
            ); */
/*        $rsp['settings_menu_items'][] = array(
            'priority'=>940,
            'label'=>'Ethernet Setup',
            'edit'=>array('app'=>'qruqsp.piadmin.ethernet'),
            ); */
        $rsp['settings_menu_items'][] = array(
            'priority'=>910,
            'label'=>'Set Date and Time',
            'edit'=>array('app'=>'qruqsp.piadmin.datetime'),
            );
    }

    return $rsp;
}
?>
