<?php
/**
 * Template part for displaying staff posts list.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package VTS
 */
$the_query = new WP_Query(array(
        'post_type'        => 'staff',
        'posts_per_page'   => -1,
        'post_status'      => 'publish',
        'order'            => 'DESC',
        'orderby'          => 'menu_order',
    )
);
?>

<?php if ($the_query->have_posts() ): while($the_query->have_posts()): $the_query->the_post();
    $src = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), array(217,325));
    $phone_list = get_post_meta(get_the_ID(), '_dat_staff_phone', true); ?>
    <div class="col-lg-3 col-md-4 col-sm-4 <?php if ($the_query->current_post % 4 == 0): ?>clear-staff<?php endif; ?>">
        <div class="staff-img"> <img alt="" src="<?php echo $src[0]; ?>" /> </div>
        <div class="more-info">
            <h5><?php the_title(); ?></h5>
            <p><?php echo get_post_meta(get_the_ID(), '_dat_staff_position', true); ?></p>
            <ul class="employees">
                <?php foreach ($phone_list as $phone): ?>
                    <li><i class="fa fa-phone"></i><?php echo $phone['_dat_staff_phone_item']; ?></li>
                <?php endforeach; ?>
                <li> <i class="fa fa-pencil"></i> <a href="mailto:<?php echo get_post_meta(get_the_ID(), '_dat_staff_email', true); ?>"><?php echo get_post_meta(get_the_ID(), '_dat_staff_email', true); ?></a></li>
                <li> <i class="fa fa-skype"></i> <a href="skype:<?php echo get_post_meta(get_the_ID(), '_dat_staff_skype',  true); ?>"><?php echo get_post_meta( get_the_ID(), '_dat_staff_skype', true); ?></a></li>
            </ul>
        </div>
    </div>
<?php endwhile; endif; wp_reset_postdata(); ?>