<?php

/*
    Plugin Name: Lock It Up
    Description: Real bloggers can lock their account and easily login to the system with only password
    Still we recommend you to save your edits
    Author: Quintet Solutions 
    Author URI: http://quintetsolutions.com
    Tags: password, login, form, lock  
    Version: 1.0
*/


if(!class_exists('QLockScreen')){
    if(!function_exists('wp_get_current_user')) {
        include(ABSPATH . "wp-includes/pluggable.php"); 
    }
    /**
     *@package WP Lock Screen
     *@author Quintet
     *@link http://quintetsolutions.com
     */
    class QLockScreen {
        var $current_user;
        
        /* constructor function */
        function __construct(){
            if(!$this->is_mobile()){
                $this->current_user = wp_get_current_user();
                
                register_activation_hook( __FILE__, array( $this, 'plugin_activated'));
                //register_deactivation_hook( __FILE__ , array( $this, 'plugin_deactivated'));
                
                add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_script'));
                add_action( 'wp_before_admin_bar_render', array($this, 'add_lock_button' ));
                
                add_action( 'wp_ajax_lock_me', array( $this, 'lock_me' ));
                add_action( 'wp_ajax_nopriv_lock_me', array( $this, 'lock_me' ));
                
                add_action( 'wp_ajax_unlock_me', array( $this, 'unlock_me' ));
                add_action( 'wp_ajax_nopriv_unlock_me', array( $this, 'unlock_me' ));
                
                add_action( 'wp_ajax_locked_getmesomething', array( $this, 'locked_getmesomething' ));
                add_action( 'wp_ajax_nopriv_locked_getmesomething', array( $this, 'locked_getmesomething' ));
                
                add_action( 'admin_menu', array( $this, 'load_menus'));
                
                add_action( 'admin_head', array($this, 'add_menu_item_icon_style' ));
                
                add_action( 'wp_login', array( $this, 'init'), 10, 2);
                
                add_filter( 'heartbeat_received', array($this, 'lock_heartbeat_receive'), 10, 2 );
                add_filter( 'heartbeat_nopriv_received', array($this, 'lock_heartbeat_receive'), 10, 2 );
                
                add_action( 'user_register', array( $this, 'update_bg_for_new_user'), 10, 1 );
                
                add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'load_action_links' ));
                
                $this->check_lock();
            }
        }
        
        /* function will invoke while activating plugin*/
        function plugin_activated(){
            $bgs = $this->get_bgs();
            
            $this->save_def_bgs(); // checking all user backgrounds
        }
        
        /* function will invoke on plugin deactivation*/
        function plugin_deactivated(){
            $users = get_users( array( 'fields' => array( 'ID' ) ) );
            foreach($users as $user){
                delete_user_option($user->ID, 'wp-lock-bg-solid');
                delete_user_option($user->ID, 'wp-lock-bg');
            }
        }
        
        /*function will check all users and will set bg image for user if user already changed */
        function save_def_bgs(){
            $bgs = $this->get_all_def_images(); // getting all images from asstes/img/bg of plugin
            $users = get_users( array( 'fields' => array( 'ID' ) ) ); //getting all wp users
            if(!empty($bgs)){
                foreach($users as $user){
                    $userbgs = $this->get_bgs($user->ID); //getting user bg image or color
                    if((isset($userbgs['color']) && trim($userbgs['color']) == '') || (isset($userbgs['images']) && empty($userbgs['images']))){
                        delete_user_option( $user->ID, 'wp-lock-bg-solid');
                        update_user_option( $user->ID, 'wp-lock-bg', serialize($bgs));
                    }
                }
            }
            else{
                foreach($users as $user){
                    $userbgs = $this->get_bgs($user->ID);
                    if((isset($userbgs['color']) && trim($userbgs['color']) == '') || (isset($userbgs['images']) && empty($userbgs['images']))){
                        delete_user_option( $user->ID, 'wp-lock-bg');
                        update_user_option( $user->ID, 'wp-lock-bg-solid', '#006AC1');
                    }
                }
            }
        }
        
        /* getting all images from asstes/img/bg of plugin
         * @return array all background images from templates/img/bg inside this plugin folder
         * */
        function get_all_def_images(){
            $dir = opendir(dirname( __FILE__ ) .'/templates/img/bg');
            $bgs = array();
            while($file = readdir($dir)):
                if($file == "." || $file == "..") continue;
                $bgs[] = plugin_dir_url( __FILE__ ) .'templates/img/bg/'.$file;
            endwhile;
            return $bgs;
        }
        
        /* function will execute on user login or user unlock screen*/
        function init(){
            delete_user_option( $this->current_user->ID, $this->get_my_cookie());
            delete_user_option( $this-> current_user->ID, 'wp-lock-auto-lock');
            $this->update_last_activity();
            $this->set_my_cookie();
        }
        
        /* function will invoke on starting and include all script and css of plugin*/
        function load_plugin_script(){
            if ( function_exists( 'wp_enqueue_media' )){
                wp_enqueue_media();
            }
            wp_enqueue_style( 'q-lock-screen-style', plugins_url('admin.css', __FILE__)); wp_enqueue_style( 'q-lock-screen-font', 'http://fonts.googleapis.com/css?family=Roboto:100,400,300,500'); 
            wp_enqueue_style( 'q-lock-screen-page-style', plugins_url('templates/css/wp-widget.css', __FILE__));
            wp_enqueue_style( 'q-lock-screen-page-vagas-css', plugins_url('templates/css/jquery.vegas.min.css', __FILE__));
            wp_enqueue_style( 'q-lock-admin-datepicker-css', plugins_url('templates/css/jquery.datetimepicker.css', __FILE__));
            
            wp_enqueue_script( 'q-lock-screen-admin-vegas-js', plugins_url('templates/js/jquery.vegas.min.js', __FILE__), array('jquery'), '1.0.0', true );
            
            wp_enqueue_script( 'q-lock-screen-datepicker', plugins_url('templates/js/jquery.datetimepicker.js', __FILE__), array('jquery'), '1.0.0', true );
            wp_enqueue_script( 'q-lock-screen-admin-js', plugins_url('admin.js', __FILE__), array('jquery'), '1.0.0', true );
            
            $plugin_dir_url = plugin_dir_url( __FILE__ );
            $admin_url = admin_url();
            
            echo "<script>";
                echo "lock_plugin_url = '$plugin_dir_url';";
                echo "lock_admin_url = '$admin_url';";
            echo "</script>";
        }
        
        
        /* function will add a lock link above logout link*/
        function add_lock_button(){
            global $wp_admin_bar;
            
            $logout_node = $wp_admin_bar->get_node( 'logout' );
            
            $wp_admin_bar->remove_node('logout');
            $args = array(
                'id'    => 'wp-lock-button',
                'title' => 'Lock ( Ctrl + q )',
                'href'  => "#",
                'meta'  => array( 'class' => 'wp-lock-button' ),
                'parent' => 'user-actions'
            );
            $wp_admin_bar->add_node( $args );    
            $wp_admin_bar->add_node( $logout_node);
        }
        
        /* function will check current user password and user entered password in lock screen*/
        function unlock_me(){
            $password = trim($_POST['userpasswd']);
            $creds = array(
                'user_login'    => $this->current_user->user_login, 
                'user_password' => $password, 
                'remember' 	   => false
            );
            
            $user = wp_signon( $creds, false );
            if ( is_wp_error($user) ):
                die(json_encode(false));
            else:
                delete_user_option( $this->current_user->ID, $this->get_my_cookie());
                $this->unset_cookie();
                die(json_encode(true));
            endif;
        }
        
        /* function to lock the screen*/
        function lock_me(){
            update_user_option( $this->current_user->ID, $this->get_my_cookie(), 'locked');
            die($this->get_lock_screen());
        }
        
        /* will invoke on user login or unlock, will create a cookie with a unique value*/
        function set_my_cookie(){
            $unique_id = $this->get_me_unique_value();
            $expiration = time() + apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $this->current_user->ID, false );
            setcookie( 'wp_lock_system', $unique_id, $expiration, COOKIEPATH, COOKIE_DOMAIN );
        }
        
        /* function will return the unique value from cookie
         * @return string will return unique value from cooking or null
         * */
        function get_my_cookie(){
            return (isset($_COOKIE['wp_lock_system'])) ? $_COOKIE['wp_lock_system'] : '';
        }
        
        /* function will destroy cookie variable which used by lock screen plugin*/
        function unset_cookie(){
            if(isset($_COOKIE['wp_lock_system'])) unset($_COOKIE['wp_lock_system']);
            setcookie('wp_lock_system', null, strtotime('-1 day'));
        }
        
        /* function will create a unique value with "wp_lock_system", "username", "timestamp", "user ip" and "useragent"*/
        function get_me_unique_value(){
            return md5("wp_lock_system:". $this->current_user->username . ":" . time() . ":" . $user_agent_ip_string = $_SERVER['REMOTE_ADDR'] . ":" . $_SERVER['HTTP_USER_AGENT']);
        }
        
        /* function will return lock screen html for ajax lock
         * all the js and css are include with load_plugin_script
         * @return string html content, without html, head and body
        */
        function get_lock_screen(){
            $comments_count = wp_count_comments();
            extract(array(
                'bgs' => $this->get_bgs(),
                'comments_count' => $comments_count->total_comments,
                'moderated' => $comments_count->moderated,
                'published_pages_count' => wp_count_posts( 'page')->publish,
                'error' => ( isset( $this->error ) &&  $this->error ) ? true : false
            ));
            
            ob_start();                    // Start output buffering
            include(dirname( __FILE__ ) . '/templates/lock-screen.php');                // Include the file
            $contents = ob_get_contents(); // Get the contents of the buffer
            ob_end_clean();                // End buffering and discard
            return $contents;              // Return the contents
        }
        
        
        /* function will return full html of lock screen including html, head, body tags,
         * all css and js will include from template
         * @return string html content, with html, head and body
        */
        function get_full_lock_screen(){
            $comments_count = wp_count_comments();
            extract(array(
                'bgs' => $this->get_bgs(),
                'site_title' => get_bloginfo('name'),
                'plugin_dir' => plugin_dir_url( __FILE__ ) ,
                'admin_url' =>  admin_url(),
                'comments_count' => $comments_count->total_comments,
                'moderated' => $comments_count->moderated,
                'published_pages_count' => wp_count_posts( 'page')->publish,
                'error' => ( isset( $this->error ) &&  $this->error ) ? true : false
            ));
            
            ob_start();                    // Start output buffering
            include(dirname( __FILE__ ) . '/templates/lock-screen-full.php');                // Include the file
            $contents = ob_get_contents(); // Get the contents of the buffer
            ob_end_clean();                // End buffering and discard
            return $contents;              // Return the contents
        }
        
        /* function will check whether user already locked or not
         * function will skip checkin few ajax requests
         * */
        function check_lock(){
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] == 'locked_getmesomething' ) {
                /*user already locked, but we need some request to serever, to check whether user unlocked from any other tab
                 *also cheating server session timeout*/
                die(json_encode(array(
                    "is_locked" => $this->is_locked(),
                    "current_server_time" => time()
                )));
            }
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] == 'heartbeat' ) {
                return;
            }
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] == 'unlock_me' ) {
                return;
            }
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && $_REQUEST['action'] == 'lock_me' ) {
                return;
            }
            if(is_admin() && $this->is_locked()){
                /* we hope this is not an ajax lock request, so we're sending full lock screen html*/
                die($this->get_full_lock_screen());
            }
            elseif(!is_admin() && $this->is_locked()){
                /* this is used to front-end, if user locked dashboard and viewing front-end*/
                show_admin_bar(false);
                add_filter( 'show_admin_bar', '__return_false' );
            }
            elseif(is_admin()){
                $this->update_last_activity();
            }
            
        }
        
        /* function will check whether user locked or not true or false
         * @return boolean true if locked else false
         * */
        function is_locked(){
            $locked = get_user_option($this->get_my_cookie(), $this->current_user->ID);
            if($locked && $locked == "locked"){
                return true;
            }
            return false;
        }
        
        /* function will retuern user lock screen backgroun, if $userID not password will return current user's
         * @return array images and colors
         * */
        function get_bgs($userID = false){
            $userID = ( !$userID ) ? $this->current_user->ID : $userID;
            $return = array();
            $selected = get_user_option('wp-lock-bg', $userID);
            $return['images'] = (is_array($selected)  && !empty($selected)) ? $selected : array();
            $solid_color = get_user_option('wp-lock-bg-solid', $userID);
            $return['color'] = ($solid_color && $solid_color != '') ? $solid_color : '';
            return $return;
        }
        
        /* function will add admin screen menus for Lock screen settings*/
        function load_menus(){
            add_menu_page('Lock It Up', 'Lock It Up', 'edit_posts', 'wp-lock-settings', array( $this, 'settings') );
            //add_plugins_page('WP Lock Screen Settings', 'WP Lock Screen Settings', 'edit_posts', 'wp-lock-settings', array( $this, 'settings') );
        }
        
        /* function will add action links in plugins page*/
        function load_action_links( $links ) {
            
            $new_links = array();
            
            $new_links[] = '<a href="'. get_admin_url(null, 'admin.php?page=wp-lock-settings') .'">Settings</a>';
            $new_links[] = $links['deactivate'];
            
            return $new_links;
        }
        
        /* Function will execute on when user click Lock screen settings menu */
        function settings(){
            if(isset($_POST['submit'])){
                if(isset($_POST['solid-color'])){
                    update_user_option( $this->current_user->ID, 'wp-lock-bg-solid', $_POST['solid-color']);
                }
                else{
                    update_user_option( $this->current_user->ID, 'wp-lock-bg-solid', "");
                }
                if(isset($_POST['wp-lock-screen-bg']) && !empty($_POST['wp-lock-screen-bg'])){
                    update_user_option( $this->current_user->ID, 'wp-lock-bg', $_POST['wp-lock-screen-bg']);
                }
                else{
                    update_user_option( $this->current_user->ID, 'wp-lock-bg', "");
                }
                
                if(isset($_POST['auto_lock_status']) && $_POST['auto_lock_status'] == 'on' && trim($_POST['lock-datetimepicker']) != ''){
                    update_user_option($this->current_user->ID, 'wp-lock-auto-lock', strtotime($_POST['lock-datetimepicker']));
                }
                else{
                    delete_user_option( $this->current_user->ID, 'wp-lock-auto-lock');
                }
                
                if(isset($_POST['auto_idle_lock_status']) && $_POST['auto_idle_lock_status'] == 'on' && trim($_POST['auto_idle_lock_timeout']) != ''){
                    update_user_option($this->current_user->ID, 'wp-lock-idle-timeout', $_POST['auto_idle_lock_timeout']);
                }
                else{
                    delete_user_option( $this->current_user->ID, 'wp-lock-idle-timeout');
                }
                
            }
            $colors = $this->get_me_colors();
            include(dirname( __FILE__ ) .'/settings.php');
        }
        
        /* function will return predifined colors
         * @return array hex code of colors
         * */
        function get_me_colors(){
            $colors = array_unique(array(
                '#99B433', '#00A300', '#1E7145', '#FF0097', '#9F00A7', '#7E3878', '#603CBA', '#1D1D1D',
                '#F3B200', '#77B900', '#2572EB', '#AD103C', '#632F00', '#B01E00', '#C1004F', '#7200AC',
                '#4617B4', '#006AC1', '#008287', '#00C13F', '#FF981D', '#FF2E12', '#FF1D77', '#AA40FF',
                '#1FAEFF', '#56C5FF', '#00D8CC', '#91D100', '#E1B700', '#FF76BC', '#00A3A3', '#575657',
                '#FE7C22', '#008299', '#2672EC', '#8C0095', '#5133AB', '#AC193D', '#D24726', '#008A00',
                '#094AB2', '#C27D4F', '#7F6E94', '#CEA539', '#E773BD', '#D36170', '#DBF355', '#062558',
                '#EA34F3'
            ));
            
            return $colors;
        }
        
        /* function will change the icon of dashboard right side menu 'Lock Screen'*/
        function add_menu_item_icon_style(){
            ?>
            <style type="text/css">
                #adminmenu #toplevel_page_wp-lock-settings div.wp-menu-image:before {
                    content: "\f160";
                }
            </style>
            <?php
        }
        
        /* wp heartbeat request has been received and we're sending is_locked along with heartbeat*/
        function lock_heartbeat_receive(){
            $response['is_locked']  = $this->is_locked();
            if(!$response['is_locked']){
                $response['lock_now'] = $this->check_auto_lock();
            }
            if(!$response['is_locked'] && !$response['lock_now']){
                $response['lock_idle'] = $this->check_idle_lock_timeout();
            }
            return $response;
        }
        
        /* function will check the timed lock, and will retun true if its time to lock
         * @return boolen true for logout else false
        */
        function check_auto_lock(){
            $autlock = get_user_option('wp-lock-auto-lock', $this->current_user->ID);
            if ( $autlock && $autlock > 0 && $autlock < time() ){
                return true;
            }
            return false; 
        }
        
        
        /* function will check idle timeout, and will return true if user dons't have any activity for specified period
         * @return boolen true for logout else false
        */
        function check_idle_lock_timeout(){
            $time = get_user_meta($this->current_user->ID, 'lock_last_active_time', true);
            if ( is_numeric($time) ) {
                $idle_timeout = get_user_option('wp-lock-idle-timeout', $this->current_user->ID);
                if($idle_timeout != '' && $idle_timeout > 0){
                    if ( (int) $time + $idle_timeout < time() ) {
                        return true;
                    }
                }
            }
            $this->update_last_activity();
            return false;
        }
        
        /* function will user last activity time*/
        function update_last_activity(){
            update_user_meta( $this->current_user->ID, 'lock_last_active_time', time() );
        }
        
        
        function locked_getmesomething(){
            /*user already locked, but we need some request to serever, to check whether user unlocked from any other tab
            *also cheating server session timeout
            *this method will not execute because we're doing same in check_lock method
            */
            die(json_encode(array('is_locked' => $this->is_locked(), 'time'=> time())));
        }
        
        /* function will invoke when new user created and will set bg for newly created users*/
        function update_bg_for_new_user($userID){
            $bgs = $this->get_all_def_images();
            if(!empty($bgs)){
                delete_user_option( $userID, 'wp-lock-bg-solid');
                update_user_option( $userID, 'wp-lock-bg', serialize($bgs));
            }
            else{
                delete_user_option( $userID, 'wp-lock-bg');
                update_user_option( $userID, 'wp-lock-bg-solid', '#006AC1');
            }
        }
        
        /* we're not using wp default wp_is_mobile, we use custom function to exclude ipad
         * @return boolen true if mobile else false
        */
        function is_mobile() {
            static $is_mobile;
        
            if ( isset($is_mobile) )
                return $is_mobile;
        
            if ( empty($_SERVER['HTTP_USER_AGENT']) ) {
                $is_mobile = false;
            } elseif (
                strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
                || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
                || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
                || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
                || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false ) {
                    $is_mobile = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') == false) {
                    $is_mobile = true;
            } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false) {
                $is_mobile = false;
            } else {
                $is_mobile = false;
            }
        
            return $is_mobile;
        }
    }
    
    new QLockScreen;
}
