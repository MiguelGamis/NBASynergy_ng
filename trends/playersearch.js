/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function playersearchconnect(playersearch, valuedump)
    $( '#'+playersearch ).autocomplete({
        source: function( request, response ) {
            $.ajax({
                url: 'trends/playersearch.php',
                dataType: 'json',
                data: {
                    q: request.term
                },
                success: function( data ) {
                    response( data );
                }
            });
        },
        select: function(e, ui) {
            $("#"+playersearch).val(ui.item.value);
            document.getElementById('player-search-playerID').value = ui.item.id;
            document.getElementById('player-search-form').submit();
        }
    });