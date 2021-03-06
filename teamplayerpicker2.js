/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function playerpickersetup(teampicked, playerpicked)
{
    $('#team-picker li').on('click', function(){
        var val = this.id.replace('player-picker-team-picker-','');
        if($('#'+teampicked).val() !== val)
        {
            $('#'+teampicked).val(val).trigger('change');
        }
    });

    $('#'+teampicked).on('change', function(){
        $('#matchup-left-players').empty();
        var team = document.getElementById(teampicked);
        $.ajax({url: 'getplayers.php?team='+team.value, 
            success: function(result){
            $('#player-picker-col-1').empty();
            $('#player-picker-col-2').empty();
            $('#player-picker-col-3').empty();
            var players = JSON.parse(result);
            for (i=0; i<players.length; i++) {
                var label = document.createElement('label');
                label.setAttribute('for', "rb-"+players[i].playerID);

                var li = document.createElement('li');
                li.id = players[i].playerID;

                var rb = document.createElement("input");
                rb.setAttribute("type", "radio");
                rb.setAttribute("class", "player-picker-radio"); 
                rb.setAttribute("value", players[i].playerID);
                rb.setAttribute("id", "rb-"+players[i].playerID);
                rb.setAttribute('style', 'float:left');
                rb.name = 'player-picker';

                var profilepic = document.createElement("img");
                profilepic.setAttribute('src', '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+players[i].playerID+'.png');
                profilepic.setAttribute('style', 'width:40%;');

                var name = document.createTextNode(players[i].firstname+' '+players[i].lastname);

                var div = document.createElement('div');
                div.id = 'matchup-left-players-'+players[i].playerID;
                div.style.cursor = 'pointer';
                div.appendChild(rb);
                div.appendChild(profilepic);
                div.appendChild(name);

                label.appendChild(div);
                li.appendChild(label);
                var col = i%3;
                $('#player-picker-col-'+(col+1)).append(li);
            }
            $('.player-picker-radio').on('change', function() {
                $('#matchup-left-players').empty();
                if(this.checked)
                {
                    var playerprofile = document.createElement('div');
                    playerprofile.id = 'matchup-left-profile-'+this.value;
                    playerprofile.className += 'multi-player-picker-player-profile';

                    var playername = document.createElement('div');
                    playername.innerHTML = $('#matchup-left-players-'+this.value).text();
                    playername.style = 'width:100%';

                    var playerimage = document.createElement('img');
                    playerimage.src = '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+this.value+'.png';
                    playerimage.style = 'width:100%';

                    playerprofile.appendChild(playerimage);
                    playerprofile.appendChild(playername);
                    $('#matchup-left-players').append(playerprofile);
                }
            });
            
            var param = getFragmentParameter('playerID');
            $('#rb-'+param).prop('checked','true').trigger('change');
        }});
    });
}

function multiplayerpickersetup(teampicked, playerspicked)
{
    $('#multi-player-picker-list').click(function(e) {
        e.stopPropagation();
    });
    
    $('#multi-player-picker-team-picker li').on('click', function(){
        var val = this.id.replace('multi-player-picker-team-picker-','');
        if($('#'+teampicked).val() !== val)
        {
            $('#'+teampicked).val(val).trigger('change');
        }
    });

    $('#'+teampicked).on('change', function(){
        $('#matchup-right-players').empty();
        var team = document.getElementById(teampicked);
        $.ajax({
            url: 'getplayers.php?team='+team.value, 
            success: function(result){
                $('#multi-player-picker-dropdown').empty();
                var players = JSON.parse(result);
                for (i=0; i<players.length; i++) {
                    var label = document.createElement('label');
                    label.className = 'bs-picker-player';
                    label.setAttribute('for', "cb-"+players[i].playerID);

                    var cb = document.createElement("input");
                    cb.type = 'checkbox';
                    cb.className = 'bs-picker-player-checkbox'; 
                    cb.value = players[i].playerID;
                    cb.id = 'cb-'+players[i].playerID;
                    
                    var profilepic = document.createElement("img");
                    profilepic.className = 'bs-picker-player-pic';
                    profilepic.src = '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+players[i].playerID+'.png';

                    var profilename = document.createElement("div");
                    profilename.className = 'bs-picker-player-name';
                    profilename.innerHTML = players[i].firstname+' '+players[i].lastname;

                    var div = document.createElement('div');
                    div.id = 'multi-player-picker-player-'+players[i].playerID;
                    div.style.cursor = 'pointer';
                    div.appendChild(cb);
                    div.appendChild(profilepic);
                    div.appendChild(profilename);
            
                    label.appendChild(div);
                    $('#multi-player-picker-dropdown').append(label);
                }

                $('.bs-picker-player-checkbox').on('change', function() {
                    var numchecked = $('.bs-picker-player-checkbox:checked').length;
                    if(numchecked === 5) {
                        $('.bs-picker-player-checkbox').attr('disabled', 'disabled');
                        $('.bs-picker-player-checkbox:checked').removeAttr('disabled');
                    }else{
                        $('.bs-picker-player-checkbox').removeAttr('disabled');
                    }

                    if(this.checked)
                    {
                        var playerprofile = document.createElement('div');
                        playerprofile.id = 'multi-player-picker-player-profile-'+this.value;
                        playerprofile.className += 'multi-player-picker-player-profile';

                        var playername = document.createElement('div');
                        playername.innerHTML = $('#multi-player-picker-player-'+this.value).text();
                        playername.style = 'width:100%';

                        var playerimage = document.createElement('img');
                        playerimage.src = '//ak-static.cms.nba.com/wp-content/uploads/headshots/nba/latest/260x190/'+this.value+'.png';
                        playerimage.style = 'width:100%';

                        playerprofile.appendChild(playerimage);
                        playerprofile.appendChild(playername);
                        $('#matchup-right-players').append(playerprofile);
                    }
                    else
                    {              
                        $('#multi-player-picker-player-profile-'+this.value).remove();
                    }
                 });
                 
                 getFragmentParameter('matchupplayers').split(',').forEach(function(param){
                    $('#cb-'+param).prop('checked','true').trigger('change');
                 });
             }
         });
     });
}
