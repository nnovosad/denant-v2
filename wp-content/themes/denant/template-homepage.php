<?php
/**
 * Template Name: Homepage
 */
?>

<?php get_header(); ?>

<section class="section white-bg" id="section2">
    <div id="ancor2"></div>
    <div class="container clearfix">
        <div class="col-lg-12 col-md-4">
            <h3><?php echo get_post_meta($post->ID, '_dat_about_title', true) ? : 'О нас'; ?></h3>
            <div class="divider"></div>
            <?php while ( have_posts() ) : the_post();
                the_content();
            endwhile; ?>
        </div>
    </div>
</section>


<section class="section" id="section3">
    <div id="ancor3"></div>
    <div class="subtitle">
        <h3><?php echo get_post_meta($post->ID, '_dat_services_metabox_block', true) ? : 'Услуги'; ?></h3>
        <div class="divider"></div>
    </div>

    <div class="subtitle"> <h4> I. <?php echo get_post_meta($post->ID, '_dat_services_subsection1_title', true) ? : 'Автомобильные перевозки грузов'; ?> </h4> </div>

    <div class="container clearfix">
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4><?php echo get_post_meta($post->ID, '_dat_services_complete_title', true) ? : 'Комплектные'; ?></h4>
                </div>
                <?php if (!empty(get_post_meta($post->ID, '_dat_services_complete_text', true))): ?>
                <p class="price-details">
                    <?php echo get_post_meta($post->ID, '_dat_services_complete_text', true); ?>
                </p>
                <?php endif; ?>
            </figure>
        </div>
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4><?php echo get_post_meta($post->ID, '_dat_services_national_title', true) ? : 'Сборные'; ?></h4>
                </div>
                <?php if (!empty(get_post_meta($post->ID, '_dat_services_national_text', true))): ?>
                <p class="price-details">
                    <?php echo get_post_meta($post->ID, '_dat_services_national_text', true); ?>
                </p>
                <?php endif; ?>
            </figure>
        </div>
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4><?php echo get_post_meta($post->ID, '_dat_services_oversized_title', true) ? : 'Негабаритные'; ?></h4>
                </div>
                <?php if (!empty(get_post_meta($post->ID, '_dat_services_national_text', true))): ?>
                <p class="price-details">
                    <?php echo get_post_meta($post->ID, '_dat_services_oversized_text', true); ?>
                </p>
                <?php endif; ?>
            </figure>
        </div>

        <div class="col-lg-12 col-md-4 col-md-4-storage">
            <div class="subtitle subtitle-services"> <h4> II. <?php echo get_post_meta($post->ID, '_dat_services_subsection2_title', true) ? : 'Складское хранение'; ?> </h4> </div>
            <p><?php echo get_post_meta($post->ID, '_dat_services_subsection2_text', true); ?></p>
        </div>

    </div>

</section>

<section class="section" id="section4">
    <div id="ancor4"></div>
    <div class="subtitle">
        <h3>Сотрудники</h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix staff">
        <div class="col-lg-12 col-md-12">
            <div class="row">

                <?php while ( have_posts() ) : the_post();
                    get_template_part( 'template-parts/content', 'staff-list' );
                endwhile; ?>

            </div>
        </div>
    </div>
</section>

