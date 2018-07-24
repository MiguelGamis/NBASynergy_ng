<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('queries.php');

$minimumlineuptime = 300000;

if(isset($_GET['team']))
{
    $team = $_GET['team'];
    
    $games = DataManager::getGamesByTeam($team);

    $lineups = [];
    
    $starttime = microtime(true);
    
    foreach($games as $game)
    {        
        $shifts = DataManager::getShiftsFromGameById3($game->gameID, $game->home);
        
        $endpoints = [];
        $lineup_starttime = 0;
        $i = 0;
        
        $length = sizeof($shifts);
        
        $testsumtotalms = 0;
        while($i < $length){
            $j = $i + 1;
            
            //See if other shifts also end here
            while($j < $length && $shifts[$i]->endtime == $shifts[$j]->endtime){
                $j++;
            }
            
            $currentfive = array_slice($shifts, $i, 5);
            if(sizeof($currentfive) < 5)
            {
                //echo "Error: ".$shifts[$i]->endtime." in game ".$game->gameID."<br/>";
            }
            $currentplayers = array_map(function($shift){return $shift->playerID;}, $currentfive);
            sort($currentplayers);
            $key = implode(',', $currentplayers);
            if(array_key_exists($shifts[$i]->endtime, $endpoints))
            {
                echo "Error: end time should not be here";
                exit();
            }
            $endpoints[$shifts[$i]->endtime] = $key;
            
            if(!array_key_exists($key, $lineups))
            {
                $lineups[$key] = new lineupstats();
            }
            //Get total ms spent by current lineup
            $totaltime = $shifts[$i]->endtime - $lineup_starttime;
            $lineups[$key]->totalms += $totaltime;
            $testsumtotalms += $totaltime;
            
            $lineup_starttime = $shifts[$i]->endtime;
            
            $i = $j;
        }
        
        $expectedtotalms = 2880000;
        if($game->finalperiod > 4) { $expectedtotalms += ($game->finalperiod - 4)*300000; }
        if($testsumtotalms != $expectedtotalms)
        {
            echo "Error in game $game->gameID: expected totalms $expectedtotalms does not match with totalms calculated $testsumtotalms";
            exit();
        }
        
        $numendpoints = sizeof($endpoints);
        if(sizeof($numendpoints) == 0){
            echo "Error there are no end points";
            exit();
        }

        $testsumperiods = 0;
        $testsumpointsfor = 0;
        $testsumpointsagainst = 0;
        
//        $shots = DataManager::getShotsFromGame($game->gameID);
//        $currentlineup = current($endpoints);
//        foreach($shots as $shot)
//        {
//            if($shot->made)
//            {
//                //look for the lineup
//                $i = 0;
//                while($currentlineup)
//                {
//                    if(key($endpoints) >= $shot->time){
//                        if($shot->home == $game->home){
//                            $pointsfor = pointValue($shot);
//                            $lineups[$currentlineup]->pointsfor += $pointsfor;
//                            $testsumpointsfor += $pointsfor;
//                        }else{
//                            $pointsagainst = pointValue($shot);
//                            $lineups[$currentlineup]->pointsagainst += $pointsagainst;
//                            $testsumpointsagainst += $pointsagainst;
//                        }
//                        break;
//                    }
//                    $currentlineup = next($endpoints);
//                    //if(!$currentlineup) echo "DOESN'T WORK"; exit();
//                }
//            }
//        }
        
        $shots = DataManager::getShotsFromGame($game->gameID);
        foreach($shots as $shot)
        {
            //look for the lineup
            foreach($endpoints as $endpointtime => $endpointplayers)
            {
                if($endpointtime >= $shot->time){
                    if($shot->made){
                        if($shot->home == $game->home){
                            $pointsfor = pointValue($shot);
                            $lineups[$endpointplayers]->pointsfor += $pointsfor;
                            $testsumpointsfor += $pointsfor;
                        }else{
                            $pointsagainst = pointValue($shot);
                            $lineups[$endpointplayers]->pointsagainst += $pointsagainst;
                            $testsumpointsagainst += $pointsagainst;
                        }
                    }
                    if($shot->home == $game->home){
                        $lineups[$endpointplayers]->FGAfor++;
                        if($shot->type == 'Free Throw'){
                            
                        }
                    }
                    else{
                        $lineups[$endpointplayers]->FGAagainst++;
                    }
                    break;
                }
            }   
        }
        
        $offrebs = DataManager::getOffensiveReboundsFromGame($game->gameID);
        foreach($offrebs as $offreb)
        {
            //look for the lineup
            foreach($endpoints as $endpointtime => $endpointplayers)
            {
                if($endpointtime >= $offreb->time){
                    if($offreb->home == $game->home){
                        $lineups[$endpointplayers]->offreboundsfor++;
                    }else{
                        $lineups[$endpointplayers]->offreboundsagainst++;
                    }
                    break;
                }
            }
        }
        
        $defrebs = DataManager::getDefensiveReboundsFromGame($game->gameID);
        foreach($defrebs as $defreb)
        {
            //look for the lineup
            foreach($endpoints as $endpointtime => $endpointplayers)
            {
                if($endpointtime >= $defreb->time){
                    if($defreb->home == $game->home){
                        $lineups[$endpointplayers]->defreboundsfor++;
                    }else{
                        $lineups[$endpointplayers]->defreboundsagainst++;
                    }
                    break;
                }
            }
        }
        
        $turnovers = DataManager::getTurnoversFromGame($game->gameID);
        foreach($turnovers as $turnover)
        {
            //look for the lineup
            foreach($endpoints as $endpointtime => $endpointplayers)
            {
                if($endpointtime >= $defreb->time){
                    if($defreb->home == $game->home){
                        $lineups[$endpointplayers]->turnoversfor++;
                    }else{
                        $lineups[$endpointplayers]->turnoversagainst++;
                    }
                    break;
                }
            }
        }
    }
    
    //Offensive Rating
    //100 x Pts / 0.5 * ((Tm FGA + 0.4 * Tm FTA - 1.07 * (Tm ORB / (Tm ORB + Opp DRB)) * (Tm FGA - Tm FG) + Tm TOV) + (Opp FGA + 0.4 * Opp FTA - 1.07 * (Opp ORB / (Opp ORB + Tm DRB)) * (Opp FGA - Opp FG) + Opp TOV))
    
    //Offensive Efficiency Rating
    //Points x (100/(Field Goals Attempted - Off Rebounds + Turnovers + (Free Throws Attempted * 0.44))))
    
    //$endtime = microtime(true);
    //echo "Execution time ".($endtime-$starttime);
    
    $expectedpointfor = ($game->home ? $game->homescore : $game->awayscore);
    if($testsumpointsfor != $expectedpointfor)
    {
        echo "Error in game $game->gameID: expected pointsfor $expectedpointfor does not match with pointsfor calculated $testsumpointsfor";
        exit();
    }
    $expectedpointagainst = ($game->home ? $game->awayscore : $game->homescore);
    if($testsumpointsagainst != $expectedpointagainst)
    {
        echo "Error in game $game->gameID: expected pointsagainst $expectedpointagainst does not match with pointsagainst calculated $testsumpointsagainst";
        exit();
    }
    
    $lineups_ = [];
    foreach($lineups as $lineupkey => $lineupstat){
        
        if($lineupstat->totalms < $minimumlineuptime){
            continue;
        }
        
        $lineupplayerIDs = explode(',', $lineupkey);
        $lineupplayers = array_map(function($playerID){ $player = DataManager::getPlayer($playerID); return $player->firstname.' '.$player->lastname;}, $lineupplayerIDs);
        $lineupstat->lineup = implode(',', $lineupplayers);
        
        $attemptfactor = $lineupstat->FGAfor - $lineupstat->offreboundsfor + $lineupstat->turnoversfor + ($lineupstat->FTAfor * 0.44);
        $lineupstat->offensiveefficiency = $attemptfactor == 0 ? "--" : number_format($lineupstat->pointsfor * (100/$attemptfactor), 1);
        
//        $lineupstat->offensiverating = 100 * ()
        
        $lineups_[] = $lineupstat;
    }
    
    echo json_encode($lineups_);
}

class lineupstats{
    public $lineup;
    public $totalms = 0;
    
    public $pointsfor = 0;
    public $pointsagainst = 0;
    public $shotsfor;
    public $shotsagainst;
    public $FGAfor = 0;
    public $FGAagainst = 0;
    public $FTAfor = 0;
    
    public $defreboundsfor = 0;
    public $defreboundsagainst = 0;
    public $offreboundsfor = 0;
    public $offreboundsagainst = 0;
    
    public $turnoversfor = 0;
    public $turnoversagainst = 0;
    
    public $offensiverating;
    public $offensiveefficiency;
    public $defensiverating;
    
    public function __construct(){
        $this->shotsfor = [];
        $this->shotsagainst = [];
    }
}

function pointValue($shot)
{
    if($shot->type == 'Free Throw'){
        return 1;
    }else if($shot->type[0] == 3){
        return 3;
    }
    return 2;
}