<?php
if(!defined("IN_MYBB"))
{
    die("Direct Access not allowed.");
}

$plugins->add_hook("admin_tools_action_handler", "permissionviewer_view");
$plugins->add_hook("admin_tools_menu", "permissionviewer_menu");

function permissionviewer_info()
{
    return array(
        "name" => "Permission Viewer",
        "description" => "Easily view what permissions users have",
        "website" => "http://www.mybb.com",
        "author" => "Mark Janssen",
        "version" => "1.0",
        "compatibility" => "18*",
        "codename" => "permissionviewer"
    );
}

function permissionviewer_activate()
{
    
}

function permissionviewer_deactivate()
{
    
}

function permissionviewer_view(&$actions)
{
    $actions['permissionviewer'] = array(
    "active" => "permissionviewer",
    "file" => "permissionviewer.php"
    );
}

function permissionviewer_menu(&$sub_menu)
{
    $key = count($sub_menu) * 10 +10;
    $sub_menu[$key] = array(
    "id" => "permissionviewer",
    "title" => "View Permissions",
    "link" => "index.php?module=tools-permissionviewer"
    );
}
