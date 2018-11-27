# PHP-Seiten

## dns/dynip.php ##

Die Vorlage stammt aus dem Forum:

  [https://www.heise.de/forum/heise-online/News-Kommentare/Telefonie-Missbrauch-anscheinend-kein-Massenhack-von-AVMs-Fritzboxen/eigener-DynDNS-Dienst/posting-4093862/show/](https://www.heise.de/forum/heise-online/News-Kommentare/Telefonie-Missbrauch-anscheinend-kein-Massenhack-von-AVMs-Fritzboxen/eigener-DynDNS-Dienst/posting-4093862/show/)

Um das Script zu nutzen, mussten zuerst die MySQL Zugriffe geändert werden (mysql_ -> mysqli_).

In einem Kommentar wurde auf Sicherheitslücken hingewiesen:

- SQL-Injection ([http://de.wikipedia.org/wiki/SQL-Injection](http://de.wikipedia.org/wiki/SQL-Injection))

  -> `mysqli_real_escape_string` bzw. Verwendung von Parametern

- XSS ([http://de.wikipedia.org/wiki/Cross-Site-Scripting](http://de.wikipedia.org/wiki/Cross-Site-Scripting))

  -> `htmlentities`


## report-uri-expect-ct ##

Um die Fälschung von Zertifikaten zu verhindern, benötigen wir [Certificate Transparency](https://de.wikipedia.org/wiki/Certificate_Transparency "Certificate Transparency"). Ein Browser kann somit die Echtheit von Zertifikaten überprüfen. Über die report-uri können Fehler an die Web-Site gemeldet werden.


## report-uri-csp ##

[Content Security Policy (CSP)](https://de.wikipedia.org/wiki/Content_Security_Policy "Content Security Policy") - ein Verfahren zur Vorbeugung gegen XSS-Angriffe.

Verstöße gegen die von der WebSite selbst aufgestellte Policy, kann der Browser an diesen Report senden.



----------
https://www.ib-leier.net/
