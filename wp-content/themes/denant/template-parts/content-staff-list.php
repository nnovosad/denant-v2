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

<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/denis.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Денис Бакач</h5>-->
<!--        <p>Директор</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(33)623-55-37 (МТС) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)139-33-62 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:6235537@gmail.com">6235537@gmail.com</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:denis_bakach">denis_bakach</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img">-->
<!--        <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/anton.jpg" />-->
<!--    </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Антон Кирилко</h5>-->
<!--        <p>Главный бухгалтер</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)265-64-05 (Viber) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)136-96-05 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:krendel-18-46@mail.ru">krendel-18-46@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:antonkakirilka">antonkakirilka</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/verobey.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Ольга Веробей</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(33)613-04-49 (Viber/WhatsApp) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)590-67-59 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:olga.verobey@mail.ru">olga.verobey@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:live:olechka_verobey ">live:olechka_verobey</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/pavel.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Павел Гладышев</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)507-55-35 (Viber/WhatsApp/Telegram) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)773-48-77 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:pashagladishev@mail.ru">pashagladishev@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:pashagladishev2">pashagladishev2</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4 clear-staff">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/sveta.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Светлана Беленко</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)586-24-32 (МТС/Viber) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)749-76-70 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:5862432@mail.ru">5862432@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:svetlanavseall">svetlanavseall</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/olga.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Ольга Гуренко</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)780-68-57 (МТС) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)678-58-66 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:hurenkoko@mail.ru">hurenkoko@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:hurenkoko">hurenkoko</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/alex_sokol.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Александр Соколовский</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)782-96-74 (Viber/WhatsApp) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:7829674@gmail.com">7829674@gmail.com</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:vseall_alexandr">vseall_alexandr</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/vova.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Владимир Жук</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(33)679-07-90 (Viber) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)553-24-25 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i><a href="mailto:vladimirzhuk790@gmail.com">vladimirzhuk790@gmail.com</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:vladimirzhuk790">vladimirzhuk790</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4 clear-staff">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/alexandra.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Александра Музычко</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)589-99-00 (Viber) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)538-01-52 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:muzychkos@mail.ru">muzychkos@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:Muzychkos1">Muzychkos1</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->
<!---->
<!--<div class="col-lg-3 col-md-4 col-sm-4">-->
<!--    <div class="staff-img"> <img alt="" src="--><?php //echo get_template_directory_uri(); ?><!--/images/team/andrei.jpg" /> </div>-->
<!--    <div class="more-info">-->
<!--        <h5>Андрей Волков</h5>-->
<!--        <p>Логист</p>-->
<!--        <ul class="employees">-->
<!--            <li> <i class="fa fa-phone"></i> +375(29)268-88-37 (МТС/Viber) </li>-->
<!--            <li> <i class="fa fa-phone"></i> +375(44)749-75-15 (Велком) </li>-->
<!--            <li> <i class="fa fa-pencil"></i> <a href="mailto:andrey.denant@mail.ru">andrey.denant@mail.ru</a></li>-->
<!--            <li> <i class="fa fa-skype"></i> <a href="skype:volkov9101">volkov9101</a></li>-->
<!--        </ul>-->
<!--    </div>-->
<!--</div>-->