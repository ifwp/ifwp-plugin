<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Media')){
    class IFWP_Media {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){
			$media = ifwp_tab('', [
				'label' => 'Media',
				'icon' => 'dashicons-admin-media',
			]);
			$media->add_field('add_larger_image_sizes', [
				'label_description' => 'Image sizes: hd and full-hd.',
				'name' => 'Add larger image sizes?',
				'type' => 'switch',
			]);
			if($media->add_larger_image_sizes){
				add_image_size('hd', 1280, 1280);
				add_image_size('full-hd', 1920, 1920);
				add_filter('image_size_names_choose', function($sizes){
					$sizes['hd'] = 'HD';
					$sizes['full-hd'] = 'Full HD';
					return $sizes;
				});
			}
			$media->add_field('remove_accents', [
				'label_description' => 'Context: filenames.',
				'name' => 'Remove accents?',
				'type' => 'switch',
			]);
			if($media->remove_accents){
				add_filter('sanitize_file_name', 'remove_accents');
			}
			$media->add_field('solve_file_type_conflicts', [
				'label_description' => 'File types: audio and video.',
				'name' => 'Solve file type conflicts?',
				'type' => 'switch',
			]);
			if($media->solve_file_type_conflicts){
				add_filter('wp_check_filetype_and_ext', function($wp_check_filetype_and_ext, $file, $filename, $mimes, $real_mime){
					if($wp_check_filetype_and_ext['ext'] and $wp_check_filetype_and_ext['type']){
						return $wp_check_filetype_and_ext;
					}
					if(strpos($real_mime, 'audio/') === 0 or strpos($real_mime, 'video/') === 0){
						$filetype = wp_check_filetype($filename);
						if(in_array(substr($filetype['type'], 0, strcspn($filetype['type'], '/')), ['audio', 'video'])){
							$wp_check_filetype_and_ext['ext'] = $filetype['ext'];
							$wp_check_filetype_and_ext['type'] = $filetype['type'];
						}
					}
					return $wp_check_filetype_and_ext;
				}, 10, 5);
			}
		}

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function load(){
			add_action('ifwp_plugin_loaded', [__CLASS__, 'ifwp_plugin_loaded']);
        }

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// functions
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_attachment_url_to_postid')){
	function ifwp_attachment_url_to_postid($url = ''){
		if($url){
			/** original */
			$post_id = ifwp_guid_to_postid($url);
			if($post_id){
				return $post_id;
			}
            /** resized */
			preg_match('/^(.+)(-\d+x\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches);
			if($matches){
				$url = $matches[1];
				if(isset($matches[3])){
					$url .= $matches[3];
				}
                $post_id = ifwp_guid_to_postid($url);
				if($post_id){
					return $post_id;
				}
			}
			/** scaled */
			preg_match('/^(.+)(-scaled)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches);
			if($matches){
				$url = $matches[1];
				if(isset($matches[3])){
					$url .= $matches[3];
				}
                $post_id = ifwp_guid_to_postid($url);
				if($post_id){
					return $post_id;
				}
			}
			/** edited */
			preg_match('/^(.+)(-e\d+)(\.' . substr($url, strrpos($url, '.') + 1) . ')?$/', $url, $matches);
			if($matches){
				$url = $matches[1];
				if(isset($matches[3])){
					$url .= $matches[3];
				}
                $post_id = ifwp_guid_to_postid($url);
				if($post_id){
					return $post_id;
				}
			}
		}
		return 0;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_byte_value')){
    function ifwp_byte_value($value = ''){ // back-compat
        return wp_convert_hr_to_bytes($value);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_guid_to_postid')){
	function ifwp_guid_to_postid($guid = ''){
        global $wpdb;
		if($guid){
			$str = "SELECT ID FROM $wpdb->posts WHERE guid = %s";
			$sql = $wpdb->prepare($str, $guid);
			$post_id = $wpdb->get_var($sql);
			if($post_id){
				return (int) $post_id;
			}
		}
		return 0;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_is_extension_allowed')){
	function ifwp_is_extension_allowed($extension = ''){
        if(!$extension){
            return false;
        }
        foreach(wp_get_mime_types() as $exts => $mime){
            if(preg_match('!^(' . $exts . ')$!i', $extension)){
                return true;
            }
        }
        return false;
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_maybe_generate_attachment_metadata')){
	function ifwp_maybe_generate_attachment_metadata($attachment = null){
		$attachment = get_post($attachment);
		if(!$attachment or $attachment->post_type != 'attachment'){
			return [];
		}
		wp_raise_memory_limit('admin');
		wp_maybe_generate_attachment_metadata($attachment);
		$metadata = wp_get_attachment_metadata($attachment->ID);
		if(!$metadata){
			return [];
		}
		return $metadata;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!function_exists('ifwp_read_file_chunk')){
    function ifwp_read_file_chunk($handle = null, $chunk_size = 0){
    	$giant_chunk = '';
    	if(is_resource($handle) and is_int($chunk_size)){
    		$byte_count = 0;
    		while(!feof($handle)){
                $length = apply_filters('ifwp_read_file_chunk_lenght', (8 * KB_IN_BYTES));
    			$chunk = fread($handle, $length);
    			$byte_count += strlen($chunk);
    			$giant_chunk .= $chunk;
    			if($byte_count >= $chunk_size){
    				return $giant_chunk;
    			}
    		}
    	}
        return $giant_chunk;
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Media::load();
