<?php

/**
 * Display a traffic light symbolizing the last time the page was modified
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Alex Krippner
 */

class action_plugin_pageage extends DokuWiki_Action_Plugin
{
    public function __construct()
    {
        $this->pageageHelper = plugin_load('helper', 'pageage');
    }

    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_CONTENT_DISPLAY', 'BEFORE', $this, 'handle_display_pageage');
    }
    
    /**
     * The function concats a traffic light symbol to the wiki content
     * when not in edit mode
     */
    public function handle_display_pageage($event, $param)
    {
        if ($_REQUEST["do"] == "edit") {
            return;
        }
        
        $trafficLight_html = $this->pageageHelper->getTrafficLight();

        $event->data .= $trafficLight_html;
    }
}
