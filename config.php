<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

try{
    //$db = new PDO('mysql:host=localhost;dbname=id1130978_nbasynergy;charset=utf8', 'id1130978_miguelgamis', 'nbasynergy');
    $db = new PDO('mysql:host=localhost;dbname=nbasynergy;charset=utf8', 'root', '');
} catch (Exception $ex) {
    echo $ex->getMessage();
}

if($db){
    //echo "<strong>Successfully connected</strong>";
}
 else {
     die("<strong>Error</strong> Could not connect to the database");
}