<?php
  // https://www.heise.de/forum/heise-online/News-Kommentare/Telefonie-Missbrauch-anscheinend-kein-Massenhack-von-AVMs-Fritzboxen/eigener-DynDNS-Dienst/posting-4093862/show/
	
  // Als Update-URL gibt man ein:
  // http://<meine-domain>/dynip.php?ip=<ipaddr>&client=fb1

  // Und nun das PHP dazu:

 <?php
  ini_set("display_errors",TRUE);
  error_reporting(E_ALL);
  include('sql.php');
  $connection=startSql("usr_web26_2");
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

 <!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN"
 "http://www.wapforum.org/DTD/wml_1.1.xml">
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
 if (isset($_REQUEST['ip']) and isset($_REQUEST['client'])) {
   echo "inserting ip address $_REQUEST[ip]<br>";

   $query="replace into dynip SET time=NOW(),client='$_REQUEST[client]',ipaddress='$_REQUEST[ip]'";
   $rc=mysql_query($query,$connection);
   if (!$rc) echo mysql_error();
 } else {
   //echo "'ip' and/or 'client' not set.<br>";
   $result = mysql_query("select client,ipaddress,time from dynip",$connection);
   if (!$result) {
         echo "1 Nichts gefunden!";
   } else {
       $treffer = mysql_num_rows($result);
       if ($treffer==0) {
         echo "2 Keine Eintraege gefunden!";
       } else {
         //$row = mysql_fetch_array($result, MYSQL_ASSOC);
         if ($mobile) {
           while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
             echo "$row[client] , $row[time] , $row[ipaddress] <br/>";
           } //while
         } else {
           echo '<table border="1"><tr><th>Client<th>Time<th>IP address</tr>';
           while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
             echo "<tr><td>$row[client]<td>$row[time]<td><a href=\"http://$row[ipaddress]\">$row[ipaddress]</a></tr>";
           } //while
           echo "</table>";
         }
       }
   }
   if ($result) mysql_free_result($result);
 }

 mysql_close($connection);

 if ($mobile) {
   echo '</p></card></wml>';
 } else {
   echo "</body></html>";
 }
 ?>
