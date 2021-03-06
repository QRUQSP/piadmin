<?php
//
// This file is the install script which will configure and setup the database
// and configuration files on disk.  The script will only run if it can't find
// a ciniki-api.ini file.  This script can be uploaded to a webserver and it
// will download and install all the ciniki modules.
//


//
// Figure out where the root directory is.  This file may be symlinked
//
$ciniki_root = dirname(dirname(dirname(dirname(__FILE__))));
$modules_dir = $ciniki_root . '/ciniki-mods';

//
// Verify no ciniki-api.ini file
//
if( file_exists($ciniki_root . '/ciniki-api.ini') ) {
    print_page('no', 'ciniki.installer.15', 'Already installed.</p><p><a href="/manager/">Login</a>');
    exit();
}

//
// Verify no .htaccess file exists.
//
if( file_exists($ciniki_root . '/.htaccess') ) {
    print_page('no', 'ciniki.installer.14', 'Already installed.</p><p><a href="/manager/">Login</a>');
    exit();
}

/*
-dh    $database_host = $args['database_host'];
-du    $database_username = $args['database_username'];
-dp    $database_password = $args['database_password'];
-dn    $database_name = $args['database_name'];
-ae    $admin_email = $args['admin_email'];
-au    $admin_username = $args['admin_username'];
-ap    $admin_password = $args['admin_password'];
-af    $admin_firstname = $args['admin_firstname'];
-al    $admin_lastname = $args['admin_lastname'];
-ad    $admin_display_name = $args['admin_display_name'];
-mn    $master_name = $args['master_name'];
-se    $system_email = $args['system_email'];
-sn    $system_email_name = $args['system_email_name'];
-sc    $sync_code_url = preg_replace('/\/$/', '', $args['sync_code_url']);
*/
$valid_args = array(
    '-de' => array('field'=>'database_engine', 'mandatory'=>'no'),
    '-dh' => array('field'=>'database_host', 'mandatory'=>'yes'),
    '-du' => array('field'=>'database_username', 'mandatory'=>'yes'),
    '-dp' => array('field'=>'database_password', 'mandatory'=>'no'),
    '-dn' => array('field'=>'database_name', 'mandatory'=>'yes'),
    '-ae' => array('field'=>'admin_email', 'mandatory'=>'yes'),
    '-au' => array('field'=>'admin_username', 'mandatory'=>'yes'),
    '-ap' => array('field'=>'admin_password', 'mandatory'=>'yes', 'minlength'=>8),
    '-af' => array('field'=>'admin_firstname', 'mandatory'=>'no'),
    '-al' => array('field'=>'admin_lastname', 'mandatory'=>'no'),
    '-ad' => array('field'=>'admin_display_name', 'mandatory'=>'no'),
    '-mn' => array('field'=>'master_name', 'mandatory'=>'yes'),
    '-se' => array('field'=>'system_email', 'mandatory'=>'no'),
    '-sn' => array('field'=>'system_email_name', 'mandatory'=>'no'),
    '-sc' => array('field'=>'sync_code_url', 'mandatory'=>'no'),
    '-un' => array('field'=>'server_name', 'mandatory'=>'yes'),
    '-ru' => array('field'=>'request_uri', 'mandatory'=>'no'),
    '-tz' => array('field'=>'timezone', 'mandatory'=>'no'),
    '-la' => array('field'=>'latitude', 'mandatory'=>'no'),
    '-lo' => array('field'=>'longitude', 'mandatory'=>'no'),
    '-al' => array('field'=>'altitude', 'mandatory'=>'no'),
    '-ri' => array('field'=>'radiointerfaceversion', 'mandatory'=>'no'),
    '-80' => array('field'=>'disable_ssl', 'mandatory'=>'no'),
    );
