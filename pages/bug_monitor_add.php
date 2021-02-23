<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file turns monitoring on or off for a bug for the current user
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses print_api.php
 * @uses utility_api.php
 */

require_once('core.php');
require_api('error_api.php');
require_api('form_api.php');
require_api('gpc_api.php');
require_api('helper_api.php');
require_api('print_api.php');
require_api('utility_api.php');

form_security_validate('bug_monitor_add');

$f_bug_id = gpc_get_int('bug_id');
$f_user_ids = gpc_get_int_array( 'user_id', array() );
$t_data = array(
    'query' => array('issue_id' => $f_bug_id),
);

if ($f_user_ids) {
    foreach ($f_user_ids as $t_user_id) {
        $t_users[] = array('id' => $t_user_id);
    }
    $t_data['payload'] = array('users' => $t_users);
}


$t_command = new MonitorAddCommand($t_data);
$t_command->execute();

form_security_purge('bug_monitor_add');

print_successful_redirect_to_bug($f_bug_id);
