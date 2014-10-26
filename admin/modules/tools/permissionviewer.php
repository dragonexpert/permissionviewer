<?php

if(!defined("IN_MYBB"))
{
    die("Direct access not allowed.");
}

$page->output_header("Permission Viewer");

$page->add_breadcrumb_item("View Permissions", "index.php?module=tools-permissionviewer.php");

$table = new DefaultTable();

if($mybb->input['username'])
{
    $usernametab = "&username=" . $mybb->input['username'];
}

$sub_tabs = array(
    "generalpermissions" => array(
    "title" => "General Permissions",
    "link" => "index.php?module=tools-permissionviewer&action=general" . $usernametab
    ),
    "guestgeneralpermissions" => array(
    "title" => "Guest General Permissions",
    "link" => "index.php?module=tools-permissionviewer&action=general&guest=1"
    ),
    "forumpermissions" => array(
    "title" => "Forum Permissions",
    "link" => "index.php?module=tools-permissionviewer&action=forum" . $usernametab
    ),
    "guestforumpermissions" => array(
    "title" => "Guest Forum Permissions",
    "link" => "index.php?module=tools-permissionviewer&action=forum&guest=1"
    )
    );

$page->output_nav_tabs($sub_tabs);

if($mybb->input['action'])
{
    $action = $mybb->input['action'];
}
else
{
    $action = "general";
}

switch($action)
{
    case "general":
    permissionviewer_general();
    break;

    case "forum":
    permissionviewer_forum();
    break;

    default:
    permissionviewer_general();
    break;
}

