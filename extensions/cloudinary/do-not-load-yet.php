<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Cloudinary')){
    class IFWP_Cloudinary {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static proteced
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected $image_sizes = [];

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static protected function image_get_intermediate_size($id = 0, $size = ''){
            $image_size = self::$image_sizes[$size];
            $image = ifwp_cloudinary_upload($id, $image_size['options']);
            $url = (isset($image['secure_url']) ? $image['secure_url'] : (isset($image['url']) ? $image['url'] : ''));
            $width = (isset($image['width']) ? $image['width'] : 0);
            $height = (isset($image['height']) ? $image['height'] : 0);
            if(!$url or !$width or !$height){
                return false;
            }
            return [$url, $width, $height, true];
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function add_image_size($name = '', $args = array()){
            $name = sanitize_title($name);
            $args = shortcode_atts([
                'name' => $name,
                'options' => [],
            ], $args);
            $args['options_md5'] = ifwp_md5($args['options']);
            self::$image_sizes[$name] = $args;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function download_cloudinary_api(WP_REST_Request $request){
            $wp_upload_dir = wp_upload_dir();
            $dir = $wp_upload_dir['basedir'] . '/ifwp-plugin/cloudinary-api';
            if(is_dir($dir)){
                return true;
            }
            if(!wp_mkdir_p($dir)){
                return false;
            }
            if(is_php_version_compatible('5.4')){
                $version = '5.4';
            } else {
                return false;
            }
            $url = 'https://github.com/cloudinary/cloudinary_php/archive/1.18.0.zip';
            $attachment_id = ifwp_download($url);
            if(is_wp_error($attachment_id)){
                return false;
            }
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            $access_type = get_filesystem_method();
            if($access_type != 'direct'){
                return false;
            }
            if(!WP_Filesystem()){
                return false;
            }
            $zip = get_attached_file($attachment_id);
            $result = unzip_file($zip, $dir);
            if(is_wp_error($result)){
                return false;
            }
            return true;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function fl_builder_photo_sizes_select($sizes){
            if(isset($sizes['full'])){
    			$id = ifwp_attachment_url_to_postid($sizes['full']['url']);
    			if($id){
    				if(self::$image_sizes){
    					foreach(self::$image_sizes as $name => $args){
    						$image = get_post_meta($id, '_ifwp_cloudinary_image_' . $args['options_md5'], true);
    						if($image and !isset($sizes[$name])){
    							 $url = (isset($image['secure_url']) ? $image['secure_url'] : (isset($image['url']) ? $image['url'] : ''));
    							 $width = (isset($image['width']) ? $image['width'] : 0);
    							 $height = (isset($image['height']) ? $image['height'] : 0);
    							 $sizes[$name] = [
                                    'url' => $url,
     								'filename' => $image['public_id'],
     								'width' => $width,
     								'height' => $height,
                                 ];
    						}
    					}
    				}
    			}
    		}
    		return $sizes;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){
            $api = ifwp_tab('Cloudinary', 'API');
            $api->add_field('cloud_name', [
                'name' => 'Cloud Name',
                'type' => 'text',
            ]);
            $api->add_field('api_key', [
                'name' => 'API Key',
                'type' => 'text',
            ]);
            $api->add_field('api_secret', [
                'name' => 'API Secret',
                'type' => 'text',
            ]);
            $api->add_field([
                'std' => '<button id="' . $api->tab_id . '_hide_password" class="button">Hide secret</button>',
                'type' => 'custom_html',
            ]);
            add_action('admin_footer', function() use($api){
                if($api->is_current_screen()){ ?>
                    <script type="text/javascript">
                        function <?php echo $api->tab_id; ?>_toggle_password(){
                            var element = jQuery('#<?php echo $api->tab_id; ?>_api_secret');
                            if(element.attr('type') == 'text'){
                                element.attr('type', 'password');
                                jQuery('#<?php echo $api->tab_id; ?>_hide_password').text('Show secret');
                            } else {
                                element.attr('type', 'text');
                                jQuery('#<?php echo $api->tab_id; ?>_hide_password').text('Hide secret');
                            }
                        }
                        jQuery(function($){<?php
                            echo $api->tab_id; ?>_toggle_password();
                            $('#<?php echo $api->tab_id; ?>_hide_password').on('click', function(e){
                                e.preventDefault();<?php
                                echo $api->tab_id; ?>_toggle_password();
                            });
                        });
                    </script><?php
                }
            });
			$wp_upload_dir = wp_upload_dir();
            $dir = $wp_upload_dir['basedir'] . '/ifwp-plugin/cloudinary-api';
			if(is_dir($dir)){
                $api->add_field('load_cloudinary_api', [
					'label_description' => 'For details, see <a href="https://github.com/cloudinary/cloudinary_php" target="_blank">Cloudinary</a>.',
					'name' => 'Load Cloudinary API?',
					'type' => 'switch',
					'std' => true,
				]);
				if($api->get_option('load_cloudinary_api', true)){
        			ifwp_maybe_load_cloudinary_api();
				}
            } else {
				$api->add_field([
					'label_description' => 'For details, see <a href="https://github.com/cloudinary/cloudinary_php" target="_blank">Cloudinary</a>.',
					'name' => 'Download Cloudinary API?',
					'type' => 'custom_html',
					'std' => '<button id="' . $api->tab_id . '_download_cloudinary_api" class="button">Download</button>',
				]);
				add_action('admin_enqueue_scripts', function() use($api){
					if($api->is_current_screen()){
						wp_enqueue_script('wp-api');
					}
				});
				add_action('admin_footer', function() use($api){
					if($api->is_current_screen()){ ?>
						<script type="text/javascript">
							jQuery(function($){
								$('#<?php echo $api->tab_id; ?>_download_cloudinary_api').on('click', function(e){
									e.preventDefault();
									if(confirm('Are you sure?')){
										$('#<?php echo $api->tab_id; ?>_download_cloudinary_api').text('Wait...');
										$.ajax({
											beforeSend: function(xhr){
												xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
											},
											method: 'GET',
											url: wpApiSettings.root + 'ifwp-plugin/v1/download-cloudinary-api',
										}).done(function(response){
											$('#<?php echo $api->tab_id; ?>_download_cloudinary_api').text('Done.');
											setTimeout(function(){
												$('#<?php echo $api->tab_id; ?>_download_cloudinary_api').text('Download');
											}, 1000);
										});
									}
								});
							});
						</script><?php
					}
				});
			}
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function image_downsize($out, $id, $size){
            if(wp_attachment_is_image($id) and is_string($size) and isset(self::$image_sizes[$size])){
                $image = self::image_get_intermediate_size($id, $size);
                if($image){
                    return $image;
                }
            }
    		return $out;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function image_size_names_choose($sizes){
            if(self::$image_sizes){
                foreach(self::$image_sizes as $name => $args){
                    if(!isset($sizes[$name])){
                        $sizes[$name] = $args['name'];
                    }
                }
            }
            return $sizes;
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
            add_action('ifwp_plugin_loaded', [__CLASS__, 'ifwp_plugin_loaded']);
            add_action('rest_api_init', [__CLASS__, 'rest_api_init']);
            add_filter('fl_builder_photo_sizes_select', [__CLASS__, 'fl_builder_photo_sizes_select']);
            add_filter('image_downsize', [__CLASS__, 'image_downsize'], 10, 3);
            add_filter('image_size_names_choose', [__CLASS__, 'image_size_names_choose']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function rest_api_init(){
            register_rest_route('ifwp-plugin/v1', '/download-cloudinary-api', [
                'callback' => [__CLASS__, 'download_cloudinary_api'],
                'permission_callback' => function(){
                    return current_user_can('manage_options');
                },
            ]);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// functions
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_add_cloudinary_image_size')){
    function ifwp_add_cloudinary_image_size($name = '', $args = []){
        ifwp_maybe_load_cloudinary_api();
        IFWP_Cloudinary::add_image_size($name, $args);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_cloudinary')){
    function ifwp_cloudinary(...$args){
        ifwp_maybe_load_cloudinary_api();
		if(class_exists('\Cloudinary')){
            return new \Cloudinary(...$args);
		}
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_cloudinary_upload')){
    function ifwp_cloudinary_upload($attachment_id = 0, $options = []){
        if(ifwp_maybe_load_cloudinary_api()){
            $md5 = ifwp_md5($options);
            $image = get_post_meta($attachment_id, '_ifwp_cloudinary_image_' . $md5, true);
            if(!$image){
                $image = \Cloudinary\Uploader::upload(get_attached_file($attachment_id), $options);
                if($image instanceof \Cloudinary\Error){
                    return [];
                }
                update_post_meta($attachment_id, '_ifwp_cloudinary_image_' . $md5, $image);
            }
            return $image;
        }
        return [];
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_maybe_load_cloudinary_api')){
    function ifwp_maybe_load_cloudinary_api(){
		if(!class_exists('\Cloudinary')){
            $wp_upload_dir = wp_upload_dir();
            $dir = $wp_upload_dir['basedir'] . '/ifwp-plugin/cloudinary-api';
            if(is_dir($dir)){
                require_once($dir . '/vendor/autoload.php');
                ifwp_set_cloudinary_credentials();
                return true;
            }
		} else {
            return true;
        }
        return false;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_set_cloudinary_credentials')){
	function ifwp_set_cloudinary_credentials($cloud_name = '', $api_key = '', $api_secret = ''){
		if(!$cloud_name){
			$cloud_name = ifwp_tab_option('Cloudinary', 'API', 'cloud_name', '');
		}
        if(!$api_key){
			$api_key = ifwp_tab_option('Cloudinary', 'API', 'api_key', '');
		}
		if(!$api_secret){
			$api_secret = ifwp_tab_option('Cloudinary', 'API', 'api_secret', '');
		}
        if(!$cloud_name or !$api_key or !$api_secret){
            return false;
        }
        \Cloudinary::config([
            'cloud_name' => $cloud_name,
            'api_key' => $api_key,
            'api_secret' => $api_secret,
        ]);
        return true;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Cloudinary::load();