//
// Check if running from command line, and display command line form
//
if( php_sapi_name() == 'cli' ) {
    //
    // Check for arguments
    //
    $args = array(
        'database_engine' => '',
        'database_host' => '',
        'database_username' => '',
        'database_password' => '',
        'database_name' => '',
        'admin_email' => '',
        'admin_username' => '',
        'admin_password' => '',
        'admin_firstname' => '',
        'admin_lastname' => '',
        'admin_display_name' => '',
        'master_name' => '',
        'system_email' => '',
        'system_email_name' => '',
        'sync_code_url' => '',
        'server_name' => '',
        'request_uri' => '',
        'http_host' => '',
        'timezone' => 'UTC',
        'latitude' => '',
        'longitude' => '',
        'altitude' => '',
        'radiointerfaceversion' => '',
        );
    //
    // Grab the args into array
    //
    if( isset($argv[1]) && $argv[1] != '' ) {
        array_shift($argv);
    }
    foreach($argv as $k => $arg) {
        if( $arg == '-80' ) {
            $args['disable_ssl'] = 'yes';
        }
        elseif( isset($valid_args[$arg]) ) {
            $args[$valid_args[$arg]['field']] = $argv[($k+1)];
        }
    }

    if( $args['admin_firstname'] == '' ) {  
        $args['admin_firstname'] = $args['admin_username'];
    }
    if( $args['admin_display_name'] == '' ) {
        $args['admin_display_name'] = $args['admin_username'];
    }
    if( $args['system_email'] == '' ) {  
        $args['system_email'] = $args['admin_email'];
    }
    if( $args['system_email_name'] == '' ) {  
        $args['system_email_name'] = $args['master_name'];
    }
    if( $args['http_host'] == '' ) {  
        $args['http_host'] = $args['server_name'];
    }
  
    $missing = '';
    foreach($valid_args as $k => $arg) {
        if( isset($arg['mandatory']) && $arg['mandatory'] == 'yes' && $args[$arg['field']] == '' ) {
            $missing .= "Missing argument: {$k} {$arg['field']} \n";
        }
        if( isset($arg['minlength']) && $arg['minlength'] > 0 && strlen($args[$arg['field']]) < $arg['minlength'] ) {
            $missing .= "Password must be minimum 8 characters\n";
        }
    }

    if( $missing != '' ) {
        print $missing;
        exit;
    }

    $rc = install($ciniki_root, $modules_dir, $args);
    if( $rc['err'] != 'install' ) {
        print "Error: {$rc['err']} - {$rc['msg']}\n";
    } else {
        print "Installed\n";
    }
} 
//
// Running via web browser
//
else {
    if( !isset($_POST['callsign']) ) {
        print_page('yes', '', '');
    } else {
        //
        // Read in the /home/pi/.my.cnf 
        //
        $mysql_settings = file_get_contents("/home/pi/.my.cnf");
        $mycnf = parse_ini_string($mysql_settings, TRUE);
        
        $args = array(
            'database_engine' => 'mysql',
            'database_host' => 'localhost',
            'database_username' => $mycnf['client']['user'],
            'database_password' => $mycnf['client']['password'],
            'database_name' => 'qruqsp',
            'admin_email' => $_POST['email'],
            'admin_username' => strtolower($_POST['username']),
            'admin_password' => $_POST['password'],
            'admin_firstname' => $_POST['first'],
            'admin_lastname' => $_POST['last'],
            'admin_display_name' => strtoupper($_POST['username']),
            'master_name' => strtoupper($_POST['callsign']) . (isset($_POST['ssid']) && $_POST['ssid'] != '' ? '-' . $_POST['ssid'] : ''),
            'system_email' => $_POST['email'],
            'system_email_name' => $_POST['callsign'],
            'sync_code_url' => 'https://qruqsp.org/ciniki-picode',
            'server_name' => $_SERVER['SERVER_NAME'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'http_host' => $_SERVER['HTTP_HOST'],
            'disable_ssl' => 'yes',
            'timezone' => $_POST['timezone'],
            'latitude' => $_POST['latitude'],
            'longitude' => $_POST['longitude'],
            'altitude' => $_POST['altitude'],
            'radiointerfaceversion' => $_POST['radiointerfaceversion'],
            );

        $rc = install($ciniki_root, $modules_dir, $args);
        print_page($rc['form'], $rc['err'], $rc['msg']);
    }
}

exit();


function print_page($display_form, $err_code, $err_msg) {
?>
<!DOCTYPE html>
<html>
<head>
<title>QRUQSP Pi Installer</title>
<style>
/******* The top bar across the window ******/
.headerbar {
    width: 100%;
    height: 2.5em;
    margin: 0px;
    padding: 0px;
    table-layout: auto;
    z-index: 2;
}
.headerbar td {
    margin: 0px;
    padding: 0px;
    vertical-align: bottom;
    background: #778;
    padding: 0.2em 0.3em 0.2em 0.3em;
    border-left: 1px solid #889;
    border-right: 1px solid #667;
}
.headerbar td.leftbuttons {
    text-align:left;
    margin: 0px;
    cursor: pointer;
    height: 100%;
    vertical-align: bottom;
    min-width: 2.5em;
    padding-top: 0px;
}
.headerbar td.rightbuttons {
    text-align:right;
    margin: 0px;
    align: right;
    height: 100%;
    cursor: pointer;
    vertical-align: bottom;
    min-width: 2.0em;
    padding-top: 0px;
}
.headerbar td.avatar {
    text-align: center;
    width: 3.0em;
    cursor: pointer;
    vertical-align: middle;
}
.headerbar td.homebutton img.avatar {
    width: 1.8em;
    height: 1.8em;
    margin: 0px;
    border: 1px solid #eee;
    vertical-align: middle;
}
.headerbar td.title {
    min-width: 10%; 
    width: 80%;
    max-width:90%;
    font-size: 1.2em;
    text-align:center;
    color: #eee;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
    padding: 0.3em 0.2em 0.2em 0.2em;
    border-left: 0px;
    border-right: 0px;
}
.headerbar td img {
    width: 1.9em;
    padding: 0px;
    margin: 0px;
    vertical-align: middle;
}
.headerbar td.helpbutton {
    border-right: 0px;
    padding-top: 0px;
}
.headerbar td.homebutton {
    cursor: pointer;
    border-left: 0px;
}
.headerbar td.hide {
    border-left: 0px;
    border-right: 0px;
    cursor: inherit;
}
.headerbar td div.button {
    display: table-cell;
    font-size: 0.8em;
    vertical-align: bottom;
    height: 100%;
    min-width: 3.5em;
    max-width: 5em;
    text-align: center;
    padding: 0em 0.2em 0em 0.2em;
    color: #ddd;
    cursor: pointer;
}
.headerbar td div.button span {
    display: inline-block;
    width: 100%;
}
.headerbar td div.button span.icon {
    font-size: 1.1em;
    text-decoration: none;
    font-family: CinikiRegular;
    color: #99a;
    max-height: 20px;
    vertical-align: top;
}
input,
table,
form,
div {
    box-sizing: border-box;
}
/* These can be specific for help or apps by add #m_container or #m_help in front */
div.narrow {
    margin: 0 auto;
    width: 20em;
    padding-top: 1em;
    padding-bottom: 1em;
}
div.mediumflex,
div.medium {
    padding-top: 1em;
    padding-bottom: 1em;
}
h2 {
    display: block;
    font-size: 1.1em;
    font-weight: normal;
    text-align: left;
    padding: 0.2em 0em 0.2em 0.5em;
    margin: 0 auto;
    border: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #555;
}
div.narrow table.list,
div.medium table.list {
    width: 100%;
}
table.list {
    text-align: left;
    padding: 0px;
    margin-bottom: 1em;
    table-layout: fixed;
}
table.form {
    table-layout: auto;
}
table.outline {
    border: 1px solid #ddd;
    padding: 0.1em 0.1em 0.1em 0.1em;
    background-color: rgba(255,255,255,0.4);
}
table.list > thead,
table.list > tbody,
table.list > tfoot {
    width: 100%;
}
table.list > thead > tr,
table.list > tfoot > tr,
table.list > tbody > tr {
    width: 100%;
}
table.border > thead:first-child > tr:first-child > th,
table.border > tbody:first-child > tr:first-child > td,
table.border > tfoot:first-child > tr:first-child > td {
    border-top: 1px solid #bbb;
}
table.border {
    background: #fff;
}
table.border > thead > tr > th:first-child,
table.border > tfoot > tr > td:first-child,
table.border > tbody > tr > td:first-child {
    border-left: 1px solid #bbb;
}
table.border > thead > tr > th:last-child,
table.border > tfoot > tr > td:last-child,
table.border > tbody > tr > td:last-child {
    border-right: 0px;
}
table.fieldhistory > tbody > tr > td:last-child {
    border-right: 1px solid #bbb; 
}
table.list > tbody > tr > td,
table.list > tfoot > tr > td {
    padding: 0.7em 0.5em 0.7em 0.5em;
}
table.border > tbody > tr > td,
table.border > tfoot > tr > td {
    border-bottom: 1px solid #bbb;
}
table.border > tbody > tr:last-child > td,
table.border > tfoot > tr:last-child > td {
    border-bottom: 0px;
}
table.border > tfoot > tr:first-child > td {
    border-top: 1px solid #bbb;
}
table.list > thead > tr > th {
    padding: 0.5em;
    background: #eee;
}
table.border > thead > tr > th {
    border-bottom: 1px solid #bbb;
}
table.list > tbody > tr > td.noborder,
table.list > tfoot > tr > td.noborder {
    border-right: 0px;
}
table.list > tbody > tr > td.addbutton {
    text-align: right;
    width: 1.7em;
    text-align: center;
    padding-right: 0px;
    vertical-align: middle;
    line-height: 1.0em;
}
table.border tr > td.label {
    border-left: 1px solid #bbb;    
    border-right: 0px solid #bbb;    
}
table.list tr.textfield > td.label {
    padding: 0.5em;
    text-align: right;
}
table.form tr.textfield > td.label > label {
    white-space: nowrap;
}
table.border tr.textfield > td.hidelabel {
    border-left: 1px solid #bbb;    
    border-right: 0px;
}
table.list tr.textfield > td.hidelabel {
    padding: 0.5em 0 0.5em 0.5em;
    width: 5px !important;
    height: 1em;
    overflow: hidden;
}
table.list tr.textfield > td.hidelabel > label {
    display: none;
    overflow: hidden;
}
table.border tr > td.input {
    border-left: 1px solid #bbb;    
}
table.list tr > td.input {
    font-size: 1em;
    width: 100%;
    padding: 0.3em 0.5em 0.3em 0.2em;
    margin: 0px;
    vertical-align: center;
}
table.list tr > td input {
    width: 100%;
    padding: 0.4em;
    padding-right: 0;
    font-size: 1.0em;
    color: #555;
    text-align: left;
    margin: 0px;
    border: 0px;
    text-overflow: ellipsis
    white-space: nowrap;
}
table.list tr.textfield > td.select {
    width: 100%;
    height: 100%;
}
table.form td.select > select {
    width: 100%;
    height: 100%;
    padding: 0.4em;
    font-size: 1.0em;
    color: #555;
    text-align: left;
    margin: 0.4em;
    border: 0px;
    text-overflow: ellipsis
    white-space: nowrap;
    border: 1px solid #bbb;
}
table.list tr.textfield > td.toggle {
    border-left: 0px;
    text-align: left;
    padding: 0.5em;
}
table.list tr > td.noedit {
    padding: 0.5em;
    width: 95%;
    color: #777;
    line-height: 1.2em;
}
table.border tr > td.historybutton {
    border-right: 1px solid #bbb;    
}
table.list tr > td.historybutton {
    padding: 0.1em 0.5em 0.1em 0.2em;
    padding: 0px;
    cursor: pointer;
    vertical-align: middle;
    text-align: right;
}
table.list tr.textarea > td.historybutton {
    vertical-align: top;
}
span.rbutton_on,
span.rbutton_off {
    display: inline-block;
    border: 1px solid #777;
    padding: 0.25em 0.05em 0.25em 0.05em;
    width: 1.4em;
    margin: 0em 0.25em 0em 0.5em;
    cursor: pointer;
    text-align: center;
    font-size: 1.0em;
    text-decoration: none;
    font-family: CinikiRegular;
}
span.rbutton_on {
    color: #000;
}
span.rbutton_off {
    color: #bbb;
}
td.input span.rbutton_on,
td.input span.rbutton_off,
h2 span.rbutton_off {
    position: relative;
    margin-left: 0.5em;
}
h2 span.rbutton_off {
    font-size: 0.7em;
}
table.list table.fieldhistory tr td {
    padding: 0.5em;
    color: #555;
    text-align: left;
}
table.list > tbody > tr > td.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

table.list td p {
    padding-top: 1em;
    line-height: 1.4em;
}
table.list td p:first-child {
    padding-top: 0em;
}
table.list td ul {
    margin-top: 1em;
    margin-bottom: 0em;
}
table.list td ul:first-child {
    margin-top: 0em;
}
table.list td dl {
    margin-top: 1em;
    margin-bottom: 0em;
}
table.list td dl:first-child {
    margin-top: 0em;
}
table.list td dl dt {
    display: inline-block;
    float: left;
    text-align: right;
    width: 10%;
}
table.list td dl dd {
    display: inline-block;
    padding-left: 1em;
    width: 90%;
}
table.list td em {
    font-weight: bold;
}
p {
    margin: 0;
}
table.list tr > td.helpbutton {
    padding: 0.1em 0.5em 0.1em 0.2em;
    cursor: pointer;
    vertical-align: middle;
    text-align: right;
    width: 1.4em;
}
table.simplegrid td.label {
    text-align: right;
}
table.simplegrid td.border {
    border-right: 1px solid #bbb;
}
table.simplegrid td.center {
    text-align: center;
}
table.list > tbody > tr > td.textbuttons {
    padding: 0.4em;
}
table.list > tbody > tr > td.multiline span.maintext {
    width: 100%;
    display: block;
}
table.list > tbody > tr > td span.subdue {
    font-size: 0.9em;
    font-decoration: normal;
    font-weight: normal;
    color: #999;
}
table.list > tbody > tr > td.multiline {
    padding: 0.5em 0.5em 0.45em 0.5em;
}
table.list > tbody > tr > td.multiline span.subtext {
    width: 100%;
    font-size: 0.8em;
    color: #999;
    display: block;
}
table.list > tbody > tr > td.multiline > span.singleline {
    overflow: hidden;
    text-overflow: ellipsis;
}

table.list > tbody > tr > td.nobreak {
    white-space: nowrap;
}
table.list > tbody > tr > td.aligntop,
table.list > tfoot > tr > td.aligntop {
    vertical-align: top;
}
table.list > tbody > tr > td.aligncenter,
table.list > tfoot > tr > td.aligncenter {
    text-align: center;
}
table.list td span.icon {
    font-size: 1.1em;
    text-decoration: none;
    font-family: CinikiRegular;
    color: #777; 
    max-height: 20px;
    vertical-align: top;
}
table.simplegrid td.lightborderright {
    border-right: 1px solid #ddd;
}
table.form > tbody > tr > td.multiselect,
table.form > tbody > tr > td.multitoggle,
table.form > tbody > tr > td.joinedflags,
table.form > tbody > tr > td.flags {
    padding: 0em 0.25em 0em 0.25em;
}
table.form > tbody > tr > td div.buttons {
    display: inline-block;
    padding-left: 0.6em;
    margin: 0px;
}
table.form > tbody > tr > td div.nopadbuttons {
    padding-left: 0em;
}
table.form > tbody > tr > td.multiselect div,
table.form > tbody > tr > td.multitoggle div {
    display: inline-block;
}
table.form > tbody > tr > td.multiselect span.hint,
table.form > tbody > tr > td.multitoggle span.hint {
    color: #999;
    padding-left: 0.6em;
}
table.form > tbody > tr > td.joinedflags div {
    display: table;
}
table.form span.flag_on {
    color: #000;
}
table.form span.flag_off {
    color: #bbb;
}
table.form td.joinedflags span.flag_on,
table.form td.joinedflags span.flag_off, 
table.form td div span.toggle_on,
table.form td div span.toggle_off {
    display: inline-block;
    border: 1px solid #777;
    font-weight: bold; 
    padding: 0.4em 0.5em 0.4em 0.5em;
    margin: 0.3em 0 0.3em 0;
    cursor: pointer;
    font-size: 0.9em;
}
table.form td div span.toggle_on {
    color: #000;
}
table.form td div span.toggle_off {
    color: #bbb;
}
table.form td span.flag_on span.icon,
table.form td span.flag_off span.icon,
table.form td span.toggle_on span.icon,
table.form td span.toggle_off span.icon {
    color: inherit;
}
span.username {
}
input.submit {
    color: #333;
    font-size: 1em;
}
.clickable {
    cursor: pointer;
}
/* Text block markups */
div.wide table.text {
    min-width: 40em;
    table-layout: auto;
}
table.text tr.text td pre {
    overflow-x: scroll;
}
table.text tr.text td p:first-child,
table.text tr.text td pre:first-child {
    padding-top: 0px;
    margin-top: 0px;
}
table.text tr.text td p:last-child,
table.text tr.text td pre:last-child {
    padding-bottom: 0px;
    margin-bottom: 0px;
}
/********** Error screen ********/
#m_error {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 98;
    width: 100%;
    height: 90%;
}
#m_error button {
    width: 10em;
    margin-top: 5px;
    margin-bottom: 5px;
    text-align: center;
    font-size: 1em;
    color: #333;
    cursor: pointer;
}
/********** Loading Spinner **************/
#m_loading {
    width: 100%;
    height: 100%;
    overflow: hidden;
    position: fixed;
    top: 0px;
    left: 0px;
    background: #fff;
    text-align: center;
    vertical-align: middle;
    opacity: .5;
    z-index:99;
}

