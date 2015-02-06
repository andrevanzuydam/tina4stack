<?php
/**
 * Use this as a starting point for creating a setting up routes
 */
Ruth::addRoute(RUTH_GET, "/", 
        function () {
            echo a(["href" => "http://tina4.com"], img(["src" => "images/helloworld.png"]));
        });
