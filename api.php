<?php

/*
 *  API 
 * Example: in functions.php use
 * register_static_template_page( 'My Home Page' , 'myhomepage.php')
 *
 * This will:
 *  1. create a page on admin site with title "My Home Page"
 *  2. HIde this page on edit.php
 * 3. allow user to add this page to custom menus.
 * 4.  allow you to refer this page form code ( get_static_template_page_permalink)
 */

function register_static_template_page($name, $filename) {
    global $kalutoStaticTemplatePage;
    $kalutoStaticTemplatePage->register_page($name, $filename);
}

/*
 *  API
 * :
 * get_static_template_page_permalink:
 * allowes you to get the permalink of the static page.
 * examples.
 *
 * $url = get_static_template_page_permalink( 'myhomepage.php')
 *  or
 * $url =   get_static_template_page_permalink('myhomepage.php')
*
 *AND   $url is the link to your static homepage
 */

function get_static_template_page_permalink($name) {
    global $kalutoStaticTemplatePage;

    $id = $kalutoStaticTemplatePage->get_id_from_name($name);

    if ($id == false) {
        $id = $kalutoStaticTemplatePage->get_id_from_file($name);
        }
   if ($id == false) {
        return false;
    }
    return get_permalink($id);
}

/*
 * ResetPLugin
 * Delete pages,
 * Delete DB
 * And recreate
 */
function kaluto_static_template_reset($once , $stop = false)
{
    global $kalutoStaticTemplatePage;
    $dbonce = get_option('kalutoStaticTemplatePageOnce');
    if ($dbonce == $once)
        return;

    $kalutoStaticTemplatePage->reset = true;
    update_option('kalutoStaticTemplatePageOnce', $once);
    if ($stop)
        $kalutoStaticTemplatePage->stop = true;

}