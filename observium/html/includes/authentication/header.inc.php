<?php

/**
 * Observium header based authentication to use with nginx and oauth2-proxy
 *
 * Inspired by remote.inc.php
 *
 * You must configure your http server beforhand.
 * Make sure headers are not forwarded without being filtered first!
 *
 * Configuration variables:
 *
 * $config['auth_mechanism'] = "header";
 *   - Enables this authentication method
 *
 * $config['auth_header_role_mapping'] = array(
 *   "role-0" => 0,
 *   "role-1" => 1,
 *   "role-5" => 5,
 *   "role-7" => 7,
 *   "role-8" => 8,
 *   "role-9" => 9,
 *   "role-10" => 10,
 * );
 *   - Mapping between roles and userlevel. https://docs.observium.org/user_levels/
 *     Highest takes precedence except if a level zero is in the roles then the account is disabled.
 *
 * $config['auth_header_user'] = 'HTTP_X_PREFERRED_USERNAME';
 *   - Header providing the username (HTTP_ prefixed, uppercase, - replaced by _)
 * $config['auth_header_roles'] = 'HTTP_X_GROUPS';
 *   - Header providing coma separated groups/roles (HTTP_ prefixed, uppercase, - replaced by _)
 *     See role mapping
 * @copyright  (C) 2023 Damien Degois
 *
 */

if (!$_SESSION['authenticated'] && !is_cli())
{
  $username_header = isset($config['auth_header_user']) ? $config['auth_header_user']: 'HTTP_X_PREFERRED_USERNAME';
  $group_header_name = isset($config['auth_header_roles']) ? $config['auth_header_roles'] : "HTTP_X_GROUPS";

  if (isset($_SERVER[$username_header]) && !empty($_SERVER[$username_header]))
  {
    $username = $_SERVER[$username_header];
    session_set_var('username', $username);
    session_set_var('authenticated', true);

    global $config;
    $roles = explode(",", isset($_SERVER[$group_header_name]) ? $_SERVER[$group_header_name] : "");
    $level = -1;
    foreach($roles as $role) {
      $rolelvl = isset($config['auth_header_role_mapping'][$role]) ? $config['auth_header_role_mapping'][$role] : -1;
      if ($rolelvl === 0) {
        header('HTTP/1.1 401 Unauthorized');

        print_error_permission("Account disabled.<br/><br/>Please contact admin and once sorted out, you can retry login <a href=\"/oauth2/sign_in?rd=/\">here</a>.", FALSE);
        die();
      }
      $level = max($level, $rolelvl);
    }

    if ($level === -1) {
      header('HTTP/1.1 401 Unauthorized');

      print_error_permission("You have no defined permissions ($level).<br/><br/>Please contact admin and once defined, retry login: <a href=\"/oauth2/sign_in?rd=/\">here</a>.", FALSE);
      die();
    }
    session_set_var('header_role_userlevel', $level);
  }
  else {
    header('HTTP/1.1 401 Unauthorized');

    print_error_permission();
    die();
  }
}

/**
 * Check if the backend allows users to log out.
 *
 * @return bool TRUE if logout is possible, FALSE if it is not
 */
function header_auth_can_logout()
{
  return TRUE;
}

/**
 * Returns the URL to lgoout.
 *
 * @return string logout url
 */
function header_auth_logout_url()
{
  return "/oauth2/sign_out?rd=/";
}

/**
 * Check if the backend allows a specific user to change their password.
 * This is not possible using the remote backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if password change is possible, FALSE if it is not
 */
function header_auth_can_change_password($username = "")
{
  return 0;
}

/**
 * Changes a user's password.
 * This is not possible using the remote backend.
 *
 * @param string $username Username to modify the password for
 * @param string $password New password
 * @return bool TRUE if password change is successful, FALSE if it is not
 */
function header_auth_change_password($username, $newpassword)
{
  # Not supported
  return FALSE;
}

/**
 * Check if the backend allows user management at all (create/delete/modify users).
 * This is not possible using the remote backend.
 *
 * @return bool TRUE if user management is possible, FALSE if it is not
 */
function header_auth_usermanagement()
{
  return 0;
}

/**
 * Check if a user, specified by username, exists in the user backend.
 * This is not possible using the remote backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if the user exists, FALSE if they do not
 */
function header_auth_user_exists($username)
{
  return FALSE;
}

/**
 * Retrieve user auth level for specified user.
 *
 * @param string $username Username to retrieve the auth level for
 * @return int User's auth level
 */
function header_auth_user_level($username)
{
  return $_SESSION["header_role_userlevel"];
}

/**
 * Retrieve user id for specified user.
 * Returns a hash of the username.
 *
 * @param string $username Username to retrieve the ID for
 * @return int User's ID
 */
function header_auth_user_id($username)
{
  //return -1;
  return string_to_id('header\0' . $username);
}

/**
 * Deletes a user from the user database.
 * This is not possible using the remote backend.
 *
 * @param string $username Username to delete
 * @return bool TRUE if user deletion is successful, FALSE if it is not
 */
function header_deluser($username)
{
  // Not supported
  return FALSE;
}

/**
 * Retrieve list of users with all details.
 * This is not possible using the remote backend.
 *
 * @return array Rows of user data
 */
function header_auth_user_list()
{
  $userlist = array();
  return $userlist;
}
