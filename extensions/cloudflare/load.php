<?php

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// classes
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if(!class_exists('IFWP_Cloudflare')){
    class IFWP_Cloudflare {

        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        //
        // static public
        //
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

        static public function ifwp_plugin_loaded(){
            if(ifwp_seems_cloudflare()){
                $checklist = ifwp_tab('Cloudflare', [
                    'icon' => 'dashicons-yes-alt',
                    'label' => 'Checklist',
                ]);
                $items = [];
                $items['Network > IP Geolocation'] = isset($_SERVER['HTTP_CF_IPCOUNTRY']) ? ifwp_checklist_success() : ifwp_checklist_error();
                $post_max_size = wp_convert_hr_to_bytes(ini_get('post_max_size'));
                $items['PHP > post_max_size <= Network > Maximum Upload Size (100 MB)'] = ($post_max_size <= (100 * MB_IN_BYTES) ? ifwp_checklist_success() : ifwp_checklist_error()) . ' (' . size_format($post_max_size) . ')';
                $checklist->add_field([
                    'name' => 'Automatically detected',
                    'std' => ifwp_checklist_table($items),
                    'type' => 'custom_html',
                ]);
                $items = [];
                $domain = wp_parse_url(site_url(), PHP_URL_HOST);
                $items['SSL/TLS > Encryption Mode'] = 'Full';
                $items['SSL/TLS > Edge Certificates <code>*.' . $domain . ', ' . $domain . '</code>'] = 'Active';
                $items['SSL/TLS > Always Use HTTPS'] = 'On';
                $items['SSL/TLS > Automatic HTTPS Rewrites'] = 'On';
                $items['Speed > Optimization > Auto Minify'] = 'JavaScript, CSS, HTML';
                $items['Speed > Optimization > Rocket Loader™'] = 'On';
                $items['Caching > Configuration > Caching Level'] = 'Standard';
                $items['Caching > Configuration > Browser Cache TTL'] = '>= 8 days';
                $items['Page Rules > <code>*' . $domain . '/*wp-login.php*</code>'] = 'Security Level: High';
                $items['Page Rules > <code>*' . $domain . '/*wp-admin/*</code>'] = 'Security Level: High, Cache Level: Bypass, Disable Apps, Disable Performance';
                $items['Page Rules > <code>*' . $domain . '/*fl_builder</code>'] = 'Rocket Loader: Off';
                $checklist->add_field([
                    'name' => 'Must be manually checked',
                    'std' => ifwp_checklist_table($items, false),
                    'type' => 'custom_html',
                ]);
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

if(!function_exists('ifwp_seems_cloudflare')){
    function ifwp_seems_cloudflare(){
        return !empty($_SERVER['HTTP_CF_RAY']);
    }
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//
// loader
//
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

IFWP_Cloudflare::load();
