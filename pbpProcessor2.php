<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    require_once("classes.php");
    require_once("queries.php");
    require_once("basic.php");
    
    class pbpProcessor2
    {
        private $game;
        private $players;
        private $home;
        private $away;
        private $shifts;
        private $rebounds;
        private $quarter = 0;
        private $totalms = 0;
        private $homescore = 0;
        private $awayscore = 0;
        private $plusminus;
        
        private $possessingTeam = 0;
        private $possessionStartTime = 0;
        
        function processGame($gamefile)
        {
            $fcontents = file_get_contents("scraper/playbyplays/".$gamefile);
            $gameID = intval(substr($gamefile, 0, 8));
            $date = substr($gamefile, 9, 8);
            $date = substr($date, 0, 4)."-".substr($date, 4, 2)."-".substr($date, 6, 2);
            $datetime = strtotime($date.' + 17 hours');
            $awayabbrev = substr($gamefile, 18, 3);
            $homeabbrev = substr($gamefile, 21, 3);
            if(DataManager::selectGame($datetime, $homeabbrev, $awayabbrev))
            {
                echo "Game '$gamefile' is already logged\n";
                return false;
            }
            echo "Logging '$gamefile'...";
            $this->game = new Game($gameID, $datetime, $homeabbrev, $awayabbrev);
            DataManager::insertGame($this->game);

            $this->home = DataManager::getTeam($homeabbrev);
            $this->away = DataManager::getTeam($awayabbrev);

            $this->players = array($this->home->abbrev=>array(), $this->away->abbrev=>array());
            $this->shifts = array($this->home->abbrev=>array(), $this->away->abbrev=>array());
            $this->rebounds = array($this->home->abbrev=>array(), $this->away->abbrev=>array());
            $this->plusminus = array($this->home->abbrev=>array(), $this->away->abbrev=>array());

            $this->quarter = 0;
            $this->totalms = 0;
            
            $this->homescore = 0;
            $this->awayscore = 0;
            
            $lines = explode("\r\n",$fcontents);

            $homeline = $lines[0];
            $homeRoster = explode(",", $homeline);
            $awayline = $lines[1];
            $awayRoster = explode(",", $awayline);

            $this->initiateRosters($homeRoster, $this->home);
            $this->initiateRosters($awayRoster, $this->away);

            foreach($lines as $line_id => $line){
                if($line_id < 2) continue;

                $lineID = $line_id + 1;
                
                if($line == '***End of scrape***')
                {
                    DataManager::updateGameScore($this->homescore, $this->awayscore, $this->quarter, $gameID);
                    echo "End of play-by-play scrape\n";
                    return true;
                }
                
                #SPECIAL CASE: LINE IS FOR START OF A PERIOD
                if(strpos($line, 'Start of ') === 0)
                {   
                    $this->quarter++;
                    continue;
                }
                else if(strpos($line, 'End of ') === 0)
                {   
                    foreach($this->shifts as $team => &$teamshifts)
                    {
//                        $numteamshifts = sizeof($teamshifts);
//                        if($numteamshifts != 5)
//                        {
//                            echo "Error: $team does not enough have players ($numteamshifts) on the floor at $this->totalms\n";
//                            exit();
//                        }
                        foreach($teamshifts as &$playershift)
                        {
                            $playershift->endtime = quarterbasetime($this->quarter + 1);
                            DataManager::insertShift($playershift);
                            unset($teamshifts[$playershift->playerID]);
                        }
                    }
                    continue;
                }
                else if($line == 'Official Timeout')
                {
                    continue;
                }

                $lineparts = explode(',', $line); 
                $numlineparts = sizeof($lineparts);
                if($numlineparts != 7)
                {
                    echo "Error: 7 components were not found ($numlineparts) in line '$line' at line $lineID\n";
                    exit();
                }

                $clock = $lineparts[0];
                $score = $lineparts[1];
                $team = $lineparts[2];
                $playerID = $lineparts[3];
                $firstname = $lineparts[4];
                $lastname = str_replace("_"," ",$lineparts[5]);
                $play = str_replace("&",",",$lineparts[6]);
                
                $scoreparts = explode('-', $score);
                $loghomescore = $scoreparts[0];
                $logawayscore = $scoreparts[1];

                if(strpos($play, "Team Turnover") !==  false)
                {
                    $this->possessingTeam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                    $this->possessionStartTime = $this->totalms;
                    continue;
                }
                if($playerID == "")
                {
                    continue;
                }
                
                #TIME
                $this->totalms = timeintotalms($this->quarter, $clock);

                #HOME
                $isHome = $team == $this->home->abbrev;
                
                #Check if player is in shifts, if not add him - unless it's technical foul
                if(!array_key_exists($playerID, $this->shifts[$team]))
                {
                    if(strpos($play, "Technical") === false)
                    {
                        $starttime = quarterbasetime($this->quarter);
                        $shift = new Shift($playerID, $this->game->gameID, $starttime, $isHome);
                        $this->shifts[$team][$playerID] = $shift;

                        if(sizeof($this->shifts[$team]) > 5)
                        {
                            echo "Error: Some player not currently in a shift caused total shifts to exceed 5 at line $lineID";
                            exit();
                        }
                    }
                }
                
                #SHOT
                if(stripos($play, "Shot:") !== false)
                {
                    $lastnamepos = stripos($play, $lastname);
                    if($lastnamepos === false)
                    {
                        echo "Error: Player '$lastname' not found in play '$play'\n";
                        exit();
                    }
                    
                    #type
                    $typestart = $lastnamepos + strlen($lastname) + 1;
                    $typeend = stripos($play, "Shot:") + 4;
                    $type = substr($play, $typestart, $typeend - $typestart);
                    
                    #made
                    $made = stripos($play, "Shot: Made") !== false;
                    
                    if($made)
                    {
                        $points = 2;
                        if(stripos($type, '3pt') !== false)
                        {
                            $points = 3;
                        }
                        if($isHome)
                        {
                            $this->homescore += $points;
                        }
                        else 
                        {
                            $this->awayscore += $points;
                        }
                    }
                    
                    $shot = new Shot($playerID, $this->game->gameID, $this->totalms, $type, $made, $isHome);
                    DataManager::insertShot($shot, $lineID);
                    if($type != 'Free Throw')
                    {
                        $shotclock = $this->totalms - $this->possessionStartTime;
//                        if($shotclock >= 24000)
//                        {
//                            echo "Error: shot clock computed is over 24 seconds at line $lineID";
//                            exit();
//                        }
                        //DataManager::addShotClockTime($shot->shotID, $shotclock);
                    }
                    
                    #ASSIST
                    if(strpos($play, "Assist:") !== false)
                    {
                        $assistpos = stripos($play, "Assist:");
                        $assistplay = substr($play, $assistpos);
                        $assister = getPlayerInPlay($assistplay, $this->players[$team]);
                        
                        $assist = new Assist($assister->playerID, $shot->shotID);
                        DataManager::insertAssist($assist, $lineID);
                    }
                    #BLOCK
                    else if(strpos($play, "Block:") !== false)
                    {
                        $blockpos = strpos($play, "Block:");
                        $blockplay = substr($play, $blockpos);
                        $oppositeteam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                        $blocker = getPlayerInPlay($blockplay, $this->players[$oppositeteam]);
                        
                        $block = new Block($blocker->playerID, $shot->shotID);
                        DataManager::insertBlock($block, $lineID);
                    }
                }
                #FREE THROW
                else if(strpos($play, "Free Throw") !== false)
                {
                    #made
                    $made = stripos($play, "MISSED") === false;
                    
                    if($made)
                    {
                        if($isHome)
                        {
                            $this->homescore++;
                        }
                        else 
                        {
                            $this->awayscore++;
                        }
                    }
                    
                    #foultype
                    if(strpos($play, "Technical") !== false)
                    {
                        $foultype = "Technical";
                    }
                    else if(strpos($play, "Flagrant") !== false)
                    {
                        $foultype = "Flagrant";
                    }
                    else
                    {
                        $foultype = "Regular";
                        $this->possessingTeam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                    }
                    $this->possessionStartTime = $this->totalms;
                    
                    #seq and total
                    $freethrow_seq_of_total_pattern = "/[0-9] of [0-9]/";
                    $matches = array();
                    preg_match($freethrow_seq_of_total_pattern, $play, $matches, PREG_OFFSET_CAPTURE);

                    $seq = 1;
                    $total = 1;

                    if(sizeof($matches) > 0)
                    { 
                        $seqoftotal = $matches[0][0];
                        $seqoftotalparts = explode(" ", $seqoftotal);
                        if(sizeof($seqoftotalparts) != 3)
                        {
                            echo "Error: Unexpected format of free throw number and total in free throw at line $lineID\n";
                            exit();
                        }

                        $seq = $seqoftotalparts[0];
                        $total = $seqoftotalparts[2];
                    }
                    
                    $freethrow = new FreeThrow($playerID, $this->game->gameID, $this->totalms, $made, $isHome, $foultype, $seq, $total);
                    DataManager::insertFreeThrow($freethrow, $lineID);
                }
                #REBOUND
                else if(strpos($play, "Rebound") !== false)
                {
                    $this->possessingTeam = $team;
                    $this->possessionStartTime = $this->totalms;
                    #offensive
                    $offrebsmatches = array(); $defrebsmatches = array();
                    preg_match("/Off:[0-9]+/", $play, $offrebsmatches, PREG_OFFSET_CAPTURE);
                    preg_match("/Def:[0-9]+/", $play, $defrebsmatches, PREG_OFFSET_CAPTURE);

                    $offrebs = intval(substr($offrebsmatches[0][0], 4));
                    $defrebs = intval(substr($defrebsmatches[0][0], 4));

                    $offensive = null;
                    if($offrebs  == $this->rebounds[$team][$playerID]["off"] && $defrebs == $this->rebounds[$team][$playerID]["def"] + 1)
                    {
                        $offensive = false; 
                        $this->rebounds[$team][$playerID]["def"]++; 
                    }
                    else if($offrebs  == $this->rebounds[$team][$playerID]["off"] + 1 && $defrebs == $this->rebounds[$team][$playerID]["def"])
                    {
                        $offensive = true; 
                        $this->rebounds[$team][$playerID]["off"]++; 
                    }
                    
                    if($offensive === null)
                    {
                        echo "Error: Lost track of offensive(".$this->rebounds[$team][$playerID]["off"].")/defensive(".$this->rebounds[$team][$playerID]["def"].") rebound data for $lastname in play '$play' at line $lineID\n";
                        exit();
                    }
                    
                    $rebound = new Rebound($playerID, $this->game->gameID, $this->totalms, $isHome, $offensive);
                    DataManager::insertRebound($rebound, $lineID);
                }
                #TURNOVER
                else if(strpos($play, "Turnover :") !== false)
                {
                    $this->possessingTeam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                    $this->possessionStartTime = $this->totalms;
                    
                    #type
                    $turnoverpos = strpos($play, "Turnover :");
                    $typestart = $turnoverpos + strlen("Turnover :") + 1;
                    $turnovertotal_pattern = "/\([0-9]+ TO\)/";
                    $matches = array();
                    preg_match($turnovertotal_pattern, $play, $matches, PREG_OFFSET_CAPTURE);
                    if(sizeof($matches) == 0)
                    {
                        echo "Error: Could not find expected turnover total in turnover at line $lineID\n";
                        exit();
                    }
                    $typeend = $matches[0][1] - 1;
                    $type = substr($play, $typestart, $typeend - $typestart);
                    
                    $turnover = new Turnover($playerID, $this->game->gameID, $this->totalms, $isHome, $type);
                    DataManager::insertTurnover($turnover, $lineID);
                    
                    #STEAL
                    if(strpos($play, "Steal:") !== false)
                    {   
                        #playerid
                        $stealpos = strpos($play, "Steal:");
                        $stealerstart = $stealpos + strlen("Steal:");
                        $stealtotal_pattern = "/\([0-9]+ ST\)/";
                        $matches = array();
                        preg_match($stealtotal_pattern, $play, $matches, PREG_OFFSET_CAPTURE);
                        if(sizeof($matches) == 0)
                        {
                            echo "Error: Could not find expected turnover total in turnover at line $lineID\n";
                            exit();
                        }
                        $stealerend = $matches[0][1] - 1;
                        $stealerlastname = substr($play, $stealerstart, $stealerend - 1);
                        $oppositeteam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                        $stealer = getPlayerInPlay($stealerlastname, $this->players[$oppositeteam]);
                        $steal = new Steal($stealer->playerID, $turnover->turnoverID);
                        DataManager::insertSteal($steal, $lineID);
                    }
                }
                #FOUL
                else if(strpos($play, "Foul:") !== false || strpos($play, "Technical") !== false)
                {
                    $this->possessingTeam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                    $this->possessionStartTime = max($this->totalms, 14000);
                    
                    #type
                    $type = null;
                    if(strpos($play, "Double Technical") !== false)
                    {
                        $type = "Double Technical";
                    }
                    else if(strpos($play, "Technical") !== false)
                    {
                        $type = "Technical";
                    }
                    else if(strpos($play, "Double Personal") !== false)
                    {
                        $type = "Double Personal";
                    }
                    else
                    {   
                        $foulpos = strpos($play, "Foul:");
                        $typestart = $foulpos + 5;
                        $pftotal_pattern = "/\([0-9]+ PF\)/";
                        preg_match($pftotal_pattern, $play, $matches, PREG_OFFSET_CAPTURE);
                        if(sizeof($matches) == 0)
                        {
                            echo "Error: Could not find expected personal foul total in foul at line $lineID";
                            exit();
                        }
                        $typeend = $matches[0][1] - 1;
                        $type = substr($play, $typestart, $typeend - $typestart);
                    }

                    #referee
                    $referee_pattern = "/\([A-Z] [A-Za-z\-]+\)$/";
                    preg_match($referee_pattern, $play, $matches, PREG_OFFSET_CAPTURE);
                    $referee = "";
                    if(sizeof($matches) == 0)
                    {
                        echo "Error: Referee not found in foul on play '$play' at line $lineID\n";
                        //exit();
                    }  
                    else
                    {
                        $referee = str_replace(array('(',')'), '', $matches[0][0]);
                    }
                    $foul = new Foul($this->game->gameID, $this->totalms, $isHome, $playerID, $type, $referee);
                    DataManager::insertFoul($foul, $lineID);
                    if(strpos($play, "Double Technical") !== false)
                    {
                        $playerpos = stripos($play, $lastname);
                        $restofplay = substr($play, $playerpos + strlen($lastname));
                        $oppositeteam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                        $player = getPlayerInPlay($restofplay, $this->players[$oppositeteam]);
                        $otherfoul = new Foul($this->game->gameID, $this->totalms, !$isHome, $player->playerID, "Double Technical", $referee);
                        DataManager::insertFoul($otherfoul, $lineID);
                    }
                    else if(strpos($play, "Double Personal") !== false)
                    {
                        $playerpos = stripos($play, $lastname);
                        $restofplay = substr($play, $playerpos + strlen($lastname));
                        $oppositeteam = $team == $this->home->abbrev ? $this->away->abbrev : $this->home->abbrev;
                        $player = getPlayerInPlay($restofplay, $this->players[$oppositeteam]);
                        $otherfoul = new Foul($this->game->gameID, $this->totalms, !$isHome, $player->playerID, "Double Personal", $referee);
                        DataManager::insertFoul($otherfoul, $lineID);
                    }
                }
                #SUBSTITUTION
                else if(strpos($play, "Substitution replaced by"))
                {
                    if(!array_key_exists($playerID, $this->shifts[$team]))
                    {
                        echo "Error could not find playerID $playerID in $team shifts in substitution at line $lineID\n";
                        exit();
                    }
                    
                    $shiftoff = $this->shifts[$team][$playerID];
                    $shiftoff->endtime = $this->totalms;
                    unset($this->shifts[$team][$playerID]);
                    DataManager::insertShift($shiftoff);
                    
                    $subpos = strpos($play, "Substitution replaced by");
                    $playeronlastnamestart = $subpos + strlen("Substitution replaced by") + 1;
                    $playeronlastname = substr($play, $playeronlastnamestart);
                    
                    $playeron = getPlayerInPlay($playeronlastname, $this->players[$team]);
                    if($playeron->playerID != 0)
                    {
                        $shifton = new Shift($playeron->playerID, $this->game->gameID, $this->totalms, $isHome);
                        $this->shifts[$team][$playeron->playerID] = $shifton;
                    }
                }
                
                #CHECK SCORE IS RIGHT
                if($loghomescore != $this->homescore || $logawayscore != $this->awayscore)
                {
                    echo "Error: play-by-play log score $loghomescore-$logawayscore does not match $this->homescore-$this->awayscore at line $lineID";
                    exit();
                }
            }
            
        }

        function initiateRosters($roster, $team)
        {
            $playernames = array_chunk($roster , 3);
            $i=0;
            foreach($playernames as $p)
            {
                $playerid = intval($p[0]);
                $firstname = $p[1];
                $lastname = $p[2];
                $player = DataManager::getPlayer($playerid);
                if(!$player)
                {
                    echo "inserting $firstname $lastname\n";
                    DataManager::insertPlayer($playerid, $firstname, $lastname, $team->abbrev);
                    $player = DataManager::getPlayer($playerid);
                }
                
                if($player)
                {
                    $this->players[$team->abbrev][$player->playerID] = $player;
                    if($i < 5)
                    {
                        $isHome = $team->abbrev == $this->home->abbrev;
                        $shift = new Shift($player->playerID, $this->game->gameID, 0, $isHome);
                        $this->shifts[$team->abbrev][$player->playerID] = $shift;
                    }
                    $this->rebounds[$team->abbrev][$player->playerID] = array("off"=>0, "def"=>0);
                    
                    $this->plusminus[$team->abbrev][$player->playerID] = 0;
                }
                else
                {
                    echo "Error: Couldn't get player\n";
                    exit();
                }
                $i++;
            }
        }
    }