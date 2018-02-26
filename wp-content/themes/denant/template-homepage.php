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
            <h3>О нас</h3>
            <div class="divider"></div>
            <p>ООО «ДенАнтТранс» — транспортно-экспедиционная компания. Наша компания успешно осуществляет свою деятельность на рынке международных грузоперевозок. Штат квалифицированных специалистов предлагает вам свои услуги в организации международных перевозок грузов любой сложности собственным и привлеченным автомобильным транспортом.
                <br/><br/>
                Направления нашей постоянной работы — транспортировка и экспедирования грузов из Беларуси в Россию, Казахстан, Украину, Венгрию, Сербию, Польшу, Чехию, Словакию, Хорватию, Германию, Голландию, Италию, Литву, Латвию, Эстонию и в обратном направлении.
                <br/><br/>
                Наша компания предоставляет услуги по доставке сборных грузов от 100 кг и более, грузов АДР класса, негабаритных грузов по вышеуказанным направлениям.
                <br/><br/>
                За время своей работы на рынке транспортных услуг, наша компания приобрела широкий круг партнёров и богатый опыт в транспортно-экспедиционном обслуживании самых различных видов грузов. Быстрота доставки, надёжность и удобство работы с нами, дополнительные сервисы, которые предоставляет наша компания — благодаря всему этому множество предприятий являются нашими постоянными клиентами.
                <br/><br/>
                Приглашаем к сотрудничеству!</p>
        </div>
    </div>
</section>


<section class="section" id="section3">
    <div id="ancor3"></div>
    <div class="subtitle">
        <h3>Услуги</h3>
        <div class="divider"></div>
    </div>

    <div class="subtitle"> <h4> I. Автомобильные перевозки грузов </h4> </div>

    <div class="container clearfix">
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4>Комплектные</h4>
                </div>
                <p class="price-details">
                    Комплектные 1 <br />
                    Комплектные 2<br />
                    Комплектные 3<br />
                    Комплектные 4<br />
                </p>
            </figure>
        </div>
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4>Сборные</h4>
                </div>
                <p class="price-details">
                    Сборные 1<br />
                    Сборные 2<br />
                    Сборные 3<br />
                    Сборные 4<br />
                </p>
            </figure>
        </div>
        <div class="col-lg-4 col-md-4">
            <figure class="price-table">
                <div class="heading">
                    <h4>Негабаритные</h4>
                </div>
                <p class="price-details">
                    Негабаритные 1<br />
                    Негабаритные 2<br />
                    Негабаритные 3<br />
                    Негабаритные 4</p>
            </figure>
        </div>

        <div class="col-lg-12 col-md-4 col-md-4-storage">
            <div class="subtitle subtitle-services"> <h4> II. Складское хранение </h4> </div>
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
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
    </div>
</section>

<section class="section white-bg" id="section5">
    <div class="subtitle">
        <h3>Документы</h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix documents">
        <div class="col-md-6">
            <div class="document-link">
                <a href="<?php echo get_template_directory_uri(); ?>/documents/certificate.jpg" class="button-document" target="_blank" download>Скачать Сертификат</a>
                <a href="<?php echo get_template_directory_uri(); ?>/documents/requisites.docx" class="button-document document-requisites" target="_blank">Скачать Реквизиты</a>
            </div>
        </div>
    </div>
</section>

