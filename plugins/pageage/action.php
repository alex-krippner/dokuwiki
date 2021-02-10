<?php

class action_plugin_pageage extends DokuWiki_Action_Plugin
{
    function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_display_pageage'); 

     }

    /**
     * The function calculates the age of the page since last modified
     * and returns a traffic light signal accordingly.
     * Green: Less than 1 month
     * Orange: Greater than 1 month and less than 3 months
     * Red: Greater than 3 months
     */
    function handle_display_pageage($event, $param) { 
        global $INFO;
        
        if($_REQUEST["do"] == "edit") return;

     
        $lastmodUNIXTimestamp = $INFO["lastmod"];
        $monthInSeconds = 2629800;
        $lastmodDate = date("Y-m-d", $lastmodUNIXTimestamp);
        $todayUNIXTimestamp = time();
        $pageageInSeconds = $todayUNIXTimestamp - $lastmodUNIXTimestamp;
        $ageColor = 'green';
        $msg = "The page has been recently modified on the $lastmodDate";

        // the default html will produce a green traffic light
        $trafficLight_html = "
        <div class='traffic-light'>
            <div class='traffic-light__box'>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
                <div class='traffic-light__lamp traffic-light__lamp--$ageColor'></div>
            </div>
            <span class='traffic-light__message'>$msg</span>
        </div>
        ";


        // check if date is older than 3 months
        if ($pageageInSeconds > (3 * $monthInSeconds)) {
            $ageColor = 'red';
            $msg = "The page has not been modified for more than 3 months on the $lastmodDate";

            $trafficLight_html = "
            <div class='traffic-light'>
            <div class='traffic-light__box'>
                <div class='traffic-light__lamp traffic-light__lamp--$ageColor'></div>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
            </div>
            <span class='traffic-light__message'>$msg</span>
        </div>
        ";
        } 

        // check if date less than 3 months and older than 1 month
        if ($pageageInSeconds < (3 * $monthInSeconds) && $pageageInSeconds > $monthInSeconds) {
            $ageColor = 'orange';
            $msg = "The page has not been modified for more than 1 month on the $lastmodDate";

            $trafficLight_html = "
            <div class='traffic-light'>
            <div class='traffic-light__box'>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
                <div class='traffic-light__lamp traffic-light__lamp--$ageColor'></div>
                <div class='traffic-light__lamp traffic-light__lamp--black'></div>
            </div>
            <span class='traffic-light__message'>$msg</span>
        </div>
        ";
    
        }

        

        $event->data .= $trafficLight_html; 
    }
}