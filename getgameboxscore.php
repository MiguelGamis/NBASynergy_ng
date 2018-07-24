<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("queries.php");
require_once("GameStatsDataManager.php");

if(isset($_GET['gameID']))
{
    $gameID = $_GET['gameID'];
    
    $gameStatsDM = new GameStatsDataManager();
    
    $gamedetails = $gameStatsDM->getGame($gameID);
    $awayboxscore = getBoxscore($gameID, false);
    $homeboxscore = getBoxscore($gameID, true);
    
    echo json_encode(array($gamedetails, $awayboxscore, $homeboxscore));
}

function getBoxscore($gameID, $home)
{
    global $gameStatsDM;
    
    $boxscore = [];
    
    $players = DataManager::getPlayersFromGame($gameID, $home);
    
    foreach($players as $player)
    {
        $playerstats = new stdClass();
        $playerstats->player = $player;
        
        $totalms = $gameStatsDM->getPlayerTimeFromGame($player->playerID, $gameID);
        $playerstats->totalms = $totalms;
        
        $shots = $gameStatsDM->getPlayerShotsFromGame($player->playerID, $gameID);
        $shotStats = getShotStats($shots);
        $playerstats->PTS = $shotStats['points'];
        $playerstats->FGM = $shotStats['shotsmade'];
        $playerstats->FGA = $shotStats['totalshots'];
        $playerstats->FTM = $shotStats['freethrowsmade'];
        $playerstats->FTA = $shotStats['totalfreethrows'];
        $playerstats->_3FGM = $shotStats['3pointersmade'];
        $playerstats->_3FGA = $shotStats['total3pointers'];
        
        $playerstats->AST = $gameStatsDM->getPlayerAssistsFromGame($player->playerID, $gameID);
        
        $playerstats->OREB = $gameStatsDM->getPlayerOffReboundsFromGame($player->playerID, $gameID);
        
        $playerstats->DREB = $gameStatsDM->getPlayerDefReboundsFromGame($player->playerID, $gameID);
        
        $playerstats->BLK = $gameStatsDM->getPlayerBlocksFromGame($player->playerID, $gameID);
        
        $playerstats->STL = $gameStatsDM->getPlayerStealsFromGame($player->playerID, $gameID);
        
        $playerstats->TO = $gameStatsDM->getPlayerTurnoversFromGame($player->playerID, $gameID);
        
        $boxscore[] = $playerstats;
    }
    return $boxscore;
}

function getShotStats($shots){
    $stats = ["points" => 0, "shotsmade" => 0, "totalshots" => 0, "freethrowsmade" => 0, "totalfreethrows" => 0, "3pointersmade" => 0, "total3pointers" => 0];
    foreach($shots as $shot)
    {
        if($shot->type == "Free Throw")
        {
            $stats['totalfreethrows']++;
            if($shot->made)
            {
                $stats['freethrowsmade']++;
                $stats['points'] += 1;
            }
        }
        else if($shot->type[0] == 3){
            $stats['total3pointers']++;
            $stats['totalshots']++;
            if($shot->made)
            {
                $stats['shotsmade']++;
                $stats['3pointersmade']++;
                $stats['points'] += 3;
            }
        }
        else
        {
            $stats['totalshots']++;
            if($shot->made)
            {
                $stats['shotsmade']++;
                $stats['points'] += 2;
            }
        }
    }
    return $stats;
}