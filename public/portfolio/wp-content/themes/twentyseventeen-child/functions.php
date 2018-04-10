<?php
/**
 * Created by PhpStorm.
 * User: Sammy
 * Date: 4/9/2018
 * Time: 10:41 PM
 */
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {

  $parent_style = 'parent-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

  wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
  wp_enqueue_style( 'child-style',
      get_stylesheet_directory_uri() . '/style.css',
      array( $parent_style ),
      wp_get_theme()->get('Version')
  );

  //Leaving as example on how to enqueue script...
  //wp_enqueue_script('fix_url', get_stylesheet_directory_uri() . '/js/fix_url.js', array('jquery'));
}