<?php

function render_page()
{
    header("Content-Type: text/html; charset=utf-8");
    include("template.php");
    exit();
}

function get_page_title()
{
    global $title;
    echo $title;
}

function get_page_headers()
{
    /*<!--    <script src='https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js'></script>
    <script src='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js'></script>
    <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'>
    <script src='//code.jquery.com/jquery-1.10.2.js'></script>
    <script src='//code.jquery.com/ui/1.11.1/jquery-ui.js'></script>
    <link rel='stylesheet' type='text/css' href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css' />-->
    <!--    <link rel='stylesheet' href='http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css'>-->
    */
    
    $headers = "<link rel='stylesheet' type='text/css' href='style/style.css'>
    <script src='coreui/jquery.min.js'></script>
    <script src='coreui/jquery-ui.js'></script>
    <link rel='stylesheet' type='text/css' href='style/jquery-ui.css' />
    <script src='coreui/bootstrap.min.js'></script>
    <script src='coreui/moment.js'></script>
    <link rel='stylesheet' href='style/bootstrap.min.css' />
    <link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.min.css' />
    <link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker3.min.css' />
    <script src='//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.min.js'></script>
    <link rel='stylesheet' href='style/font-awesome-4.7.0/css/font-awesome.min.css'/>
    <link rel='stylesheet' href='//cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css'/>
    <script src='//cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js'></script>";
    echo $headers;
}

function get_page_scripts()
{
    $scripts = "<script src='page.js'></script><script src='inc/fragmentfuncs.js'></script>";
    echo $scripts;
}

function get_page_content()
{
    global $content;
    echo $content;
}

function get_page_menu()
{
    $menu = "<div id='menu-site-logo'><img id='menu-logo' src='images/statball.png'/><a href='/games.php' id='menu-sitename'>NBA &Sigma;ynergy</a></div>";
    $menu .= "<ul>
                <li class='menu-option'><a href='.'>Games</a></li>
                <li class='menu-option'><a href='matchups.php'>Matchups</a></li>
                <li class='menu-option'><a href='lineups.php'>Lineups</a></li>
                <li class='menu-option'><a href='trends.php'>Trends</a></li>
                <li class='menu-option'><a href='#'>Contact</a></li>
                <li id='menu-dropdown' class='dropdown'>
                    <a href='#' class='dropdown-toggle' data-toggle='dropdown'>Games<span class='caret'></span></a>
                    <div class='dropdown-menu'>
                        <div><a href='.'>Games</a></div>
                        <div><a href='matchups.php'>Matchups</a></div>
                        <div><a href='lineups.php'>Lineups</a></div>
                        <div><a href='trends.php'>Trends</a></div>
                        <div><a href='#'>Contact</a></div>
                    </div>
                </li>
            </ul>";
    echo $menu;
}