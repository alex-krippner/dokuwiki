<?php
class helper_plugin_pageage extends DokuWiki_Plugin
{
    
/**
 * Display a traffic light symbolizing the last time the page was modified
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Alex Krippner
 */

    /** @var helper_plugin_sqlite */
    private $db = null;

    /**
     * Constructor. Initializes the SQLite DB Connection
     */
    public function __construct()
    {
        $this->db = plugin_load('helper', 'sqlite');
        if (!$this->db) {
            msg('Please install sqlite plugin to log page visits.');
            return;
        }
        if (!$this->db->init('pageage', dirname(__FILE__) . '/db/')) {
            $this->db = null;
        }
    }
    
    public function getTrafficSignal()
    {
        global $INFO;
        if ($_REQUEST["do"] == "edit") {
            return;
        }

        $lastmodUNIXTimestamp = $INFO["lastmod"];
        $monthInSeconds = 2629800;
        $lastmodDate = date("Y-m-d H:i:s", $lastmodUNIXTimestamp);
        $todayUNIXTimestamp = time();
        $pageageInSeconds = $todayUNIXTimestamp - $lastmodUNIXTimestamp;


        // save page visit in sqlite database
        $this->savePagevisit();
    
        // display last visitor username and date
        $lastVisitHTML =  $this->createLastVisitHTML();

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

        return $lastVisitHTML . $trafficLight_html;
    }

    public function savePagevisit()
    {
        if (!$this->db) {
            return;
        };
        
        global $INFO;
        global $USERINFO;

        $dateToday = date("Y-m-d H:i:s", time());

        $this->db->query(
            'INSERT INTO pages(page, date, user)
             VALUES (?, ?, ?)',
            $INFO["id"],
            $dateToday,
            $USERINFO["name"]
        );
    }

    public function createLastVisitHTML()
    {
        if (!$this->db) {
            return;
        };

        global $INFO;
        $currentPage = $INFO["id"];

        $query = "SELECT page, date, user FROM pages WHERE page = '$currentPage' ORDER BY date DESC;";

        $res = $this->db->query($query);

        $lastVisitorsLog = $this->db->res2arr($res);
        $lastVisitorDetails = $lastVisitorsLog[1];
        $lastVisitor = $lastVisitorDetails["user"];
        $lastVisitedDate = $lastVisitorDetails["date"];

        $lastVisitorHTML = "<div> The last visitor was $lastVisitor on the $lastVisitedDate </div>";
 
        return $lastVisitorHTML;
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
