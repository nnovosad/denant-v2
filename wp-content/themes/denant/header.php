<!DOCTYPE html>
<html class="no-js" lang="ru">
<head>
    <meta charset="utf-8">
    <title>Денант</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php wp_head(); ?>
</head>
<body>
<!-- Preloader -->
<div id="preloader">
    <div id="status">
        <div class="parent">
            <div class="child">
                <p class="small">Загрузка</p>
            </div>
        </div>
    </div>
</div>
<!-- end preloader -->
<section class="intro parallax section" id="section1">
    <div class="overlay">
        <div id="headline_cycler">
            <div class="headline_cycler_centralizer">
                <ul class="flexslider">
                    <li class="slide first">
                        <h2 class="atxt_hl"><?php echo get_post_meta($post->ID, '_dat_hero_text_1', true); ?></h2>
                    </li>
                    <li class="slide">
                        <h2 class="atxt_hl"><?php echo get_post_meta($post->ID, '_dat_hero_text_2', true); ?></h2>
                        <p class="atxt_sl"><?php echo get_post_meta($post->ID, '_dat_hero_text_3', true) ? : 'ДентАнтТранс'; ?></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <a href="#section2" data-title="" id="arrow-down" class="aligncenter">Поехали!</a> </section>
<!-- start header -->
<header class="clearfix">
    <div id="logo"> <a href="/"><?php bloginfo('name'); ?></a> </div>
    <div class="tagline"><span><?php bloginfo('name'); ?></span> <span class="header-phone"><?php echo get_post_meta($post->ID, '_dat_about_phone', true); ?></span> </div>
    <div id="nav-button"> <span class="nav-bar"></span> <span class="nav-bar"></span> <span class="nav-bar"></span> </div>
    <nav>
        <ul id="nav">
            <li><a href="#section2">О нас</a> </li>
            <li><a href="#section3">Услуги</a> </li>
            <li><a href="#section4">Сотрудники</a> </li>
            <li><a href="#section5">Документы</a> </li>
            <li><a href="#section6">Смежные компании</a> </li>
            <li><a href="#section7">Партнеры</a> </li>
            <li><a href="#section8">Контакты</a> </li>
        </ul>
    </nav>
</header>
<!-- end header -->