/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var playersearch= document.getElementById('player-search');

var linegraphconfig = {
    scales: {
      xAxes: [{
        type: 'time',
        time: {
          unit: 'day',
          'stepSize': '1',
          displayFormats: {
            'millisecond': 'MMM DD',
            'second': 'MMM DD',
            'minute': 'MMM DD',
            'hour': 'MMM DD',
            'day': 'MMM DD',
            'week': 'MMM DD',
            'month': 'MMM DD',
            'quarter': 'MMM DD',
            'year': 'MMM DD'
          }
        }
      }]
    }
};

$(document).ready(function()
{
    var playerID = $('#player-search-playerID').val();
    if(playerID)
    {
        show_player(playerID);
        show_trends_stats(playerID);
    }
    
    $('#player-picked').change(function()
    {
        location.href = '/trends.php?playerID='+this.value;
//        show_player(this.value);
//        show_trends_stats(this.value);
    });
});

function show_player(playerID)
{
    $.ajax({
        url: 'getplayer.php?playerID='+playerID, 
        success: function(result){
            var player = JSON.parse(result);
            $('#player-picked-photo').attr('src', '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+player.playerID+'.png');
            $('#player-picked-name').empty();
            $('#player-picked-name').append(player.firstname + ' ' + player.lastname);
            $('#trends-player-display-background').attr('src', '//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/'+player.team+'.svg');
            
            $('#team-picked').val(player.team).change();
        }
    });
}

function show_trends_stats(playerID)
{
    var overallstatstable = document.createElement('table');
    overallstatstable.className += 'table stats';
    var overallstatsheaderrow = document.createElement('tr');
    overallstatsheaderrow.className += 'table-header-row'
    var overallstatsheadernames = ['MPG','PPG','FG%','3FG%','FT%','AST','REB','BLK','STL','TO'];
    overallstatsheadernames.forEach(function(headername){
        var header = document.createElement('th');
        header.innerHTML = headername;
        overallstatsheaderrow.appendChild(header);
    });
    overallstatstable.appendChild(overallstatsheaderrow);
    
    var pergamestatstable = document.createElement('table');
    pergamestatstable.className += 'table stats';
    var headerrow = document.createElement('tr');
    var headernames = ['','','MIN','PTS','FGM','FGA','FG%','3PM','3PA','3FG%','FTM','FTA','FT%','AST','OREB','DREB','REB','BLK','STL','TO'];
    headernames.forEach(function(headername){
        var header = document.createElement('th');
        header.innerHTML = headername;
        headerrow.appendChild(header);
    });
    pergamestatstable.appendChild(headerrow);
    $.ajax({
        url: 'getdata2.php?playerID='+playerID, 
        success: function(result){
            var json = JSON.parse(result);
            
            overallstats = json[0];
            var totalms = overallstats['totalms'];
            var gamesplayed = overallstats['gamesplayed'];
            var points = overallstats['points'];
            var totalshots = overallstats['totalshots'];
            var shotsmade = overallstats['shotsmade'];
            var total3pointers = overallstats['total3pointers'];
            var _3pointersmade = overallstats['3pointersmade'];
            var freethrowsmade = overallstats['freethrowsmade'];
            var totalfreethrows = overallstats['totalfreethrows'];
            var totalassists = overallstats['totalassists'];
            var totalrebounds = overallstats['totalrebounds'];
            var totalblocks = overallstats['totalblocks'];
            var totalsteals = overallstats['totalsteals'];
            var totalturnovers = overallstats['totalturnovers'];
            
            var row = document.createElement('tr');
            
            var MPGcell = document.createElement('td');
            MPGcell.innerHTML = ((totalms/60000)/gamesplayed).toFixed(1);
            row.appendChild(MPGcell);
            
            var PPGcell = document.createElement('td');
            PPGcell.innerHTML = (points/gamesplayed).toFixed(1);
            row.appendChild(PPGcell);
            
            var FGPcell = document.createElement('td');
            FGPcell.innerHTML = ((shotsmade/totalshots) * 100).toFixed(1);
            row.appendChild(FGPcell);
            
            var _3FGPcell = document.createElement('td');
            var _3FGP = total3pointers == 0 ? 0 : ((_3pointersmade/total3pointers) * 100);
            _3FGPcell.innerHTML = _3FGP.toFixed(1);
            row.appendChild(_3FGPcell);
            
            var FTPcell = document.createElement('td');
            var FTP = totalfreethrows == 0 ? 0 : ((freethrowsmade/totalfreethrows) * 100);
            FTPcell.innerHTML = FTP.toFixed(1);
            row.appendChild(FTPcell);
            
            var APGcell = document.createElement('td');
            APGcell.innerHTML = (totalassists/gamesplayed).toFixed(1);
            row.appendChild(APGcell);
            
            var RPGcell = document.createElement('td');
            RPGcell.innerHTML = (totalrebounds/gamesplayed).toFixed(1);
            row.appendChild(RPGcell);
            
            var BPGcell = document.createElement('td');
            BPGcell.innerHTML = (totalblocks/gamesplayed).toFixed(1);
            row.appendChild(BPGcell);
            
            var SPGcell = document.createElement('td');
            SPGcell.innerHTML = (totalsteals/gamesplayed).toFixed(1);
            row.appendChild(SPGcell);
            
            var TOPGcell = document.createElement('td');
            TOPGcell.innerHTML = (totalturnovers/gamesplayed).toFixed(1);
            row.appendChild(TOPGcell);
            
            overallstatstable.appendChild(row);
            
            $('#trends-overall-statistics').empty();
            $('#trends-overall-statistics').append(overallstatstable);
            
            pergamestats = json[1];
            var games = []; var pointsbygame = []; var reboundsbygame = []; var assistsbygame = [];
            
            for (var key in pergamestats) {
                if (pergamestats.hasOwnProperty(key)) {
                    var game = pergamestats[key]['game'];
                    var seconds = game['date'];
                    var date = moment.unix(seconds).format("DD MMM YYYY");
                    games.push(date);
                    
                    var milliseconds = pergamestats[key]['totalms'];
                    
                    var points = pergamestats[key]['points'];
                    pointsbygame.push(points);
                    
                    var defrebounds = pergamestats[key]['defrebounds'];
                    var offrebounds = pergamestats[key]['offrebounds'];
                    reboundsbygame.push(defrebounds+offrebounds);
                    
                    var assists = pergamestats[key]['totalassists'];
                    assistsbygame.push(assists);
                    
                    var row = document.createElement('tr');
                    
                    var datecell = document.createElement('td');
                    datecell.innerHTML = date;
                    row.appendChild(datecell);
                    
                    var gamecell = document.createElement('td');
                    var isHome = game['home'];
                    var home = game['hometeam'];
                    var away = game['awayteam'];
                    var team = isHome ? home : away;
                    var oppteam = isHome ? away : home;
                    var vs = isHome ? 'vs' : '@';
                    gamecell.innerHTML = '<strong>'+team+'</strong> '+vs+' '+oppteam;
                    row.appendChild(gamecell);
                    
                    var minutescell = document.createElement('td');
                    minutescell.innerHTML = (milliseconds/60000).toFixed(1);
                    row.appendChild(minutescell);
                    
                    var pointscell = document.createElement('td');
                    pointscell.innerHTML = points;
                    row.appendChild(pointscell);
                    
                    var FGMcell = document.createElement('td');
                    var FGM = pergamestats[key]['shotsmade'];
                    FGMcell.innerHTML = FGM;
                    row.appendChild(FGMcell);
                    
                    var FGAcell = document.createElement('td');
                    var FGA = pergamestats[key]['totalshots'];
                    FGAcell.innerHTML = FGA;
                    row.appendChild(FGAcell);
                    
                    var FGPcell = document.createElement('td');
                    var FGP = FGA === 0 ? 0 : (FGM/FGA) * 100;
                    FGPcell.innerHTML = FGP.toFixed(1);
                    row.appendChild(FGPcell);
                    
                    var _3FGMcell = document.createElement('td');
                    var _3FGM = pergamestats[key]['_3pointersmade'];
                    _3FGMcell.innerHTML = _3FGM;
                    row.appendChild(_3FGMcell);
                    
                    var _3FGAcell = document.createElement('td');
                    var _3FGA = pergamestats[key]['total3pointers'];
                    _3FGAcell.innerHTML = _3FGA;
                    row.appendChild(_3FGAcell);
                    
                    var _3FGPcell = document.createElement('td');
                    var _3FGP = _3FGA === 0 ? 0 : (_3FGM/_3FGA) * 100;
                    _3FGPcell.innerHTML = _3FGP.toFixed(1);
                    row.appendChild(_3FGPcell);
                    
                    var FTMcell = document.createElement('td');
                    var FTM = pergamestats[key]['freethrowsmade'];
                    FTMcell.innerHTML = FTM;
                    row.appendChild(FTMcell);
                    
                    var FTAcell = document.createElement('td');
                    var FTA = pergamestats[key]['totalfreethrows'];
                    FTAcell.innerHTML = FTA;
                    row.appendChild(FTAcell);
                    
                    var FTPcell = document.createElement('td');
                    var FTP = FTA === 0 ? 0 : (FTM/FTA) * 100;
                    FTPcell.innerHTML = FTP.toFixed(1);
                    row.appendChild(FTPcell);
                    
                    var ASTcell = document.createElement('td');
                    ASTcell.innerHTML = assists;
                    row.appendChild(ASTcell);
                    
                    var OREBcell = document.createElement('td');
                    OREBcell.innerHTML = offrebounds;
                    row.appendChild(OREBcell);
                    
                    var DREBcell = document.createElement('td');
                    DREBcell.innerHTML = defrebounds;
                    row.appendChild(DREBcell);
                    
                    var REBcell = document.createElement('td');
                    REBcell.innerHTML = offrebounds+defrebounds;
                    row.appendChild(REBcell);
                    
                    var BLKcell = document.createElement('td');
                    BLKcell.innerHTML = pergamestats[key]['totalblocks'];
                    row.appendChild(BLKcell);
                    
                    var STLcell = document.createElement('td');
                    STLcell.innerHTML = pergamestats[key]['totalsteals'];
                    row.appendChild(STLcell);
                    
                    var TOcell = document.createElement('td');
                    TOcell.innerHTML = pergamestats[key]['totalturnovers'];
                    row.appendChild(TOcell);
                    
                    pergamestatstable.appendChild(row);
                }
            }
            
            $('#player-trending-statistics').empty();
            $('#player-trending-statistics').append(pergamestatstable);
            
            var ctx = document.getElementById('trends-canvas').getContext('2d');
            
            var myChart = new Chart(ctx, {
              type: 'line',
              data: {
                labels: games,
                datasets: [{
                  fill: false,
                  lineTension: 0,
                  label: 'points',
                  data: pointsbygame,
                  borderColor: "rgba(153,255,51,0.4)"
                }, {
                  fill: false,
                  lineTension: 0,
                  label: 'rebounds',
                  data: reboundsbygame,
                  borderColor: "rgba(255,153,0,0.4)"
                }, {
                  fill: false,
                  lineTension: 0,
                  label: 'assists',
                  data: assistsbygame,
                  borderColor: "rgba(255,102,102,0.4)"  
                }]
              },
              options: linegraphconfig
            });
        }
    });
}

//$(window).resize(function(){
//    if()
//    $('#trends-roster-search-label').hide()
//}
    