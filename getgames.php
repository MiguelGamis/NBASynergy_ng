<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("queries.php");

if(isset($_GET['date']))
{
    $datestr = $_GET['date'];
    
    $date = strtotime($datestr.' + 17 hours');

    $games = DataManager::getGamesFromDate($date);
    
    echo json_encode($games);
}