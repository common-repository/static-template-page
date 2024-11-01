<?php
/*
Plugin Name:  static template page
Version: 0.1
Plugin Author: Oren Kolker
Description: Allows the theme writer to add a single page  with a single template, and refer to it from code, without user configuration !!!

Copyright (C) 2010 Oren Kolker  (orenkolker@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


class KalutoStaticTemplatePage {

    /*
     * Collect the pages registered on functions php;
     * Hold created pages Name , with there file name ( name=>filname);
     */
    var $register_pages;
    /*
     * Indicate REset;
     */
    var $reset;
    var $stop;

    /*
     * THis is the DB!
     * Hold created pages ids, with there file name ( id=>filname);
     */
    var $created_pages;
    var $pages_registered = false;

    /*
     * Debug variables
     */
    var $debug = false;
    var $run_one_time_always = false;


    // if ($debug) {echo "line:" . __LINE__." <br>";}

    /*
     * Constructor.
     * Init values;
     * Add actions;
     */

    function KalutoStaticTemplatePage() {

        require_once dirname( __FILE__ ) . '/api.php';
        
        $this->register_pages = array();  // name -> filename
        $this->created_pages = array();   //id --> filename


        add_action('init', array($this, 'one_time'));
        add_action('pre_get_posts', array($this, 'exclude_pages_from_edit'));
        add_filter('template_include', array($this, 'change_template'));
    }

    /*
     *
     *  For finding Permalink.
     * GEt the id of the page, by its filename
     */
    function get_id_from_file($file) {

        foreach ($this->created_pages as $id => $filename) {
            if ($file == $filename)
                return $id;
        }

        return false;
    }

        /*
     *
     *  For finding Permalink.
     * GEt the id of the page, by its name
     */
    function get_id_from_name($name) {

        if (!isset($this->register_pages[$name])) {
            return false;
        }

        $file = $this->register_pages[$name];
        foreach ($this->created_pages as $id => $filename) {
            if ($file == $filename)
                return $id;
        }

        return false;
    }


    /*
     * Remove the static pages from the edit.php page on admin side.
     * This is (pre_get_posts) action;
     */
    function exclude_pages_from_edit($query) {
        if (!is_admin())
            return $query;

        global $pagenow;

        if (isset($_REQUEST['staticshow']))
            return $query ;
        if ('edit.php' == $pagenow && ( get_query_var('post_type') && 'page' == get_query_var('post_type') ))
            $query->set('post__not_in', array_keys($this->created_pages));

        return $query;
    }

    /*
     * Interfear the WP Template Hirerchy
     *
     * If its a static page, change it to its own template.
     *  action (template_include);
     */
    function change_template($template) {
        if (is_page ()) {
            $id = get_the_ID();
            if (isset($this->created_pages[$id])) {
                $template = dirname($template) . '/' . $this->created_pages[$id];
            }
        }
        return $template;
    }

    /*
     * Poplate  $this->register_page
     *
     */
    function register_page($name, $filename) {
        $this->register_pages[$name] = $filename;
        $this->pages_registered = true;
    }

    /*
     *  The first time it runs:
     *  It creates the static pages,
     *  And Update the DB.
     *  The rest of the Times:
     *  Populate created_pages array.
     */
    function one_time() {
        if ($this->run_one_time_always || $this->reset) {
            $this->created_pages = get_option('KalutoStaticTemplatePages' , array());

            foreach ( array_keys($this->created_pages) as $id)
            {
                wp_delete_post($id);
            }
            delete_option('KalutoStaticTemplatePages' , array());

        }

        if ($this->stop)
                return;

        if (!$this->pages_registered)
            return;
        $this->created_pages = get_option('KalutoStaticTemplatePages');
        if ($this->created_pages != false) {
            if ($this->debug) {
                echo "line:" . __LINE__ . "  <br>";
                $this->debug();
            }

            return;
        }

        if ($this->debug) {
            echo "line:" . __LINE__ . " ONE_TIME is running <br>";
        }

        // Create pages. and save there Ids
        foreach ($this->register_pages as $name => $filename) {
            $id = $this->createPage($name);
            $this->created_pages[$id] = $filename;
        }
        add_option('KalutoStaticTemplatePages', $this->created_pages);


        if ($this->debug) {
            echo "line:" . __LINE__ . "  <br>";
            $this->debug();
        }
    }

    /*
     *  Create Page, and return its ID
     */
    function createPage($name) {
        // Create post object
        $my_post = array(
            'post_title' => $name,
            'post_content' => 'This page is place order for $name.',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_type' => 'page'
        );

        // Insert the post into the database
        $id = wp_insert_post($my_post);
        return $id;
    }

    //////////DEBUG;
    function debug() {
        echo "Register pages: <br>";
        foreach ($this->register_pages as $name => $filename) {
            echo " $name   \t $filename<br> ";
        }
        foreach ($this->created_pages as $id => $filename) {
            echo " $id   \t $filename<br> ";
        }
    }

}

global $kalutoStaticTemplatePage;
$kalutoStaticTemplatePage = new KalutoStaticTemplatePage;

