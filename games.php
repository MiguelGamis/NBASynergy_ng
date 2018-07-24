<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once("inc/common.php");

$title .= " | Games";

$content .= "
<div id='games-navigation'>
    <div id='season-picker'>
        <div class='tab'>
            <button id='season1' class='tablinks'>2016-2017</button>
            <button id='season2' class='tablinks'>2017-2018</button>
        </div>
    </div>
    <div id='date-bar'>
        <div id='date-nav'>
            <div id='date-prev'>
                <i class='fa fa-caret-left fa-2x'></i>
            </div>
            <div class='dropdown' id='date-dropdown'>
                <div class='dropbtn'>
                    <i class='fa fa-calendar'></i>
                    <span id='date-nav-date'></span>
                    <span id='date-nav-games'></span>
                </div>
                <div id='calendar-dropdown' class='dropdown-content'>
                    <div id='embeddingDatePicker'></div>
                </div>
            </div>
            <div id='date-next'>
                <i class='fa fa-caret-right fa-2x'></i>
            </div>
        </div>
    </div>
    <div id='games-container'/>
</div>
<div id='gameInfo'>
    <div id='game-summary-container'></div>
    <div id='game-boxscores-container'>
        <div id='game-boxscores-loader' class='loader hide'></div>
        <div id='game-boxscores-content' class='hide'>
            <div class='tab'>
                <button id='away-tab-btn' class='tablinks'></button>
                <button id='home-tab-btn' class='tablinks'></button>
            </div>
            <div id='away-tab' class='tabcontent'></div>
            <div id='home-tab' class='tabcontent'></div>
        </div>
    </div>
</div>

<input type='hidden' id='selectedDate' autocomplete='off'/>
<input type='hidden' id='gameSelected' autocomplete='off'/>
<script src='games.js'></script>";

render_page();
