<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $site_title; ?> | Locked</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='http://fonts.googleapis.com/css?family=Roboto:100,400,300,500' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" type="text/css" href="<?php echo plugin_dir_url( __FILE__ ) . 'css/wp-widget.css'; ?>" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <!--[if lt IE 9]>
            <script type='text/javascript' src="<?php echo plugin_dir_url( __FILE__ ) . 'js/html5shiv.js'; ?>"></script>
        <![endif]-->
        <script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) . 'js/jquery.vegas.min.js'; ?>"></script>
        
        <script>
            var direct_loding = true;
            var lock_plugin_url = '<?php echo $plugin_dir; ?>';
            var lock_admin_url = '<?php echo $admin_url; ?>';
            
            var lock_bgs = []
            <?php
                $div_style = 'margin:0; padding:0;';
                if(isset($bgs['images']) && is_array($bgs['images']) && !empty($bgs['images'])):
                    foreach($bgs['images'] as $bg): ?>
                        lock_bgs.push(
                            <?php echo "{ src: '$bg'}"; ?>
                        );
                        <?php
                    endforeach;
                elseif(isset($bgs['color']) && $bgs['color'] != ''):
                    $div_style = 'background:'.$bgs['color'].';transition: background-color 1s linear;';
                endif;
            ?>
        </script>
        <script type="text/javascript" src="<?php echo $plugin_dir; ?>/admin.js"></script>
    </head>
    <body class=" lockoverflow lock-black-back" style="<?php echo $div_style; ?>">
        <div id='lock-mask'></div>
        <div id="lock-screen" class="body" >
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
    </body>
</html>
