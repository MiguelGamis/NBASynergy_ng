<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("inc/common.php");
require_once('classes.php');

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

$title .= " | Lineups";

$content .= "<div id='lineups-options'>";
$content .= "<div id='lineups-team-select'>";
$content .= "<form id='team-picker-form' action='lineups.php' method='get'>";
$content .= "<select id='team-picker' name='team' class='form-control' autocomplete='off'>";
$content.=  "<option>Select a team</option>";
foreach($teams as $team)
{
    $content.=  "<option value='$team->abbrev'>$team->city $team->teamname</option>";
}
$content .= "</select>";
$content .= "</form>";
$content .= "</div>";
$content .= "</div>";

$content .= "<div id='lineups-container'></div>";

$content .= "<script src='lineups.js'></script>";

render_page();