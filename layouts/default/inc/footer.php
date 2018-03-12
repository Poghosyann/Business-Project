<!-- Footer -->
<footer id="footer" class="footer" data-bg-color="#252A31">
    <div class="container pt-60 pb-20">
        <div class="col-sm-6 col-md-4">
            <div class="widget dark">
                <a href="/"><img alt="" src="/public/images/logo-wide.png"></a>
                <p class="mt-20">Amiryan Street, Erevan, Armenia.</p>
                <ul class="fotter-address list-inline">
                    <li> <i class="fa fa-phone text-gray mr-5 p-10 pl-0"></i> <a class="text-gray" href="#">(+374) 12 345 678</a> </li>
                    <li> <i class="fa fa-envelope-o text-gray mr-5 p-10 pl-0"></i> <a class="text-gray" href="#">contact@blcarmenia.com</a> </li>
                    <li> <i class="fa fa-globe text-gray mr-5 p-10 pl-0"></i> <a class="text-gray" href="http://blcarmenia.com">www.blcarmenia.com</a> </li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="widget dark">
                <h4 class="widget-title line-bottom-theme-colored2">Site Map</h4>
                <div class="row clearfix">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <ul class="footer-link">
                            <li><a href="/">Home</a></li>
                            <li><a href="/about">About Us</a></li>
                            <li><a href="/business_professional_certification">Business Professional Certification</a></li>
                            <li><a href="/consulting">Consulting</a></li>
                            <li><a href="/events_and_trainings">Events and Trainings</a></li>
                            <li><a href="/contact">Contact Us</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-4">
            <div class="widget dark">
                <h4 class="widget-title line-bottom-theme-colored2">Subscribe</h4>
                <div class="widget-subscribe">
                    <h5 class="subscribe-title text-gray mb-10">To get latest news and update keep connected with us by mailing</h5>
                    <p class="subscribe-sub-title mb-0">Subscribe to Connect with us</p>
                    <form id="mailchimp-subscription-form2" class="newsletter-form mt-10 form-transparent" novalidate="true">
                        <div class="input-group">
                            <input value="" name="EMAIL" placeholder="Your Email" class="form-control" data-height="45px" id="mce-EMAIL" style="height: 45px;" type="email">
                            <span class="input-group-btn">
                  <button data-height="45px" class="btn btn-colored btn-theme-colored2 text-white m-0" type="submit"><i class="fa fa-paper-plane-o font-20"></i></button>
                </span>
                        </div>
                    </form>
                    <!-- Mailchimp Subscription Form Validation-->
                    <script type="text/javascript">
                        $('#mailchimp-subscription-form2').ajaxChimp({
                            callback: mailChimpCallBack,
                            url: '//scot.us9.list-manage.com/subscribe/post?u=a01f440178e35febc8cf4e51f&amp;id=49d6d30e1e'
                        });

                        function mailChimpCallBack(resp) {
                            // Hide any previous response text
                            var $mailchimpform = $('#mailchimp-subscription-form2'),
                                $response = '';
                            $mailchimpform.children(".alert").remove();
                            if (resp.result === 'success') {
                                $response = '<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + resp.msg + '</div>';
                            } else if (resp.result === 'error') {
                                $response = '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + resp.msg + '</div>';
                            }
                            $mailchimpform.prepend($response);
                        }
                    </script>
                    <ul class="styled-icons clearfix mt-10">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-vk"></i></a></li>
                        <li><a href="#"><i class="fa fa-instagram"></i></a></li>
                        <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom" data-bg-color="#2B3037">
        <div class="container pt-20 pb-20">
            <div class="row">
                <div class="col-md-6">
                    <p class="font-14 text-black-777 m-0"> Blc Armenia | Copyright &copy;2018 </p>
                </div>

            </div>
        </div>
    </div>
</footer>
<a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
</div>

<script src="/public/js/chart.js"></script>
<script src="/public/js/custom.js"></script>

<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.actions.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.carousel.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.kenburn.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.layeranimation.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.migration.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.navigation.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.parallax.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.slideanims.min.js"></script>
<script type="text/javascript" src="/public/js/revolution-slider/js/extensions/revolution.extension.video.min.js"></script>

</body>

</html>