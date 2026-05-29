<?php
require('config.php');

// S3 generate secure link v4
function el_s3_getTemporaryLink($accessKey, $secretKey, $bucket, $path, $expires = TIMEOUT_MINUTES) {
    $service = 's3';
    $host = S3ENDPOINT;
    $endpoint = S3PROTOCOL."$host";
    $region = S3REGION;
    $path = $bucket.'/'.$path;

    $timestamp = time();
    $amzDate = gmdate('Ymd\THis\Z', $timestamp);
    $datestamp = gmdate('Ymd', $timestamp);
    $region = S3REGION;

    $queryParameters = [
        'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
        'X-Amz-Credential'    => "$accessKey/$datestamp/$region/$service/aws4_request",
        'X-Amz-Date'          => $amzDate,
        'X-Amz-Expires'       => $expires,
        'X-Amz-SignedHeaders' => 'host',
    ];
    ksort($queryParameters);
    $queryString = http_build_query($queryParameters, '', '&', PHP_QUERY_RFC3986);

    $canonicalUri = '/' . ltrim($path, '/');
    $canonicalHeaders = "host:$host\n";
    $signedHeaders = "host";
    $payloadHash = "UNSIGNED-PAYLOAD";

    $canonicalRequest = "GET\n$canonicalUri\n$queryString\n$canonicalHeaders\n$signedHeaders\n$payloadHash";

    $scope = "$datestamp/$region/$service/aws4_request";
    $stringToSign = "AWS4-HMAC-SHA256\n$amzDate\n$scope\n" . hash('sha256', $canonicalRequest);

    $kSecret = "AWS4" . $secretKey;
    $kDate    = hash_hmac('sha256', $datestamp, $kSecret, true);
    $kRegion  = hash_hmac('sha256', $region, $kDate, true);
    $kService = hash_hmac('sha256', $service, $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    return "$endpoint$canonicalUri?$queryString&X-Amz-Signature=$signature";
}
// S3 generate secure link v4

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
