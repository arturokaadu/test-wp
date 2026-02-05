<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <div class="stars-background">
        <div class="stars-background__layer stars-background__layer--small"></div>
        <div class="stars-background__layer stars-background__layer--medium"></div>
        <div class="stars-background__layer stars-background__layer--big"></div>
    </div>