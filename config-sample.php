<?php
##################################################################
#  SETTINGS START
##################################################################

// Add login/password pairs below, like described above
// NOTE: all rows except last must have comma "," at the end of line
$LOGIN_INFORMATION = array(
  'admin' => 'password'
);

// request login? true - show login and password boxes, false - password box only
define('USE_USERNAME', true);

// User will be redirected to this page after logout
define('LOGOUT_URL', '');

// time out after NN minutes of inactivity. Set to 0 to not timeout
define('TIMEOUT_MINUTES', 60);

// This parameter is only useful when TIMEOUT_MINUTES is not zero
// true - timeout time from last activity, false - timeout time from login
define('TIMEOUT_CHECK_ACTIVITY', true);

// Enable web autorization
define('USE_AUTH', false);

// Define S3 endpoint and token
define('S3PROTOCOL', 'https://');
define('S3ENDPOINT', '');
define('S3BUCKET', '');
define('S3USEPATH', true);
define('ACCESSKEY', '');
define('SECRETKEY', '');