#m_loading table {
    width: 100%;
    height: 100%;
}

div.scrollable {
    overflow: auto;
    width: 100%;
}
table.form td.textarea,
table.form td.search input,
table.form td.multiselect input,
table.form td.input input {
    -webkit-border-radius: 3px;
    -webkit-box-shadow: #eee 0px 1px 1px inset; 
    border: 1px solid #ddd;
    margin-right: 1.5em;
    box-sizing: border-box;
}
input:focus {
    outline: none;
}
h2 {
    text-shadow: #fff 1px 1px 0px;
}
span.count {
    -webkit-border-radius: 1.2em;
    text-shadow: #fff 1px 1px 0px;
}
button, input.button {
    box-sizing: border-box;
    border-radius: 3px;
    border: 1px solid #ccc;
    background: -webkit-gradient(linear, left top, left bottom, from(#999), to(#555));
    padding: 0.5em 0.5em 0.45em 0.5em;
    padding: 0.5em;
    color: #eee;
    font-size: 1.0em;
    font-weight: bold;
    width: 100%;
}
input, textarea {
    -webkit-appearance: none;
}
input:-webkit-autofill {
    background: #fff !important;
}
table.list {
    -webkit-border-radius: 3px;
}
table.list > tbody:first-child > tr:first-child > td:first-child > textarea,
table.list > tbody:first-child > tr table,
table.list > tbody:first-child > tr table tr:first-child > td:first-child,
table.header > thead > tr:first-child > th:first-child,
table.noheader > tbody:first-child > tr:first-child > td:first-child,
table.list > tfoot:first-child > tr:first-child > td:first-child {
    -webkit-border-top-left-radius: 3px;
}
table.list > tbody:first-child > tr:first-child > td:last-child > textarea,
table.list > tbody:first-child > tr table,
table.list > tbody:first-child > tr table tr:first-child > td:last-child,
table.header > thead > tr:first-child > th:last-child,
table.noheader > tbody:first-child > tr:first-child > td:last-child,
table.list > tfoot:first-child > tr:first-child > td:last-child {
    -webkit-border-top-right-radius: 3px;
}
table.list > tbody:last-child > tr:last-child > td:first-child > textarea,
table.list > tbody:last-child > tr table,
table.list > tbody:last-child > tr table tr:last-child > td:first-child,
table.list > thead:last-child > tr:last-child > th:first-child,
table.list > tbody:last-child > tr:last-child > th:first-child,
table.list > tbody:last-child > tr:last-child > td:first-child,
table.list > tfoot > tr:last-child > td:first-child {
    -webkit-border-bottom-left-radius: 3px;
}
table.list > tbody:last-child > tr:last-child > td:last-child > textarea,
table.list > tbody:last-child > tr table,
table.list > tbody:last-child > tr table tr:last-child > td:last-child,
table.list > thead:last-child > tr:last-child > th:last-child,
table.list > tbody:last-child > tr:last-child > td:last-child,
table.list > tfoot > tr:last-child > td:last-child {
    -webkit-border-bottom-right-radius: 3px;
}
body {
    font-size: 100%;
    color: #303030;
    font-family: arial, helvetica;
    padding: 0px;
    margin: 0px;
    border: 0px;
    height: 100%;
}

.headerbar {
    border-bottom: 1px solid #667;
}

#m_error table.list td {
    background: #fff;
}

#m_container {
    margin: 0px;
    border: 0px;
    padding: 0px;
    width: 100%;
    height: 100%;
}

#mc_apps {
    left: 0px;
    width: 100%;
    margin: 0px;
    padding: 0px;
}

div.narrow {
    width: 20em;
    margin: 0 auto;
    padding-top: 1em;
    padding-bottom: 1em;
}

