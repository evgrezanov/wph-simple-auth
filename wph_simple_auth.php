<?php

/**
 * Plugin Name:         WPHire Simple Autentification
 * Plugin URI:          https://en.wphire.ru
 * Description:         Simple auth by sekret key in URL.
 * Author:              Evgenii Rezanov
 * Author URI:          https://en.wphire.ru
 * Version:             1.0
 **/

/**
 * Programmatic user login
 * 
 * @param string $username
 * @return bool
 */
function wph_programmatic_login( $username ) {
   
  if ( is_user_logged_in() ) {
    wp_logout();
  }
   
  add_filter( 'authenticate', 'wph_allow_programmatic_login', 10, 3 );
  $user = wp_signon( array( 'user_login' => $username ) );
  remove_filter( 'authenticate', 'wph_allow_programmatic_login', 10, 3 );
   
  if ( is_a( $user, 'WP_User' ) ) {
    wp_set_current_user( $user->ID, $user->user_login );
     
    if ( is_user_logged_in() ) {
      return true;
    }
  }
  
  return false;
}
  
/**
 * Patching call-back for filter 'authenticate', for we can login,
 * used only username
 * 
 * @param WP_User $user
 * @param string $username
 * @param string $password
 * @return bool|WP_User
 */
function wph_allow_programmatic_login( $user, $username, $password ) {
  return get_user_by( 'login', $username );
}


add_filter('user_contactmethods', 'wph_user_contactmethods'); 
function wph_user_contactmethods($user_contactmethods){
 	if ( current_user_can('administrator') ) {
  		$user_contactmethods['secret_key'] = 'Secret Key';
 		return $user_contactmethods;
 	}
}


add_action( 'after_setup_theme', 'wph_simple_auth' );
function wph_simple_auth() {
	if ( isset($_GET['secret_code']) ) {
		$args = array(
			'meta_key'     => 'secret_key',
			'meta_value'   => $_GET['secret_code'],
			'fields'       => ['user_login']
		);
		$users = get_users( $args );
		foreach( $users as $user ){
			$user_login = $user->user_login;
		}
		wph_programmatic_login($user_login);
		wp_redirect( home_url().'/wp-admin/profile.php');
		exit;
	}
}

?>