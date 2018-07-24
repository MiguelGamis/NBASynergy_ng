<?php
require_once("queries.php");
require_once("classes.php");
require_once("basic.php");

if(isset($_GET['playerID']))
{   
    $playerID = intval($_GET['playerID']);

    $shots = DataManager::getShots($playerID);
    $assists = DataManager::getAssists($playerID);
    $rebounds = DataManager::getRebounds($playerID);
    $blocks = DataManager::getBlocks($playerID);
    $steals = DataManager::getSteals($playerID);
    $turnovers = DataManager::getTurnovers($playerID);
    $games = DataManager::getGamesPlayedbyPlayer($playerID);
    $shifts = DataManager::getShiftsFromPlayer($playerID);
    
    $shotLocations = DataManager::getShotLocations($playerID);
    
    $matchupplayerIDs = [];
    for($i = 1; $i <= 5; $i++)
    {
        if(isset($_GET["matchup$i"]))
        {
            $matchupplayerIDs[] = $_GET["matchup$i"];
        }
    }
    
    if(sizeof($matchupplayerIDs))
    {
        $vs = boolval($_GET["vs"]);
        
        $matchupshifts = [];
        $matchupshifts[$playerID] = $shifts;
        foreach($matchupplayerIDs as $matchupplayerID)
        {
            $matchupshifts[$matchupplayerID] = DataManager::getMatchedShiftsFromPlayer($matchupplayerID, $playerID, $vs);
        }
        
        $commonintervals = commonintervals($matchupshifts, $vs);
        
        #SIFT SHOTS STATS
        $shotsplits = siftstats($shots, $commonintervals);
        $shotswithout = $shotsplits["without"];
        $shotswith = $shotsplits["with"];
        $shotstatswithout = getShotStats($shotswithout);
        $shotstatswith = getShotStats($shotswith);
        
        $totalshotswithout = $shotstatswithout['totalshots'];
        $shotsmadewithout = $shotstatswithout['shotsmade'];
        $freethrowsmadewithout = $shotstatswithout['freethrowsmade'];
        $totalfreethrowswithout = $shotstatswithout['totalfreethrows'];
        $_3pointersmadewithout = $shotstatswithout['3pointersmade'];
        $total3pointerswithout = $shotstatswithout['total3pointers'];
        
        $FGwithout = $totalshotswithout == 0 ? 0 : number_format($shotsmadewithout/$totalshotswithout, 3);
        $_3FGwithout = $total3pointerswithout == 0 ? 0: number_format($_3pointersmadewithout/$total3pointerswithout, 3);
        $FTPwithout = $totalfreethrowswithout == 0 ? 0: number_format($freethrowsmadewithout/$totalfreethrowswithout, 3);
        
        $totalshotswith = $shotstatswith['totalshots'];
        $shotsmadewith = $shotstatswith['shotsmade'];
        $freethrowsmadewith = $shotstatswith['freethrowsmade'];
        $totalfreethrowswith = $shotstatswith['totalfreethrows'];
        $_3pointersmadewith = $shotstatswith['3pointersmade'];
        $total3pointerswith = $shotstatswith['total3pointers'];
        
        $FGwith = $totalshotswith == 0 ? 0 : number_format($shotsmadewith/$totalshotswith, 3);
        $_3FGwith = $total3pointerswith == 0 ? 0: number_format($_3pointersmadewith/$total3pointerswith, 3);
        $FTPwith = $totalfreethrowswith == 0 ? 0: number_format($freethrowsmadewith/$totalfreethrowswith, 3);
        
        $shotlocationsplits = siftstats($shotLocations, $commonintervals);
        $shotlocationswith = $shotlocationsplits['with'];
        $shotlocationswithout = $shotlocationsplits['without'];
        
        #SIFT REBOUNDS STATS
        $reboundsplits = siftstats($rebounds, $commonintervals);
        $reboundswithout = $reboundsplits["without"];
        $reboundswith = $reboundsplits["with"];
        $offreboundswithout = 0;
        $defreboundswithout = 0;
        foreach($reboundswithout as $rebound)
        {
            if($rebound->offensive)
                $offreboundswithout++;
            else
                $defreboundswithout++;
        }
        $totalreboundswithout = $offreboundswithout + $defreboundswithout;
    
        $offreboundswith = 0;
        $defreboundswith = 0;
        foreach($reboundswith as $rebound)
        {
            if($rebound->offensive)
                $offreboundswith++;
            else
                $defreboundswith++;
        }
        $totalreboundswith = $offreboundswith + $defreboundswith;
        
        #SIFT ASSISTS STATS
        $assistsplits = siftstats($assists, $commonintervals);
        $assistswithout = sizeof($assistsplits["without"]);
        $assistswith = sizeof($assistsplits["with"]);
        
        #SIFT BLOCK STATS
        $blocksplits = siftstats($blocks, $commonintervals);
        $blockswithout = sizeof($blocksplits["without"]);
        $blockswith = sizeof($blocksplits["with"]);
        
        #SIFT STEAL STATS
        $stealsplits = siftstats($steals, $commonintervals);
        $stealswithout = sizeof($stealsplits["without"]);
        $stealswith = sizeof($stealsplits["with"]);
        
        #SIFT TURNOVERS
        $turnoversplits = siftstats($turnovers, $commonintervals);
        $turnoverswithout = sizeof($turnoversplits["without"]);
        $turnoverswith = sizeof($turnoversplits["with"]);

        $totalminuteswith = 0;
        foreach($commonintervals as $gameID => $gamematchedintervals)
        {
            $totalminuteswith += array_reduce($gamematchedintervals, function($carry, $interval){
                $length = ($interval->endtime - $interval->starttime);
                $carry += $length;
                return $carry;
            });
        }
        
        $totalminutes = 0;
        foreach($commonintervals as $gameID => $gamematchedintervals)
        {
            $totalminutes += array_reduce($shifts[$gameID], function($carry, $interval){
                $carry += ($interval->endtime - $interval->starttime);
                return $carry;
            });
        }
        $totalminuteswithout = $totalminutes - $totalminuteswith;
        $minswithout = number_format($totalminuteswithout/60000, 1);
        $minswith = number_format($totalminuteswith/60000, 1);
        
        $matchup = join(", ",array_map(function($item) { $player = DataManager::getPlayer($item); return $player->firstname[0].'. '.$player->lastname;}, $matchupplayerIDs));
        
        $html = "<table class='table stats'>
        <tr>
        </th><th><th>MIN</th><th>FGM</th><th>FGA</th><th>FG%</th><th>3PM</th><th>3PA</th><th>3FG%</th><th>FTM</th><th>FTA</th><th>FT%</th><th>AST</th><th>OREB</th><th>DREB</th><th>REB</th><th>BLK</th><th>STL</th><th>TO</th>
        </tr>";
        
        $html .= "<tr><td>w/out $matchup</td><td>$minswithout</td><td>$shotsmadewithout</td><td>$totalshotswithout</td><td>$FGwithout</td><td>$_3pointersmadewithout</td><td>$total3pointerswithout</td><td>$_3FGwithout</td><td>$freethrowsmadewithout</td><td>$totalfreethrowswithout</td><td>$FTPwithout</td><td>$assistswithout</td><td>$offreboundswithout</td><td>$defreboundswithout</td><td>$totalreboundswithout</td><td>$blockswithout</td><td>$stealswithout</td><td>$turnoverswithout</td></tr>";
        $html .= "<tr><td>w/ $matchup</td><td>$minswith</td><td>$shotsmadewith</td><td>$totalshotswith</td><td>$FGwith</td><td>$_3pointersmadewith</td><td>$total3pointerswith</td><td>$_3FGwith</td><td>$freethrowsmadewith</td><td>$totalfreethrowswith</td><td>$FTPwith</td><td>$assistswith</td><td>$offreboundswith</td><td>$defreboundswith</td><td>$totalreboundswith</td><td>$blockswith</td><td>$stealswith</td><td>$turnoverswith</td></tr>";
        
        $html .= "</table>";
        
        $ingameshotswith = array_values(array_filter($shotswith, function($shot){if($shot->type == 'Free Throw') return false; return true;}));
        $ingameshotswithout = array_values(array_filter($shotswithout, function($shot){if($shot->type == 'Free Throw') return false; return true;}));
        
        $jsonpackage = json_encode(array($html, $ingameshotswith, $ingameshotswithout, $shotlocationswith, $shotlocationswithout));
        
        echo $jsonpackage;
        return;
    }

    $pergamestats = array_map(function($game){$gamelog = new Stats(); $gamelog->game = $game; return $gamelog;}, $games);
    $pergametimeshares = array_map(function($game){return [];}, $games);
    
    $totalms = 0; $points = 0; $totalfreethrows = 0; $freethrowsmade = 0; $_3pointersmade = 0; $total3pointers = 0; $totalshots = 0; $shotsmade = 0;
    $totalassists = 0; $totalblocks = 0;$totalsteals = 0; $totalturnovers = 0; $defrebounds = 0; $offrebounds = 0;
    
    foreach($shifts as $gameID => $gameshifts)
    {
        foreach($gameshifts as $shift)
        {
            $shiftlength = $shift->endtime - $shift->starttime;
            $pergamestats[$gameID]->totalms += $shiftlength;
            $totalms += $shiftlength;
        }
    }
    
    foreach($shots as $gameID => $gameshots)
    {
        foreach($gameshots as $shot)
        {
            if($shot->type == "Free Throw")
            {
                $pergamestats[$gameID]->totalfreethrows++;
                $totalfreethrows++;
                if($shot->made)
                {
                    $pergamestats[$gameID]->freethrowsmade++;
                    $freethrowsmade++;
                    $pergamestats[$gameID]->points++;
                    $points += 1;
                }
            }
            else if($shot->type[0] == 3){
                $pergamestats[$gameID]->total3pointers++;
                $total3pointers++;
                $pergamestats[$gameID]->totalshots++;
                $totalshots++;
                if($shot->made)
                {
                    $pergamestats[$gameID]->shotsmade++;
                    $shotsmade++;
                    $pergamestats[$gameID]->_3pointersmade++;
                    $_3pointersmade++;
                    $pergamestats[$gameID]->points += 3;
                    $points += 3;
                }
            }
            else
            {
                $pergamestats[$gameID]->totalshots++;
                $totalshots++;
                if($shot->made)
                {
                    $pergamestats[$gameID]->shotsmade++;
                    $shotsmade++;
                    $pergamestats[$gameID]->points += 2;
                    $points += 2;
                }
            }
        }
    }

    foreach($assists as $gameID => $gameassists)
    {
        $pergamestats[$gameID]->totalassists = sizeof($gameassists);
        $totalassists += sizeof($gameassists);
    }
    
    foreach($rebounds as $gameID => $gamerebounds)
    {
        foreach($gamerebounds as $rebound)
        {
            if($rebound->offensive)
            {
                $pergamestats[$gameID]->offrebounds++;
                $offrebounds++;
            }
            else
            {
                $pergamestats[$gameID]->defrebounds++;
                $defrebounds++;
            }
        }
    }
    $totalrebounds = $defrebounds + $offrebounds;
    
    foreach($blocks as $gameID => $gameblocks)
    {
        $pergamestats[$gameID]->totalblocks = sizeof($gameblocks);
        $totalblocks += sizeof($gameblocks);
    }

    foreach($steals as $gameID => $gamesteals)
    {
        $pergamestats[$gameID]->totalsteals = sizeof($gamesteals);
        $totalsteals += sizeof($gamesteals);
    }

    foreach($turnovers as $gameID => $gameturnovers)
    {
        $pergamestats[$gameID]->totalturnovers = sizeof($gameturnovers);
        $totalturnovers += sizeof($gameturnovers);
    }

    $totalgames = sizeof($games);
    
    $jsonpackage = [];
    
    $player = DataManager::getPlayer($playerID);
    $playerinfo = array('firstname'=>$player->firstname,'lastname'=>$player->lastname);
    $jsonpackage[] = $playerinfo;
    
    foreach($shifts as $gameID => $gameshifts)
    {
        if(!array_key_exists($gameID, $shifts))
        {
            echo "Error: Game ID could not be found as key in player's per game stats";
            exit();
        }
        $pergamestats[$gameID]->totalms = array_reduce($gameshifts, function($carry, $item){ return $carry + ($item->endtime - $item->starttime);}, 0);
    }
    
    $stats = array("totalms"=>$totalms, "gamesplayed"=>$totalgames, "points"=>$points, "shotsmade"=>$shotsmade, "totalshots"=>$totalshots, "total3pointers" => $total3pointers, 
        "3pointersmade"=>$_3pointersmade, "freethrowsmade"=>$freethrowsmade, "totalfreethrows"=>$totalfreethrows, "totalassists" => $totalassists,
        "totalrebounds"=>$totalrebounds, "totalblocks"=>$totalblocks, "totalsteals"=>$totalsteals, "totalturnovers"=>$totalturnovers);
    
    foreach($games as $gameID => $game)
    {
        #Get a map of player to shifts in game 
        $playersshifts = DataManager::getShiftsFromGameById2($gameID, $game->home);
        foreach($playersshifts as $teammateID => $playershifts)
        {
            #skip the player because only concerned about other teammates
            if($teammateID == $playerID)
            {
                continue;
            }
            $commonintervals = commongameintervals(array($shifts[$gameID], $playershifts));
            $commontime = array_reduce($commonintervals, function($carry, $item){ return $carry + ($item->endtime - $item->starttime);}, 0);
            $pergametimeshares[$gameID][$teammateID] = $commontime;
        }
    }
    
    $package = array($stats, $pergamestats, $pergametimeshares);
    
    echo json_encode($package);
}

