<?php
class helper_plugin_pageage extends DokuWiki_Plugin
{
    
/**
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
    
    public function getLastVisitAndTrafficLight()
    {
        if ($_REQUEST["do"] == "edit") {
            return;
        }

        // save page visit in sqlite database
        $this->savePagevisit();
            
        // display last visitor username and date
        $lastVisitHTML =  $this->createLastVisitHTML();

        // get traffic light HTML
        $trafficLight_html = $this->getTrafficLight();

        return $lastVisitHTML . $trafficLight_html;
    }

    public function getTrafficLight()
    {
        global $INFO;

        $lastmodUNIXTimestamp = $INFO["lastmod"];
        $monthInSeconds = 2629800;
        $lastmodDate = date("Y-m-d H:i:s", $lastmodUNIXTimestamp);
        $todayUNIXTimestamp = time();
        $pageageInSeconds = $todayUNIXTimestamp - $lastmodUNIXTimestamp;

        // create a red traffic light if the page was last modified more than 3 months ago
        if ($pageageInSeconds > (3 * $monthInSeconds)) {
            $msg = "The page has not been modified for more than 3 months on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLightHTML(0, $msg);
        }

        // create a orange traffic light if the page was last modified between 1 and 3 months
        if ($pageageInSeconds < (3 * $monthInSeconds) && $pageageInSeconds > $monthInSeconds) {
            $msg = "The page has not been modified for more than 1 month on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLightHTML(1, $msg);
        }

        // create a green traffic light if the page was last modified less than a month
        if ($pageageInSeconds <= $monthInSeconds) {
            $msg = "The page has been recently modified on the $lastmodDate";
            $trafficLight_html = $this->createTrafficLightHTML(2, $msg);
        };

        return $trafficLight_html;
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

        /**
         * A page visit is logged before the lastVisitHTML is created, so the first array element
         * holds the page visit that just occured and the
         * second array element holds the last visitor
         */

        $lastVisitorDetails = $lastVisitorsLog[1];
        $lastVisitor = $lastVisitorDetails["user"];
        $lastVisitedDate = $lastVisitorDetails["date"];

        $lastVisitorHTML = "<div> The last visitor was $lastVisitor on the $lastVisitedDate </div>";
 
        return $lastVisitorHTML;
    }

    /**
     * Creates a HTML and CSS traffic light symbol
     *
     * @param int $position Index position of the color in the $colorArray
     * @param string $msg Message displayed to the user
     * @return string
     */


    public function createTrafficLightHTML($position, $msg)
    {
        $colorArray = array('red', 'orange', 'green');
        $divArray = array();

        foreach ($colorArray as $idx=>$color) {
            if ($idx == $position) {
                $divArray[] = "<div class='traffic-light__lamp traffic-light__lamp--$color'></div> ";
            } elseif ($idx !== $position) {
                $divArray[] = "<div class='traffic-light__lamp traffic-light__lamp--black'></div>";
            }
        };

        $divArray = implode("", $divArray);

        return "
        <div class='traffic-light'>
            <div class='traffic-light__box'>
                $divArray 
            </div>
            <span class='traffic-light__message'>$msg</span>
        </div>";
    }
}
