
Place your HTML pages into the /html directory.

Place your CGIs into the /cgi-bin directory. You can place and run any CGI
under /html as well, but it should end with .cgi or .pl.

To make a subdomain sub.yourdomain.com, just create subdirectories /sub
and /sub/html, and place files related to your subdomain into /sub/html.
You can create /sub/cgi-bin and use it as CGI directory.

The subdirectory /logs (read-only) contains your raw access log.
It refreshes hourly, and rotates daily with maximum history one month.

The subdirectory /stats (read-only) contains your web statistics based on
the above log. These stats are being re-generated at least once a day.

The file /.passwd is used for Control Panel authentication and should not
be modified nor used for any other purposes.
