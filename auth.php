<?php
require('config.php');

// S3 generate secure link
if (!function_exists('el_crypto_hmacSHA1')) {
        function el_crypto_hmacSHA1($key, $data, $blocksize = 64)
        {
                if (strlen($key) > $blocksize) $key = pack('H*', sha1($key));
                $key = str_pad($key, $blocksize, chr(0x00));
                $ipad = str_repeat(chr(0x36), $blocksize);
                $opad = str_repeat(chr(0x5c), $blocksize);
                $hmac = pack('H*', sha1(
                        ($key ^ $opad) . pack('H*', sha1(
                                ($key ^ $ipad) . $data
                        ))
                ));
                return base64_encode($hmac);
        }
}

if (!function_exists('el_s3_getTemporaryLink')) {
        function el_s3_getTemporaryLink($accessKey, $secretKey, $bucket, $path, $expires = TIMEOUT_MINUTES)
        {
                $expires = time() + intval(floatval($expires) * 60);
                $path = str_replace('%2F', '/', rawurlencode($path = ltrim($path, '/')));
                $signpath = '/' . $bucket . '/' . $path;
                $signsz = implode("\n", $pieces = array('GET', null, null, $expires, $signpath));
                $signature = el_crypto_hmacSHA1($secretKey, $signsz);
                if (S3USEPATH) $url = sprintf(S3PROTOCOL.S3ENDPOINT.'/%s/%s', $bucket, $path); else $url = sprintf(S3PROTOCOL.'%s.'.S3ENDPOINT.'/%s', $bucket, $path);
                $qs = http_build_query($pieces = array(
                        'AWSAccessKeyId' => $accessKey,
                        'Expires' => $expires,
                        'Signature' => $signature,
                ));
                return $url . '?' . $qs;
        }
}
// S3 generate secure link

if (USE_AUTH == false) return;

// timeout in seconds
$timeout = (TIMEOUT_MINUTES == 0 ? 0 : time() + TIMEOUT_MINUTES * 60);

// logout?
if(isset($_GET['logout'])) {
  setcookie("verify", '', $timeout, '/'); // clear password;
  header('Location: ' . LOGOUT_URL);
  exit();
}

if(!function_exists('showLoginPasswordProtect')) {

// show login form
function showLoginPasswordProtect($error_msg) {
?>
<html>
<head>
  <title>Please enter password to access this page</title>
  <meta http-equiv="cache-control" content="no-cache">
  <meta http-equiv="pragma" content="no-cache">
  <link rel="stylesheet" href="assets/css/styles.css" /> 
</head>
<body>
  <style>
    input { border: 3px solid #373743;margin:8px; padding: 4px;border-radius: 6px }
  </style>
  <div class='password'>
  <form method="post">
    <h3>Please enter password to access this page</h3>
    <font color="black"><?php echo $error_msg; ?></font><br />
<?php if (USE_USERNAME) echo 'Login:<br /><input type="input" name="access_login" /><br />Password:<br />'; ?>
    <input type="password" name="access_password" /><p></p><input type="submit" name="Submit" value="Submit" />
  </form>
  </div>
</body>
</html>

<?php
  // stop at this point
  die();
}
}

// user provided password
if (isset($_POST['access_password'])) {

  $login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
  $pass = $_POST['access_password'];
  if (!USE_USERNAME && !in_array($pass, $LOGIN_INFORMATION)
  || (USE_USERNAME && ( !array_key_exists($login, $LOGIN_INFORMATION) || $LOGIN_INFORMATION[$login] != $pass ) ) 
  ) {
    showLoginPasswordProtect("Incorrect password.");
  }
  else {
    // set cookie if password was validated
    setcookie("verify", sha1($login.'%'.$pass), $timeout, '/');
    
    // Some programs (like Form1 Bilder) check $_POST array to see if parameters passed
    // So need to clear password protector variables
    unset($_POST['access_login']);
    unset($_POST['access_password']);
    unset($_POST['Submit']);
  }

}
else {
  // check if password cookie is set
  if (!isset($_COOKIE['verify'])) {
    showLoginPasswordProtect("");
  }

  // check if cookie is good
  $found = false;
  foreach($LOGIN_INFORMATION as $key=>$val) {
    $lp = (USE_USERNAME ? $key : '') .'%'.$val;
    if ($_COOKIE['verify'] == sha1($lp)) {
      $found = true;
      // prolong timeout
      if (TIMEOUT_CHECK_ACTIVITY) {
        setcookie("verify", sha1($lp), $timeout, '/');
      }
      break;
    }
  }
  if (!$found) {
    showLoginPasswordProtect("");
  }
}
