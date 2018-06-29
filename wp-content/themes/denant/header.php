<!DOCTYPE html>
<html class="no-js" lang="ru">
<head>
    <meta charset="utf-8">
    <title>Денант</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/images/favicon.ico" />
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
                        <p class="atxt_sl"><?php echo get_post_meta($post->ID, '_dat_hero_text_3', true) ? : 'ДенАнтТранс'; ?></p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <a href="#section2" data-title="" id="arrow-down" class="aligncenter">Поехали!</a> </section>
<!-- start header -->

<header class="clearfix">
    <div id="logo"> <a href="/"><?php bloginfo('name'); ?></a> </div>
    <div class="tagline">
        <span><?php bloginfo('name'); ?></span> <span class="header-phone"><?php echo get_post_meta($post->ID, '_dat_about_phone', true); ?></span>
        <!--        <span class="flags">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/ru.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/en.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/pl.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/fr.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/de.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/it.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/fr.png">-->
        <!--            <img src="--><?php //echo get_template_directory_uri() ?><!--/images/flags/ch.png">-->
        <!--        </span>-->
    </div>
    <div id="nav-button"> <span class="nav-bar"></span> <span class="nav-bar"></span> <span class="nav-bar"></span> </div>
    <nav class="desktop-menu">
        <ul class="nav">
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

<div class="menu">
    <ul>
        <li><a href="#section2">О нас</a> </li>
        <li><a href="#section3">Услуги</a> </li>
        <li><a href="#section4">Сотрудники</a> </li>
        <li><a href="#section5">Документы</a> </li>
        <li><a href="#section6">Смежные компании</a> </li>
        <li><a href="#section7">Партнеры</a> </li>
        <li><a href="#section8">Контакты</a> </li>
    </ul>
</div>

<div class="nav_container">
    <a href="#" class="toggle-text">Навигация</a>
</div>