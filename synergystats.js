/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$('#team-picked').change(function(){
    var logo = document.getElementById('team-picked-logo');
    var teampicked = document.getElementById('team-picked');
    logo.src ='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/'+teampicked.value+'.svg';
    var name = document.getElementById('team-picked-name');
    name.textContent = document.getElementById('player-picker-team-picker-'+teampicked.value).textContent;
    updatematchuptype();
});

$('#matchup-team-picked').change(function(){
    var logo = document.getElementById('matchup-team-picked-logo');
    var matchupteampicked = document.getElementById('matchup-team-picked');
    logo.src ='//i.cdn.turner.com/nba/nba/assets/logos/teams/secondary/web/'+matchupteampicked.value+'.svg';
    var name = document.getElementById('matchup-team-picked-name');
    name.textContent = document.getElementById('multi-player-picker-team-picker-'+matchupteampicked.value).textContent;
    updatematchuptype();
});

function updatematchuptype()
{
    var teampicked = document.getElementById('team-picked');
    var matchupteampicked = document.getElementById('matchup-team-picked');
    if(!teampicked.value || !matchupteampicked.value)
    {
        return;
    }
    if(teampicked.value == matchupteampicked.value)
    {
        $('#matchup-vs').val(0);
        $('#matchup-icon').attr('src','images/plus-icon.png');
    }
    else
    {
        $('#matchup-vs').val(1);
        $('#matchup-icon').attr('src','images/vs-icon.png');
    }
}

$('#matchup-toggle-button').on('click', function (){
    var vs = document.getElementById('matchup-vs');
    vs.value ^= 1;
    if(vs.value==1)
    {
        $('#matchup-icon').attr('src','images/vs-icon.png');
    }
    else
    {
        $('#matchup-icon').attr('src','images/plus-icon.png');
    }
});

