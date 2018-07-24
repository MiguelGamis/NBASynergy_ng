/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(window).scroll(function () {
    //if you hard code, then use console
    //.log to determine when you want the 
    //nav bar to stick.  
    //console.log($(window).scrollTop())
  if ($(window).scrollTop() > 100) {
    $('#menu').addClass('navbar-fixed');
  }else{
    $('#menu').removeClass('navbar-fixed');
  }
});
  
$('#menu-inner ul li a').on('click', function (){
    this.classList.add('active');
});

var id;
adjustmenucontents();
$(window).resize(function(){
    adjustmenucontents();
});

function adjustmenucontents() {
    if($(window).outerWidth(true) > 780){
        $('#menu-site-logo').show();
        $('.menu-option').show();
        $('#menu-dropdown').hide();
    }else if($(window).outerWidth(true) > 558){
        $('#menu-site-logo').hide();
        $('.menu-option').show();
        $('#menu-dropdown').hide();
    }else{
        $('#menu-site-logo').hide();
        $('.menu-option').hide();
        $('#menu-dropdown').show();
    }
}