<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("inc/common.php");
require_once("pickercontrols.php");

$title .= " | Matchups";

$content .= "<div id='synergy-board'>";

$content .= "<input id='matchup-team-picked' type='hidden' autocomplete='off'/>"
        . "<input id='matchup-players-picked' type='hidden' autocomplete='off'/>"
        . "<input id='team-picked' type='hidden' autocomplete='off'/>"
        . "<input id='player-picked' type='hidden' autocomplete='off'/>"
        . "<input id='matchup-vs' type='hidden' autocomplete='off'/>";

$content .= "<div id='synergy-board-inner'>";

$content .= "<div id='matchup-left'>";
//-------------------------LEFT-TOP---------------------------------------------
$content .= "<div id='matchup-left-display'>";

$content .= "<img id='matchup-left-display-background' class='display-background'/>";

$content .= "<div id='matchup-left-team'>";
$content .= "<img id='matchup-left-team-logo' />";
$content .= "<span id='matchup-left-team-name' class='matchup-left-team-name'></span>";
$content .= "</div>";

$content .= "<div id='matchup-left-players'></div>";

//------------------------------------------------------------------------------
$content .= "</div>";

//-------------------------LEFT-BOTTOM------------------------------------------
$content .= "<div id='matchup-left-controls'>";
$content .= player_select('team-picked', 'player-picked', 'radio');
$content .= "</div>";
//------------------------------------------------------------------------------
$content .= "</div>";

$content .= "<div id='matchup-divider'>";
//-------------------------MID-TOP----------------------------------------------
$content .= "<div id='matchup-divider-display'>";
$content .= "<div id='matchup-divider-identifier'></div>";
$content .= "</div>";
//------------------------------------------------------------------------------
//-------------------------MID-BOTTOM-------------------------------------------
$content .= "<div id='matchup-divider-controls'>";
$content .= "<button id='matchup-toggle-button' class='btn'><span id='matchup-toggle-button-text'>Toggle matchup</span></button>";
$content .= "</div>";
//------------------------------------------------------------------------------
$content .= "</div>";

$content .= "<div id='matchup-right'>";
//-------------------------RIGHT-TOP--------------------------------------------
$content .= "<div id='matchup-right-display'>";

$content .= "<img id='matchup-right-display-background' class='display-background'/>";

$content .= "<div id='matchup-right-team'>";
$content .= "<img id='matchup-right-team-logo'/>";
$content .= "<span id='matchup-right-team-name' class='matchup-team-name'></span>";
$content .= "</div>";

$content .= "<div id='matchup-right-players'></div>";
$content .= "</div>";
//------------------------------------------------------------------------------
//-------------------------RIGHT-BOTTOM-----------------------------------------
$content .= "<div id='matchup-right-controls'>";
$content .= multi_player_select('matchup-team-picked', 'matchup-players-picked');
$content .= "</div>";
//------------------------------------------------------------------------------
$content .= "</div>";

$content .= "</div>";

$content .= "</div>";

$content .= "<div id='compute-button-wrapper'><button id='compute-button' class='btn btn-primary'>See matchup stats</button></div>";

$content .= "<div id='customcontent'></div>";

$content .= "<script src='matchups.js'></script>";
$content .= "<script>window.onload = checkfragment()</script>";

render_page();