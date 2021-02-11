<?php

class action_plugin_pageage extends DokuWiki_Action_Plugin
{
    public function register(Doku_Event_Handler $controller)
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
    public function handle_display_pageage($event, $param)
    {
        global $INFO;
        
        if ($_REQUEST["do"] == "edit") {
            return;
        }

     
        $lastmodUNIXTimestamp = $INFO["lastmod"];
        $monthInSeconds = 2629800;
        $lastmodDate = date("Y-m-d", $lastmodUNIXTimestamp);
        $todayUNIXTimestamp = time();
        $pageageInSeconds = $todayUNIXTimestamp - $lastmodUNIXTimestamp;
 
        // create a green traffic light if the page was last modified less than a month
        if ($pageageInSeconds <= $monthInSeconds) {
            $msg = "The page has been recently modified on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(2, $msg);
        };

        // create a red traffic light if the page was last modified more than 3 months ago
        if ($pageageInSeconds > (3 * $monthInSeconds)) {
            $msg = "The page has not been modified for more than 3 months on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(0, $msg);
        }

        // create a orange traffic light if the page was last modified between 1 and 3 months
        if ($pageageInSeconds < (3 * $monthInSeconds) && $pageageInSeconds > $monthInSeconds) {
            $msg = "The page has not been modified for more than 1 month on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(1, $msg);
        }
        

        $event->data .= $trafficLight_html;
    }

    private function createTrafficLight($position, $msg)
    {
        $colorArray = array('red', 'orange', 'green');

        foreach ($colorArray as $idx=>$color) {
            if ($idx == $position) {
                $divArray[] = "<div class='traffic-light__lamp traffic-light__lamp--$color'></div> ";
            } elseif ($idx !== $position) {
                $divArray[] = "<div class='traffic-light__lamp traffic-light__lamp--black'></div>";
            }
        };

        $divArray = implode("", $divArray);

        $finalString =  "
        <div class='traffic-light'>
            <div class='traffic-light__box'>
                $divArray 
            </div>
            <span class='traffic-light__message'>$msg</span>
        </div>";

        return $finalString;
    }
}
