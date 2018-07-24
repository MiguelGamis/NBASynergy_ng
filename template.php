<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="images/statballicon.ico" />
    <title><?php get_page_title(); ?> </title>
    <?php get_page_headers(); ?>
</head>
<body>
<div id="wrapper">
    <div id="header">
        <div id="banner">
<!--            <div><img id='banner-logo' src='images/statball.png'/><div id="banner-sitename">NBA &Sigma;ynergy</div></div>-->
        </div>
        <div id="menu">
            <div id="menu-inner">
                <?php get_page_menu(); ?>
            </div>
        </div>
    </div>
    <div id="content">
        <?php get_page_content(); ?>
    </div>
</div>
<?php get_page_scripts(); ?>
<div id="footer">
</div>
</body>
</html>
