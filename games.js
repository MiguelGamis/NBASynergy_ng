/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#gameSelected').on('change', function(){
    //change active game in UI
    $('.game-score.active').removeClass('active');
    $('#game-score-'+this.value).addClass('active');
    
    updateGameInfo(this.value);
});

$('#selectedDate').on('change', function(){
    validateDateIncrementors(this.value);
    $('#date-nav-games').text('');
    
    var date = moment(this.value);
    $('#date-nav-date').text(date.format('dddd, MMM DD'));
    
    getGames();
});

$('#embeddingDatePicker')
.datepicker({
    format: 'mm/dd/yyyy',
    startDate: '10/25/2016',
    endDate: '04/12/2017',
})
.on('changeDate', function(e) {
    // Set the value for the date input
    var newDate = $("#embeddingDatePicker").datepicker('getFormattedDate');
    //if(newDate) $("#embeddingDatePicker").datepicker('update', $("#selectedDate").val());
    //else 
    $("#selectedDate").val(newDate).trigger('change');
})

$('#date-next').on("click", function(){
    var selectedDate = document.getElementById('selectedDate');
    var nextDate = moment(selectedDate.value).add('days', 1);
    $("#embeddingDatePicker").datepicker('update', nextDate.format('MM/DD/YYYY')).trigger('changeDate');
});

$('#date-prev').on("click", function(){
    var selectedDate = document.getElementById('selectedDate');
    var previousDate = moment(selectedDate.value).add('days', -1);
    $("#embeddingDatePicker").datepicker('update', previousDate.format('MM/DD/YYYY')).trigger('changeDate');
});

$('#home-tab-btn').on('click', function(event){ openBoxScore(event, 'home-tab') });
$('#away-tab-btn').on('click', function(event){ openBoxScore(event, 'away-tab') });

function validateDateIncrementors(newDate)
{
    newMoment = moment(newDate);
    if(newMoment.isSame(moment('10/25/2016'))) { $('#date-prev').css('display','none'); }
    else { $('#date-prev').css('display', 'block'); }
    if(newMoment.isSame(moment('04/12/2017'))) { $('#date-next').css('display','none'); }
    else { $('#date-next').css('display', 'block'); }
}

var datestr = '';
var today = moment();
var lastRegularSeasonDay = moment('4/12/2017');
if(today.isAfter(lastRegularSeasonDay)) datestr = lastRegularSeasonDay.format('MM/DD/YYYY');
else datestr = today.format('MM/DD/YYYY');
$("#embeddingDatePicker").datepicker('update', datestr).trigger('changeDate');

$('#date-dropdown').on('click', showClass);

function showClass() {
    document.getElementById("calendar-dropdown").classList.toggle("show");
}