div.medium {
    width: 92%;
    max-width: 40em;
    margin: 0 auto;
}
input {
    padding: 0.2em;
}
#m_help {
    float: right;
    min-height: 100%;
}
table.list tr.gridfields > td.input input.small,
table.list tr > td input.small {
    max-width: 8em;
}
table.list tr.textfield > td > input.medium {
    max-width: 15em;
}
table.list tr.textfield > td > input.large {
    max-width: 45em;
}
table.list > tbody > tr.followup > td.userdetails {
    border-right: 1px dashed #bbb;
    text-decoration: normal;
    white-space: nowrap;
}
table.list > tbody > tr.followup > td.content {
    text-decoration: normal;
    white-space: pre-wrap;
}
table.list dt {
    text-align: right;
}
table.help td:first-child {
    text-align: right;
    min-width: 3em;
    padding-right: 1em;
} 
table.list tr.textfield > td.select {
    width: 100%;
    height: 100%;
}
table.form td.input > select,
table.form td.select > select {
    width: 100%;
    height: 100%;
    padding: 0.4em;
    font-size: 1.0em;
    color: #555;
    text-align: left;
    margin: 0.4em 0em 0.4em 0em;
    border: 0px;
    text-overflow: ellipsis;
    white-space: nowrap;
    border: 1px solid #ccc;
}
table.form td.input > select {
    height: 2em;
    margin: 0em;
    border-radius: initial;
}
table.form td.small input {
    max-width: 10em;
}
table.form td.tiny input {
    max-width: 5em;
}
</style>
<meta content='text/html;charset=UTF-8' http-equiv='Content-Type' />
<meta content='UTF-8' http-equiv='encoding' />
<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=yes' />
</head>
<body id="m_body">
<div id="m_loading" style="display:none;"><table><tr><td><?php print "<img src='ciniki-mods/core/ui/themes/default/img/spinner.gif' />"; ?>
</td></table></div>
<div id='m_container' class="s-normal">
    <table id="mc_header" class="headerbar" cellpadding="0" cellspacing="0">
        <tr>
        <td id="mc_home_button" style="display:none;"><img src="ciniki-mods/core/ui/themes/default/img/home_button.png"/></td>
        <td id="mc_title" class="title">QRUQSP Pi Installer</td>
        <td id="mc_help_button" style="display:none;"><img src="ciniki-mods/core/ui/themes/default/img/help_button.png"/></td>
        </tr>
    </table>
    <div id="mc_content">
    <div id="mc_content_scroller" class="scrollable">
    <div id="mc_apps">
        <div id="mapp_installer" class="mapp">
            <div id="mapp_installer_content" class="panel">
                <div class="medium">
                <?php
                    if( $err_code == 'installed' ) {
                        print "<h2 class=''>Installed</h2><div class='bordered error'><p>QRUQSP has been installed and configured, you can now login at </p><p><a href='/manager'>/manager</a></p></div>";

                    }
                    elseif( $err_code != '' ) {
                        print "<h2 class='error'>Error</h2><div class='bordered error'><p>Error $err_code - $err_msg</p></div>";
                    }
                ?>
                <?php if( $display_form == 'yes' ) { ?>
                    <form id="mapp_installer_form" method="POST" name="mapp_installer_form" onsubmit="document.getElementById('m_loading').style.display='block';">
                        <div class="section">
                        <h2>Station Information</h2>
                        <table class="list noheader form outline" cellspacing='0' cellpadding='0'>
                            <tbody>
                            <tr class="textfield"><td class="label"><label for="callsign">Station Callsign</label></td>
                                <td class="input small"><input type="text" id="callsign" name="callsign" /></td></tr>
                            <tr class="textfield"><td class="label"><label for="ssid">APRS SSID</label></td>
                                <td class="input tiny"><select id="ssid" name="ssid">
                                <option value="">0 - No SSID</option>
                                <option value="1">1 - Digipeater</option>
                                <option value="2">2 - Digipeater</option>
                                <option value="3">3 - Digipeater</option>
                                <option value="4">4 - Digipeater</option>
                                <option value="5">5 - Smartphones</option>
                                <option value="6">6 - Satellite or special operations</option>
                                <option value="7">7 - Reserved for handhelds</option>
                                <option value="8">8 - Boats or maritime mobile</option>
                                <option value="9">9 - Mobiles or trackers</option>
                                <option value="10">10 - Igates or internet operation</option>
                                <option value="11">11 - Reserved for balloons</option>
                                <option value="12">12 - Tracker Boxes</option>
                                <option value="13">13 - Weather Stations</option>
                                <option value="14">14 - Truckers</option>
                                <option value="15">15 - Any other use</option>
                                </select></td></tr>
                            <tr class="textfield"><td class="label"><label for="ssid">QRUQSP Radio Interface</label></td>
                                <td class="input"><select id="radiointerfaceversion" name="radiointerfaceversion">
                                    <option value="">None</option>
                                    <option value="CS-101">Radio Interface CS-101</option>
                                    <option value="1.0">Radio Interface v1.0</option>
                                    <option value="0.2">Radio Interface v0.2</option>
                                </select></td></tr>
                            </tbody>
                        </table>
                        <table class="list noheader form outline" cellspacing='0' cellpadding='0'>
                            <tbody>
                            <tr class="textfield"><td class="label"><label for="timezone">Timezone</label></td>
                                <td class="input"><select id='timezone' name='timezone'>
<?php
    $zones = timezone_identifiers_list();

    foreach($zones as $zone) {
        print "<option value='$zone'" . ($zone == 'UTC' ? ' selected':'') . ">$zone</option>";
    }
?>
                                </select></td></tr>
                            <tr class="textfield"><td class="label"><label for="latitude">Latitude (decimal degrees)</label></td>
                                <td class="input"><input type='text' id='latitude' name='latitude' value='' /></td></tr>
                            <tr class="textfield"><td class="label"><label for="longitude">Longitude (decimal degrees)</label></td>
                                <td class="input"><input type='text' id='longitude' name='longitude' value='' /></td></tr>
                            <tr class="textfield"><td class="label"><label for="altitude">Altitude (meters)</label></td>
                                <td class="input"><input type='text' id='altitude' name='altitude' value='' /></td></tr>
                            <tr class="textfield">
                                <td class="input" colspan="2">Latitude and Longitude must be specified in Decimal Degrees, eg 44.296555, -79.609505. The Altitude must be in meters from sea level.<br/><br/>To lookup your position use: <a target="_blank" href="https://www.mapcoordinates.net/en">www.mapcoordinates.net</a></td></td>
                            </tbody>
                        </table>
                        <h2>Operator Information</h2>
                        <table class="list noheader form outline" cellspacing='0' cellpadding='0'>
                            <tbody>
                            <tr class="textfield"><td class="label"><label for="username">Operator Callsign</label></td>
                                <td class="input"><input type="text" id="username" name="username" /></td></tr>
                            <tr class="textfield"><td class="label"><label for="first">First Name</label></td>
                                <td class="input"><input type="text" id="first" name="first" /></td></tr>
                            <tr class="textfield"><td class="label"><label for="last">Last Name</label></td>
                                <td class="input"><input type="text" id="last" name="last" /></td></tr>
                            <tr class="textfield"><td class="label"><label for="email">Email</label></td>
                                <td class="input"><input type="email" id="email" name="email" /></td></tr>
                            <tr class="textfield"><td class="label"><label for="password">Password</label></td>
                                <td class="input"><input type="password" id="password" name="password" /><br/>
                                <b>Must be minimum 8 characters</b></td></tr>
                            </tbody>
                        </table>
                        </div>
                        <div style="text-align:center;">
                            <input type="submit" value=" Configure Station " class="button">
                        </div>
                    </form>
                <?php } ?>
            </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>
</body>
</html>
<?php
}