<section class="section white-bg" id="section5">
    <div class="subtitle">
        <h3><?php echo get_post_meta($post->ID, '_dat_documents_metabox_block', true) ? : 'Документы'; ?></h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix documents">
        <div class="col-md-6">
            <div class="document-link">
                <a href="<?php echo get_post_meta($post->ID, '_dat_certificate_file', true); ?>" class="button-document" download>
                    <?php echo get_post_meta($post->ID, '_dat_certificate_text', true) ? : 'Скачать Сертификат'; ?>
                </a>
                <a href="<?php echo get_post_meta($post->ID, '_dat_requisites_file', true) ?>" class="button-document document-requisites" download>
                    <?php echo get_post_meta($post->ID, '_dat_requisites_text', true) ? : 'Скачать Реквизиты'; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section white-bg" id="section6">
    <div id="ancor6"></div>
    <div class="subtitle">
        <h3><?php echo get_post_meta($post->ID, '_dat_company_metabox_block', true) ? : 'Смежные компании'; ?></h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-md-12">
            <div class="row">
                <div id="Container" class="clearfix">

                    <?php
                    foreach (get_post_meta($post->ID, 'company_group', true) as $key=>$company) : ?>
                    <div class="mix category-1 col-lg-6 col-md-6 <?php if ($key == 0): ?>related-companies<?php endif; ?>">
                        <div class="margin-wrapper">
                            <div>
                                <div class="info-box-content">
                                    <div class="parent">
                                        <div class="child <?php if ($key == 0): ?>left-related-company<?php endif; ?>">
                                            <div class="inner">
                                                <p>
                                                    <span class="ip-title"><?php echo $company['name_company']; ?></span>
                                                </p>
                                            </div>
                                            <div class="related-document">
                                                <a href="<?php echo $company['_dat_company_requisites_file'] ? : 'javascript:void(0)'; ?>" class="button-document" download>
                                                    <?php echo $company['_dat_company_requisites_text'] ? : 'Скачать Реквизиты'; ?>
                                                </a>
                                                <a href="<?php echo $company['_dat_company_diploma_file'] ? : 'javascript:void(0)'; ?>" class="button-document" download>
                                                    <?php echo $company['_dat_company_diploma_text'] ? : 'Скачать Свидетельство'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section white-bg" id="section7">
    <div class="subtitle">
        <h3><?php echo get_post_meta($post->ID, '_dat_partners_title', true) ? : 'Партнеры'; ?></h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-lg-12 centered client-block owl-carousel owl-theme">
            <?php foreach (get_post_meta($post->ID, '_dat_partners_item', true) as $value) : ?>
                <div class="client">
                    <img alt="" src="<?php echo $value; ?>" />
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="section8">
    <div id="ancor5"></div>
    <div class="subtitle">
        <h3><?php echo get_post_meta($post->ID, '_dat_contact_title', true) ? : 'Контакты'; ?></h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-lg-8 col-md-6 col-sm-6">
            <div id="contact">
                <?php echo do_shortcode("[ninja_form id=2]"); ?>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <ul class="contact-list">
                <li> <a href=""><i class="fa fa-phone"></i><?php echo get_post_meta($post->ID, '_dat_contact_phone', true); ?></a></li>
                <li> <a href="mailto:<?php echo get_post_meta($post->ID, '_dat_contact_email', true); ?>"><i class="fa fa-pencil"></i><?php echo get_post_meta($post->ID, '_dat_contact_email', true); ?></a></li>
                <li> <a href="skype:<?php echo get_post_meta($post->ID, '_dat_contact_skype', true); ?>"><i class="fa fa-skype"></i><?php echo get_post_meta($post->ID, '_dat_contact_skype', true); ?></a></li>
                <li> <a href="<?php echo get_post_meta($post->ID, '_dat_vk_link', true); ?>" target="_blank"><i class="fa fa-vk"></i><?php echo get_post_meta($post->ID, '_dat_vk_text', true); ?></a> </li>
            </ul>
            <div class="break"></div>
            <div class="row">
                <div class="col-lg-12 col-md-6">
                    <p> <?php echo get_post_meta($post->ID, '_dat_address', true); ?> </p>
                    <p><span class="road-map"><a href="#roadmap">КАРТА ПРОЕЗДА</a></span></p>
                </div>
            </div>
        </div>
    </div>

    <div id="roadmap">
        <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Aa1013891403314e578bb86d24a0f5a8e64b8e7ea60c812f16ef5e9b6bf81f344&amp;width=100%&amp;height=541&amp;lang=ru_RU&amp"></script>
    </div>

</section>

<?php get_footer(); ?>