var getGamesCall = null;
function getGames()
{
    if(getGamesCall) { getGamesCall.abort(); }

    var date = $("#selectedDate").val();
    getGamesCall = $.ajax({url:'getgames.php?date='+date, success: function(result){
        var games = JSON.parse(result);

        if(games.length == 0)
        {
            $('#gameSelected').val(null).trigger('change');
            $('#games-container').html("<div class='notification-message'><div class='no-data-label'>No games scheduled</div></div>");
            return;
        }

        var gamesContainer = document.getElementById('games-container');
        gamesContainer.innerHTML = '';
        for(var i = 0; i < games.length; i++)
        {
            var game = games[i];
            var gameend = '';
            if(game.finalperiod == 4)
                gameend = 'Final';
            else if(game.finalperiod == 5)
                gameend = 'Final OT';
            else if(game.finalperiod > 5)
                gameend = 'Final '+(game.finalperiod-4)+'OT';
            var content = "<div class='game-score-wrapper'>";
            content += "<div class='game-score' id='game-score-"+game.gameID+"'>";
            content += "<div class='game-score-inner'>";
            content += "<div class='game-score-logo'><img src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+game.awayteam+".svg' class='logo-small'></img></div>";
            content += "<div class='game-score-team-score'><div class='game-score-team'>"+game.awayteam+"</div><div class='game-score-score'>"+game.awayscore+"</div></div>";
            content += "<div class='game-score-final'>"+gameend+"</div>";
            content += "<div class='game-score-team-score'><div class='game-score-team'>"+game.hometeam+"</div><div class='game-score-score'>"+game.homescore+"</div></div>";
            content += "<div class='game-score-logo'><img src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+game.hometeam+".svg' class='logo-small'></img></div>";
            content += "</div>";
            content += "</div>";
//            var content = "<div class='game-score' id='game-score-"+game.gameID+"'>\n\
//                <table class='table'>\n\
//                <tr><td><img class='game-score-logo' src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+game.awayteam+".svg'></img><span class='game-score-team'>"+game.awayteam+"</span></td><td class='game-score-score'>"+game.awayscore+"</td></tr>\n\
//                <tr><td><img class='game-score-logo' src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+game.hometeam+".svg'></img><span class='game-score-team'>"+game.hometeam+"</span></td><td class='game-score-score'>"+game.homescore+"</td></tr>\n\
//                </table>";
//            content += "<span id='game-score-final'>"+gameend+"</span>";
//            content += "</div>";
            gamesContainer.innerHTML += content;
        }

        //Add click event to all games from date
        $('.game-score').click(function(){
            var gameID = this.id.replace('game-score-','');
            if($('#gameSelected').val() != gameID)
            {   
                //change hidden input value
                $('#gameSelected').val(gameID).trigger('change');
            }
        });
        
        //Game selected is the first game from date
        var gameID = games[0].gameID;
        if($('#gameSelected').val() != gameID)
        {
            $('#gameSelected').val(gameID).trigger('change');
        }
        
        //Show number of games in calendar button
        var _games = 'game';
        if(games.length > 1) _games = 'games' ;
        $('#date-nav-games').text(games.length + ' ' + _games);
    }});
}

function updateGameInfo(gameID)
{
    getGameSummary(gameID);
    getBoxScores(gameID);
}

var getGameSummaryCall = null;
function getGameSummary(gameID)
{
    if(getGameSummaryCall) { getGameSummaryCall.abort(); }
    
    if(!gameID)
    {
        var gameSummaryContainer = document.getElementById('game-summary-container');
        gameSummaryContainer.innerHTML = "<div class='notification-message'><div class='no-data-label'>Select a game using the left navigation</div></div>";
        return;
    }
    var gameSummaryContainer = document.getElementById('game-summary-container');
    gameSummaryContainer.innerHTML = ""; //<div class='loader'></div>";
    getGameSummaryCall = $.ajax({url:'getgamesummary.php?gameID='+gameID,
        success: function(result){
            var gamesummary = JSON.parse(result);
            var game = gamesummary[0];
            var content = "<table class='table'>";

            content += "<tr><th></th>";
            for(var i = 0; i < gamesummary[3].length; i++)
            {
                var period = i + 1;
                var periodtype = 'Q';
                if(period > 4)
                {
                    periodtype = 'OT';
                    period -= 4;
                }
                content += "<th>"+periodtype+period+"</th>";
            }
            content += "<th>Total</th></tr>";

            content += "<tr><td><img src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+gamesummary[0].awayteam+".svg' class='logo-small'></img>"+gamesummary[1].city+' '+gamesummary[1].teamname+"</td>";
            for(var i = 0; i < gamesummary[3].length; i++)
            {
                content += "<td>"+gamesummary[3][i]+"</td>";
            }
            content += "<td>"+game.awayscore+"</td></tr>";

            content += "<tr><td><img src='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/"+gamesummary[0].hometeam+".svg' class='logo-small'></img>"+gamesummary[2].city+' '+gamesummary[2].teamname+"</td>";
            for(var i = 0; i < gamesummary[4].length; i++)
            {
                content += "<td>"+gamesummary[4][i]+"</td>";
            }
            content += "<td>"+game.homescore+"</td></tr>";

            content += "</table>";
            
            var gameSummaryContainer = document.getElementById('game-summary-container');
            gameSummaryContainer.innerHTML = content;
        }
    });
}

