<?php 

?>

<div class="dgzz-container">
    <h1>WP CUSTOM FONTS</h1>
    
    <div class="dgzz-custom-font-container">
        <div class="dgzz-custom-font-list">
            <h2><?php echo __('Custom Font List', 'dgzz') ?></h2>
            <?php

            $args = [
                'post_type' => 'dgzz_wp_custom_fonts'
            ];

            $the_query = new WP_Query( $args );
            
            if ( $the_query->have_posts() ) {
                echo '<ul>';
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    echo 
                        '<li>
                            <p>' . get_the_title() . '</p>
                            <div class="action-btn">
                                <a class="edit" href="post.php?post=' . get_the_ID() . '&action=edit">' . __('Edit', 'dgzz') .'</a>
                                <a class="delete" href="' . get_delete_post_link(get_the_ID()) . '">' . __('Delete', 'dgzz') .'</a>
                            </div>
                        </li>';
                }
                echo '</ul>';
            } else {
                echo '<ul>';
                    echo '<li><p>No Custom Fonts found</p></li>';
                echo '</ul>';
            }
            
            wp_reset_postdata();

            ?>
        </div>
        <div class="dgzz-custom-font-sidebar">
            <a href="post-new.php?post_type=dgzz_wp_custom_fonts"><?php echo __('Add New Custom Font', 'dgzz') ?></a>
        </div>
    </div>   


</div>