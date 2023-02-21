<?php

function gmail_smtp_display_addons()
{
    /*
    echo '<div class="wrap">';
    echo '<h2>' .__('Gmail SMTP Add-ons', 'wp-login-form') . '</h2>';
    */
    $addons_data = array();

    $addon_1 = array(
        'name' => 'Reply-To',
        'thumbnail' => GMAIL_SMTP_URL.'/addons/images/gmail-smtp-reply-to.png',
        'description' => 'Set a Reply-To address for all outgoing email messages',
        'page_url' => 'https://wphowto.net/?p=6756',
    );
    array_push($addons_data, $addon_1);
    
    $addon_2 = array(
        'name' => 'Cc',
        'thumbnail' => GMAIL_SMTP_URL.'/addons/images/gmail-smtp-cc.png',
        'description' => 'Set a Cc recipient for all outgoing email messages',
        'page_url' => 'https://wphowto.net/?p=6770',
    );
    array_push($addons_data, $addon_2);
    
    //Display the list
    foreach ($addons_data as $addon) {
        ?>
        <div class="gmail_smtp_addons_item_canvas">
        <div class="gmail_smtp_addons_item_thumb">
            <img src="<?php echo esc_url($addon['thumbnail']);?>" alt="<?php echo esc_attr($addon['name']);?>">
        </div>
        <div class="gmail_smtp_addons_item_body">
        <div class="gmail_smtp_addons_item_name">
            <a href="<?php echo esc_url($addon['page_url']);?>" target="_blank"><?php echo esc_html($addon['name']);?></a>
        </div>
        <div class="gmail_smtp_addons_item_description">
        <?php echo esc_html($addon['description']);?>
        </div>
        <div class="gmail_smtp_addons_item_details_link">
        <a href="<?php echo esc_url($addon['page_url']);?>" class="gmail_smtp_addons_view_details" target="_blank">View Details</a>
        </div>    
        </div>
        </div>
        <?php
    }
    echo '</div>';//end of wrap
}