var getBoxScoresCall = null;
function getBoxScores(gameID)
{
    if(getBoxScoresCall) { getBoxScoresCall.abort(); }
    
    if(!gameID)
    {
        var awaytabbtn = document.getElementById('away-tab-btn').innerHTML = '';
        var hometabbtn = document.getElementById('home-tab-btn').innerHTML = '';
        var awaytab = document.getElementById('away-tab').innerHTML = '';
        var hometab = document.getElementById('away-tab').innerHTML = '';
        var gameboxscorescontent = document.getElementById('game-boxscores-content');
        gameboxscorescontent.classList.add('hide');
        return;
    }
    
    var loader = document.getElementById('game-boxscores-loader');
    loader.classList.remove('hide'); loader.classList.add('show');
    
    var gameboxscorescontent = document.getElementById('game-boxscores-content');
    gameboxscorescontent.classList.add('hide');

    getBoxScoresCall = $.ajax({url:'getgameboxscore.php?gameID='+gameID,
        success: function(result){
            var boxscorelines = JSON.parse(result);

            var gamedetails = boxscorelines[0];
            var awayboxscorelines = boxscorelines[1];
            var homeboxscorelines = boxscorelines[2];

            var awaytabbtn = document.getElementById('away-tab-btn');
            awaytabbtn.innerHTML = gamedetails.awayteam;
            var hometabbtn = document.getElementById('home-tab-btn');
            hometabbtn.innerHTML = gamedetails.hometeam;

            var awayboxscore = boxScoreLinesToTable(awayboxscorelines);
            var awaytab = document.getElementById('away-tab');
            awaytab.innerHTML = awayboxscore;

            var homeboxscore = boxScoreLinesToTable(homeboxscorelines);
            var hometab = document.getElementById('home-tab');
            hometab.innerHTML = homeboxscore;

            loader.classList.remove('show'); loader.classList.add('hide');
            gameboxscorescontent.classList.remove('hide');

            // Get the element with id="home-tab-btn" and click on it
            document.getElementById("home-tab-btn").click();
        }
    })
}

var headernames = ['Player','MIN','PTS','FGM','FGA','FG%','3PM','3PA','3FG%','FTM','FTA','FT%','AST','OREB','DREB','REB','BLK','STL','TO'];