//
// Install Procedure
//
function install($ciniki_root, $modules_dir, $args) {

    $database_host = $args['database_host'];
    $database_username = $args['database_username'];
    $database_password = $args['database_password'];
    $database_name = $args['database_name'];
    $admin_email = $args['admin_email'];
    $admin_username = $args['admin_username'];
    $admin_password = $args['admin_password'];
    $admin_firstname = $args['admin_firstname'];
    $admin_lastname = $args['admin_lastname'];
    $admin_display_name = $args['admin_display_name'];
    $master_name = $args['master_name'];
    $system_email = $args['system_email'];
    $system_email_name = $args['system_email_name'];
    $sync_code_url = preg_replace('/\/$/', '', $args['sync_code_url']);

    $manage_api_key = md5(date('Y-m-d-H-i-s') . rand());

    //
    // Build the config file
    //
    $config = array('ciniki.core'=>array(), 'ciniki.users'=>array());
    $config['ciniki.core']['php'] = '/usr/bin/php';
    $config['ciniki.core']['root_dir'] = $ciniki_root;
    $config['ciniki.core']['modules_dir'] = $ciniki_root . '/ciniki-mods';
    $config['ciniki.core']['lib_dir'] = $ciniki_root . '/ciniki-lib';
    $config['ciniki.core']['storage_dir'] = $ciniki_root . '/ciniki-storage';
    $config['ciniki.core']['cache_dir'] = $ciniki_root . '/ciniki-cache';
    $config['ciniki.core']['backup_dir'] = $ciniki_root . '/ciniki-backups';
    $config['ciniki.core']['code_dir'] = $ciniki_root . '/ciniki-picode';
    $config['ciniki.core']['logging.api.dir'] = dirname($ciniki_root) . '/logs';
    $config['ciniki.core']['logging.api.db'] = "'no'";
    $config['ciniki.core']['logging.api.file'] = "'no'";

    // Default session timeout to 7 days 
    $config['ciniki.core']['session_timeout'] = 604800;

    // Database information
    if( isset($args['database_engine']) && $args['database_engine'] != '' ) {
        $config['ciniki.core']['database.engine'] = $args['database_engine'];
    }
    $config['ciniki.core']['database'] = $database_name;
    $config['ciniki.core']['database.names'] = $database_name;
    $config['ciniki.core']["database.$database_name.hostname"] = $database_host;
    $config['ciniki.core']["database.$database_name.username"] = $database_username;
    $config['ciniki.core']["database.$database_name.password"] = $database_password;
    $config['ciniki.core']["database.$database_name.database"] = $database_name;

    // The master tenant ID will be set later on, once information is in database
    $config['ciniki.core']['master_tnid'] = 0;
    $config['ciniki.core']['qruqsp_tnid'] = 0;
    $config['ciniki.core']['single_tenant_mode'] = "'yes'";

    $config['ciniki.core']['alerts.notify'] = $admin_email;
    $config['ciniki.core']['system.email'] = $system_email;
    $config['ciniki.core']['system.email.name'] = $system_email_name;

    // Configure packages and modules 
    $config['ciniki.core']['packages'] = 'ciniki,qruqsp';

    // Sync settings
    $config['ciniki.core']['sync.name'] = $master_name;
    if( isset($args['disable_ssl']) && $args['disable_ssl'] == 'yes' ) {
        $config['ciniki.core']['sync.url'] = "http://" . $args['server_name'] . "/" . preg_replace('/^\//', '', dirname($args['request_uri']) . "ciniki-sync.php");
    } else {
        $config['ciniki.core']['sync.url'] = "https://" . $args['server_name'] . "/" . preg_replace('/^\//', '', dirname($args['request_uri']) . "ciniki-sync.php");
    }
    $config['ciniki.core']['sync.full.hour'] = "13";
    $config['ciniki.core']['sync.partial.hour'] = "13";
    $config['ciniki.core']['sync.code.url'] = $sync_code_url;
    $config['ciniki.core']['sync.log_lvl'] = 0;
    $config['ciniki.core']['sync.log_dir'] = dirname($ciniki_root) . "/logs";
    $config['ciniki.core']['sync.lock_dir'] = dirname($ciniki_root) . "/logs";
    if( isset($args['disable_ssl']) && $args['disable_ssl'] == 'yes' ) {
        $config['ciniki.core']['manage.url'] = "http://" . $args['server_name'] . "/" . preg_replace('/^\//', '', dirname($args['request_uri']) . "manager");
    } else {
        $config['ciniki.core']['manage.url'] = "https://" . $args['server_name'] . "/" . preg_replace('/^\//', '', dirname($args['request_uri']) . "manager");
    }

/*    Moved into ciniki_tenant_details table
    
    //
    // Add coordinates
    //
    $config['ciniki.core']['latitude'] = $args['latitude'];
    $config['ciniki.core']['longitude'] = $args['longitude'];
    $config['ciniki.core']['altitude'] = $args['altitude'];
*/
    // Configure users module settings for password recovery
    $config['ciniki.users']['password.forgot.notify'] = $admin_email;
    if( isset($args['disable_ssl']) && $args['disable_ssl'] == 'yes' ) {
        $config['ciniki.users']['password.forgot.url'] = "http://" . $args['server_name'] . "/" . preg_replace('/^\/$/', '', dirname($args['request_uri']));
    } else {
        $config['ciniki.users']['password.forgot.url'] = "https://" . $args['server_name'] . "/" . preg_replace('/^\/$/', '', dirname($args['request_uri']));
    }

    $config['ciniki.web'] = array();
    $config['ciniki.mail'] = array();

    $config['qruqsp.core'] = array();
    $config['qruqsp.core']['modules_dir'] = dirname($ciniki_root) . '/site/qruqsp-mods';
    $config['qruqsp.core']['log_dir'] = dirname($ciniki_root) . '/logs';
    $config['qruqsp.43392'] = array();
    $config['qruqsp.43392']['listener'] = 'active';
    $config['qruqsp.43392']['rtl_433_cmd'] = '/ciniki/sites/qruqsp.local/site/qruqsp-mods/pibin/bin/rtl_433';

    //
    // Setup ciniki variable, just like ciniki-mods/core/private/init.php script, but we
    // can't load that script as the config file isn't on disk, and the user is not 
    // in the database
    //
    $ciniki = array('config'=>$config);
    $ciniki['request'] = array('api_key'=>$manage_api_key, 'auth_token'=>'', 'method'=>'', 'args'=>array());

    //
    // Check to see if the code already exists on server, if not grab the code and install
    //
    if( !file_exists($ciniki_root . "/ciniki-mods/core") ) {
        if( $sync_code_url == '' ) {
            return array('form'=>'yes', 'err'=>'ciniki.installer.200', 'msg'=>"Ciniki has not been downloaded, please check Code URL.}");
        }
        $remote_versions = file_get_contents($sync_code_url . '/_versions.ini');
        if( $remote_versions === false ) {
            return array('form'=>'yes', 'err'=>'ciniki.installer.201', 'msg'=>"Unable to sync code, please check Code URL.}");
        }
        $remote_modules = parse_ini_string($remote_versions, true);
        
        # Create directory structure
        if( !file_exists($ciniki_root . "/ciniki-mods") ) {
            mkdir($ciniki_root . "/ciniki-mods");
        }
        if( !file_exists($ciniki_root . "/ciniki-cache") ) {
            mkdir($ciniki_root . "/ciniki-cache");
        }
        if( !file_exists($ciniki_root . "/ciniki-backups") ) {
            mkdir($ciniki_root . "/ciniki-backups");
        }
        if( !file_exists($ciniki_root . "/ciniki-storage") ) {
            mkdir($ciniki_root . "/ciniki-storage");
        }
        if( !file_exists($ciniki_root . "/ciniki-picode") ) {
            mkdir($ciniki_root . "/ciniki-picode");
        }
        if( !file_exists($ciniki_root . "/ciniki-lib") ) {
            mkdir($ciniki_root . "/ciniki-lib");
        }

        # This code also exists in ciniki-mods/core/private/syncUpgradeSystem
        foreach($remote_modules as $mod_name => $module) {
            $remote_zip = file_get_contents($sync_code_url . "/$mod_name.zip");
            if( $remote_zip === false ) {
                return array('form'=>'yes', 'err'=>'ciniki.installer.202', 'msg'=>"Unable to get {$mod_name}.zip, please check Code URL.}");
            }
            $zipfilename = $ciniki_root . "/ciniki-picode/$mod_name.zip";
            if( ($bytes = file_put_contents($zipfilename, $remote_zip)) === false ) {
                return array('form'=>'yes', 'err'=>'ciniki.installer.203', 'msg'=>"Unable to save {$zipfilename}");
            }
            if( $bytes == 0 ) {
                return array('form'=>'yes', 'err'=>'ciniki.installer.204', 'msg'=>"Unable to open {$zipfilename}");
            }
            $zip = new ZipArchive;
            $res = $zip->open($zipfilename);
            if( $res === true ) {
                $mpieces = preg_split('/\./', $mod_name);
                $mod_dir = $ciniki_root . '/' . $mpieces[0] . '-' . $mpieces[1] . '/' . $mpieces[2];
                if( !file_exists($mod_dir) ) {
                    mkdir($mod_dir);
                }
                $zip->extractTo($mod_dir);
                $zip->close();
            } else {
                return array('form'=>'yes', 'err'=>'ciniki.installer.205', 'msg'=>"Unable to open {$mod_name}.zip");
            }
        }
    }

    //
    // Check if this pi was setup in "Black Box" mode giving it full control of the pi
    // Change the pi users password along with hostapd passwod
    //
    if( file_exists(dirname($ciniki_root) . '/.blackbox') ) {
        if( isset($admin_password) && $admin_password != '' ) {
            //
            // Change the system password for the pi user
            //
            $hashed_pwd = trim(`echo $admin_password | openssl passwd -6 -stdin`);
            if( $hashed_pwd != '' ) {
                `sudo usermod --pass='$hashed_pwd' pi`;
            }
            //
            // Update wifi hotspot SSID & password in /etc/hostapd/hostapd.conf
            //
            `sudo sed -i 's/ssid=QRUQSP/ssid=$master_name/' /etc/hostapd/hostapd.conf`;
            `sudo sed -i 's/wpa_passphrase=hamradio/wpa_passphrase=$admin_password/' /etc/hostapd/hostapd.conf`;
            `sudo service hostapd restart`;
        }

        //
        // Setup the hostname to the station name
        //
        `sudo echo '$master_name' >/etc/hostname`;
    }

    //
    // Initialize the database connection
    //
    require_once($modules_dir . '/core/private/loadMethod.php');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInit');
    $rc = ciniki_core_dbInit($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to to connect to the database, please check your connection settings and try again.<br/><br/>" . $rc['err']['msg']);
    }

    //
    // Run the upgrade script, which will upgrade any existing tables,
    // so we don't have to check first if they exist.
    // 
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbUpgradeTables');
    $rc = ciniki_core_dbUpgradeTables($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to to connect to the database, please check your connection settings and try again.<br/><br/>" . $rc['err']['msg']);
    }

    // FIXME: Add code to upgrade other packages databases


    //
    // Check if any data exists in the database
    //
    $strsql = "SELECT 'num_rows', COUNT(*) FROM ciniki_core_api_keys, ciniki_users";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');
    $rc = ciniki_core_dbCount($ciniki, $strsql, 'core', 'count');
    if( $rc['stat'] != 'ok' ) {
        return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to check for existing data<br/><br/>" . $rc['err']['msg']);
    }
    if( $rc['count']['num_rows'] != 0 ) {
        return array('form'=>'yes', 'err'=>'ciniki.installer.220', 'msg'=>"Failed to check for existing data");
    }
    $db_exists = 'no';

    //
    // FIXME: Check if api_key already exists for ciniki-manage, and add if doesn't
    //



    //
    // FIXME: Add the user, if they don't already exist
    //

    //
    // Start a new database transaction
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbInsert');
    $rc = ciniki_core_dbTransactionStart($ciniki, 'core');
    if( $rc['stat'] != 'ok' ) {
        return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
    }

    if( $db_exists == 'no' ) {
        //
        // Add the user
        //
        $strsql = "INSERT INTO ciniki_users (id, uuid, email, username, password, avatar_id, perms, status, timeout, "
            . "firstname, lastname, display_name, date_added, last_updated) VALUES ( "
            . "'1', UUID(), '$admin_email', '$admin_username', SHA1('$admin_password'), 0, 1, 1, 0, "
            . "'$admin_firstname', '$admin_lastname', '$admin_display_name', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'users');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'core');
            return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
        }

        //
        // Add the master tenant, if it doesn't already exist
        //
        $strsql = "INSERT INTO ciniki_tenants (id, uuid, flags, name, tagline, description, status, date_added, last_updated) "
            . "VALUES ("
            . "'1', UUID(), 2, '$master_name', '', '', 1, UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'core');
            return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
        }
        $config['ciniki.core']['master_tnid'] = 1;
        $config['ciniki.core']['qruqsp_tnid'] = 1;
        if( isset($args['disable_ssl']) && $args['disable_ssl'] == 'yes' ) {
            $config['ciniki.core']['ssl'] = "'off'";
        }
        $config['ciniki.web']['master.domain'] = $args['http_host'];
        $config['ciniki.web']['poweredby.url'] = "http://ciniki.com/";
        $config['ciniki.web']['poweredby.name'] = "Ciniki";
        $config['ciniki.mail']['poweredby.url'] = "http://ciniki.com/";
        $config['ciniki.mail']['poweredby.name'] = "Ciniki";

        //
        // Add the timezone
        //
        $strsql = "INSERT INTO ciniki_tenant_details (tnid, detail_key, detail_value, date_added, last_updated) "
            . "VALUES ("
            . "'1', 'intl-default-timezone', '" . $args['timezone'] . "', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'core');
            return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup timezone<br/><br/>" . $rc['err']['msg']);
        }
    
        //
        // Add the latitude, longitude and altitude
        //
        if( isset($args['latitude']) && isset($args['longitude']) && isset($args['altitude']) ) {
            $args['latitude'] = $args['latitude'] != '' ? $args['latitude'] : 0;
            $args['longitude'] = $args['longitude'] != '' ? $args['longitude'] : 0;
            $args['altitude'] = $args['altitude'] != '' ? $args['altitude'] : 0;

            $strsql = "INSERT INTO ciniki_tenant_details (tnid, detail_key, detail_value, date_added, last_updated) "
                . "VALUES ("
                . "'1', 'gps-current-latitude', '" . $args['latitude'] . "', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup latitude<br/><br/>" . $rc['err']['msg']);
            }

            $strsql = "INSERT INTO ciniki_tenant_details (tnid, detail_key, detail_value, date_added, last_updated) "
                . "VALUES ("
                . "'1', 'gps-current-longitude', '" . $args['longitude'] . "', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup longitude<br/><br/>" . $rc['err']['msg']);
            }

            $strsql = "INSERT INTO ciniki_tenant_details (tnid, detail_key, detail_value, date_added, last_updated) "
                . "VALUES ("
                . "'1', 'gps-current-altitude', '" . $args['altitude'] . "', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup altitude<br/><br/>" . $rc['err']['msg']);
            }
        }
        
        //
        // Add sysadmin as the owner of the master tenant
        //
        $strsql = "INSERT INTO ciniki_tenant_users (uuid, tnid, user_id, package, permission_group, status, date_added, last_updated) VALUES ("
            . "UUID(), '1', '1', 'ciniki', 'owners', '10', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'core');
            return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
        }

        //
        // Enable the QRUQSP modules
        //
        foreach(['aprs', '43392', 'i2c', 'weather', 'tnc', 'dashboard', 'piadmin'] as $module) {
            $strsql = "INSERT INTO ciniki_tenant_modules (tnid, package, module, status, ruleset, date_added, last_updated) "
                . "VALUES ('1', 'qruqsp', '" . $module . "', 1, '', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
            }
        }

        //
        // Setup the default TNC if radio interface version specified
        //
        if( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '0.2' ) {
            $strsql = "INSERT INTO qruqsp_tnc_devices (uuid, tnid, name, status, dtype, device, flags, settings, date_added, last_updated) "
                . "VALUES (UUID(), '1', '144.390', 40, 10, '', 0, 'a:2:{s:7:\"ADEVICE\";s:10:\"plughw:1,0\";s:3:\"PTT\";s:7:\"GPIO 24\";}', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
            }
        } elseif( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '1.0' ) {
            $strsql = "INSERT INTO qruqsp_tnc_devices (uuid, tnid, name, status, dtype, device, flags, settings, date_added, last_updated) "
                . "VALUES (UUID(), '1', '144.390', 40, 10, '', 0, 'a:2:{s:7:\"ADEVICE\";s:10:\"plughw:1,0\";s:3:\"PTT\";s:7:\"GPIO 23\";}', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
            }
        } elseif( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == 'CS-101' ) {
            $strsql = "INSERT INTO qruqsp_tnc_devices (uuid, tnid, name, status, dtype, device, flags, settings, date_added, last_updated) "
                . "VALUES (UUID(), '1', '144.390', 40, 10, '', 0, 'a:2:{s:7:\"ADEVICE\";s:10:\"plughw:1,0\";s:3:\"PTT\";s:7:\"GPIO 13\";}', UTC_TIMESTAMP(), UTC_TIMESTAMP())";
            $rc = ciniki_core_dbInsert($ciniki, $strsql, 'tenants');
            if( $rc['stat'] != 'ok' ) {
                ciniki_core_dbTransactionRollback($ciniki, 'core');
                return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
            }
        }

        //
        // Add the api key for the UI
        //
        $strsql = "INSERT INTO ciniki_core_api_keys (api_key, status, perms, user_id, appname, notes, "
            . "last_access, expiry_date, date_added, last_updated) VALUES ("
            . "'$manage_api_key', 1, 0, 2, 'ciniki-manage', '', 0, 0, UTC_TIMESTAMP(), UTC_TIMESTAMP())";
        $rc = ciniki_core_dbInsert($ciniki, $strsql, 'core');
        if( $rc['stat'] != 'ok' ) {
            ciniki_core_dbTransactionRollback($ciniki, 'core');
            return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
        }
    }

    // 
    // Save ciniki-api config file
    //
    $new_config = "";
    foreach($config as $module => $settings) {
        $new_config .= "[$module]\n";
        foreach($settings as $key => $value) {
            $new_config .= "    $key = $value\n";
        }
        $new_config .= "\n";
    }
    $num_bytes = file_put_contents($ciniki_root . '/ciniki-api.ini', $new_config);
    if( $num_bytes == false || $num_bytes < strlen($new_config)) {
        unlink($ciniki_root . '/ciniki-api.ini');
        ciniki_core_dbTransactionRollback($ciniki, 'core');
        return array('form'=>'yes', 'err'=>'ciniki.installer.99', 'msg'=>"Unable to write configuration, please check your website settings.");
    }

    //
    // Setup the /boot/config.txt file
    //
    $rc = setup_boot_config($args);
    if( $rc['stat'] != 'ok' ) {
        return array('form'=>'yes', 'err'=>'ciniki.installer.99', 'msg'=>"Unable to update /boot/config.txt.");
    }

    //
    // Setup files for Real Time Clock
    //
    if( (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '1.0')
        || (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == 'CS-101')
        ) {
        `sudo apt-get -y remove fake-hwclock`;
        `sudo update-rc.d -f fake-hwclock remove`;
        `sudo systemctl disable fake-hwclock`;

        `echo '# set the time from RTC now when it is available.' >/tmp/85-hwclock.rules`;
        `echo 'KERNEL=="rtc0", RUN+="/sbin/hwclock --rtc=$root/$name --hctosys' >>/tmp/85-hwclock.rules`;
        `sudo chown root:root /tmp/85-hwclock.rules`;
        `sudo mv /tmp/85-hwclock.rules /etc/udev/rules.d/`;

//        $hwclock_set = file_get_contents('/lib/udev/hwclock-set');

//        $hwclock_set = preg_replace("/^(if [ -e \/run\/systemd\/system ] ; then)$^(\s*exit 0)$^(fi)$/m", '#{$1}\n#{$2}\n#{$3}\n', $hwclock_set);
//        $hwclock_set = preg_replace("/^(if [ yes = \"\$BADYEAR\" ] ; then)$^(\s*\/sbin\/hwclock --rtc=\$dev --systz --badyear)$^(\s*\/sbin\/hwclock --rtc=\$dev --hctosys --badyear)$^(else)$^(\s*\/sbin\/hwclock --rtc=\$dev --systz)$^(\s*\/sbin\/hwclock --rtc=\$dev --hctosys)$^(fi)/m", '{$1}\n#{$2}\n{$3}\n{$4}\n#{$5}\n{$6}\n{$7}\n', $hwclock_set);

//        if( file_put_contents('/tmp/hwclock-set', $hwclock_set) !== false ) {
//            `sudo cp /lib/udev/hwclock-set /lib/udev/hwclock-set.backup`;
//            `sudo chown root:root /tmp/hwclock-set`;
//            `sudo mv /tmp/hwclock-set /lib/udev/hwclock-set`;
//        }
    }

    //
    // Save ciniki-manage config file
    //
    $manage_config = ""
        . "[ciniki.core]\n"
        . "manage_root_url = /ciniki-mods\n"
        . "themes_root_url = " . preg_replace('/^\/$/', '', dirname($args['request_uri'])) . "/ciniki-mods/core/ui/themes\n"
        . "json_url = " . preg_replace('/^\/$/', '', dirname($args['request_uri'])) . "/ciniki-json.php\n"
        . "api_key = $manage_api_key\n"
        . "site_title = '" . $master_name . "'\n"
        . "help.mode = internal\n"
        . "help.url = https://qruqsp.org/\n"
        . "";

    $num_bytes = file_put_contents($ciniki_root . '/ciniki-manage.ini', $manage_config);
    if( $num_bytes == false || $num_bytes < strlen($manage_config)) {
        unlink($ciniki_root . '/ciniki-api.ini');
        unlink($ciniki_root . '/ciniki-manage.ini');
        ciniki_core_dbTransactionRollback($ciniki, 'core');
        return array('form'=>'yes', 'err'=>'ciniki.installer.98', 'msg'=>"Unable to write configuration, please check your website settings.");
    }

    //
    // Save the .htaccess file
    //
    $htaccess = ""
        . "# Block evil spam bots\n"
        . "# List found on : http://perishablepress.com/press/2006/01/10/stupid-htaccess-tricks/#sec1\n"
        . "RewriteBase /\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^Anarchie [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^ASPSeek [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^attach [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^autoemailspider [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^Xaldon\ WebSpider [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^Xenu [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^Zeus.*Webster [OR]\n"
        . "RewriteCond %{HTTP_USER_AGENT} ^Zeus\n"
        . "RewriteRule ^.* - [F,L]\n"
        . "\n"
        . "# Block access to internal code\n"
        . "\n"
        . "Options All -Indexes\n"
        . "RewriteEngine On\n"
        . "# Force redirect to strip www from front of domain names\n"
        . "RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]\n"
        . "RewriteRule ^(.*)$ http://%1/$1 [R=301,L]\n"
        . "RewriteRule ^$ /manager [R=307,L]\n"
        . "# Allow access to artweb themes and cache, everything is considered public\n"
        . "RewriteRule ^qruqsp-dashboard-themes/(.*\.)(css|js|html|png|jpg)$ qruqsp-mods/dashboard/themes/$1$2 [L]\n"
        . "RewriteRule ^ciniki-web-layouts/(.*\.)(css|js|png|eot|ttf|woff|svg)$ ciniki-mods/web/layouts/$1$2 [L]\n"
        . "RewriteRule ^ciniki-web-themes/(.*\.)(css|js|html|png|jpg)$ ciniki-mods/web/themes/$1$2 [L]\n"
        . "RewriteRule ^ciniki-web-cache/(.*\.)(css|js|gif|jpg|png|mp3|ogg|wav)$ ciniki-mods/web/cache/$1$2 [L]\n"
        . "RewriteRule ^ciniki-picode/(.*\.)(zip|ini)$ ciniki-picode/$1$2 [L]\n"
        . "RewriteBase /\n"
        . "\n"
        . "AddType text/cache-manifest .manifest\n"
        . "\n"
        . "RewriteCond %{REQUEST_FILENAME} -f [OR]\n"
        . "RewriteCond %{REQUEST_FILENAME} -d\n"
        . "RewriteRule ^manager/(.*)$ ciniki-manage.php [L]                                     # allow all manager\n"
        . "RewriteRule ^(manager)$ ciniki-manage.php [L]                                        # allow all manager\n"
        . "RewriteRule ^dashboard/(.*)$ qruqsp-dashboard.php [L]                                # allow all dashboard\n"
        . "RewriteRule ^(dashboard)$ qruqsp-dashboard.php [L]                                   # allow all dashboard\n"
        . "RewriteRule ^([a-z]+-mods/[^\/]*/ui/.*)$ $1 [L]                                      # Allow manage content\n"
        . "RewriteRule ^(ciniki-web-themes/.*)$ $1 [L]                                          # Allow manage-theme content\n"
        . "RewriteRule ^(ciniki-mods/web/layouts/.*)$ $1 [L]                                    # Allow web-layouts content\n"
        . "RewriteRule ^(ciniki-mods/web/themes/.*)$ $1 [L]                                     # Allow web-themes content\n"
        . "RewriteRule ^(qruqsp-mods/dashboard/themes/.*)$ $1 [L]                                     # Allow web-themes content\n"
        . "RewriteRule ^(ciniki-mods/web/cache/.*\.(css|js|jpg|png|mp3|ogg|wav))$ $1 [L]                                      # Allow web-cache content\n"
        . "RewriteRule ^(ciniki-login|ciniki-sync|ciniki-json|index|ciniki-manage|qruqsp-dashboard).php$ $1.php [L]  # allow entrance php files\n"
        . "RewriteRule ^([_0-9a-zA-Z-]+/)(.*\.php)$ index.php [L]                                  # Redirect all other php requests to index\n"
        . "RewriteRule ^$ index.php [L]                                                              # Redirect all other requests to index\n"
        . "RewriteRule . index.php [L]                                                              # Redirect all other requests to index\n"
        . "\n"
        . "php_value post_max_size 20M\n"
        . "php_value upload_max_filesize 20M\n"
        . "php_value magic_quotes 0\n"
        . "php_flag magic_quotes off\n"
        . "php_value magic_quotes_gpc 0\n"
        . "php_flag magic_quotes_gpc off\n"
        . "php_value session.cookie_lifetime 3600\n"
        . "php_value session.gc_maxlifetime 3600\n"
        . "";

    $num_bytes = file_put_contents($ciniki_root . '/.htaccess', $htaccess);
    if( $num_bytes == false || $num_bytes < strlen($htaccess)) {
        unlink($ciniki_root . '/ciniki-api.ini');
        unlink($ciniki_root . '/ciniki-manage.ini');
        unlink($ciniki_root . '/.htaccess');
        ciniki_core_dbTransactionRollback($ciniki, 'core');
        return array('form'=>'yes', 'err'=>'ciniki.installer.97', 'msg'=>"Unable to write configuration, please check your website settings.");
    }

    //
    // Create symlinks into scripts
    //
    symlink($ciniki_root . '/ciniki-mods/core/scripts/sync.php', $ciniki_root . '/ciniki-sync.php');
    symlink($ciniki_root . '/ciniki-mods/core/scripts/json.php', $ciniki_root . '/ciniki-json.php');
    symlink($ciniki_root . '/qruqsp-mods/core/scripts/manage.php', $ciniki_root . '/ciniki-manage.php');
    symlink($ciniki_root . '/ciniki-mods/core/scripts/login.php', $ciniki_root . '/ciniki-login.php');
    symlink($ciniki_root . '/qruqsp-mods/dashboard/scripts/dashboard.php', $ciniki_root . '/qruqsp-dashboard.php');

    $rc = ciniki_core_dbTransactionCommit($ciniki, 'core');
    if( $rc['stat'] != 'ok' ) {
        ciniki_core_dbTransactionRollback($ciniki, 'core');
        unlink($ciniki_root . '/ciniki-api.ini');
        unlink($ciniki_root . '/ciniki-manage.ini');
        unlink($ciniki_root . '/.htaccess');
        unlink($ciniki_root . '/ciniki-json.php');
        unlink($ciniki_root . '/ciniki-manage.php');
        unlink($ciniki_root . '/ciniki-login.php');
        unlink($ciniki_root . '/index.php');
        return array('form'=>'yes', 'err'=>'ciniki.' . $rc['err']['code'], 'msg'=>"Failed to setup database<br/><br/>" . $rc['err']['msg']);
    }

    if( file_exists($ciniki_root . '/index.php') ) {
        unlink($ciniki_root . '/index.php');
    }
    symlink($ciniki_root . '/ciniki-mods/web/scripts/index.php', $ciniki_root . '/index.php');

    //
    // Setup the udev rules for accessing the SDR
    //
    if( file_exists($ciniki_root . '/qruqsp-mods/pibin/rtl-sdr.rules') ) {
        `sudo cp $ciniki_root/qruqsp-mods/pibin/rtl-sdr.rules /etc/udev/rules.d/`;
    }

    return array('form'=>'no', 'err'=>'installed', 'msg'=>'');
}

