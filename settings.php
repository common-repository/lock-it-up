<div class="wrap wp-lock-screen-settings">
    <h2>Lock It Up Settings</h2>
    <form action="" method="post">
        <h3>Background Color</h3>
        <?php $solid_color = get_user_option('wp-lock-bg-solid', $this->current_user->ID); ?>
        <div class="lock-color-pallette">
            <?php foreach($colors as $key=>$color): ?>
                <div class="single-palette">
                    <?php $checked = ($solid_color == $color) ? "checked='checked'" : ""; ?>
                    <input type="radio" name="solid-color" id="solid-color-<?php echo $key; ?>"  value="<?php echo $color; ?>" <?php echo $checked; ?> />
                    <label for="solid-color-<?php echo $key; ?>" style="background: <?php echo $color; ?>"></label>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="clear cb "></div>
        <input type="submit" name="submit" class="button button-primary" value="Save Changes">
            
        <h3>Background Images</h3>
        <?php
            $selected = get_user_option('wp-lock-bg', $this->current_user->ID);
            $selected = (is_array($selected) && !empty($selected)) ? $selected : array();
            $dir = opendir(dirname( __FILE__ ) .'/templates/img/bg');
            $count = 1;
            while($file = readdir($dir)):
            if($file == "." || $file == "..") continue;
                ?>
            <div class="img_container">
                <?php $checked = (in_array(plugin_dir_url( __FILE__ ) .'templates/img/bg/'.$file, $selected)) ? "checked='checked'" : ""; ?>
                <input type="checkbox" name="wp-lock-screen-bg[]" id="wp-lock-screen-bg-<?php echo $count; ?>" value="<?php echo plugin_dir_url( __FILE__ ) .'templates/img/bg/'.$file; ?>" <?php echo $checked; ?> />
                <label for="wp-lock-screen-bg-<?php echo $count; ?>">
                    <img src="<?php echo plugin_dir_url( __FILE__ ) .'templates/img/bg/'.$file; ?>"  />
                </label>
            </div>
                <?php
            $count++;
            endwhile;
            closedir($dir);
        ?>
        <div class="clear cb"></div>
        <h3>From Your media</h3>
        <div id="lock-media-images">
            <?php
                $query_images_args = array(
                    'post_type' => 'attachment', 'post_mime_type' =>'image', 'post_status' => 'inherit', 'posts_per_page' => -1,
                    'order' => 'ASC'
                );
                $query_images = new WP_Query( $query_images_args );
                $images = 0;
                foreach ( $query_images->posts as $image):
                    $images++;
                    ?>
                    <div class="img_container">
                        <?php $checked = (in_array(wp_get_attachment_url( $image->ID ), $selected)) ? "checked='checked'" : ""; ?>
                        <input type="checkbox" name="wp-lock-screen-bg[]" id="wp-lock-screen-bg-<?php echo $count; ?>" value="<?php echo wp_get_attachment_url( $image->ID ); ?>" <?php echo $checked; ?> />
                        <label for="wp-lock-screen-bg-<?php echo $count; ?>">
                            <img src="<?php echo wp_get_attachment_url( $image->ID ); ?>"  />
                        </label>
                    </div>
                    <?php
                        $count++;
                endforeach;
                if($images == 0):
                    ?>
                    <p class="nothing-found">No images available in library</p>
                    <?php
                endif;
            ?>
            <div class="lock-dynamic-images"></div>
            
            <div class="lock-btn-div" id="btn-lock-upload">
                <label title="Add New Image" alt="Add New Image">
                    <img src="<?php echo plugin_dir_url( __FILE__ ) . 'templates/img/add.png'; ?>" alt="Add New Image" />
                </label>
            </div>
        </div>
        
        <div class="clear cb "></div>
        <input type="submit" name="submit" class="button button-primary" value="Save Changes">
            
        <div class="clear cb"></div>
        <h3>Auto Lock</h3>
        <?php
            $idlelock = get_user_option('wp-lock-idle-timeout', $this->current_user->ID);
            $checked = ( $idlelock && $idlelock > 0 ) ?  " checked='checked' ": "";
        ?>
        
        
        <div class="lock_setting_group">
            <label for="auto_lock_status">Enable Auto Lock</label>
            <input type="checkbox" name="auto_idle_lock_status" id="auto_idle_lock_status" <?php echo $checked; ?> />
            <input type="number" name="auto_idle_lock_timeout" id="auto_idle_lock_timeout" value="<?php echo ( $idlelock && $idlelock > 0 ) ? $idlelock : 10 ; ?>" min="60" class="txtbox" />
            <p class="description">How long (in seconds) should users be idle for before being logged out?</p>
        </div>
        
        <?php
            $autlock = get_user_option('wp-lock-auto-lock', $this->current_user->ID);
            $checked = ( $autlock && $autlock > 0 ) ?  " checked='checked' ": "";
        ?>
        
        <div class="lock_setting_group">
            <label for="auto_lock_status">Enable Timed Lock</label>
            <input type="checkbox" name="auto_lock_status" id="auto_lock_status" <?php echo $checked; ?> />
            <p class="description">Will lock all active sessions, on specified time.</p>
        </div>
        <div class="lock_setting_group">
            <label>When? </label>
            <input type="text" id="lock-datetimepicker" name="lock-datetimepicker" data-selected="<?php echo ($autlock && $autlock > 0) ? date("d-m-Y H:i", $autlock) : date("d-m-Y H:i:s"); ?>" />
            <p class="description">All about server time. Right now its <?php echo date("d-m-Y H:i:s"); ?></p>
        </div>
        
        <div class="wp-save"></div>
        <div class="clear cb "></div>
        <input type="submit" name="submit" class="button button-primary" value="Save Changes">
    </form>
</div>