class Stats{
    public $game;
    public $totalms = 0;
    public $points = 0; 
    public $totalfreethrows = 0; 
    public $freethrowsmade = 0; 
    public $_3pointersmade = 0; 
    public $total3pointers = 0; 
    public $totalshots = 0; 
    public $shotsmade = 0;
    public $totalassists = 0;
    public $totalblocks = 0;
    public $totalsteals = 0; 
    public $totalturnovers = 0; 
    public $defrebounds = 0; 
    public $offrebounds = 0;
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

function commonintervals($shifts)
{
    #find games they played together
    $playergames = array_map('array_keys', $shifts);
    $commongames = call_user_func_array('array_intersect',$playergames);
    
    $commonintervals = [];
    
    foreach($commongames as $gameID)
    {
        #initiate intervals where all players were in for game
        $commonintervals[$gameID] = [];
        
        $gameshifts = array_map(function($item) use ($gameID) {return $item[$gameID];}, $shifts);
        $currentshifts = array_map('current', $gameshifts);
        
        while(!in_array(null, $currentshifts))
        {
            $lateststart = array_reduce($currentshifts, function($carry, $item){return max($carry, $item->starttime);}, 0);
            $earliestend = array_reduce($currentshifts, function($carry, $item){return min($carry, $item->endtime);}, INF);
            if($lateststart >= $earliestend)
            {
                foreach($currentshifts as $playerID => $shift)
                {
                    if($lateststart >= $shift->endtime)
                    {
                        $currentshifts[$playerID] = next($gameshifts[$playerID]);
                    }
                }
                continue;
            }
            
            $commoninterval = new stdClass();
            $commoninterval->starttime = $lateststart;
            $commoninterval->endtime = $earliestend;
            $commonintervals[$gameID][] = $commoninterval;
            
            foreach($currentshifts as $playerID => $shift)
            {
                if($earliestend == $shift->endtime)
                {
                    $currentshifts[$playerID] = next($gameshifts[$playerID]);
                }
            }
        }
    }
    return $commonintervals;
}

function commongameintervals($gameshifts)
{
    $commonintervals = [];
    $currentshifts = array_map('current', $gameshifts);
    
    while(!in_array(null, $currentshifts))
    {
        $lateststart = array_reduce($currentshifts, function($carry, $item){return max($carry, $item->starttime);}, 0);
        $earliestend = array_reduce($currentshifts, function($carry, $item){return min($carry, $item->endtime);}, INF);
        if($lateststart >= $earliestend)
        {
            foreach($currentshifts as $playerID => $shift)
            {
                if($lateststart >= $shift->endtime)
                {
                    $currentshifts[$playerID] = next($gameshifts[$playerID]);
                }
            }
            continue;
        }

        $commoninterval = new stdClass();
        $commoninterval->starttime = $lateststart;
        $commoninterval->endtime = $earliestend;
        $commonintervals[] = $commoninterval;

        foreach($currentshifts as $playerID => $shift)
        {
            if($earliestend == $shift->endtime)
            {
                $currentshifts[$playerID] = next($gameshifts[$playerID]);
            }
        }
    }
    return $commonintervals;
}

function siftstats($stats, $matchedintervals)
{
    //var_dump($stats);
    
    $splitstats = ["with"=>[], "without"=>[]];
    foreach($stats as $gameID => $gamestats)
    {
        if(!array_key_exists($gameID, $matchedintervals))
        {
            continue;
        }
        foreach($gamestats as $stat){
            $with = false;
            foreach($matchedintervals[$gameID] as $interval)
            {
                if($stat->time > $interval->starttime && $stat->time <= $interval->endtime)
                {
                    $splitstats["with"][] = $stat;
                    $with = true;
                    break;
                }
            }
            if(!$with)
            {
                $splitstats["without"][] = $stat;
            }
        }
    }
    return $splitstats;
}

function getCommonShifts($shift, $gameshifts)
{
    
}

?>