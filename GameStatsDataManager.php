<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('config.php');
require_once("classes.php");

class GameStatsDataManager
{
    private $db;
    
    function __construct() {
        global $db;
        $this->db = $db;
    }
    
    function getGame($gameID)
    {
        $query = "SELECT * FROM game WHERE gameID = ?";
        $sh = $this->db->prepare($query);
        $sh->execute(array($gameID));
        $row = $sh->fetch();
        $game = new stdClass();
        $game->gameID = $row['gameID'];
        $game->date = $row['date'];
        $game->hometeam = $row['hometeam'];
        $game->awayteam = $row['awayteam'];
        $game->homescore = $row['homescore'];
        $game->awayscore = $row['awayscore'];
        $game->finalperiod = $row['finalperiod'];
        return $game;
    }
    
    function getQuarterBreakdown($gameID, $home)
    {
        $query = "SELECT * FROM shot WHERE made = 1 AND gameID = ? AND home = ? ORDER BY time";
        $sh = $this->db->prepare($query);
        $sh->execute(array($gameID, $home));
        $pointsbyperiod = [];
        while($row = $sh->fetch())
        {
            $time = $row['time'];
            $type = $row['type'];
            $period = 0;
            if($time <= 2880000)
            {
                $period = intval(ceil($time/720000));
            }
            else
            {
                $time -= 2880000;
                $period = intval(ceil($time/300000)) + 4;
            }
            if(!array_key_exists($period-1, $pointsbyperiod))
            {
                $pointsbyperiod[$period-1] = 0;
            }
            $pointsbyperiod[$period-1] += self::getPointValue($type);
        }
        return $pointsbyperiod;
    }
    
    static function getPointValue($type)
    {
        if($type == "Free Throw"){
            return 1;
        }
        else if($type[0] == 3){
            return 3;
        }
        return 2;
    }
    
    function getPlayerTimeFromGame($playerID, $gameID)
    {
        $query = "SELECT * FROM shift WHERE playerID = ? AND gameID = ?";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $totalms = 0;
        while($row = $sh->fetch())
        {
            $starttime = intval($row['starttime']);
            $endtime = intval($row['endtime']);
            $totalms += $endtime - $starttime;
        }
        return $totalms;
    }
    
    function getPlayerShotsFromGame($playerID, $gameID)
    {
        $query = "SELECT * FROM shot WHERE playerID = ? AND gameID = ?";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $shots = [];
        while($row = $sh->fetch())
        {
            $shot = new stdClass();
            $shot->type = $row['type'];
            $shot->made = intval($row['made']);
            $shots[] = $shot;
        }
        return $shots;
    }
    
    function getPlayerAssistsFromGame($playerID, $gameID)
    {
        $query = "SELECT count(*) as total FROM (SELECT shotID FROM assist WHERE playerID = ?) playerassists JOIN (SELECT shotID FROM shot WHERE gameID = ?) gameshots ON (gameshots.shotID = playerassists.shotID)";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        return intval($row['total']);
    }
    
    function getPlayerOffReboundsFromGame($playerID, $gameID)
    {
        $offrebquery = "SELECT count(*) as total FROM rebound WHERE offensive = 1 AND playerID = ? AND gameID = ?";
        $sh = $this->db->prepare($offrebquery);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        $offrebs = $row['total'];
        
        return intval($offrebs);
    }
    
    function getPlayerDefReboundsFromGame($playerID, $gameID)
    {
        $defrebquery = "SELECT count(*) as total FROM rebound WHERE offensive = 0 AND playerID = ? AND gameID = ?";
        $sh = $this->db->prepare($defrebquery);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        $defrebs = $row['total'];
        
        return intval($defrebs);
    }
    
    function getPlayerBlocksFromGame($playerID, $gameID)
    {
        $query = "SELECT count(*) as total FROM (SELECT shotID FROM block WHERE playerID = ?) playerblocks JOIN (SELECT shotID FROM shot WHERE gameID = ?) gameshots ON (gameshots.shotID = playerblocks.shotID)";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        $blocks = $row['total'];
        
        return intval($blocks);
    }
    
    function  getPlayerStealsFromGame($playerID, $gameID)
    {
        $query = "SELECT count(*) as total FROM (SELECT turnoverID FROM steal WHERE playerID = ?) playersteals JOIN (SELECT turnoverID FROM turnover WHERE gameID = ?) gameturnovers ON (gameturnovers.turnoverID = playersteals.turnoverID)";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        $steals = $row['total'];
        
        return intval($steals);
    }
    
    function  getPlayerTurnoversFromGame($playerID, $gameID)
    {
        $query = "SELECT count(*) as total FROM turnover WHERE playerID = ? AND gameID = ?";
        $sh = $this->db->prepare($query);
        $sh->execute(array($playerID, $gameID));
        $row = $sh->fetch();
        $turnovers = $row['total'];
        
        return intval($turnovers);
    }
}