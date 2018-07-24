/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var getLineupsCall = null;

function comparer(index) {
    return function(a, b) {
        var valA = getCellValue(a, index), valB = getCellValue(b, index);
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
    };
}
function getCellValue(row, index){ return $(row).children('td').eq(index).html(); }


$(document).ready(function()
{
    $('#team-picker').on('change', function(){
        //document.getElementById('team-picker-form').submit();
        $('#lineups-container').html("<div class='loader'></div>");
        getLineupsCall = $.ajax({url:'getlineups.php?team='+this.value, 
            success: function(result){
                var json = JSON.parse(result);
                var content = '';
                content += "<table id='lineups-table' class='table-condensed stats'>";
                content += "<tr><th>Lineup</th><th>MIN</th><th>Points for</th><th>Points against</th><th>Offensive Efficiency</th></tr>";
                for(var i = 0; i < json.length; i++)
                {
                    var lineupstats = json[i];
                    //100 x Pts / 0.5 * ((Tm FGA + 0.4 * Tm FTA - 1.07 * (Tm ORB / (Tm ORB + Opp DRB)) * (Tm FGA - Tm FG) + Tm TOV) + (Opp FGA + 0.4 * Opp FTA - 1.07 * (Opp ORB / (Opp ORB + Tm DRB)) * (Opp FGA - Opp FG) + Opp TOV))
                    content += "<tr>";
                    content += "<td>"+lineupstats.lineup+"</td>";
                    content += "<td>"+(lineupstats.totalms/60000).toFixed(1)+"</td>";
                    content += "<td>"+lineupstats.pointsfor+"</td>";
                    content += "<td>"+lineupstats.pointsagainst+"</td>";
                    content += "<td>"+lineupstats.offensiveefficiency+"</td>";
                    content += "</tr>";
                }
                content += "</table>";
                $('#lineups-container').html(content);
                
                $('th').click(function(){
                    var table = $(this).parents('table').eq(0);
                    var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
                    this.asc = !this.asc;
                    if (!this.asc){rows = rows.reverse();}
                    for (var i = 0; i < rows.length; i++){table.append(rows[i]);}
                });
            }
        });
    });
});