function permissionviewer_general()
{
    global $mybb, $db, $table, $groupzerogreater, $lang, $cache, $plugins;
    $lang->load("user_groups");
    if($mybb->input['username'] || $mybb->input['guest'])
    {
        // We have a username so figure out their permissions
        $where = $db->escape_string($mybb->input['username']);
        if(!$mybb->input['guest'])
        {
            $query = $db->simple_select("users", "usergroup,additionalgroups", "username='$where'");
            $userinfo = $db->fetch_array($query);
        }
        else
        {
            $userinfo['usergroup'] = 1;
        }
        $groups = $userinfo['usergroup'];
        if($userinfo['additionalgroups'])
        {
            $groups .= "," . $userinfo['additionalgroups'];
        }
        // We need a hook here for $groupzerogreater if plugin authors want a 0 to be unlimited.
        $plugins->run_hooks("tools_permissionviewer_general_zero", $groupzerogreater);
        $userpermissions = usergroup_permissions($groups);
        $table->construct_header("Permission");
        $table->construct_header("Value");
        $table->construct_row();
        // The array of keys that should be skipped
        $skip_keys = array("gid", "type", "title", "description", "namestyle", "usertitle", "stars", "starimage", "image", "disporder");

        // Create an array of default permission language strings.

        $language_strings = array(
        "isbannedgroup"=> "is_banned_group",
        "canview" => "can_view_board",
        "canviewthreads" => "can_view_threads",
        "canviewprofiles" => "can_view_profiles",
        "candlattachments" => "can_download_attachments",
        "canviewboardclosed" => "can_view_board_closed",
        "canpostthreads" => "can_post_threads",
        "canpostreplys" => "can_post_replies",
        "canpostattachments" => "can_post_attachments",
        "canratethreads" => "can_rate_threads",
        "modposts" => "mod_new_posts",
        "modthreads" => "mod_new_threads",
        "mod_edit_posts" => "mod_after_edit",
        "modattachments" => "mod_new_attachments",
        "caneditposts" => "can_edit_posts",
        "candeleteposts" => "can_delete_posts",
        "candeletethreads" => "can_delete_threads",
        "caneditattachments" => "can_edit_attachments",
        "canpostpolls" => "can_post_polls",
        "canvotepolls" => "can_vote_polls", 
        "canundovotes" => "can_undo_votes",
        "canusepms" => "can_use_pms",
        "cansendpms" => "can_send_pms",
        "cantrackpms" => "can_track_pms",
        "candenypmreceipts" => "can_deny_reciept",
        "pmquota" => "message_quota",
        "maxpmrecipients" => "max_recipients",
        "cansendemail" => "can_email_users",
        "cansendemailoverride" => "can_email_users_override",
        "maxemails" => "max_emails_per_day",
        "emailfloodtime" => "email_flood_time",
        "canviewmemberlist" => "can_view_member_list",
        "canviewcalendar" => "can_view_calendar",
        "canaddevents" => "can_post_events",
        "canbypasseventmod" => "can_bypass_event_moderation",
        "canmoderateevents" => "can_moderate_events",
        "canviewonline" => "can_view_whos_online",
        "canviewwolinvis" => "can_view_invisible",
        "canviewonlineips" => "can_view_ips",
        "cancp" => "can_access_admin_cp",
        "issupermod" => "is_super_mod",
        "cansearch" => "can_search_forums",
        "canusercp" => "can_access_usercp",
        "canuploadavatars" => "can_upload_avatars",
        "canratemembers" => "can_give_reputation",
        "canchangename" => "can_change_username",
        "canbereported" => "can_be_reported",
        "canchangewebsite" => "can_change_website",
        "showforumteam" => "forum_team",
        "usereputationsystem" => "show_reputations",
        "cangivereputations" => "can_give_reputation",
        "reputationpower" => "points_to_award_take",
        "maxreputationsday" => "max_reputations_daily",
        "maxreputationsperuser" => "max_reputations_peruser",
        "maxreputationsperthread" => "max_reputations_perthread",
        "candisplaygroup" => "can_set_as_display_group",
        "attachquota" => "attach_quota",
        "cancustomtitle" => "can_use_usertitles",
        "canwarnusers" => "can_send_warnings",
        "canreceivewarnings" => "can_receive_warnings",
        "maxwarningsday" => "warnings_per_day",
        "canmodcp" => "can_access_mod_cp",
        "showinbirthdaylist" => "show_in_birthday_list",
        "canoverridepm" => "can_override_pms",
        "canusesig" => "can_use_signature",
        "canusesigxposts" => "can_use_signature_posts",
        "signofollow"  => "uses_no_follow",
        "edittimelimit" => "edit_time_limit",
        "maxposts" => "max_posts_per_day",
        "showmemberlist" => "member_list",
        "canmanageannounce" => "can_manage_announce",
        "canmanagemodqueue" => "can_manage_mod_queue",
        "canmanagereportedcontent" => "can_manage_reported_content",
        "canviewmodlogs" => "can_view_mod_logs",
        "caneditprofiles" => "can_edit_profiles",
        "canbanusers" => "can_ban_users",
        "canviewwarnlogs" => "can_view_warnlogs",
        "canuseipsearch" => "can_use_ipsearch",
        );

        /* Now we are going to load the plugin cache.  
        * This lets us try and find additional language files.
        */

        $active_plugins = $cache->read("plugins");
        foreach($active_plugins['active'] as $plugin)
        {
            require_once MYBB_ROOT . "/inc/plugins/" . $plugin . ".php";
            $info_function = $plugin . "_info";
            $plugin_info = $info_function();
            if(array_key_exists("language_file", $plugin_info))
            {
                $lang->load($plugin_info['language_file']);
                if(array_key_exists("language_prefix", $plugin_info))
                {
                    $prefixes[] = $plugin_info['language_prefix'];
                }
            }
        }

        // For those who would rather use a hook for language
       $plugins->run_hooks("tools_permissionviewer_general_language");


        foreach($userpermissions as $key => $value)
        {
            if(in_array($key, $skip_keys))
            {
                continue;
            }
            $language_string = $key;
            if(array_key_exists($key, $language_strings))
            {
                $language_string = $lang->$language_strings[$key];
            }
            else
            {
                // No immediate key is available. Try to create one
                foreach($prefixes as $prefix)
                {
                    $keyname = $prefix . $key;
                    if(property_exists($lang, $keyname))
                    {
                        $language_string = $lang->$keyname;
                        continue;
                    }
                }
            }
            $table->construct_cell($language_string);
            // Figure out if it should be a yes/no language string
            if(!in_array($key, $groupzerogreater))
            {
                if($value == 1)
                {
                    $table->construct_cell($lang->yes);
                }
                elseif ($value == 0)
                {
                    $table->construct_cell($lang->no);
                }
                else
                {
                    $table->construct_cell($value);
                }
            }
            else
            {
                if($value == 0)
                {
                    $table->construct_cell("Unlimited");
                }
                else
                {
                    $table->construct_cell($value);
                }
            }
            $table->construct_row();
        }
        if($mybb->input['guest'])
        {
            $mybb->input['username'] = "Guest";
        }
        $table->output("General Permissions for " . $mybb->input['username']);
        // Show the form to let them search again
        $form = new DefaultForm("index.php", "get");
        $form_container = new FormContainer("Search");
        $form_container->output_row('', '', $form->generate_hidden_field("module", "tools-permissionviewer"), '');
        $form_container->output_row("Username", "Enter the username of the person you wish to check.", $form->generate_text_box("username", $mybb->input['username']), "username");
        $form_container->end();
        $form->output_submit_wrapper(array($form->generate_submit_button("View Permissions")));
        $form->end();
    }
    else
    {
        // We show the form
        $form = new DefaultForm("index.php", "get");
        $form_container = new FormContainer("Search");
        $form_container->output_row('', '', $form->generate_hidden_field("module", "tools-permissionviewer"), '');
        $form_container->output_row("Username", "Enter the username of the person you wish to check.", $form->generate_text_box("username", $mybb->user['username']), "username");
        $form_container->end();
        $form->output_submit_wrapper(array($form->generate_submit_button("View Permissions")));
        $form->end();
    }
}

