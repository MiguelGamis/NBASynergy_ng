<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once('queries.php');

$conferences = [
         'Western Conference' => [
            new Team('DAL', 'Dallas', 'Mavericks'),
            new Team('DEN', 'Denver', 'Nuggets'),
            new Team('GSW', 'Golden State', 'Warriors'),
            new Team('HOU', 'Houston', 'Rockets'),
            new Team('LAC', 'Los Angeles', 'Clippers'),
            new Team('LAL', 'Los Angeles', 'Lakers'),
            new Team('MEM', 'Memphis', 'Grizzlies'),
            new Team('MIN', 'Minnesota', 'Timberwolves'),
            new Team('NOP', 'New Orleans', 'Pelicans'),
            new Team('OKC', 'Oklahoma City', 'Thunder'),
            new Team('PHX', 'Phoenix', 'Suns'),
            new Team('POR', 'Portland', 'Trail Blazers'),
            new Team('SAC', 'Sacramento', 'Kings'),
            new Team('SAS', 'San Antonio', 'Spurs'),
            new Team('UTA', 'Utah', 'Jazz')
        ],
        'Eastern Conference' => [
            new Team('ATL', 'Atlanta', 'Hawks'),
            new Team('BKN', 'Brooklyn', 'Nets'),
            new Team('BOS', 'Boston', 'Celtics'),
            new Team('CHA', 'Charlotte', 'Hornets'),
            new Team('CHI', 'Chicago', 'Bulls'),
            new Team('CLE', 'Cleveland', 'Cavaliers'),
            new Team('DET', 'Detroit', 'Pistons'),
            new Team('IND', 'Indiana', 'Pacers'),
            new Team('MIA', 'Miami', 'Heat'),
            new Team('MIL', 'Milwaukee', 'Bucks'),
            new Team('NYK', 'New York', 'Knicks'),
            new Team('ORL', 'Orlando', 'Magic'),
            new Team('PHI', 'Philadelphia', '76ers'),
            new Team('TOR', 'Toronto', 'Raptors'),
            new Team('WAS', 'Washington', 'Wizards')
        ]
];

$teams = [
        new Team('ATL', 'Atlanta', 'Hawks'),
        new Team('BKN', 'Brooklyn', 'Nets'),
        new Team('BOS', 'Boston', 'Celtics'),
        new Team('CHA', 'Charlotte', 'Hornets'),
        new Team('CHI', 'Chicago', 'Bulls'),
        new Team('CLE', 'Cleveland', 'Cavaliers'),
        new Team('DAL', 'Dallas', 'Mavericks'),
        new Team('DEN', 'Denver', 'Nuggets'),
        new Team('DET', 'Detroit', 'Pistons'),
        new Team('GSW', 'Golden State', 'Warriors'),
        new Team('HOU', 'Houston', 'Rockets'),
        new Team('IND', 'Indiana', 'Pacers'),
        new Team('LAC', 'Los Angeles', 'Clippers'),
        new Team('LAL', 'Los Angeles', 'Lakers'),
        new Team('MEM', 'Memphis', 'Grizzlies'),
        new Team('MIA', 'Miami', 'Heat'),
        new Team('MIL', 'Milwaukee', 'Bucks'),
        new Team('MIN', 'Minnesota', 'Timberwolves'),
        new Team('NOP', 'New Orleans', 'Pelicans'),
        new Team('NYK', 'New York', 'Knicks'),
        new Team('OKC', 'Oklahoma City', 'Thunder'),
        new Team('ORL', 'Orlando', 'Magic'),
        new Team('PHI', 'Philadelphia', '76ers'),
        new Team('PHX', 'Phoenix', 'Suns'),
        new Team('POR', 'Portland', 'Trail Blazers'),
        new Team('SAC', 'Sacramento', 'Kings'),
        new Team('SAS', 'San Antonio', 'Spurs'),
        new Team('TOR', 'Toronto', 'Raptors'),
        new Team('UTA', 'Utah', 'Jazz'),
        new Team('WAS', 'Washington', 'Wizards')
];

function show_player_search($playersearch)
{
    $content = "<input type='text' id='$playersearch' placeholder='Active Player Search' autocomplete='off' class='form-control'/>";
    
    return $content;
}

function player_select_one_column($teaminput, $playerinput){
    global $teams;
    
    $content = "<div id='trends-roster-search-label' style='display: inline-block'>Search by roster</div>";
    $content .= "<div>";
    $content .= "<select id='$teaminput' class='form-control' autocomplete='off'>";
    $content.=  "<option>Select a team</option>";
    foreach($teams as $team)
    {
        $content.=  "<option value='$team->abbrev'>$team->city $team->teamname</option>";
    }
    $content .= "</select>";
    $content .= "</div>";
    
    $content .= "<div>";
    $content .= "<select id='$playerinput' class='form-control'></select>";
    $content .= "</div>";
    return $content;
}

