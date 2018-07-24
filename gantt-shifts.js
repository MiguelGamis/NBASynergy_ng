/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function()
{
    $('#gameSelect').on('change', function(){
        if(this.value)
        {
            $.ajax({url:'displaydata.php?gameID='+this.value, 
                success: function(result){
                    var shifts = JSON.parse(result);
                    var game = shifts['game'];
                    var awayshifts = shifts['away'];
                    var homeshifts = shifts['home'];
                    renderGanttShifts(game, awayshifts, 'away-gantt');
                    renderGanttShifts(game, homeshifts, 'home-gantt');
                }
            });
        }
    });
});

function renderGanttShifts(game, shifts, divID)
{   
    var finalperiod = game['finalperiod'];
    
    var playersdiv = document.createElement('div');
    playersdiv.setAttribute("style", "float: left");
    var shiftsdiv = document.createElement('canvas');
    
    var length = 1400;
    var totaltime = finalperiod > 4 ? (2880000 + (finalperiod - 4)*300000) : 2880000;
    
    shiftsdiv.setAttribute("width", length);
    var height = shifts.length * 21;
    shiftsdiv.setAttribute("height", height);
    shiftsdiv.setAttribute("style", "border:1px solid #000000;");
    
    
    var ctx = shiftsdiv.getContext('2d');
    ctx.beginPath();
    for(var p = 1; p < finalperiod; p++){
        if(p < 5){
            var x = (720000*p/totaltime)*length;
            ctx.moveTo(x,0);
            ctx.lineTo(x,height);
        }
        else{
            var x = ((2880000+p*300000)/totaltime)*length;
            ctx.moveTo(x,0);
            ctx.lineTo(x,height);
        }
        ctx.stroke();
    }
    
    shifts.forEach(
        function(playershift, index){
            var newDiv = document.createElement("div"); 
            var t = document.createTextNode(playershift.player.firstname + ' ' + playershift.player.lastname);
            newDiv.appendChild(t);
            //newDiv.setAttribute("class", "child");
            playersdiv.appendChild(newDiv);
            
            playershift.shifts.forEach(
                function(shift){
                    var timestart = (shift.starttime / totaltime) * length;
                    var timelength = ((shift.endtime - shift.starttime) / totaltime) * length;
                    ctx.rect(timestart, 5 + index * 21, timelength, 10);
                    ctx.stroke();
                }
            )
        }
    )
    
    var basediv = document.getElementById(divID);
    while (basediv.hasChildNodes()) {
        basediv.removeChild(basediv.lastChild);
    }
    basediv.appendChild(playersdiv);
    basediv.appendChild(shiftsdiv);
}