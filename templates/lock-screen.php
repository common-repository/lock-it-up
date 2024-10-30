<script type="text/javascript">
    var lock_bgs = []
    <?php
        $bgcolor = '';
        if(isset($bgs['images']) && is_array($bgs['images']) && !empty($bgs['images'])):
            foreach($bgs['images'] as $bg): ?>
                lock_bgs.push(
                    <?php echo "{ src: '$bg'}"; ?>
                );
                <?php
            endforeach;
        elseif(isset($bgs['color']) && $bgs['color'] != ''):
            $bgcolor = $bgs['color'];
        endif;
    ?>
</script>
<div id="lock-screen" class="body" <?php echo ($bgcolor != '') ? "style=\"background: $bgcolor; transition: background-color 1s linear;\"": ""; ?> >
    <div class="login-wrap">
        <?php
            global $current_user;
            get_currentuserinfo();
        ?>
        <div class="profile-pic"><?php echo get_avatar($current_user->ID, 117); ?></div>
        <div class="credential-cntnr">
             <h2><?php echo $current_user->display_name; ?> </h2>
            <p class="locked">
                <?php if(isset($error) && $error): ?>
                    <strong class="lock-error">Invalid password</strong>
                <?php else: ?>
                    Locked
                <?php endif; ?>
            </p>
            <form action="" method="post" id="wp-unlock-form">
                <input type="password" name="unlock-password" id="unlock-password" placeholder="Password" />
                <a href="javascript:void(0);" class="btn-unlock"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/img/login-arrow.png'; ?>" alt="" /></a>
            </form>
        </div>
       
    </div>
    <div class="widget-wrap">
        <div class="time-clock" id="lock-date-time">
            <h4></h4>
            <p></p>
        </div>
        <div class="normal-notifications">
            <ul>
                <li><a href="javascript:void(0);"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/img/comments-icon.png'; ?>" alt="" /> <?php echo $comments_count; ?> Comments</a></li>
                <li><a href="javascript:void(0);"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/img/pages-icon.png'; ?>" alt="" /> <?php echo $published_pages_count; ?> Published Pages</a></li>
                <li><a href="javascript:void(0);"><img src="<?php echo plugin_dir_url( __FILE__ ) . '/img/moderation-icon.png'; ?>" alt="" /> <?php echo $moderated; ?> in moderation</a></li>
            </ul>
        </div>
    </div>
</div>