//
// Setup the radio interface
//
function setup_boot_config($args) {
    //
    // Load boot config and check for any changes that are required
    //
    $boot_config = file_get_contents('/boot/config.txt');

    //
    // Check i2c is enabled
    //
    if( (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '0.2') ) {
        if( preg_match('/^\s*dtparam\s*=\s*i2c_arm\s*=\s*(.*)$/m', $boot_config, $m) ) {
            if( $m[1] != 'on' ) {
                $boot_config = preg_replace('/^(\s*dtparam\s*=\s*i2c_arm\s*=\s*)(.*)$/m', '${1}on', $boot_config);
            }
        } elseif( preg_match('/^#dtparam\s*=\s*i2c_arm\s*=\s*(.*)$/m', $boot_config, $m) ) {
            $boot_config = preg_replace('/^#(dtparam\s*=\s*i2c_arm\s*=\s*)(.*)$/m', '${1}on', $boot_config);
        } else {
            $boot_config .= "dtparam=i2c_arm=on\n";
        }
        //
        // Check i2c is enabled on pins 17 and 27
        //
        if( preg_match('/^\s*dtoverlay\s*=\s*i2c-gpio(.*)$/m', $boot_config, $m) ) {
            if( preg_match('/i2c_gpio_sda\s*=\s*([0-9]+)/', $m[1], $sda) ) {
                if( $sda[1] != 17 ) {
                    $boot_config = preg_replace('/^(\s*dtoverlay.*i2c-gpio.*i2c_gpio_sda\s*=\s*)([0-9]+)/m', '${1}17', $boot_config);
                }
            }
            if( preg_match('/i2c_gpio_scl\s*=\s*([0-9]+)/', $m[1], $scl) ) {
                if( $scl[1] != 27 ) {
                    $boot_config = preg_replace('/^(\s*dtoverlay.*i2c-gpio.*i2c_gpio_scl\s*=\s*)([0-9]+)/m', '${1}27', $boot_config);
                }
            }
        } else {
            $boot_config .= "dtoverlay=i2c-gpio,i2c_gpio_sda=17,i2c_gpio_scl=27\n";
        }
        //
        // The following lines are taken from /usr/bin/raspi-config script
        //
        if( !file_exists("/etc/modprobe.d/raspi-blacklist.conf") ) {
            `sudo touch /etc/modprobe.d/raspi-blacklist.conf`;
        }
        `sudo sed /etc/modprobe.d/raspi-blacklist.conf -i -e "s/^\(blacklist[[:space:]]*i2c[-_]bcm2708\)/#\1/"`;
        `sudo sed /etc/modules -i -e "s/^#[[:space:]]*\(i2c[-_]dev\)/\1/"`;
        `sudo dtparam i2c_arm=on`;
        `sudo modprobe i2c-dev`;
    }

    //
    // Setup gpio shutdown pin
    //
    if( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '0.2' ) {
        // GPIO Pin 3
        if( preg_match('/^\s*dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*([0-9]+)$/m', $boot_config, $m) ) {
            if( $m[1] != 3 ) {
                $boot_config = preg_replace('/^\s*(dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*)([0-9]+)$/m', '${1}3', $boot_config);
            }
        } else {
            $boot_config .= "dtoverlay=gpio-shutdown,gpio_pin=3\n";
        }
    } elseif( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '1.0' ) {
        // GPIO Pin 27
        if( preg_match('/^\s*dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*([0-9]+)$/m', $boot_config, $m) ) {
            if( $m[1] != 3 ) {
                $boot_config = preg_replace('/^\s*(dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*)([0-9]+)$/m', '${1}27', $boot_config);
            }
        } else {
            $boot_config .= "dtoverlay=gpio-shutdown,gpio_pin=27\n";
        }
    } elseif( isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == 'CS-101' ) {
        // GPIO Pin 23
        if( preg_match('/^\s*dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*([0-9]+)$/m', $boot_config, $m) ) {
            if( $m[1] != 3 ) {
                $boot_config = preg_replace('/^\s*(dtoverlay\s*=\s*gpio-shutdown\s*,\s*gpio_pin\s*=\s*)([0-9]+)$/m', '${1}23', $boot_config);
            }
        } else {
            $boot_config .= "dtoverlay=gpio-shutdown,gpio_pin=23\n";
        }
    }

    //
    // Setup real time clock
    //
    if( (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '1.0')
        || (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == 'CS-101')
        ) {
        // Enable ds1307
        if( preg_match('/^\s*dtoverlay\s*=\s*itc-rtc\s*,\s*ds1307\s*$/m', $boot_config, $m) ) {
            if( $m[1] != 3 ) {
                $boot_config = preg_replace('/^\s*(dtoverlay\s*=\s*itc-rtc\s*,\s*ds1307\s*$/m', '${1}', $boot_config);
            }
        } else {
            $boot_config .= "dtoverlay=i2c-rtc,ds1307\n";
        }
        
    }


    if( file_put_contents('/tmp/config.txt', $boot_config) !== false ) {
        `sudo cp /boot/config.txt /boot/config.txt.backup`;
        `sudo chown root:root /tmp/config.txt`;
        `sudo mv /tmp/config.txt /boot/config.txt`;
    }

    //
    // Turn on i2c via raspi-config
    //
    if( (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == '1.0')
        || (isset($args['radiointerfaceversion']) && $args['radiointerfaceversion'] == 'CS-101')
        ) {
        `sudo /usr/bin/raspi-config nonint do_i2c 0`;
/*        if( preg_match('/^\s*dtparam\s*=\s*i2c_arm\s*=\s*(.*)$/m', $boot_config, $m) ) {
            if( $m[1] != 'on' ) {
                $boot_config = preg_replace('/^(\s*dtparam\s*=\s*i2c_arm\s*=\s*)(.*)$/m', '${1}on', $boot_config);
            }
        } elseif( preg_match('/^#dtparam\s*=\s*i2c_arm\s*=\s*(.*)$/m', $boot_config, $m) ) {
            $boot_config = preg_replace('/^#(dtparam\s*=\s*i2c_arm\s*=\s*)(.*)$/m', '${1}on', $boot_config);
        } else {
            $boot_config .= "dtparam=i2c_arm=on\n";
        }
        //
        // The following lines are taken from /usr/bin/raspi-config script
        //
        if( !file_exists("/etc/modprobe.d/raspi-blacklist.conf") ) {
            `sudo touch /etc/modprobe.d/raspi-blacklist.conf`;
        }
        `sudo sed /etc/modprobe.d/raspi-blacklist.conf -i -e "s/^\(blacklist[[:space:]]*i2c[-_]bcm2708\)/#\1/"`;
        `sudo sed /etc/modules -i -e "s/^#[[:space:]]*\(i2c[-_]dev\)/\1/"`;
        `sudo dtparam i2c_arm=on`;
        `sudo modprobe i2c-dev`; */
    }
    return array('stat'=>'ok');
}

?>
