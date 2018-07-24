<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("inc/common.php");
require_once("pickercontrols.php");

$title .= " | Trends";

$content .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js'></script>";

$valueplayerID = '';
if(isset($_GET['playerID']))
{
    $playerID = $_GET['playerID'];
    $valueplayerID = "value='$playerID'";
}

$content = "<div id='trends-player-search'>";
$content .= "<div>";
$content .= player_select_one_column('team-picked', 'player-picked');
$content .= "</div>";
$content .= "<div>";
$content .= "<form id='player-search-form' action='trends.php' method='get'>";
$content .= "<input type='hidden' id='player-search-playerID' name='playerID' autocomplete='off' $valueplayerID/>";
$content .= show_player_search('player-search');
$content .= "</form>";
$content .= "</div>";
$content .= "</div>";

$content .= "<div id='trends-player-display'>";
$content .= "<img id='trends-player-display-background'/>";
$content .= "<div id='trends-player-profile'><img id='player-picked-photo'/><div id='player-picked-name'></div></div>";
$content .= "<div id='trends-overall-statistics'></div>";
$content .= "</div>";
$content .= "<div id='trends-graph'><canvas id='trends-canvas'></canvas></div>";
$content .= "<div id='player-trending-statistics'><div>";

$content .= "<script src='trends/trends.js'></script>";
$content .= "<script src='teamplayerpicker.js'></script>";
$content .= "<script>playerpicker1colsetup('team-picked', 'player-picked');</script>";
$content .= "<script src='trends/playersearch.js'></script>";
$content .= "<script>playersearchconnect('player-search', 'player-picked-input')</script>";

render_page();