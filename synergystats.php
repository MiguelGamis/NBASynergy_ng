<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("inc/common.php");
require_once("synergystatscontrols.php");
require_once("pickercontrols.php");

$title .= " | Matchups";

$content .= "<div id='synergy-board'>";

$content .= "<div style='width:12%'>";
$content .= "<img id='team-picked-logo'/>";
$content .= "<div id='team-picked-name' class='picker-team-name'></div>";
$content .= "</div>";

$content .= "<div style='width:12%'>";
$content .= "<div id='player-identifier'><img id='player-picked-photo'/><div id='player-picked-name'></div></div>";
$content .= "</div>";

$content .= "<div style='width:4%'><image id='matchup-icon'></div>";

$content .= "<div style='width:60%' id='multi-player-picker-players-identifier'></div>";

$content .= "<div style='width:12%'>";
$content .= "<img id='matchup-team-picked-logo'/>";
$content .= "<div id='matchup-team-picked-name' class='picker-team-name'></div>";
$content .= "</div>";

$content .= "<input id='matchup-team-picked' type='hidden' autocomplete='off'></input>"
        . "<input id='matchup-players-picked' type='hidden' autocomplete='off'></input>"
        . "<input id='team-picked' type='hidden' autocomplete='off'></input>"
        . "<input id='player-picked' type='hidden' autocomplete='off'></input>"
        . "<input id='matchup-vs' type='hidden' autocomplete='off'></input>";

$content .= "</div>";

$content .= "<div id='synergy-options-bar'>";
$content .= "<div style='width:24%'>";
$content .= player_select('team-picked', 'player-picked');
$content .= "</div>";
$content .= "<div style='width:4%'>";
$content .= "<button id='matchup-toggle-button' class='btn'>VS</button>";
$content .= "</div>";
$content .= "<div style='width:72%'>";
$content .= multi_player_select('matchup-team-picked', 'matchup-players-picked');
$content .= "</div>";
$content .= "</div>";

$content .= "<div id='compute-button-wrapper'><button id='compute-button' class='btn btn-primary'>See matchup stats</button></div>";

$content .= "<div id='customcontent'></div>";

$content .= "<script src='synergystats.js'></script>";
$content .= "<script>window.onload = checkfragment()</script>";

render_page();