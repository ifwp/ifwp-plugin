<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Sessions')){
    class IFWP_Sessions {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){
			$general = ifwp_tab('', 'Sessions');
            $general->add_field('support_native_php_sessions', [
				'label_description' => 'You shoud use in conjunction with the <a href="https://wordpress.org/plugins/wp-native-php-sessions/" target="_blank">WordPress Native PHP Sessions</a> plugin by <a href="https://pantheon.io/" target="_blank">Pantheon</a>.',
                'name' => 'Support native PHP Sessions?',
            	'type' => 'switch',
            ]);
            if($general->support_native_php_sessions){
                add_action('init', [__CLASS__, 'init'], 9);
                add_action('wp_login', [__CLASS__, 'wp_login'], 10, 2);
                add_action('wp_logout', [__CLASS__, 'wp_logout']);
            }
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function init(){
            if(!session_id()){
        		session_start();
        	}
        	if(empty($_SESSION['ifwp_current_user_id'])){
        		$_SESSION['ifwp_current_user_id'] = get_current_user_id();
        	}
            if(empty($_SESSION['ifwp_utm'])){
        		$_SESSION['ifwp_utm'] = [];
                foreach($_GET as $key => $value){
                    if(substr($key, 0, 4) == 'utm_'){
                        $_SESSION['ifwp_utm'][$key] = $value;
                    }
                }
        	}
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
            add_action('ifwp_plugin_loaded', [__CLASS__, 'ifwp_plugin_loaded']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function wp_login($user_login, $user){
            $_SESSION['ifwp_current_user_id'] = $user->ID;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function wp_logout(){
            if(session_id()){
        		session_destroy();
        	}
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// functions
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_get_utm')){
    function ifwp_get_utm(){
        $utm = empty($_SESSION['ifwp_utm']) ? [] : $_SESSION['ifwp_utm'];
        return shortcode_atts([
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_term' => '',
            'utm_content' => '',
        ], $utm);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Sessions::load();
