<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('queries.php');
require_once("GameStatsDataManager.php");

if(isset($_GET['gameID']))
{
    $gameID = $_GET['gameID'];
    
    $gameStatsDM = new GameStatsDataManager();
    
    $game = $gameStatsDM->getGame($gameID);
    $hometeam = DataManager::getTeam($game->hometeam);
    $awayteam = DataManager::getTeam($game->awayteam);
    $awaypointsbyperiod = $gameStatsDM->getQuarterBreakdown($gameID, false);
    $homepointsbyperiod = $gameStatsDM->getQuarterBreakdown($gameID, true);
    
    echo json_encode(array($game, $awayteam, $hometeam, $awaypointsbyperiod, $homepointsbyperiod));
}