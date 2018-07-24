/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function checkfragment()
{
    var kvp = window.location.hash.substr(2).split('&');
    for(var i = 0; i<kvp.length; i++)
    {
        x = kvp[i].split('=');
        if(x[0]=='teamID')
        {
           var teampicked = x[1];
           $('#team-picked').val(teampicked).trigger('change');
        }
        else if(x[0] == 'matchupteamID')
        {
            var matchupteam = x[1];
            $('#matchup-team-picked').val(matchupteam).trigger('change');
        }
    }
}

function getFragmentParameter(parameter)
{
    var pairs = window.location.hash.substr(2).split('&');
    
    for(var i = 0; i<pairs.length; i++)
    {
        var pair = pairs[i].split('=');
        if(parameter == pair[0])
        {
            return pair[1];
        }
    }
    
    return '';
}