$('#player-picked').change(function(){
    var playerID = $('#player-picked').val();
    $.ajax({url:'getplayername.php?playerID='+playerID,
        success:function(response){
//            var playerprofile = document.createElement('div');
//            var playerphoto = document.createElement('img');
//            playerphoto.src = '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+playerID+'.png';
//            var playerprofile.textContent = response;
//            playerprofile.appendChild()
            $('#player-picked-photo').attr('src', '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+playerID+'.png');
            $('#player-picked-name').text(response);
    }});
});

$('#compute-button').on('click', function(){
    synergize();
});

function synergize(){
    var teamID = $('#team-picked').val();
    var matchupteamID = $('#matchup-team-picked').val();
    
    var vs = $('#matchup-vs').val();
    
    var playerID = $('#player-picked').val();
    var matchupplayers = [];
    $('.multi-player-picker-checkbox:checked').each(function(){
        matchupplayers.push($(this).val());
    });
    if(playerID === null || matchupplayers.length === 0 || vs === null || matchupteamID === null || teamID === null)
    {
        document.location.hash = '';
        return;
    }
    else if(matchupplayers.indexOf(playerID) > -1)
    {
        alert('Cannot have the same player on both sides of a matchup');
        return;
    }
    document.location.hash = '?teamID='+teamID+'&playerID='+playerID+'&matchupteamID='+matchupteamID+'&matchupplayers='+matchupplayers.join(",");

    var matchupargs = "";
    matchupplayers.forEach(function(playerID, index){ matchupargs += '&matchup'+(index+1)+'='+playerID; });

    //alert('getdata.php?playerID='+playerID+'&vs='+vs.toString()+matchupargs);

    $('#customcontent').html("<div class='loader'></div>");

    $.ajax({url:'getdata.php?playerID='+playerID+'&vs='+vs.toString()+matchupargs, success: function(result){
        var json = JSON.parse(result);
        var customdata = document.createElement('div');
        customdata.className += 'animate-bottom';
        customdata.innerHTML = "<div id='stats-wrapper' style='float: none'>"+json[0]+"</div>";
        
        var shottypechartsdiv = document.createElement("div");
        shottypechartsdiv.id = 'shottype-charts';
        shottypechartsdiv.style = 'width: 100%; height: 200px';
        
        var shottypechartwithoutdiv = document.createElement("div");
        var shottypechartwithdiv = document.createElement("div");
            
        shottypechartsdiv.appendChild(shottypechartwithoutdiv);
        shottypechartsdiv.appendChild(shottypechartwithdiv);
        customdata.appendChild(shottypechartsdiv);
        
        shottypechartwithoutdiv.style = "float:left; width: 300px; height: 200px;";
        var withoutlabel = document.createElement("p");
        withoutlabel.innerHTML = 'Without';
        shottypechartwithoutdiv.appendChild(withoutlabel);
        if(json[2].length > 0)
        {
            shottypechartwithoutdiv.id = 'shottype-chart-without-div';
            var shottypecanvaswithout = document.createElement("canvas");
            shottypecanvaswithout.id = "shottype-without";
            shottypechartwithoutdiv.appendChild(shottypecanvaswithout);
            plotShotTypeData(json[2], shottypecanvaswithout);
        }
        else
        {
            var nodatalabel = document.createElement("div");
            nodatalabel.style = 'background-color: #f2f2f2; margin: auto; width: 50px';
            var text = document.createTextNode('No shots taken');
            nodatalabel.appendChild(text);
            shottypechartwithoutdiv.appendChild(nodatalabel);
        }
        
        shottypechartwithdiv.style = "float:left; width: 300px; height: 200px";
        var withlabel = document.createElement("p");
        withlabel.innerHTML = 'With';
        shottypechartwithdiv.appendChild(withlabel);
        if(json[1].length > 0)
        {
            shottypechartwithdiv.id = 'shottype-chart-with-div';
            var shottypecanvaswith = document.createElement("canvas");
            shottypecanvaswith.id = "shottype-with";
            shottypechartwithdiv.appendChild(shottypecanvaswith);
            plotShotTypeData(json[1], shottypecanvaswith);
        }
        else
        {
            var nodatalabel = document.createElement("div");
            nodatalabel.style = 'background-color: #f2f2f2; margin: auto; width: 50px';
            var text = document.createTextNode('No shots taken');
            nodatalabel.appendChild(text);
            shottypechartwithdiv.appendChild(nodatalabel);
        }
        
        var shotlocationchartsdiv = document.createElement("div");
        shotlocationchartsdiv.id = 'shotlocation-charts';
        shotlocationchartsdiv.style = 'width: 100%';
        customdata.appendChild(shotlocationchartsdiv);
        
        $('#customcontent').empty();
        $('#customcontent').append(customdata);
        
        if(json[4].length > 0)
        {
            shotlocationchartsdiv.innerHTML = "<div style='float: left; width:50%'><div id='shotlocationswithoutsvglabel'>Without</div><div id='shotlocationwithoutavgdistances'></div><svg id='shotlocationswithoutsvg' width='520' height='430'><image x='0' y='0' width='456' height='429' xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='images/fade-court.png'/></svg></div>";
            plotShotLocationData(json[4], 'shotlocationswithoutsvg', 'shotlocationwithoutavgdistances');
        }
        else
        {
            shotlocationchartsdiv.innerHTML += "<div style='float: left; width:50%'><div id='shotlocationswithsvglabel'>Without</div><div class='no-data-label'>No shots taken</div></div>";
        }
        if(json[3].length > 0)
        {
            shotlocationchartsdiv.innerHTML += "<div style='float: left; width:50%'><div id='shotlocationswithsvglabel'>With</div><div id='shotlocationwithavgdistances'></div><svg id='shotlocationswithsvg' width='520' height='430'><image x='0' y='0' width='456' height='429' xmlns:xlink='http://www.w3.org/1999/xlink' xlink:href='images/fade-court.png'/></svg></div>";
            plotShotLocationData(json[3], 'shotlocationswithsvg', 'shotlocationwithavgdistances');
        }
        else
        {
            shotlocationchartsdiv.innerHTML += "<div style='float: left; width:50%'><div id='shotlocationswithsvglabel'>With</div><div class='no-data-label'>No shots taken</div></div>";
        }
    }});
}

var types = ['dunk', 'layup', 'hook', 'jump', 'fadeaway', '3pt', 'other'];
var makecolors = ["#ff5050","#ffb366","#ffff66","#85e085","#66ffcc","#66b3ff","#b3b3b3"];
var misscolors = ["#ff9999","#ffe5cc","#ffffcc","#d7f4d7","#ccffee","#cce6ff","#e6e6e6"];

function plotShotTypeData(data, canvas) {
    if(data.length == 0)
    {
        document.getElementById();
        return;
    }
    var canvas;
    var ctx;
    var lastend = 0;
    var myTotal = data.length;

    var typemakes = Array.apply(null, Array(types.length)).map(Number.prototype.valueOf,0);
    var typecounts = Array.apply(null, Array(types.length)).map(Number.prototype.valueOf,0);

    for (var i = 0; i < data.length; i++) {
        var shot = data[i];
        var typefound = false;
        for(var t = 0; t < types.length - 1; t++)
        {
            if(shot.type.toLowerCase().indexOf(types[t]) >= 0)
            {
                typecounts[t]++;
                if(shot.made)
                {
                    typemakes[t]++;
                }
                typefound = true;
                break;
            }
        }
        if(!typefound)
        {
            typecounts[types.length - 1]++;
            if(shot.made)
            {
                typemakes[types.length - 1]++;
            }
        }
    }

    //render pie charts
    var x = (canvas.width)/2;
    var y = (canvas.height)/2;
    var radius = Math.min(x,y) * 0.9;
    ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, x, y);

    for (var i = 0; i < typecounts.length; i++) {
        ctx.beginPath();
        ctx.moveTo(x,y);
        ctx.fillStyle = misscolors[i];
        ctx.arc(x,y,radius,lastend,lastend+
          (Math.PI*2*(typecounts[i]/myTotal)),false);
        ctx.lineTo(x,y);
        ctx.fill();

        ctx.beginPath();
        ctx.moveTo(x,y);
        ctx.fillStyle = makecolors[i];
        ctx.arc(x,y,(typemakes[i]/typecounts[i])*radius,lastend,lastend+
          (Math.PI*2*(typecounts[i]/myTotal)),false);
        ctx.lineTo(x,y);
        ctx.fill();
        lastend += Math.PI*2*(typecounts[i]/myTotal);
    }
}

