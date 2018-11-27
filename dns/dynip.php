<?php
  // https://www.heise.de/forum/heise-online/News-Kommentare/Telefonie-Missbrauch-anscheinend-kein-Massenhack-von-AVMs-Fritzboxen/eigener-DynDNS-Dienst/posting-4093862/show/

  // Als Update-URL gibt man ein:
  // http://<meine-domain>/dynip.php?ip=<ipaddr>&client=fb1

  /*
   * 2018-06-29 - htmlentities added -> against Cross-Site-Scripting (XSS)
   * 2018-06-27 - first version with mysqli_real_escape_string -> against SQL-Injection
   */

  // Und nun das PHP dazu:

  ini_set("display_errors",TRUE);
  error_reporting(E_ALL);
  /*include('sql.php');*/
  
  $host_name = '***';
  $database = '***';
  $user_name = '***';
  $password = '***';

  $connect = mysqli_connect($host_name, $user_name, $password, $database);
  if (mysqli_connect_errno()) {
    die('<p>Verbindung zum MySQL Server fehlgeschlagen: '.mysqli_connect_error().'</p>');
  } else {
    //echo '<p>Verbindung zum MySQL Server erfolgreich aufgebaut.</p >';
  }
  
  /* $connection=startSql("usr_web26_2"); */
  $connection=$connect;
  setlocale(LC_TIME,'de_DE');

  if ( (isset($_SERVER['HTTP_ACCEPT']) and stristr($_SERVER['HTTP_ACCEPT'], "text/vnd.wap.wml"))
    or (isset($_SERVER['HTTP_ACCEPT']) and stristr($_SERVER['HTTP_ACCEPT'], "application/vnd.wap.wml+xml"))
    or (isset($_REQUEST['mobile']) and $_REQUEST['mobile']=='true')
    or stristr($_SERVER['HTTP_USER_AGENT'],"MIDP-")
     ) {
   $mobile=TRUE;
    header ("Content-Type: text/vnd.wap.wml;charset=iso-8859-1");
    echo '<?xml version="1.0" encoding="iso-8859-1"?>';
    /* die leerzeile nach dem php-ende muss sein! (weil vorher das \n fehlt?) */
?>

<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">
 <wml><card id="card1" title="DynIp">
<p>
 <?php
 } else {
   $mobile=FALSE;
   header ("Content-Type: text/html; charset=ISO-8859-1");
 ?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">
 <html>
<head>
   <title>IP Update</title>
   <meta name="robots" content="noindex,nofollow">
</head>
<body>
<?php
} //end wml/html

/* only for mysql_query
 $query="USE $database;";
 $rc=mysqli_query($connection,$query);
 echo "rc1: $rc</br>";
*/

 if (isset($_REQUEST['ip']) and isset($_REQUEST['client'])) {
   echo "deleting</br>";
   $query="DELETE FROM `dynip` WHERE time < SUBDATE(NOW(), INTERVAL 7 DAY)";
   $rc=mysqli_query($connection,$query);
   echo "rc1: $rc</br>";

   echo "inserting ip address $_REQUEST[ip]</br>";

   // escape variables for security
   $client = mysqli_real_escape_string($connection, $_REQUEST['client']);
   $ip = mysqli_real_escape_string($connection, $_REQUEST['ip']);

   $query="INSERT INTO `dynip`(`time`, `client`, `ipaddress`) VALUES (now(),'$client','$ip')";
   //$query="UPDATE 'dynip' SET 'time'=NOW(),'client'='$_REQUEST[client]','ipaddress'='$_REQUEST[ip]'";
   $queryStr = htmlentities($query, ENT_QUOTES);
   echo "query: $queryStr</br>";
   // echo "connection: $connection</br>";
   
   $rc=mysqli_query($connection,$query);
   echo "rc2: $rc</br>";

   if (!$rc) {
	   echo "mysqli_error: ";
	   echo mysqli_error();
	}
 } else {
   echo "'ip' and/or 'client' not set.<br>";
   $result = mysqli_query($connection,"SELECT client,ipaddress,time FROM dynip ORDER BY time desc");
   if (!$result) {
         echo "1 Nichts gefunden!";
   } else {
       $treffer = mysqli_num_rows($result);
       if ($treffer==0) {
         echo "2 Keine Eintraege gefunden!";
       } else {
         //$row = mysqli_fetch_array($result, mysqli_ASSOC);
         if ($mobile) {
           while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
             echo "$row[client] , $row[time] , $row[ipaddress] <br/>";
           } //while
         } else {
           echo '<table border="1"><tr><th>Client<th>Time<th>IP address</tr>';
           while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			   $clientStr = htmlentities("$row[client]", ENT_QUOTES);
			   $timeStr = htmlentities("$row[time]", ENT_QUOTES);
			   $ipaddressStr = htmlentities("$row[ipaddress]", ENT_QUOTES);
             echo "<tr><td>$clientStr<td>$timeStr<td><a href=\"http://$ipaddressStr\">$ipaddressStr</a></tr>";
           } //while
           echo "</table>";
         }
       }
  }
  if ($result) mysqli_free_result($result);
}

 mysqli_close($connection);

if ($mobile) {
   echo '</p></card></wml>';
 } else {
  echo "</body></html>";
 }
?>
