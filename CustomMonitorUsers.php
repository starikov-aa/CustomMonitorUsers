<?php
/**
 * Plugin for authorization in MantisBT on multiple LDAP servers
 * Copyright (C) 2021  Starikov Anton - starikov_aa@mail.ru
 * https://github.com/starikov-aa/MultiLdapAuth
 */

class CustomMonitorUsersPlugin extends MantisPlugin
{
    /**
     * A method that populates the plugin information and minimum requirements.
     * @return void
     */
    function register()
    {
        $this->name = 'CustomMonitorUsers';
        $this->description = 'CustomMonitorUsers';

        $this->version = '0.1';
        $this->requires = array(
            'MantisCore' => '2.3.0-dev',
        );

        $this->author = 'Starikov Anton';
        $this->contact = 'starikov_aa@mail.ru';
        $this->url = 'https://github.com/starikov-aa/';
    }


    function config()
    {
        return [
        ];
    }

    function init()
    {
//        plugin_require_api('core/mla_Tools.class.php');
    }


    /**
     * plugin hooks
     * @return array
     */
    function hooks()
    {
        $t_hooks = array(
            'EVENT_LAYOUT_BODY_END' => 'add_js_code'
        );

        return $t_hooks;
    }

    function add_js_code()
    {
        print_project_user_list();
        if (preg_match('/.*\/view\.php/i', $_SERVER['SCRIPT_NAME'])) {
//            return "<script>$('#bug_monitor_list_username').replaceWith('<select name=username></select>')</script>";
            return print_r($this->create_monitor_user_selectbox(), true);
        }
    }

    function create_monitor_user_selectbox()
    {
        $t_users_can_monitor = project_get_all_user_rows($g_project_override, config_get('monitor_bug_threshold'));

        $t_display = array();
        $t_sort = array();
        $t_show_realname = (ON == config_get('show_realname'));
        $t_sort_by_last_name = (ON == config_get('sort_by_last_name'));

        foreach ($t_users_can_monitor as $key => $t_user) {

            # If user is already monitoring the issue, remove them from list
            if (in_array($t_user['id'], $t_users)) {
                unset($t_users_can_monitor[$key]);
                continue;
            }

            $t_user_name = string_attribute($t_user['username']);
            $t_sort_name = mb_strtolower($t_user_name);
            if ($t_show_realname && ($t_user['realname'] <> '')) {
                $t_user_name = string_attribute($t_user['realname']);
                if ($t_sort_by_last_name) {
                    $t_sort_name_bits = explode(' ', mb_strtolower($t_user_name), 2);
                    $t_sort_name = (isset($t_sort_name_bits[1]) ? $t_sort_name_bits[1] . ', ' : '') . $t_sort_name_bits[0];
                } else {
                    $t_sort_name = mb_strtolower($t_user_name);
                }
            }
            $t_display[] = $t_user_name;
            $t_sort[] = $t_sort_name;
        }
    }
}