var xoffset = 260;
var yoffset = 60;
var scale = 0.877;
var crosslength = 4;
var radius = 5;

function plotShotLocationData(data, svgid, avgshotdistancesdiv) {
    var svg = document.getElementById(svgid);
    var totalshotdistance = 0;
    var totalmadeshotdistance = 0; var totalmadeshots = 0;

    for(var i = 0; i < data.length; i++)
    {
        var shot = data[i];
        var x = shot.Y;
        var y = shot.X;
        var distance = (Math.sqrt(x * x + y * y) * 0.1);
        totalshotdistance = totalshotdistance + distance;
        x += xoffset;
        y += yoffset;

        x*=scale;
        y*=scale;

        if(shot.made)
        {
            var madeShot = document.createElementNS("http://www.w3.org/2000/svg","circle"); 
            madeShot.setAttributeNS(null,"cx",x);
            madeShot.setAttributeNS(null,"cy",y);
            madeShot.setAttributeNS(null,"r",radius);
            madeShot.setAttributeNS(null,"fill","none");
            madeShot.setAttributeNS(null,"stroke-width",2);
            madeShot.setAttributeNS(null,"stroke","green");
            svg.appendChild(madeShot);
            
            totalmadeshotdistance = totalmadeshotdistance + distance;
            totalmadeshots++;
        }
        else
        {
            var ex = document.createElementNS("http://www.w3.org/2000/svg","g");

            var slash1 = document.createElementNS("http://www.w3.org/2000/svg","line");
            slash1.setAttributeNS(null,"x1",x-crosslength);
            slash1.setAttributeNS(null,"x2",x+crosslength);
            slash1.setAttributeNS(null,"y1",y-crosslength);
            slash1.setAttributeNS(null,"y2",y+crosslength);
            slash1.setAttributeNS(null,"stroke-width",2);
            slash1.setAttributeNS(null,"stroke","red");
            ex.appendChild(slash1);

            var slash2 = document.createElementNS("http://www.w3.org/2000/svg","line");
            slash2.setAttributeNS(null,"x1",x-crosslength);
            slash2.setAttributeNS(null,"x2",x+crosslength);
            slash2.setAttributeNS(null,"y1",y+crosslength);
            slash2.setAttributeNS(null,"y2",y-crosslength);
            slash2.setAttributeNS(null,"stroke-width",2);
            slash2.setAttributeNS(null,"stroke","red");
            ex.appendChild(slash2);

            svg.appendChild(ex);
        }
    }
    
    var averageshotdistancediv = document.getElementById(avgshotdistancesdiv);
    var averageshotdistance = data.length === 0 ? '--' : (totalshotdistance / data.length).toFixed(1);
    var averagemadeshotdistance = totalmadeshots === 0 ? '--' : (totalmadeshotdistance / totalmadeshots).toFixed(1);
    
    averageshotdistancediv.innerHTML = "<table><tr><th>Average shot distance</th><td>"+averageshotdistance+"'</td><tr>\n\
                                        <th>Average made shot distance</th><td>"+averagemadeshotdistance+"'</td><tr></table>";
}

function checkfragment()
{
    var kvp = window.location.hash.substr(2).split('&');
    var teampicked = null;
    var playerpicked = null;
    var matchupteam = null;
    var matchupplayers = null;
    for(var i = 0; i<kvp.length; i++)
    {
        x = kvp[i].split('=');
        if(x[0]=='teamID')
        {
           teampicked = x[1];
        }
        else if(x[0]=='playerID')
        {
            playerpicked = x[1];
        }
        else if(x[0] == 'matchupteamID')
        {
            matchupteam = x[1];
        }
        else if(x[0] == 'matchupplayers')
        {
            matchupplayers = x[1];
        }
    }
    if(teampicked)
    {
        $('#team-picked').val(teampicked).trigger('change');
    }
    if(playerpicked)
    {
        $('#player-picked').val(playerpicked).trigger('change');
    }
    if(matchupteam)
    {
        $('#matchup-team-picked').val(matchupteam).trigger('change');
    }
    if(matchupplayers)
    {
        matchupplayers.split(',').forEach(function(playerID){
            $('#cb-'+playerID).attr('checked', true).trigger('change');
        });
    }
}