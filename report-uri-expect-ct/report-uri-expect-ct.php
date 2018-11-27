<?php
	// https://shaunc.com/blog/article/implementing-a-reporturi-endpoint-for-expectct-and-other-headers~Xdf4cU8EurV1
	
/*
 * report-uri-expect-ct.php
 * 
 * From <https://shaunc.com/go/Xdf4cU8EurV1>
 *
 * This script accepts incoming Expect-CT browser reports and emails their
 * contents to the site administrator. For more about the Expect-CT header, see:
 *
 * <https://tools.ietf.org/html/draft-ietf-httpbis-expect-ct-02>
 * <https://scotthelme.co.uk/a-new-security-header-expect-ct/>
 *
 * Point to this script in the "report-uri" parameter of your Expect-CT header.
 *
 * Some browsers send an OPTIONS request first, prior to POSTing the report,
 * to ensure the server is willing to accept a POST in the first place. This
 * is known as a "pre-flight," as defined here:
 *
 * <https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS#Preflighted_requests>
 *
 * The appropriate response will be sent depending upon the HTTP verb.
 */

define('ADMIN_EMAIL', 'you@example.com');

//Test for a CORS preflight
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    //Confirm we accept POST in addition to OPTIONS
    header('Access-Control-Allow-Methods: OPTIONS, POST');

    //Numerous domains point here, so indicate that any origin is acceptable
    header('Access-Control-Allow-Origin: *');

    //Explicitly allow whatever custom headers, if any, the requestor hinted
    foreach (getallheaders() as $key=>$val) {
        if (strcasecmp($key, 'Access-Control-Request-Headers') == 0) {
            header('Access-Control-Allow-Headers:' . htmlentities($val, ENT_QUOTES));
            break;
        }
    }
    exit;
}

//Test for a posted report
elseif ($_SERVER['REQUEST_METHOD'] == 'POST'
    && strlen($json = @file_get_contents('php://input')) > 0) {

    //Turn the JSON report into a slightly more readable array
    $report = var_export(json_decode($json, true), true);

    //Grab the headers, too
    $headers = var_export(getallheaders(), true);

    //Build a message - gethostbyaddr
    $body = <<<EOT
An Expect-CT report was posted by {$_SERVER['REMOTE_ADDR']} on {$_SERVER['SERVER_NAME']}.

Headers follow:

$headers

Report follows:

$report
EOT;

    //Send the email
    @mail(ADMIN_EMAIL,
        '[' . $_SERVER['SERVER_NAME'] . '] Expect-CT Report',
        $body, 'From: ' . ADMIN_EMAIL);
    exit;
}

?>
