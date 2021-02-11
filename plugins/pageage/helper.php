<?php
class helper_plugin_pageage extends DokuWiki_Plugin
{
    
/**
 * Build a navigation menu from a list
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Alex Krippner
 */
    
    public function getTrafficSignal()
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


        // the default html will produce a green traffic light
        if ($pageageInSeconds <= $monthInSeconds) {
            $msg = "The page has been recently modified on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(2, $msg);
        };

        // check if date is older than 3 months
        if ($pageageInSeconds > (3 * $monthInSeconds)) {
            $msg = "The page has not been modified for more than 3 months on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(0, $msg);
        }

        // check if date less than 3 months and older than 1 month
        if ($pageageInSeconds < (3 * $monthInSeconds) && $pageageInSeconds > $monthInSeconds) {
            $msg = "The page has not been modified for more than 1 month on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLight(1, $msg);
        }

        return $trafficLight_html;
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
