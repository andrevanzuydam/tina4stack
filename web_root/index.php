<html>
    <head>
        <title>Tina4</title>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    </head>
    
    <body style="margin: 10px">
        <div class="alert alert-danger" role="alert">
             <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
            If you see this message then Ruth is not happy with how you have setup your web server routing.            
        </div>
        <?php 
          $isNGINX = false;
          $isAPACHE = false;
          $isOTHER = false;
          if (stripos($_SERVER["SERVER_SOFTWARE"], "nginx") !== false) {
             $isNGINX = true;
          } else   
          if (stripos($_SERVER["SERVER_SOFTWARE"], "apache") !== false) {
             $isAPACHE = true;
          }
            else {
             $isOTHER = true;      
          } 
        ?>
        <?php
             
        if ($isNGINX) {
            ?>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Ruth recommends your NGINX configuration to include</h3>
                </div>
                <div class="panel-body">
<pre> 
location / {
    root   <?php echo __DIR__ ?>;
    try_files $uri $uri/ /tina4.php;
    index  tina4.php index.php index.html index.htm;
}
</pre>
                </div>
                <div class="panel-footer">If you are using the fast-cgi with PHP then make sure you kill all your PHP threads before restarting NGINX</div>
            </div>    
            <?php
        }  
          else 
        if ($isAPACHE) {
        ?>    
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Ruth recommends your Apache .htaccess file configuration to include</h3>
                </div>
                <div class="panel-body">
<pre> 
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ tina4.php [QSA,L]
</pre>
                </div>
                <div class="panel-footer">If you are using the fast-cgi with PHP then make sure you kill all your PHP threads before restarting Apache</div>
            </div>    
        <?php
        }   
          else
        if ($isOTHER) { 
        ?>    
            <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">Ruth hasn't encountered the web server you are using before</h3>
                </div>
                <div class="panel-body">
                    <p>In order for Tina4 to work correctly you will need to route all your PHP requests to the tina4.php located in your web root folder.</p>
                    <p>Consult your server documentation to see how to do redirects</p>
                    <p>Alternatively you should download the tina4stack from GitHub</p>
                </div>
                <div class="panel-footer">If you are using the fast-cgi with PHP then make sure you kill all your PHP threads before restarting the web server</div>
            </div>    
        <?php             
        }
          else {
                
        }
        ?>
    </body>    
</html>