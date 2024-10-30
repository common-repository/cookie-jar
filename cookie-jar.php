<?php
/*
Plugin Name: Cookie Jar
Plugin URI: https://bitbucket.org/gregorybialowas/cookie-jar/src/master/
Description: Displays a cookie consent on pages (as an image) and provides a link to Cookie Policy page
Version: 2.0.1
Author: Greg Bialowas
Author URI: http://gregbialowas.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: cookie-monster
Domain Path: /lang
*/

class Cookie_Monster {

    function __construct()
    {
        add_action(
            'init',
            array( $this, 'init' )
        );
    }

    function init()
    {
        add_action(
            'admin_menu',
            array ( $this, 'admin_menu' )
        );

		add_action(
			'wp_footer',
			array ( $this, 'generate_cookie_monster_div' )
		);

		add_action(
			'wp_enqueue_scripts',
			array ( $this, 'theme_enqueue_styles' )
        );

		add_action(
			'admin_enqueue_scripts',
			array ( $this, 'admin_enqueue_styles' )
        );

        add_option(
            'cookie_jar_privacy_link',
            '["","cookies","left",null]',
            '',
            'no'
        );
    }

    function cookie_monster_load_translation()
    {
        load_plugin_textdomain( 'cookie-monster', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
    }

    function generate_cookie_monster_div()
    {
       $get_link = json_decode( get_option( 'cookie_jar_privacy_link' ) );
       $get_url = get_site_url() . '/' . $get_link[3];
       $cookie_image_output = plugin_dir_url(__FILE__) . 'img/' . $get_link[1] . '.png';
       $cookie_image_sizes = getimagesize($cookie_image_output);

	   echo '
            <div class="cookie_monster_div ' . $get_link[2] . '"
            style="background-image: url(' . $cookie_image_output . ');
                width: ' . $cookie_image_sizes[0] . 'px;
                height: ' . $cookie_image_sizes[1] . 'px;
            ">
                <b class="t_fixed">
                    <p>' . esc_html__('This site uses cookies', 'cookie-monster') . '</p>
                    <a href="JavaScript:void(0);" class="killCookie">' . esc_html__('Close', 'cookie-monster') . '</a>
                    <a href="' .  esc_url($get_url) . '">' . esc_html__('Read more', 'cookie-monster') . '</a>
                </b>
            </div>
       ';
       echo "\n";
	}

    function theme_enqueue_styles()
    {
	    wp_enqueue_style( 'cookie-monster', plugin_dir_url(__FILE__) . 'css/cookie.css' );
	    wp_enqueue_script('cookie-monster-1', plugin_dir_url(__FILE__) . 'js/jquery.cookie.js', array('jquery'));
        wp_enqueue_script('cookie-monster-2', plugin_dir_url(__FILE__) . 'js/para.js');
    }

    function admin_enqueue_styles()
    {
	    wp_enqueue_style( 'admin-styles', plugin_dir_url(__FILE__) . 'css/admin.css' );
	}

    function admin_menu()
    {
        add_options_page(
            esc_html__('Cookie Jar - select the correspoding page', 'cookie-monster'),  //page title (tab title)
            esc_html__('Cookie Jar - settings', 'cookie-monster'), //menu title displayed on the left hand side
            'manage_options', //capability
            'list', //menu slug eg. admin.php?page=<list>
            array( $this, 'options' ) //function
        );
    }

    function options()
    {
        include('main_admin.php');
    }
}

//global $cookie_monster;
$cookie_monster = new Cookie_Monster;
add_action('plugins_loaded', array($cookie_monster, 'cookie_monster_load_translation'));