function player_select($teampickedid, $playerpickedid, $type = null, $sideways = false){
    global $conferences;

    $style = '';
    if($sideways) $style = "style = 'display: inline-block;'";
    
    $content = "<div id='multi-player-picker'>";

    $content .= "<div class='dropdown' $style>
      <button id='team-picker-button' class='btn btn-default dropdown-toggle' type='button' id='menu1' data-toggle='dropdown'>
        Choose a team
        <span class='caret'></span>
      </button>";
    $content .= "<div id='player-picker-team-picker' class='dropdown-menu bs-dropdown-content'>";
    foreach($conferences as $conferencename => $conference)
    {
        $content .= "<div class='col-sm-6'><ul class='multi-column-dropdown'><h4>$conferencename</h4>";
        foreach($conference as $team)
        {
            $content.= "<li id='player-picker-team-picker-$team->abbrev' class='bs-picker-team'><img class='bs-picker-team-logo' src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/$team->abbrev.svg'/><div class='bs-picker-team-name'>$team->city $team->teamname</div></li>";
        }
        $content .= "</ul></div>";
    }
    $content .= "</div></div>";
    
    $content .= "<div class='dropdown' $style>
                    <button id='player-picker-button' class='btn btn-default dropdown-toggle' type='button' id='menu1' data-toggle='dropdown'>
                        Choose a player
                        <span class='caret'></span>
                    </button>
                    <div class='dropdown-menu bs-dropdown-content' role='menu' aria-labelledby='menu1'>
                        <div class='col-sm-4'><ul id='player-picker-col-1' class='multi-column-dropdown'></ul></div>
                        <div class='col-sm-4'><ul id='player-picker-col-2' class='multi-column-dropdown'></ul></div>
                        <div class='col-sm-4'><ul id='player-picker-col-3' class='multi-column-dropdown'></ul></div>
                    </div>
                </div>";
    
    $content .= "</div>";
    
    $content .= "<script src='teamplayerpicker.js'></script>";;
    if($type == 'radio')
        $content .= "<script>playerpickersetup('$teampickedid', '$playerpickedid');</script>";
    else
        $content .= "<script>playerpickersetup2('$teampickedid', '$playerpickedid');</script>";
    return $content;
}

function multi_player_select($teampickedid, $playerspickedid){
    global $conferences;
    //$teamcols = array_chunk($teams, 10);
    
    $content = "<div id='multi-player-picker'>";

    $content .= "<div class='dropdown'>
      <button class='btn btn-default dropdown-toggle' type='button' id='menu1' data-toggle='dropdown'>
         Choose a team
         <span class='caret'></span>
      </button>";
    $content .= "<div id='multi-player-picker-team-picker' class='dropdown-menu bs-dropdown-content'>";
    foreach($conferences as $conferencename => $conference)
    {
        $content .= "<div class='col-sm-6'><ul class='multi-column-dropdown'><h4>$conferencename</h4>";
        foreach($conference as $team)
        {
            $content.= "<li id='multi-player-picker-team-picker-$team->abbrev' class='bs-picker-team'><img class='bs-picker-team-logo' src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/$team->abbrev.svg'/><div class='bs-picker-team-name'>$team->city $team->teamname</div></li>";
        }
        $content .= "</ul></div>";
    }
    $content .= "</div></div>";
    
    $content .= "<div class='dropdown'>
                    <button class='btn btn-default dropdown-toggle' type='button' id='menu1' data-toggle='dropdown'>
                       Choose a player
                       <span class='caret'></span>
                    </button>
                    <div id='multi-player-picker-player-picker' class='dropdown-menu bs-dropdown-content' role='menu' aria-labelledby='menu1'>
                        <div class='col-sm-4'><ul id='multi-player-picker-col-1' class='multi-column-dropdown'></ul></div>
                        <div class='col-sm-4'><ul id='multi-player-picker-col-2' class='multi-column-dropdown'></ul></div>
                        <div class='col-sm-4'><ul id='multi-player-picker-col-3' class='multi-column-dropdown'></ul></div>
                    </div>
                </div>";
    
    $content .= "</div>";
    
    $content .= "<script src='teamplayerpicker.js'></script>";
    $content .= "<script>multiplayerpickersetup('$teampickedid','$playerspickedid');</script>";
    
    return $content;
}