function boxScoreLinesToTable(boxscorelines)
{
    var boxscoretable = "<table class='table table-condensed stats'>";
    boxscoretable+= '<tr>';
    headernames.forEach(function(headername){
        boxscoretable += "<th>"+headername+"</th>";
    });
    boxscoretable += '</tr>';

    var ttotalms = 0;
    var tPTS = 0;
    var tFGM = 0;
    var tFGA = 0;
    var t_3FGM = 0;
    var t_3FGA = 0;
    var tFTM = 0;
    var tFTA = 0;
    var tAST = 0;
    var tOREB = 0;
    var tDREB = 0;
    var tREB = 0;
    var tBLK = 0;
    var tSTL = 0;
    var tTO = 0;

    for(var i = 0; i < boxscorelines.length; i++)
    {
        var boxscoreline = boxscorelines[i];

        var player = boxscoreline.player;
        var playerimg = "<img src='//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/"+player.playerID+".png' alt='"+player.firstname+' '+player.lastname+' '+'image'+"'/>";
        var playername = '<div><p>'+player.firstname+'</p><p>'+player.lastname+'</p></div>';
        var totalms = boxscoreline['totalms'];
        var MIN = (totalms/60000).toFixed(1);
        var PTS = boxscoreline['PTS'];
        var FGM = boxscoreline['FGM'];
        var FGA = boxscoreline['FGA'];
        var FGP = FGA > 0 ? (FGM/FGA) * 100 : 0;
        FGP = FGP.toFixed(1);
        var _3FGM = boxscoreline['_3FGM'];
        var _3FGA = boxscoreline['_3FGA'];
        var _3FGP = _3FGA > 0 ? (_3FGM/_3FGA) * 100 : 0;
        _3FGP = _3FGP.toFixed(1);
        var FTM = boxscoreline['FTM'];
        var FTA = boxscoreline['FTA'];
        var FTP = FTA > 0 ? (FTM/FTA) * 100 : 0;
        FTP = FTP.toFixed(1);
        var AST = boxscoreline['AST'];
        var OREB = boxscoreline['OREB'];
        var DREB = boxscoreline['DREB'];
        var REB = OREB + DREB;
        var BLK = boxscoreline['BLK'];
        var STL = boxscoreline['STL'];
        var TO = boxscoreline['TO'];

        ttotalms += totalms;
        tPTS += PTS;
        tFGM += FGM;
        tFGA += FGA;
        t_3FGM += _3FGM;
        t_3FGA += _3FGA;
        tFTM += FTM;
        tFTA += FTA;
        tAST += AST;
        tOREB += OREB;
        tDREB += DREB;
        tREB += REB;
        tBLK += BLK;
        tSTL += STL;
        tTO += TO;

        boxscoretable += "<tr><td class='playercell' align='left'>"+playerimg+playername+"</td><td>"+MIN+"</td><td>"+PTS+"</td><td>"+FGM+"</td><td>"+FGA+"</td><td>"+FGP+"</td><td>"+_3FGM+"</td><td>"+_3FGA+"</td><td>"+_3FGP+"</td><td>"+FTM+"</td><td>"+FTA+"</td><td>"+FTP+"</td>\n\
                                <td>"+AST+"</td><td>"+OREB+"</td><td>"+DREB+"</td><td>"+REB+"</td><td>"+BLK+"</td><td>"+STL+"</td><td>"+TO+"</td></tr>";
    }

    var tMIN = (ttotalms/60000).toFixed(1);

    var tFGP = tFGA > 0 ? (tFGM/tFGA) * 100 : 0;
    tFGP = tFGP.toFixed(1);

    var t_3FGP = t_3FGA > 0 ? (t_3FGM/t_3FGA) * 100 : 0;
    t_3FGP = t_3FGP.toFixed(1);

    var tFTP = tFTA > 0 ? (tFTM/tFTA) * 100 : 0;
    tFTP = tFTP.toFixed(1);

    boxscoretable += "<tr id='game-boxscore-totals'><td>Totals</td><td>"+tMIN+"</td><td>"+tPTS+"</td><td>"+tFGM+"</td><td>"+tFGA+"</td><td>"+tFGP+"</td><td>"+t_3FGM+"</td><td>"+t_3FGA+"</td><td>"+t_3FGP+"</td><td>"+tFTM+"</td><td>"+tFTA+"</td><td>"+tFTP+"</td>\n\
                                <td>"+tAST+"</td><td>"+tOREB+"</td><td>"+tDREB+"</td><td>"+tREB+"</td><td>"+tBLK+"</td><td>"+tSTL+"</td><td>"+tTO+"</td></tr>";

    return boxscoretable;
}

function openBoxScore(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}

//HACK: hardcoded list of classes in datepicker for detecting if click is inside or outside datepicker
var datepickingclass = ['dropbtn','day','dow','prev','next', 'datepicker-switch', 'month', 'year', 'datepicker'];
// Close the dropdown menu if the user clicks outside of it
window.onclick = function(event) {
    if(datepickingclass.indexOf(event.target.className) == -1)
    {
        var openDropdown = document.getElementById('calendar-dropdown');
        if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
        }
    }
}

$('#calendar-dropdown').click(function(e) {
    e.stopPropagation();
});

//$(window).resize(function(){
//    if($(window).outerWidth(true) > 780){
//        $('#games-container').show();
//        $('#games-navigation').removeClass('gameInfoToTheRight');
//    }else{
//        $('#games-container').hide();
//        $('#games-navigation').addClass('gameInfoToTheRight');
//    }
//});