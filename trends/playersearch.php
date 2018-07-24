<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../queries.php");

if (isset($_REQUEST['q'])) {
    $q=$_REQUEST["q"]; 
    $query="SELECT playerID, firstname, lastname FROM player WHERE firstname LIKE :q OR lastname LIKE :q";
    $sh = $db->prepare($query);
    $result = $sh->execute(array('q'=>"$q%"));

    $json=[];

    while($row = $sh->fetch()) {
      $json[] = array (
            'label' => $row['firstname'].' '.$row['lastname'],
            'value' => $row['firstname'].' '.$row['lastname'],
            'id' => $row['playerID']
        );
    }

    echo json_encode($json);
}

?>