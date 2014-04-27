    <?php
        if($message = Selfie_Utility::getBroadstreetMessage())
        {
            echo $message;
        }
    ?>   
    <!--
    <h3>Tell Your Friends if You Think They'd Like Selfie</h3>

    <p>
        <div class="fb-share-button" data-href="http://wordpress.org/plugins/selfie/" data-type="box_count"></div>
    </p>
    -->    

    <div>
        <div class="fb-like" data-href="http://www.facebook.com/broadstreetads" data-send="false" data-layout="box_count" data-width="450" data-show-faces="true"></div>
    <a href="https://twitter.com/broadstreetads" class="twitter-follow-button" data-show-count="false">Follow @broadstreetads</a>
    <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>    
    </div>

    <h3>Getting Started with Selfie</h3>
    <p>
        If you haven't already, read our <a href="?page=Selfie-Help">Getting Started</a>
        guide, which will walk you through how to use Selfie.
    </p>
    
    <h3>On Our Blog: The Streetlight</h3>
    <div id="bs-blog"></div>
    <script>
        jQuery(function() {
            var bs = new Broadstreet.Network();
            bs.postList('#bs-blog');
        });
    </script>

    <h3>Have a bug report?</h3>
    <p>
        We like to crush bugs as soon as we hear about them!
        Be sure to give us as much detail as possible,
        such as the number of posts you have, any error messages that
        were given, and any behavior you've observed.
    </p>
    <p>
        Send any and all reports to <a href="mailto:ohcrap@broadstreetads.com">ohcrap@broadstreetads.com</a>. Thanks
        for using Broadstreet!
    </p>