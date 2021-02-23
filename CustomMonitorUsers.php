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
        $this->url = 'https://github.com/starikov-aa/CustomMonitorUsers';
    }


    function config()
    {
        return [
            'add_monitoring_users_via_selectbox' => 0
        ];
    }

    function init()
    {
        foreach ($this->config() as $k => $v) {
            if (plugin_config_get($k, "notfound") == "notfound") {
                plugin_config_set($k, $v);
            }
        }
    }


    /**
     * plugin hooks
     * @return array
     */
    function hooks()
    {
        $t_hooks = array(
            'EVENT_LAYOUT_BODY_END' => 'add_scripts'
        );

        return $t_hooks;
    }

    function add_scripts()
    {
        if (preg_match('/.*\/view\.php/i', $_SERVER['SCRIPT_NAME']) && plugin_config_get('add_monitoring_users_via_selectbox')) {
            $html = "<link rel='stylesheet' href='" . plugin_file("bootstrap-select.min.css") . "'>";
            $html .= "<script src='" . plugin_file("bootstrap-select.min.js") . "'></script>";
            $html .= "<script>$('form[action=\'bug_monitor_add.php\']').attr('action', '" . plugin_page("bug_monitor_add") . "').attr('method', 'post')</script>";
            $html .= $this->create_monitor_user_selectbox();
            return $html . plugin_config_get('add_monitoring_users_via_selectbox');
        }
    }

    function create_monitor_user_selectbox()
    {
        $t_issue_id = gpc_get_int('id');

        $t_monitor_can_add = access_has_bug_level(config_get('monitor_add_others_bug_threshold'), $t_issue_id) ? true : false;

        if (!$t_monitor_can_add) {
            return false;
        }

        $t_bug = bug_get($t_issue_id);


        $t_users_can_monitor = project_get_all_user_rows($t_bug->project_id, config_get('monitor_bug_threshold'));

        $t_display = array();
        $t_sort = array();
        $t_show_realname = (ON == config_get('show_realname'));
        $t_sort_by_last_name = (ON == config_get('sort_by_last_name'));

        foreach ($t_users_can_monitor as $key => $t_user) {

            $t_user['username'] = str_replace('\\', '\\\\', $t_user['username']);

            # If user is already monitoring the issue, remove them from list
            if (in_array($t_user['id'], bug_get_monitors($t_issue_id))) {
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

        if (count($t_users_can_monitor) > 0) {
            array_multisort($t_sort, SORT_ASC, SORT_STRING, $t_users_can_monitor, $t_display);
            $select_opt = "";
            foreach ($t_users_can_monitor as $key => $t_user) {
                $select_opt .= '<option value="' . $t_user['id'] . '">' . string_attribute($t_display[$key]) . '</option>';
            }

            $html = '<select name="user_id[]" class="selectpicker" multiple data-style="btn-sm btn-white" ';
            $html .= 'data-container="body">' . $select_opt . '</select>';

        } else {
            $html = "All users have already been added";
        }

        return "<script>$('#bug_monitor_list_username').replaceWith('" . $html . "')</script>";


    }
}