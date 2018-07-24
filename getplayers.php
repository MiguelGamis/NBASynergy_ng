<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("queries.php");

if(isset($_GET['team']))
{
    $team = $_GET['team'];

    $players = DataManager::getPlayersFromTeam($team);

    echo json_encode($players);
}