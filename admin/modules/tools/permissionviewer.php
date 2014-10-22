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
    global $mybb, $db, $table, $groupzerogreater, $lang;
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
        "cansendemailoveride" => "can_email_users_override",
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
        "maxrreputationsperthread" => "max_reputations_perthread",
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

        foreach($userpermissions as $key => $value)
        {
            if(in_array($key, $skip_keys))
            {
                continue;
            }
            if(array_key_exists($key, $language_strings))
            {
                $language_string = $lang->$language_strings[$key];
            }
            else
            {
                $language_string = $key;
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
    global $mybb, $db, $lang, $groupzerogreater, $table;
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
        if(!$mybb->input['guest'])
        {
            $usergroupinfo = usergroup_permissions($gids);
        }
        else
        {
            $usergroupinfo = usergroup_permissions(1);
            $gids = 1;
            $mybb->input['username']= "Guest";
        }

        // Get the forum list
        $forumquery = $db->simple_select("forums", "fid,name", "", array("order_by" => "name", "order_dir" => "ASC"));
        while($forum = $db->fetch_array($forumquery))
        {
            $table->construct_header("Permission");
            $table->construct_header("Value");
            $table->construct_row();
            $fid = $forum['fid'];
            $permissionquery = $db->simple_select("forumpermissions", "*", "fid=$fid AND gid IN(" . $gids . ")");
            $forumpermissions = array();
            $zerogreater = array("canonlyreplyownthreads", "canonlyviewownthreads");
            while($permission = $db->fetch_array($permissionquery))
            {
                foreach($permission as $key => $value)
                {
                    if(in_array($key,$zerogreater) && $forumpermissions[$key] != 1)
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
            }
            if(!$forumpermissions['fid'])
            {
                $forumpermissions = $usergroupinfo;
            }

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

         //Now permissions are built, lets display them.
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
            $table->output("Permissions in " . $forum['name']);
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
