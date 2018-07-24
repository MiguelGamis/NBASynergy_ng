<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("inc/common.php");
require_once("queries.php");

if(isset($_GET['gameID']))
{
    $gameID = $_GET['gameID'];
    
    $game = DataManager::getGame($gameID);
    
    $awayshifts = DataManager::getShiftsFromGameById($gameID, false);

    $homeshifts = DataManager::getShiftsFromGameById($gameID, true);
    
    $jsonpackage = array('game'=>$game, 'home'=>$homeshifts, 'away'=>$awayshifts);
    echo json_encode($jsonpackage);
    return;
}

$content .= "<select id='gameSelect'>";
$games = DataManager::getGames();
foreach($games as $gameID)
{
    $content .= "<option value='$gameID'>$gameID</option>";
}
$content .= "</select>";

$content .= "<div id='away-gantt' class='gantt-chart-container'></div>
        <div id='home-gantt' class='gantt-chart-container'></div>";

$content .= "<script src='gantt-shifts.js'></script>";

render_page();