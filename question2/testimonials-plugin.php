<?php
/*
Plugin Name: Testimonials Plugin
Description: Custom Testimonials system
Version: 1.0
Author: Ansh
*/

if(!defined('ABSPATH')) exit;

/* CPT */
function tp_register_testimonials(){
register_post_type('testimonials',[
'labels'=>[
'name'=>'Testimonials',
'singular_name'=>'Testimonial'
],
'public'=>true,
'supports'=>['title','editor','thumbnail'],
'show_in_rest'=>true,
'menu_icon'=>'dashicons-testimonial'
]);
}
add_action('init','tp_register_testimonials');


/* Meta Box */
function tp_add_meta_box(){
add_meta_box('tp_meta','Client Details','tp_meta_callback','testimonials');
}
add_action('add_meta_boxes','tp_add_meta_box');

function tp_meta_callback($post){
wp_nonce_field('tp_save_meta','tp_nonce');

$client=get_post_meta($post->ID,'client_name',true);
$position=get_post_meta($post->ID,'client_position',true);
$company=get_post_meta($post->ID,'company_name',true);
$rating=get_post_meta($post->ID,'rating',true);
?>

<p>Client Name <input type="text" name="client_name" value="<?php echo esc_attr($client); ?>" required></p>
<p>Position <input type="text" name="client_position" value="<?php echo esc_attr($position); ?>"></p>
<p>Company <input type="text" name="company_name" value="<?php echo esc_attr($company); ?>"></p>

<p>Rating
<select name="rating">
<?php for($i=1;$i<=5;$i++): ?>
<option value="<?php echo $i; ?>" <?php selected($rating,$i); ?>><?php echo $i; ?></option>
<?php endfor; ?>
</select>
</p>

<?php
}

/* Save Meta */
function tp_save_meta($post_id){
if(!isset($_POST['tp_nonce']) || !wp_verify_nonce($_POST['tp_nonce'],'tp_save_meta')) return;

update_post_meta($post_id,'client_name',sanitize_text_field($_POST['client_name']));
update_post_meta($post_id,'client_position',sanitize_text_field($_POST['client_position']));
update_post_meta($post_id,'company_name',sanitize_text_field($_POST['company_name']));
update_post_meta($post_id,'rating',intval($_POST['rating']));
}
add_action('save_post','tp_save_meta');


/* Shortcode */
function tp_shortcode($atts){

$atts=shortcode_atts([
'count'=>-1,
'orderby'=>'date',
'order'=>'DESC'
],$atts);

$q=new WP_Query([
'post_type'=>'testimonials',
'posts_per_page'=>$atts['count'],
'orderby'=>$atts['orderby'],
'order'=>$atts['order']
]);

ob_start();

echo '<div class="tp-slider">';

while($q->have_posts()):$q->the_post();

$client=get_post_meta(get_the_ID(),'client_name',true);
$pos=get_post_meta(get_the_ID(),'client_position',true);
$comp=get_post_meta(get_the_ID(),'company_name',true);
$rating=get_post_meta(get_the_ID(),'rating',true);

?>

<div class="tp-item">
<?php if(has_post_thumbnail()) the_post_thumbnail('thumbnail'); ?>

<p><?php the_content(); ?></p>

<p><strong><?php echo esc_html($client); ?></strong></p>
<p><?php echo esc_html($pos.' '.$comp); ?></p>

<p>
<?php for($i=1;$i<=5;$i++){
echo ($i <= $rating) ? "⭐" : "";
} ?>
</p>
</div>

<?php endwhile;

echo '</div>';

wp_reset_postdata();

return ob_get_clean();
}
add_shortcode('testimonials','tp_shortcode');
