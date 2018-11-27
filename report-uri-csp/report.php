<?php

// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-uri -> Deprecated ->
// https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/report-to

// https://websec.be/blog/cspreporting/
// https://content-security-policy.com/
// https://www.tollmanz.com/content-security-policy-report-samples/ -> https://github.com/tollmanz/report-only-capture

// report-uri endpoint example
// https://shaunc.com/blog/article/implementing-a-reporturi-endpoint-for-expectct-and-other-headers~Xdf4cU8EurV1

// https://www.phpgangsta.de/die-reporting-funktion-der-content-security-policy-csp
// https://www.w3schools.com/php/php_superglobals.asp

  ini_set("display_errors",TRUE);
  error_reporting(E_ALL);

// Start configure
$log_file = dirname(__FILE__) . '/csp-violations.log';
$log_file_size_limit = 1000000; // bytes - once exceeded no further entries are added
$email_address = 'admin@example.com';
$email_subject = 'Content-Security-Policy violation';
// End configuration

$current_domain = preg_replace('/www\./i', '', $_SERVER['SERVER_NAME']);
$email_subject = $email_subject . ' on ' . $current_domain;
$remote_name = gethostbyaddr($_SERVER['REMOTE_ADDR']);

http_response_code(204); // HTTP 204 No Content

//echo "log_file: $log_file</br>";
//echo "current_domain: $current_domain</br>";
//$rc = file_put_contents($log_file, "json_data\n", FILE_APPEND | LOCK_EX);
//echo "file_put_contents rc: $rc</br>";

//Grab the headers, too
$headers = var_export(getallheaders(), true);
echo "headers: $headers</br>";
//$request = var_export($_REQUEST, true);
//echo "request: $request</br>";
$post = var_export($_POST, true);
echo "post: $post</br>";
$get = var_export($_GET, true);
echo "get: $get</br>";
$server = var_export($_SERVER, true);
//echo "server: $server</br>";

$json_data = file_get_contents('php://input');

// We pretty print the JSON before adding it to the log file
if ($json_data = json_decode($json_data)) {
  $json_data = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

  if (!file_exists($log_file)) {
    // Send an email
    $message = "The following Content-Security-Policy violation occurred on " .
      $current_domain . " by " . $_SERVER['REMOTE_ADDR'] . "(" . $remote_name . "):\n\n" .
      $json_data . "\n\n" .
      "headers: " . $headers . "\n\n" .
      // "request: " . $request . "\n\n" .
      "post: " . $post . "\n\n" .
      "get: " . $get . "\n\n" .
      "server: " . $server . "\n\n" .
      "\n\nFurther CPS violations will be logged to the following log file, but no further email notifications will be sent until this log file is deleted:\n\n" .
      $log_file;
    mail($email_address, $email_subject, $message,
         'Content-Type: text/plain;charset=utf-8');
		 
  } else if (filesize($log_file) > $log_file_size_limit) {
    exit(0);
  }

}

//date_default_timezone_set('Europe/Berlin');
echo 'date_default_timezone_set: ' . date_default_timezone_set('Europe/Berlin') . '<br />';
//echo 'date_default_timezone_get: ' . date_default_timezone_get() . '<br />';

// Current date/time in your computer's time zone.
//$date = new DateTime();
//echo $date->format('Y-m-d H:i:sP') . "<br />\n";	// 2018-07-07 21:41:44+02:00 

$date = new DateTime("now");
//echo $date->format('Y-m-d\TH:i:s.u') . '<br />';	// 2011-01-01T15:03:01.012345 / 2018-07-07T21:46:15.000000
//echo $date->format(DateTime::ATOM) . '<br />';		// 2018-07-07T21:46:15+02:00

$info =	"\n\n" . $date->format(DateTime::ATOM) . " - " . $_SERVER['REQUEST_METHOD'] .
		"\nOn " . $current_domain . " by " . $_SERVER['REMOTE_ADDR'] . "(" . $remote_name . ")\n" .
		$_SERVER['HTTP_USER_AGENT'] . "\n";
file_put_contents($log_file, $info . $json_data, FILE_APPEND | LOCK_EX);

?>
