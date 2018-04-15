<?php

/**
*
* @package phpBB Extension - Oxpus Downloads
* @copyright (c) 2014 OXPUS - www.oxpus.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/*
* connect to phpBB
*/
if ( !defined('IN_PHPBB') )
{
	exit;
}

$check_user = $request->variable('check_user', '');

$s_display_perms = false;

if ($submit && $check_user)
{
    $username = utf8_clean_string($check_user);

    $sql = 'SELECT * FROM ' . USERS_TABLE . "
        WHERE username_clean = '" . $db->sql_escape($username) . "'";
    $result			= $db->sql_query($sql);
    $row			= $db->sql_fetchrow($result);
    $db->sql_freeresult($result);

    if ($row)
    {
        foreach($row as $key => $value)
        {
            $$key = $value;
        }

        // Check for selected user and reinit the download classes to get the right content
        $reset_user_data = false;
        if ($user_id <> $user->data['user_id'])
        {
            $tmp_user_data = $user->data;
            $user->data = $row;
            $user->data['is_registered'] = true;
            $user->data['session_browser'] = $tmp_user_data['session_browser'];
            $user->data['session_ip'] = $tmp_user_data['session_ip'];
            $auth->acl($user->data);
            \oxpus\dlext\phpbb\classes\ dl_init::init($ext_path);
            $reset_user_data = true;
        }

        // Fetch category permissions
        $cat_perm_ary   = array();
        $dl_index       = array();
        $dl_index       = \oxpus\dlext\phpbb\classes\ dl_main::full_index();

        foreach ($dl_index as $cat_id => $value)
        {
        	$cat_perm_ary[$cat_id]['cat_name']		= $dl_index[$cat_id]['cat_name'];
        	$cat_perm_ary[$cat_id]['auth_view']		= \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_view');
        	$cat_perm_ary[$cat_id]['auth_dl']		= \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_dl');
        	$cat_perm_ary[$cat_id]['auth_up']		= \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_up');
        	$cat_perm_ary[$cat_id]['auth_mod']		= \oxpus\dlext\phpbb\classes\ dl_auth::user_auth($cat_id, 'auth_mod');
        	$cat_perm_ary[$cat_id]['comment_read']	= \oxpus\dlext\phpbb\classes\ dl_auth::cat_auth_comment_read($cat_id);
        	$cat_perm_ary[$cat_id]['comment_post']	= \oxpus\dlext\phpbb\classes\ dl_auth::cat_auth_comment_post($cat_id);

            $cat_perm_ary[$cat_id]['cat_remain']    = ($config['dl_traffic_off']) ? true : false;
            if (($dl_index[$cat_id]['cat_traffic'] && ($dl_index[$cat_id]['cat_traffic'] - $dl_index[$cat_id]['cat_traffic_use'] <= 0)) && !$config['dl_traffic_off'])
            {
                if (FOUNDER_TRAFFICS_OFF == true)
                {
                    $cat_perm_ary[$cat_id]['cat_remain'] = true;
                }
            }
        }

        // General user permissions
        $template->assign_vars(array(
            'USER_IS_ADMIN'         => \oxpus\dlext\phpbb\classes\ dl_auth::user_admin(),
            'USER_IS_BANNED'        => \oxpus\dlext\phpbb\classes\ dl_auth::user_banned(),
            'USER_CAN_VIEW_STATS'   => \oxpus\dlext\phpbb\classes\ dl_auth::stats_perm(),
            'USER_CAN_SEE_TRACKER'  => \oxpus\dlext\phpbb\classes\ dl_auth::bug_tracker(),
            'USER_HAVE_TRAFFIC'     => \oxpus\dlext\phpbb\classes\ dl_format::dl_size($user->data['user_traffic']),
            'USER_HAVE_POSTS'       => $user->data['user_posts'] . ' / ' .$config['dl_posts'],
            'CHECK_USERNAME'        => $user->data['username'],

    		'U_BACK'				=> $this->u_action,
        ));

        foreach($cat_perm_ary as $cat_id => $data_ary)
        {
            $template->assign_block_vars('cat_row', array(
                'CAT_NAME'  => $data_ary['cat_name'],
                'CAT_VIEW'  => $data_ary['auth_view'],
                'CAT_DL'    => $data_ary['auth_dl'],
                'CAT_UP'    => $data_ary['auth_up'],
                'CAT_MOD'   => $data_ary['auth_mod'],
                'CAT_CREAD' => $data_ary['comment_read'],
                'CAT_CPOST' => $data_ary['comment_post'],
            ));
        }

        // Reset userdata to the real current user and reinit the download classes to get the right content
        if ($reset_user_data)
        {
            $user->data = $tmp_user_data;
            unset($tmp_user_data);
            $auth->acl($user->data);
            \oxpus\dlext\phpbb\classes\ dl_init::init($ext_path);
        }

        $s_display_perms = true;
    }
}

$u_user_select = append_sid("{$phpbb_root_path}memberlist.$phpEx", "mode=searchuser&amp;form=select_user&amp;field=check_user&amp;select_single=true");

$template->assign_vars(array(
    'S_FORM_ACTION'     => $basic_link,
    'S_DL_PERM_CHECK'   => true,
    'S_DISPLAY_PERMS'   => $s_display_perms,

    'U_FIND_USERNAME'   => $u_user_select,
));
