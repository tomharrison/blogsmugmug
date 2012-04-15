<?php
/*
Plugin Name: Blog SmugMug
Plugin URI: http://www.ofzenandcomputing.com
Description: Easily blog your latest SmugMug photos.
Version: 0.1
Author: Tom Harrison (tomharrison@gmail.com)
Author URI: http://www.thetomharrison.com/
License: GPL2
*/

// WordPress Settings API

add_action('admin_menu', 'bsm_admin_add_page');
function bsm_admin_add_page() {
  add_options_page(
    'Blog SmugMug Page',
    'Blog SmugMug',
    'manage_options',
    'bsm',
    'bsm_options_page');
}

function bsm_options_page() {
?>

<div class="wrap">
  <h2>Blog SmugMug</h2>
  <form action="options.php" method="post">
    <?php settings_fields('bsm_options'); ?>
    <?php do_settings_sections('bsm'); ?>
    <p>
      <input name="Submit" class="button-primary" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </p>
  </form>
</div>

<?php
}

add_action('admin_init', 'bsm_admin_init');
function bsm_admin_init() {
  register_setting('bsm_options', 'bsm_options', 'bsm_options_validate');
  add_settings_section('bsm_main', 'Main Settings', 'bsm_section_text', 'bsm');
  add_settings_field('bsm_nickname', 'SmugMug Nickname', 'bsm_nickname_field', 'bsm', 'bsm_main');
}

function bsm_section_text() {
  echo '';
}

function bsm_nickname_field() {
  $options = get_option('bsm_options');
  echo '<input id="bsm_nickname" name="bsm_options[nickname]" size="40" type="text" value="'.$options['nickname'].'" />';
}

function bsm_options_validate($input) {
  $newinput['nickname'] = trim($input['nickname']);
  return $newinput;
}

/**
 * Add a SmugMug button to the "Upload/Insert" tabs above the post editor.
 */
function bsm_add_media_icon($context) {
  return $context . 
    '<a href="' . plugins_url('bsmbrowser.php', __FILE__) . '?nickname=thetomharrison" class="thickbox" onclick="return false;"><img src="' . plugins_url('smugmug-logo.png', __FILE__) . '" width="15" height="15" alt="SmugMug Logo" /></a>';
}
add_filter('media_buttons_context', 'bsm_add_media_icon');


function bsm_enqueue_scripts() {
  wp_enqueue_script(
    'blogsmugmug', 
    plugins_url('blogsmugmug.js', __FILE__),
    array('jquery')
  );
}

add_action('admin_enqueue_scripts', 'bsm_enqueue_scripts');