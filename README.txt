permissionviewer
================

Easily view permissions your users have.  For MyBB 1.8.

Installation:
1) Upload /inc/plugins/permissionviewer.php to your /inc/plugins directory.
2) Upload /admin/modules/tools/permissionviewer.php to your /admin/modules/tools directory.
3) Install from the Admin CP.

Usage
1) Log into the Admin CP.
2) Click Tools.
3) Click View Permissions.
4) Enter the username who you wish to check.

Making Plugins Work with Permission Viewer
==========================================

In your _info function add the following keys: "language_file" and "language_prefix".  Language file will be the file name of the 
language file without the .lang.php extension.  The key "language_prefix" should be used if you intend on using a different name
forthe language variable than its database field.

When you are creating your language file that goes in the /inc/languages/english/admin folder, any database field created by theplugin
in the mybb_usergroups table should use your prefix followed by the database field name.  

Ex. A plugin adds the field canlockownthreads.

The plugin file should look like this:
lockownthreads_info()
{
  return array(
  // normal keys go here
  "language_file" => "lockownthreads",
  "language_prefix" => "lot_"
  );
}

Now our language file should be named "lockownthreads.lang.php".  It should look like this:
<?php
$l['lot_lockownthreads'] = "Can lock own threads?";
// any other language variables
?>

Now when you use the permission viewer, it will automatically load those language variables so everyone can get human readbale
descriptions of the permissions.