<section class="section white-bg" id="section6">
    <div id="ancor6"></div>
    <div class="subtitle">
        <h3>Смежные Компании</h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-md-12">
            <div class="row">
                <div id="Container" class="clearfix">
                    <div class="mix category-1 col-lg-6 col-md-6 related-companies">
                        <div class="margin-wrapper">
                            <div>
                                <div class="info-box-content">
                                    <div class="parent">
                                        <div class="child left-related-company">
                                            <div class="inner">
                                                <p>
                                                    <span class="ip-title">Индивидуальный предприниматель <br/> Бакач Денис Леонардович</span>
                                                </p>
                                            </div>
                                            <div class="related-document">
                                                <a href="<?php echo get_template_directory_uri(); ?>/documents/requisites-bakach.docx" class="button-document" target="_blank" download>Скачать Реквизиты</a>
                                                <a href="#" class="button-document" target="_blank" download>Скачать Свидетельство</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mix category-2 col-lg-6 col-md-6">
                        <div class="margin-wrapper">
                            <div>
                                <div class="info-box-content">
                                    <div class="parent">
                                        <div class="child">
                                            <div class="inner">
                                                <p>
                                                    <span class="ip-title">Индивидуальный предприниматель <br/> Кирилко Антон Александрович </span>
                                                </p>
                                            </div>
                                            <div class="related-document">
                                                <a href="<?php echo get_template_directory_uri(); ?>/documents/requisites-kirilko.docx" class="button-document" target="_blank" download>Скачать Реквизиты</a>
                                                <a href="#" class="button-document" target="_blank" download>Скачать Свидетельство</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section white-bg" id="section7">
    <div class="subtitle">
        <h3>Партнеры</h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-lg-12 centered client-block owl-carousel owl-theme">
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/typhoon.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/idea-bank.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/belarus-kabel.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/bagoria.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/kronon.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/santa-bremor.jpg" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/belxim-2.png" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/forfor-2.png" /></div>
            <div class="client"><img alt="" src="<?php echo get_template_directory_uri(); ?>/images/inkom-2.png" /></div>
        </div>
    </div>
</section>

<section class="section" id="section8">
    <div id="ancor5"></div>
    <div class="subtitle">
        <h3>Контакты</h3>
        <div class="divider"></div>
    </div>
    <div class="container clearfix">
        <div class="col-lg-8 col-md-6 col-sm-6">
            <div id="contact">
                <?php echo do_shortcode("[ninja_form id=2]"); ?>
<!--                <form method="post" action="contact.php" name="contactform" id="contactform" autocomplete="off">-->
<!--                    <fieldset>-->
<!--                        <label for="name" accesskey="U"><span class="required">Your Name</span></label>-->
<!--                        <input name="name" type="text" id="name" title="Your Name" />-->
<!--                        <label for="email" accesskey="E"><span class="required">Email</span></label>-->
<!--                        <input name="email" type="text" id="email" title="Email" />-->
<!--                        <label for="comments" accesskey="C"><span class="required">Tell us what you think!</span></label>-->
<!--                        <textarea name="comments" id="comments" title="Tell us what you think!"></textarea>-->
<!--                        <input type="submit" class="submit" id="submit" value="Submit" />-->
<!--                        <span id="message"></span>-->
<!--                    </fieldset>-->
<!--                </form>-->
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6">
            <ul class="contact-list">
                <li> <a href=""><i class="fa fa-phone"></i>+375(33)6235537</a></li>
                <li> <a href="mailto:olga.verobey@mail.ru"><i class="fa fa-pencil"></i>olga.verobey@mail.ru</a></li>
                <li> <a href="skype:antonkakirilka"><i class="fa fa-skype"></i>antonkakirilka</a></li>
                <li> <a href="http://vk.com/denanttrans" target="_blank"><i class="fa fa-vk"></i>Мы в Контакте</a> </li>
            </ul>
            <div class="break"></div>
            <div class="row">
                <div class="col-lg-12 col-md-6">
                    <p>РБ г.Гродно</p>
                    <p>
                        230023 ул. Дзержинского 40 <br>
                        помещение 25<br>
                        <span class="road-map"><a href="#roadmap">Карта проезда</a></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div id="roadmap">
        <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3Aa1013891403314e578bb86d24a0f5a8e64b8e7ea60c812f16ef5e9b6bf81f344&amp;width=100%&amp;height=541&amp;lang=ru_RU&amp"></script>
    </div>

</section>

<?php get_footer(); ?>