<?php include_once 'layouts/default/inc/header.php';?>

    <!-- Start main-content -->
    <div class="main-content">

        <div class="mt-100 section-title mb-40">
            <div class="row">
                <div class="col-md-6 col-md-offset-3  text-center">
                    <h3 class="title text-uppercase">Contact Us</h3>
                    <div class="line-bottom-centered"></div>
                </div>
            </div>
        </div>

        <!-- Section: Have Any Question -->
        <section class="divider">
            <div class="container pt-60 pb-60">
                <div class="section-content">
                    <div class="row">
                        <div class="col-sm-12 col-md-4">
                            <div class="contact-info text-center">
                                <i class="fa fa-phone font-36 mb-10 numbers"></i>
                                <h4>Call Us</h4>
                                <h6 class="text-gray">Phone: +374 12 345 678</h6>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="contact-info text-center">
                                <i class="fa fa-map-marker font-36 mb-10 numbers"></i>
                                <h4>Address</h4>
                                <h6 class="text-gray">Amiryan Street, Erevan, Armenia</h6>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="contact-info text-center">
                                <i class="fa fa-envelope font-36 mb-10 numbers"></i>
                                <h4>Email</h4>
                                <h6 class="text-gray">you@blcarmenia.com</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Contact -->
        <section class="contact-bg">
            <div class="container">
                <div class="section-content">
                    <div class="row">
                        <div class="col-md-12">

                            <!-- Contact Form -->
                            <form id="contact_form" name="contact_form" class="contact-form-transparent" action="#" method="post">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Name <small>*</small></label>
                                            <input name="form_name" class="form-control" type="text" placeholder="Enter Name" required="">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email <small>*</small></label>
                                            <input name="form_email" class="form-control required email" type="email" placeholder="Enter Email">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Subject <small>*</small></label>
                                            <input name="form_subject" class="form-control required" type="text" placeholder="Enter Subject">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Phone</label>
                                            <input name="form_phone" class="form-control" type="text" placeholder="Enter Phone">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="form_message" class="form-control required" rows="5" placeholder="Enter Message"></textarea>
                                </div>
                                <div class="form-group">
                                    <input name="form_botcheck" class="form-control" type="hidden" value="" />
                                    <button type="submit" class="contactbtn btn  btn-flat btn-block" data-loading-text="Please wait...">Send your message</button>
                                </div>
                            </form>

                            <!-- Contact Form Validation-->
                            <script type="text/javascript">
                                $("#contact_form").validate({
                                    submitHandler: function(form) {
                                        var form_btn = $(form).find('button[type="submit"]');
                                        var form_result_div = '#form-result';
                                        $(form_result_div).remove();
                                        form_btn.before('<div id="form-result" class="alert alert-success" role="alert" style="display: none;"></div>');
                                        var form_btn_old_msg = form_btn.html();
                                        form_btn.html(form_btn.prop('disabled', true).data("loading-text"));
                                        $(form).ajaxSubmit({
                                            dataType:  'json',
                                            success: function(data) {
                                                if( data.status == 'true' ) {
                                                    $(form).find('.form-control').val('');
                                                }
                                                form_btn.prop('disabled', false).html(form_btn_old_msg);
                                                $(form_result_div).html(data.message).fadeIn('slow');
                                                setTimeout(function(){ $(form_result_div).fadeOut('slow') }, 6000);
                                            }
                                        });
                                    }
                                });
                            </script>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Divider: Google Map -->
        <section>
            <div class="container-fluid pt-0 pb-0">
                <div class="row">

                    <!-- Google Map HTML Codes -->
                    <div
                        data-address="121 King Street, Melbourne Victoria 3000 Australia"
                        data-popupstring-id="#popupstring1"
                        class="map-canvas autoload-map"
                        data-mapstyle="style2"
                        data-height="400"
                        data-title="Blc Armenia"
                        data-latlng="40.179520,44.510415"
                        data-zoom="16"
                        data-marker="/public/images/map-marker.png">
                    </div>
                    <div class="map-popupstring hidden" id="popupstring1">
                        <div class="text-center">
                            <h3>Business Link Consulting</h3>
                        </div>
                    </div>
                    <!-- Google Map Javascript Codes -->
                    <script src="http://maps.google.com/maps/api/js?key=AIzaSyAYWE4mHmR9GyPsHSOVZrSCOOljk8DU9B4"></script>
                    <script src="/public/js/google-map-init.js"></script>

                </div>
            </div>
        </section>
    </div>
    <!-- end main-content -->

<?php include_once 'layouts/default/inc/footer.php';?>