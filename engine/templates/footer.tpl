<?php if(!defined('NEO')) { ?>

                        </div>
                    </div>
                    <?if($$footer_bill) $footer_bill = true?>	
                    <? include ("footer.new.html") ?>
                </div>
            </div>
        </div>
        </div>
        <?php if (!$no_personal) include (ABS_PATH . "/templates/personal.php") ?>
        <? //include_once('./user/sex_demand.php');?>
        <? if ( !empty($_SESSION['everesttech_conter']) ) { 
            unset($_SESSION['everesttech_conter']);
        ?>
            <script language="javascript" src="//www.everestjs.net/static/st.v2.js"></script>
            <script language="javascript">
                var ef_event_type="transaction";
                var ef_transaction_properties = "ev_Registrations=0&ev_Payments=1&ev_Paid_Amount=<?=$fullsum?>";
                /*
                * Do not modify below this line
                */
                var ef_segment = "";
                var ef_search_segment = "";
                var ef_userid="3208";
                var ef_pixel_host="pixel.everesttech.net";
                var ef_fb_is_app = 0;
                effp();
            </script>
            <noscript><img src='http://pixel.everesttech.net/3208/t?ev_Registrations=0&ev_Payments=1&ev_Paid_Amount=<?=$fullsum?>' width='1' height='1'/></noscript>
        <? } ?>

        <!-- Yandex.Metrika counter -->
        <script type="text/javascript">
        var yaParams = {/*Здесь параметры визита электронной торговли*/};
        </script>

        <script type="text/javascript">
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function() {
                try {
                    w.yaCounter6051055 = new Ya.Metrika({id:6051055,
                            webvisor:true,
                            clickmap:true,
                            trackLinks:true,
                            accurateTrackBounce:true,params:window.yaParams||{ }});
                } catch(e) { }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () { n.parentNode.insertBefore(s, n); };
            s.type = "text/javascript";
            s.async = true;
            s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else { f(); }
        })(document, window, "yandex_metrika_callbacks");

        function yaCounter6051055reachGoal(p) {
            try {
                yaCounter6051055.reachGoal(p);
            } catch(e) { }

        }
        </script>
        <noscript><div><img src="//mc.yandex.ru/watch/6051055" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter -->


</body>
</html>
<?php } // if(!defined('NEO')) ?>