function permissionviewer_forum()
{
    global $mybb, $db, $lang, $groupzerogreater, $table, $cache, $plugins;
    if($mybb->input['username'] || $mybb->input['guest'] == 1)
    {
        $lang->load("forum_management");
        // We have a username so fetch the permissions
        $escapedusername = $db->escape_string($mybb->input['username']);
        $query = $db->simple_select("users", "uid,usergroup,additionalgroups", "username='$escapedusername'");
        // We will let it accept an invalid username so an admin can get a guest permission overview
        $userinfo = $db->fetch_array($query);
        $gids = $userinfo['usergroup'];
        if($userinfo['additionalgroups'])
        {
            $gids .= "," . $userinfo['additionalgroups'];
        }

        // Get the usergroup info for permissions
        if($mybb->input['guest'])
        {
            $mybb->input['username']= "Guest";
        }
        // Get the forum list
        $forums = $cache->read("forums");

         // This is used if there are no custom permissions for that forum.
            $good_keys = array(
                "canview" => "viewing_field_canview",
                "canviewthreads" => "viewing_field_canviewthreads",
                "canonlyviewownthreads" => "viewing_field_canonlyviewownthreads",
                "candlattachments" => "viewing_field_candlattachments",
                "canpostthreads" => "posting_rating_field_canpostthreads",
                "canpostreplys" => "posting_rating_field_canpostreplys",
                "canonlyreplyownthreads" => "posting_rating_field_canonlyreplyownthreads",
                "canpostattachments" => "posting_rating_field_canpostattachments",
                "canratethreads" => "posting_rating_field_canratethreads",
                "caneditposts" => "editing_field_caneditposts",
                "candeleteposts" => "editing_field_candeleteposts",
                "candeletethreads" => "editing_field_candeletethreads",
                "caneditattachments" => "editing_field_caneditattachments",
                "modposts" => "moderate_field_modposts",
                "modthreads" => "moderate_field_modthreads",
                "mod_edit_posts" => "moderate_field_mod_edit_posts",
                "modattachments" => "moderate_field_modattachments",
                "canpostpolls" => "polls_field_canpostpolls",
                "canvotepolls" => "polls_field_canvotepolls",
                "cansearch" => "misc_field_cansearch"
                );

        // Let plugin authors add their own keys to the array.
         $plugins->run_hooks("tools_permissionviewer_forum_good_keys", $good_keys);

         // Now permissions are built, lets display them.
         foreach($forums as $forum)
         {
             $forumname = $forum['name'];
             // Guests require special tricks
             if(!$mybb->input['guest'])
             {
                $totalforumpermissions = forum_permissions($forum['fid'], $userinfo['uid']);
             }
             else
             {
                 $totalforumpermissions = forum_permissions($forum['fid'], 0, 1);
             }
             // Now format the permissions so it doesn't show it a billion times.
             $zerogreater = array("canonlyreplyownthreads", "canonlyviewownthreads");
             $forumpermissions = array();
             foreach($totalforumpermissions as $key => $value)
              {
                    if(!array_key_exists($key, $good_keys))
                    {
                        continue;
                    }
                    if(in_array($key, $zerogreater) && $forumpermissions[$key] != 1)
                    {
                        if($value == 1)
                        {
                            $forumpermissions[$key] = 0; // Use this to avoid wrong language
                        }
                        else
                        {
                            $forumpermissions[$key] = 1;
                        }
                    }
                    else
                    {
                        if(!$forumpermissions[$key] || $forumpermissions[$key] < $value)
                        {
                            $forumpermissions[$key] = $value;
                        }
                    }
                }
                // Show whether or not the forum is active
                $table->construct_cell($lang->forum_is_active . "<br />" . $lang->forum_is_active_desc);
                if($forum['active'])
                {
                    $table->construct_cell($lang->yes);
                }
                else
                {
                    $table->construct_cell($lang->no);
                }
                $table->construct_row();
                // Show if the forum is open for posting
                $table->construct_cell($lang->forum_is_open . "<br />" . $lang->forum_is_open_desc);
                if($forum['open'])
                {
                    $table->construct_cell($lang->yes);
                }
                else
                {
                    $table->construct_cell($lang->no);
                }
                $table->construct_row();
                foreach($forumpermissions as $permissionname => $value)
                {
                    if(!array_key_exists($permissionname, $good_keys))
                    {
                        continue;
                    }
                    $table->construct_cell($lang->$good_keys[$permissionname]);
                    if($value == 1)
                    {
                        $table->construct_cell($lang->yes);
                    }
                    else
                    {
                        $table->construct_cell($lang->no);
                    }
                    $table->construct_row();
                }
                $table->output("Permissions in " . $forumname);
         }
        // Show the form so they can search again
        $form = new DefaultForm("index.php", "get");
        $form_container = new FormContainer("Search");
        $form_container->output_row('', '', $form->generate_hidden_field("module", "tools-permissionviewer"), '');
        $form_container->output_row('', '', $form->generate_hidden_field("action", "forum"), '');
        $form_container->output_row("Username", "Enter the username of the person you wish to check.", $form->generate_text_box("username", $mybb->input['username']), "username");
        $form_container->end();
        $form->output_submit_wrapper(array($form->generate_submit_button("View Permissions")));
        $form->end();
    }
    else
    {
        // We show the form
        $form = new DefaultForm("index.php", "get");
        $form_container = new FormContainer("Search");
        $form_container->output_row('', '', $form->generate_hidden_field("module", "tools-permissionviewer"), '');
        $form_container->output_row('', '', $form->generate_hidden_field("action", "forum"), '');
        $form_container->output_row("Username", "Enter the username of the person you wish to check.", $form->generate_text_box("username", $mybb->user['username']), "username");
        $form_container->end();
        $form->output_submit_wrapper(array($form->generate_submit_button("View Permissions")));
        $form->end();
    }
}

$page->output_footer();
?>
