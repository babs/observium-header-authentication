# Observium header based auth and userlevel definition


Inpired by `remote.inc.php`, this authentication module uses http headers to authenticate and define proper userlevel.

## Installation

### Obervium

* Copy `observium/html/includes/authentication/header.inc.php` in your Observium's `html/includes/authentication/` folder
* Edit your `config.php` to change auth mecanism
    ```php
    $config['auth_mechanism'] = "header";
    ```
* Still in `config.php`, define your role/userlevel mapping
   ```php
    $config['auth_header_role_mapping'] = array(
      "role-0" => 0,
      "role-1" => 1,
      "role-5" => 5,
      "role-10" => 10,
    );
    ```

### Nginx
* Deploy the `nginx/snippets/oauth2-protected.conf` into your nginx snippets folder
* Adapt your server definition as show in `nginx/sites-available/observium`
* Reload or restart nginx

### Oauth2-proxy & oidc provider

* Define and assign roles according to what you set in observium's `config.php`
* Create the required mapper, ex for keycloak:
  * _User Property_ `username` to `preferred_username` in ID token
  * _User Client Role_ as multivalued string named `groups` also in ID token
* Configure your `oauth2-proxy` instance as usual with the following specificities:
  * OAUTH2_PROXY_SET_XAUTHREQUEST=true
  * OAUTH2_PROXY_PASS_USER_HEADERS=true
  * OAUTH2_PROXY_COOKIE_SAMESITE=lax
  * OAUTH2_PROXY_COOKIE_CSRF_PER_REQUEST=true
  * OAUTH2_PROXY_COOKIE_CSRF_EXPIRE=5m
  * OAUTH2_PROXY_COOKIE_REFRESH=5m
