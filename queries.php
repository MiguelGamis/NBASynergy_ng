<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('config.php');
require_once("classes.php");

class DataManager
{
    private $database;
    
    function __construct() {
        global $db;
        $database = $db;
    }
    
    static function insertShift($shift)
    {
        global $db;
        if($db)
        {
            $query = "INSERT IGNORE INTO shift (playerID, gameID, starttime, endtime, home) VALUES (?, ?, ?, ?, ?);";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($shift->playerID, $shift->gameID, $shift->starttime, $shift->endtime, $shift->isHome));
            if(!$result)
            {
                echo "Error: Something went wrong inserting a shift: ".$sh->errorCode();
                exit();
            }
        }
    }
    
    static function insertGame(&$game)
    {
        global $db;
        $query = "INSERT IGNORE INTO game (gameID, date, hometeam, awayteam) VALUES (?, FROM_UNIXTIME(?), ?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($game->gameID, $game->date, $game->home, $game->away));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a game: ".$sh->errorCode();
            exit();
        }
    }
    
    static function selectGame($date, $home, $away)
    {
        global $db;
        $query = "SELECT gameID FROM game WHERE date = FROM_UNIXTIME(?) AND hometeam = ? AND awayteam = ?;";
        $sh = $db->prepare($query);
        $sh->execute(array($date, $home, $away));
        $row = $sh->fetch();
        if($row)
        {
            $game = new Game($row['gameID'], $date, $home, $away);
            return $game;
        }
    }
    
    static function getTeam($abbrev)
    {
        global $db;
        
        $query = "SELECT * FROM team WHERE shortName = ?;";
        $sh = $db->prepare($query);
        $sh->execute(array($abbrev));
        $result = $sh->fetch();
        if(!$result)
        {
            echo "Error: Could not find team from abbreviation '$abbrev'";
            exit();
        }
        $team = new Team($result['shortName'], $result['city'], $result['teamName']);
        return $team;
    }
    
    static function insertPlayer($playerID, $firstname, $lastname, $team)
    {
        #Go and include the assignment page
        global $db;
        if($db)
        {
            $query = "INSERT INTO player (playerID, firstname, lastname, team) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE team = ?;";
            $sh = $db->prepare($query);
            $sh->execute(array($playerID, ucfirst($firstname), ucfirst($lastname), $team, $team));
        }
    }

    static function getPlayer($playerID)
    {
        global $db;
        #Go and include the assignment page
        if($db)
        {
            $query = "SELECT * FROM player WHERE playerID = ?";
            $sh = $db->prepare($query);
            $sh->execute(array($playerID));
            $row = $sh->fetch();
            
            if($row)
            {
                $player = new Player($row['firstname'], $row['lastname'], $row['playerID'], $row['team']);
                return $player;
            }
        }
    }

    static function getPlayerByID($id)
    {
        global $db;
        if($db)
        {
            $query = "SELECT * FROM player WHERE playerID = ?;";
            $sh = $db->prepare($query);
            $sh->execute(array($id));
            $row = $sh->fetch();
            $player = new Player($row['firstname'], $row['lastname'], $id, $row['team']);
            return $player;
        }
    }
    
    static function insertShot(&$shot, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO shot (playerID, type, made, gameID, lineID, time, home) VALUES (?, ?, ?, ?, ?, ?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($shot->playerID, $shot->type, $shot->success, $shot->gameID, $lineID, $shot->time, $shot->isHome));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a shot at line $lineID";
            exit();
        }
        $shotID = $db->lastInsertID();
        if($shotID)
        {
            $shot->shotID = $shotID;
        }
        else
        {
            $query = "SELECT shotID FROM shot WHERE gameID = ? AND lineID = ?";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($shot->gameID, $lineID));
            $row = $sh->fetch();
            if($row)
            {
                $shot->shotID = $row['shotID'];
            }
        }
    }
    
    static function getShot($gameID, $time, $playerID, $isHome, $made)
    {
        global $db;
        if($db)
        {
            $query = "SELECT shotID FROM shot WHERE gameID = ? AND time = ? AND playerID = ? AND home = ? AND made = ?";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($gameID, $time, $playerID, $isHome, $made));
            if(!$result)
            {
                echo "Error: Something went wrong when finding a shot";
                exit();
            }
            $row = $sh->fetch();
            if($row)
            {
                return $row['shotID'];
            }
        }
    }
    
    static function addShotClockTime($shotID, $shotclock)
    {
        global $db;
        if($db)
        {
            $query = "INSERT INTO shotdetails (shotID, shotclock) VALUES (:shotID, :shotclock) ON DUPLICATE KEY UPDATE shotclock = :shotclock";
            $sh = $db->prepare($query);
            $result = $sh->execute(array('shotID'=>$shotID, 'shotclock'=>$shotclock));
            if(!$result)
            {
                echo "Error: Something went wrong when adding shot clock time at line $lineID: ".$sh->errorCode();
                exit();
            }
        }
    }
    
    static function addShotLocation($shotID, $X, $Y, $lineID)
    {
        global $db;
        if($db)
        {
            $query = "INSERT INTO shotdetails (shotID, X, Y) VALUES (:shotID, :X, :Y) ON DUPLICATE KEY UPDATE X = :X AND Y = :Y";
            $sh = $db->prepare($query);
            $result = $sh->execute(array('shotID'=>$shotID, 'X'=>$X, 'Y'=>$Y));
            if(!$result)
            {
                echo "Error: Something went wrong when adding a shot location at line $lineID: ".$sh->errorCode();
                exit();
            }
        }
    }
    
    static function addMissingShotLocation($gameID, $totalms, $playerID, $made, $isHome, $X, $Y, $lineID)
    {
        global $db;
        if($db)
        {
            $query = "INSERT IGNORE INTO missingshotlocation (gameID, time, playerID, made, home, X, Y, lineID) VALUES (:gameID, :time, :playerID, :made, :home, :X, :Y, :lineID)";
            $sh = $db->prepare($query);
            $result = $sh->execute(array('gameID'=>$gameID, 'time'=>$totalms, 'playerID'=>$playerID, 'made'=>$made, 'home'=>$isHome, 'X'=>$X, 'Y'=>$Y, 'lineID'=>$lineID));
            if(!$result)
            {
                echo "Error: Something went wrong when inserting a missing shot location at line $lineID: ".$sh->errorCode();
                exit();
            }
        }
    }
    
    static function addShotWithLocation($gameID, $totalms, $playerID, $made, $isHome, $X, $Y, $lineID)
    {
        global $db;
        if($db)
        {
            $query = "INSERT IGNORE INTO shotlocation (gameID, time, playerID, made, home, X, Y, lineID) VALUES (:gameID, :time, :playerID, :made, :home, :X, :Y, :lineID)";
            $sh = $db->prepare($query);
            $result = $sh->execute(array('gameID'=>$gameID, 'time'=>$totalms, 'playerID'=>$playerID, 'made'=>$made, 'home'=>$isHome, 'X'=>$X, 'Y'=>$Y, 'lineID'=>$lineID));
            if(!$result)
            {
                echo "Error: Something went wrong when inserting a shot location at line $lineID: ".$sh->errorCode();
                exit();
            }
        }
    }
    
    static function insertFreeThrow(&$freethrow, $lineID)
    {
        global $db;
        if($db)
        {
            $query = "INSERT IGNORE INTO shot (playerID, type, made, gameID, lineID, time, home) VALUES (?, ?, ?, ?, ?, ?, ?);";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($freethrow->playerID, $freethrow->type, $freethrow->success, $freethrow->gameID, $lineID, $freethrow->time, $freethrow->isHome));
            $freethrow->shotID = $db->lastInsertID();
            if(!$result)
            {
                echo "Error: Something went wrong inserting a free throw at line $lineID: ".$sh->errorCode();
                exit();
            }
            
            $query2 = "INSERT IGNORE INTO freethrow (shotID, foultype, seq, total) VALUES (?, ?, ?, ?);";
            $sh2 = $db->prepare($query2);
            $result2 = $sh2->execute(array($freethrow->shotID, $freethrow->foultype, $freethrow->seq, $freethrow->total));
            if(!$result2)
            {
                echo "Error: Something went wrong inserting a free throw at line $lineID: ".$sh2->errorCode();
                exit();
            }
        }
    }
    
    static function insertFoul(&$foul, $lineID)
    {
        global $db;
        if($db)
        {
            $query = "INSERT IGNORE INTO foul (foulerID, type, referee, gameID, lineID, time, home) VALUES (?, ?, ?, ?, ?, ?, ?);";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($foul->foulerID, $foul->type, $foul->referee, $foul->gameID, $lineID, $foul->time, $foul->isHome));
            if(!$result)
            {
                echo "Error: Something went wrong inserting a foul at line $lineID: ".$sh->errorCode();
                exit();
            }
            $foul->foulID = $db->lastInsertID();
        }
    }
    
    static function insertAssist($assist, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO assist (playerID, shotID) VALUES (?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($assist->playerID, $assist->shotID));
        if(!$result)
        {
            echo "Error: Something went wrong inserting an assist at line $lineID: ".$sh->errorCode();
            exit();
        }
    }
    
    static function insertRebound($rebound, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO rebound (playerID, gameID, lineID, time, home, offensive) VALUES (?, ?, ?, ?, ?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($rebound->playerID, $rebound->gameID, $lineID, $rebound->time, $rebound->isHome, $rebound->offensive));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a rebound at line $lineID: ".$sh->errorCode();
            exit();
        }
    }
    
    static function insertTurnover(&$turnover, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO turnover (playerID, gameID, lineID, time, home, type) VALUES (?, ?, ?, ?, ?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($turnover->playerID, $turnover->gameID, $lineID, $turnover->time, $turnover->isHome, $turnover->type));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a turnover at line $lineID: ".$sh->errorCode();
            exit();
        }
        $turnoverID = $db->lastInsertID();
        if($turnoverID)
        {
            $turnover->turnoverID = $turnoverID;
        }
        else
        {
            $query = "SELECT turnoverID FROM turnover WHERE gameID = ? AND lineID = ?";
            $sh = $db->prepare($query);
            $result = $sh->execute(array($turnover->gameID, $lineID));
            $row = $sh->fetch();
            if($row)
            {
                $turnover->shotID = $row['turnoverID'];
            }
        }
    }
    
    static function insertSteal($steal, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO steal (playerID, turnoverID) VALUES (?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($steal->playerID, $steal->turnoverID));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a steal at line $lineID: ".$sh->errorCode();
            exit();
        }
    }
    
    static function insertBlock($block, $lineID)
    {
        global $db;
        $query = "INSERT IGNORE INTO block (playerID, shotID) VALUES (?, ?);";
        $sh = $db->prepare($query);
        $result = $sh->execute(array($block->playerID, $block->shotID));
        if(!$result)
        {
            echo "Error: Something went wrong inserting a block at line $lineID: ".$sh->errorCode();
            exit();
        }
    }
    
    static function getShiftsFromGame($date, $awayteam, $hometeam, $home)
    {
        global $db;
        $query = "SELECT * FROM shift JOIN (SELECT gameID FROM game WHERE date = FROM_UNIXTIME(?) AND awayteam = ? AND hometeam = ?) specificgame ON specificgame.gameID = shift.gameID WHERE home = ?";
        $sh = $db->prepare($query);
        $homebit = $home ? 1 : 0;
        $sh->execute(array($date, $awayteam, $hometeam, $homebit));
        $shifts = array();
        while($res = $sh->fetch())
        {
            $shift = new Shift($res['playerID'], $res['gameID'], $res['starttime'], boolval($res['home']), $res['endtime']);
            if(!array_key_exists($res['playerID'], $shifts))
            {
                $playershifts = new stdClass();
                $playershifts->player = DataManager::getPlayerByID($res['playerID']);
                $playershifts->shifts = array();
                $shifts[$res['playerID']] = $playershifts;
            }
            $shifts[$res['playerID']]->shifts[] = $shift; 
        }
        return array_values($shifts);
    }
    
    static function getShiftsFromGameById($gameID, $home)
    {
        global $db;
        $query = "SELECT * FROM shift WHERE gameID = ? AND home = ?";
        $sh = $db->prepare($query);
        $homebit = $home ? 1 : 0;
        $sh->execute(array($gameID, $homebit));
        $shifts = array();
        while($res = $sh->fetch())
        {
            $shift = new Shift($res['playerID'], $res['gameID'], $res['starttime'], boolval($res['home']), $res['endtime']);
            if(!array_key_exists($res['playerID'], $shifts))
            {
                $playershifts = new stdClass();
                $playershifts->player = DataManager::getPlayerByID($res['playerID']);
                $playershifts->shifts = array();
                $shifts[$res['playerID']] = $playershifts;
            }
            $shifts[$res['playerID']]->shifts[] = $shift; 
        }
        return array_values($shifts);
    }
    
    static function getShiftsFromGameById2($gameID, $home)
    {
        global $db;
        $query = "SELECT * FROM shift WHERE gameID = ? AND home = ? ORDER BY starttime";
        $sh = $db->prepare($query);
        $homebit = $home ? 1 : 0;
        $sh->execute(array($gameID, $homebit));
        $shifts = array();
        while($res = $sh->fetch())
        {
            $shift = new Shift($res['playerID'], $res['gameID'], $res['starttime'], boolval($res['home']), $res['endtime']);
            if(!array_key_exists($res['playerID'], $shifts))
            {
                $shifts[$res['playerID']] = [];
            }
            $shifts[$res['playerID']][] = $shift; 
        }
        return $shifts;
    }
    
    static function getShiftsFromGameById3($gameID, $home)
    {
        global $db;
        $query = "SELECT * FROM shift WHERE gameID = ? AND home = ? ORDER BY endtime ASC, starttime ASC;";
        $sh = $db->prepare($query);
        $homebit = $home ? 1 : 0;
        $sh->execute(array($gameID, $homebit));
        $shifts = array();
        while($res = $sh->fetch())
        {
            $shift = new Shift($res['playerID'], $res['gameID'], $res['starttime'], boolval($res['home']), $res['endtime']);
            $shifts[] = $shift; 
        }
        return $shifts;
    }
    
    static function getTeammateShifts($playerID)
    {
        global $db;
        $query = "SELECT * FROM shift JOIN (SELECT gameID, home FROM shift WHERE playerID = ? GROUP BY gameID) as gamesplayed ON shift.gameID = gamesplayed.gameID AND shift.home = gamesplayed.home";
    }
    
    static function getPlayersFromTeam($team)
    {
        global $db;
        $query = "SELECT * FROM player WHERE team = ?;";
        $sh = $db->prepare($query);
        $args = array($team);
        $result = $sh->execute($args);
        
        if(!$result)
        {
            return $sh->errorCode();
        }
        $players = [];
        
        while($res = $sh->fetch())
        {
            $player = new Player($res['firstname'], $res['lastname']);
            $player->playerID = $res['playerID'];
            $player->team = $res['team'];
            $players[] = $player;
        }
        return $players;
    }
    
    static function getPlayersFromGame($gameID, $home)
    {
        global $db;
        $query = "SELECT * FROM player JOIN (SELECT DISTINCT(playerID) FROM shift WHERE gameID = ? AND home = ?) gameshift ON player.playerID = gameshift.playerID";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID, $home));
        $players = array();
        while($res = $sh->fetch())
        {
            $player = new stdClass();
            $player->playerID = $res['playerID'];
            $player->firstname = $res['firstname'];
            $player->lastname = $res['lastname'];
            $players[] = $player;
        }
        return $players;
    }
    
    static function getTeams()
    {
        global $db;
        $query = "SELECT * FROM team";
        $sh = $db->prepare($query);
        $sh->execute();
        $teams = [];
        while($row = $sh->fetch())
        {
            $teams[] = new Team($row['shortName'], $row['city'], $row['teamName']);
        }
        return $teams;
    }
    
    static function getShotLocations($playerID)
    {
        global $db;
        $query = "SELECT * FROM shotlocation WHERE playerID = ? ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $shotlocs = [];
        while($row = $sh->fetch())
        {
            $shot = new stdClass();
            $shot->time = $row['time'];
            $shot->made = intval($row['made']);
            $shot->home = intval($row['home']);
            $shot->X = floatval($row['X']);
            $shot->Y = floatval($row['Y']);
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $shotlocs))
            {
                $shotlocs[$gameID] = [];
            }
            $shotlocs[$gameID][] = $shot;
        }
        
        return $shotlocs;
    }
    
    static function getRandom3pointers($playerID)
    {
        global $db;
        $query = "SELECT * FROM shotlocation JOIN (SELECT gameID, time FROM shot WHERE type LIKE '%3pt%' LIMIT 0, 30) as threepointshot ON (threepointshot.gameID = shotlocation.gameID AND threepointshot.time = shotlocation.time)";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $random3pointers = [];
        while($row = $sh->fetch())
        {
            $shot = new stdClass();
            $shot->time = $row['time'];
            $shot->made = intval($row['made']);
            $shot->home = intval($row['home']);
            $shot->X = floatval($row['X']);
            $shot->Y = floatval($row['Y']);
            $random3pointers[] = $shot;
        }
        
        return $random3pointers;
    }
    
    static function getShots($playerID)
    {
        global $db;
        $query = "SELECT * FROM shot WHERE playerID = ? ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $shots = [];
        while($row = $sh->fetch())
        {
            $shot = new stdClass();
            $shot->time = $row['time'];
            $shot->type = $row['type'];
            $shot->made = intval($row['made']);
            $shot->home = intval($row['home']);
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $shots))
            {
                $shots[$gameID] = [];
            }
            $shots[$gameID][] = $shot;
        }
        
        return $shots;
    }
    
    static function getShotsFromGame($gameID, $madeonly = false)
    {
        global $db;
        $madeonlyquery = '';
        if($madeonly){
            $madeonlyquery = 'AND made = 1';
        }
        $query = "SELECT * FROM shot WHERE gameID = ? $madeonlyquery ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID));
        
        $shots = [];
        while($row = $sh->fetch())
        {
            $shot = new stdClass();
            $shot->time = $row['time'];
            $shot->type = $row['type'];
            $shot->lineID = $row['lineID'];
            $shot->home = intval($row['home']);
            $shot->made = $row['made'];
            $gameID = $row['gameID'];
            $shots[] = $shot;
        }
        
        return $shots;
    }
    
    static function getOffensiveReboundsFromGame($gameID)
    {
        global $db;
        $query = "SELECT * FROM rebound WHERE gameID = ? AND offensive = 1 ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID));
        
        $rebounds = [];
        while($row = $sh->fetch())
        {
            $rebound = new stdClass();
            $rebound->time = $row['time'];
            $rebound->home = $row['home'];
            $rebounds[] = $rebound;
        }
        
        return $rebounds;
    }
    
    static function getDefensiveReboundsFromGame($gameID)
    {
        global $db;
        $query = "SELECT * FROM rebound WHERE gameID = ? AND offensive = 0 ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID));
        
        $rebounds = [];
        while($row = $sh->fetch())
        {
            $rebound = new stdClass();
            $rebound->time = $row['time'];
            $rebound->home = $row['home'];
            $rebounds[] = $rebound;
        }
        
        return $rebounds;
    }
    
    static function getTurnoversFromGame($gameID)
    {
        global $db;
        $query = "SELECT * FROM turnover WHERE gameID = ? ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID));
        
        $turnovers = [];
        while($row = $sh->fetch())
        {
            $turnover = new stdClass();
            $turnover->time = $row['time'];
            $turnover->home = $row['home'];
            $turnovers[] = $turnover;
        }
        
        return $turnovers;
    }
    
    static function getAssists($playerID)
    {
        global $db;
        $query = "SELECT shot.gameID, shot.time FROM shot JOIN (SELECT * FROM assist WHERE playerID = ?) playerassists ON shot.shotID = playerassists.shotID ORDER BY shot.time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $assists = [];
        while($row = $sh->fetch())
        {
            $assist = new stdClass();
            $assist->time = $row['time'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $assists))
            {
                $assists[$gameID] = [];
            }
            $assists[$gameID][] = $assist;
        }
        
        return $assists;
    }
    
    static function getShiftsFromPlayer($playerID)
    {
        global $db;
        $query = "SELECT * FROM shift WHERE playerID = ? ORDER BY starttime;";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $shifts = [];
        while($row = $sh->fetch())
        {
            $shift = new stdClass();
            $shift->starttime = $row['starttime'];
            $shift->endtime = $row['endtime'];
            $shift->home = $row['home'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $shifts))
            {
                $shifts[$gameID] = array();
            }
            $shifts[$gameID][] = $shift;
        }
        
        return $shifts;
    }
    
    static function getMatchedShiftsFromPlayer($playerID, $matchedplayerID, $vs = true)
    {
        global $db;
        $matchup = $vs ? "<>" : "==";
        $query = "SELECT * FROM shift s1 WHERE playerID = ? AND gameID IN (SELECT DISTINCT(gameID) FROM shift WHERE playerID = ? AND home $matchup s1.home) ORDER BY starttime;";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID, $matchedplayerID));
        
        $shifts = [];
        while($row = $sh->fetch())
        {
            $shift = new stdClass();
            $shift->starttime = $row['starttime'];
            $shift->endtime = $row['endtime'];
            $shift->home = $row['home'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $shifts))
            {
                $shifts[$gameID] = array();
            }
            $shifts[$gameID][] = $shift;
        }
        
        return $shifts;
    }
    
    static function getGamesVsTeam($playerID, $team)
    {
        global $db;
        $query = "SELECT playergames.gameID, home, awayteam, hometeam FROM (SELECT DISTINCT(gameID), home FROM shift WHERE playerID = ?) playergames JOIN (SELECT gameID, awayteam, hometeam from game WHERE hometeam = ? OR awayteam = ?) teamgames ON (playergames.gameID = teamgames.gameID)";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID, $team, $team));
        
        $games = [];
        while($row = $sh->fetch())
        {
            if($row['home'] ? $team == $row['awayteam'] : $team == $row['hometeam'])
            {
                $games[] = intval($row['gameID']);
            }
        }
        
        return $games;
    }
    
    static function getRebounds($playerID)
    {
        global $db;
        $query = "SELECT * FROM rebound WHERE playerID = ? ORDER by time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $rebounds = [];
        while($row = $sh->fetch())
        {
            $rebound = new stdClass();
            $rebound->time = $row['time'];
            $rebound->offensive = $row['offensive'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $rebounds))
            {
                $rebounds[$gameID] = [];
            }
            $rebounds[$gameID][] = $rebound;
        }
        
        return $rebounds;
    }
    
    static function getBlocks($playerID)
    {
        global $db;
        $query = "SELECT shot.gameID, shot.time FROM shot JOIN (SELECT * FROM block WHERE playerID = ?) playerblocks ON shot.shotID = playerblocks.shotID ORDER BY shot.time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $blocks = [];
        while($row = $sh->fetch())
        {
            $block = new stdClass();
            $block->time = $row['time'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $blocks))
            {
                $blocks[$gameID] = [];
            }
            $blocks[$gameID][] = $block;
        }
        
        return $blocks;
    }
    
    static function getTurnovers($playerID)
    {
        global $db;
        $query = "SELECT * FROM turnover WHERE playerID = ? ORDER BY time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $turnovers = [];
        while($row = $sh->fetch())
        {
            $turnover = new stdClass();
            $turnover->time = $row['time'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $turnovers))
            {
                $turnovers[$gameID] = [];
            }
            $turnovers[$gameID][] = $turnover;
        }
        
        return $turnovers;
    }
    
    static function getSteals($playerID)
    {
        global $db;
        $query = "SELECT turnover.gameID, turnover.time FROM turnover JOIN (SELECT * FROM steal WHERE playerID = ?) playersteals ON turnover.turnoverID = playersteals.turnoverID ORDER BY turnover.time";
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        
        $steals = [];
        while($row = $sh->fetch())
        {
            $steal = new stdClass();
            $steal->time = $row['time'];
            $gameID = $row['gameID'];
            if(!array_key_exists($gameID, $steals))
            {
                $steals[$gameID] = [];
            }
            $steals[$gameID][] = $steal;
        }
        
        return $steals;
    }
    
    static function getGamesByTeam($team)
    {
        global $db;
        $query = "SELECT gameID, IF(hometeam = ?, 1, 0) as home, homescore, awayscore, finalperiod from game WHERE hometeam = ? OR awayteam = ?";
        $sh = $db->prepare($query);
        $sh->execute(array($team, $team, $team));
        
        $games = [];
        while($row = $sh->fetch())
        {
            $game = new stdClass();
            $game->home = $row['home'];
            $game->gameID = $row['gameID'];
            $game->homescore = intval($row['homescore']);
            $game->awayscore = intval($row['awayscore']);
            $game->finalperiod = intval($row['finalperiod']);
            $games[] = $game;
        }
        
        return $games;
    }
    
    static function getGamesPlayedbyPlayer($playerID, $latest = 0)
    {
        global $db;
        
        if($latest > 0)
        {
            $query = "SELECT game.gameID, UNIX_TIMESTAMP(date) as date, hometeam, awayteam, home FROM game JOIN (SELECT DISTINCT(gameID), home FROM shift WHERE playerID = ?) gamesplayed ON game.gameID = gamesplayed.gameID ORDER BY date DESC LIMIT $latest;";
        }
        else
        {
            $query = "SELECT game.gameID, UNIX_TIMESTAMP(date) as date, hometeam, awayteam, home FROM game JOIN (SELECT DISTINCT(gameID), home FROM shift WHERE playerID = ?) gamesplayed ON game.gameID = gamesplayed.gameID ORDER BY date DESC;";
        }
        $sh = $db->prepare($query);
        $sh->execute(array($playerID));
        $games = [];
        while($row = $sh->fetch())
        {
            $game = new stdClass();
            $game->gameID = $row['gameID'];
            $game->hometeam = $row['hometeam'];
            $game->awayteam = $row['awayteam'];
            $game->date = $row['date'];
            $game->home = $row['home'] == 1 ? true: false;
            $games[$game->gameID] = $game;
        }
        return $games;
    }
    
    static function getGamesFromDate($date)
    {
        global $db;
        $query = "SELECT * FROM game WHERE date = FROM_UNIXTIME(?) ORDER BY gameID";
        $sh = $db->prepare($query);
        $sh->execute(array($date));
        $games = [];
        while($row = $sh->fetch())
        {
            $game = new stdClass();
            $game->gameID = $row['gameID'];
            $game->date = $row['date'];
            $game->hometeam = $row['hometeam'];
            $game->awayteam = $row['awayteam'];
            $game->homescore = $row['homescore'];
            $game->awayscore = $row['awayscore'];
            $game->finalperiod = $row['finalperiod'];
            $games[] = $game;
        }
        return $games;
    }
    
    static function getGames(){
        global $db;
        $query = "SELECT * FROM game ORDER BY gameID";
        $sh = $db->prepare($query);
        $sh->execute();
        $games = [];
        while($row = $sh->fetch())
        {
            $games[] =  $row['gameID'];
        }
        return $games;
    }
    
    static function getGame($gameID){
        global $db;
        $query = "SELECT * FROM game WHERE gameID = ?";
        $sh = $db->prepare($query);
        $sh->execute(array($gameID));
        $row = $sh->fetch();
        $game = new stdClass();
        $game->hometeam = $row['hometeam'];
        $game->awayteam = $row['awayteam'];
        $game->finalperiod = $row['finalperiod'];
        $game->awayscore = $row['awayscore'];
        $game->homescore = $row['homescore'];
        
        return $game;
    }
    
    static function updateGameScore($homescore, $awayscore, $finalperiod, $gameID)
    {
        global $db;
        $query = "UPDATE `game` SET `homescore`=:homescore,`awayscore`=:awayscore,`finalperiod`=:finalperiod WHERE `gameID` = :gameID;";
        $sh = $db->prepare($query);
        $sh->execute(array('homescore'=>$homescore, 'awayscore'=>$awayscore, 'finalperiod'=>$finalperiod, 'gameID'=>$gameID));
    }
    
    static function getCommonGames($players)
    {
        global $db;
        for($i = 0; $i < sizeof($players); $i++)
        {
            "SELECT gameID from game JOIN (SELECT DISTINCT(gameID) FROM shift WHERE playerID = ?);";
        }
    }
    
    static function prepareQuery($name, $query)
    {
        if(!isset($this->$name)) {
            $this->$name = $this->db->prepare($query);
        }
        return $this->$name;
    }
}