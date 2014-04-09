<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<div id="main">
    
      <?php Selfie_View::load('admin/global/header') ?>
    
    <div class="left_column help-col">
        <h1>How to Use Selfie: The Complete Guide</h1>
        
        <p>
            This guide will walk you through how to set up Selfie for maximum
            performance on your site. We'll start with the basics, like
            getting a Broadstreet account set up, and end with tips on how
            to fully utilize Selfie's features to ensure optimal sales.
        </p>
        
        <h2>But First: About Pricing and Revenue Share</h2>
        
        <p>
            We're in business to grow your business.</p>
        
        <p>Selfie's model is that we charge a 20% fee for any sales made on your site.
            That takes care of the credit card fee and our own support and technical 
            costs. Most users won't ever have to pay a single dime until
            they make a sale. And even then, we take our fee from the proceeds
            and deposit the remainder to your account.
        </p>
        
        <p><strong>Really big users:</strong> If a Selfie is rendered over 100,000
            times, Broadstreet will ad a very small adserving cost to your account.
            We will add a $10 charge to your account for every 1 million ads served. 
            We don't require a credit card to get started, but if you are a 
            high-traffic publisher, we need a way to cover our own technical costs.
            We have a very expensive server bill!
        </p>
        
        <p>If you need whitelabeling, ask us about our enterprise version of Selfie:
            frontdesk@broadstreetads.com</p>
            
        
        <h2>1. Get A Broadstreet Account</h2>
        
        <p>
            Selfie relies heavily on Broadstreet's adverving technology.
            In order for Selfie to work, and to get paid once you have had a
            few ad sales, you'll need a Broadstreet account.
        </p>
        
        <p>
            When you first view the <a href="?page=Selfie">Selfie settings page</a>
            you're going to need to set up a Broadstreet account. This is
            very easy &minus; You can just enter your email address and click
            "Vroom!" 
        </p>
        
        <p>It's important to use an email address that you check frequently. This
            is where confirmations of purchases will be sent, which you
            need to approve.
        </p>
        
        <img class="figure" alt="Get an account" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/get-account.png" />
        
        <p>
            Once that's set up, you're ready to set up base pricing rules.
        </p>
        
        <h2>2. Set Up Pricing Rules</h2>
        
        <p>
            When one of your readers wants to book a Selfie, they are going to
            pay a price that you set. That price can be fine tuned based on the
            length of the time that the ad will run, which category the content
            is in, or how old the content is.
        </p>
        
        <p>
            Scroll down the Selfie settings page for the area that says 
            <strong>Selfie Pricing</strong>. This is where you can set the base
            pricing for Selfie purchases accross your site.
        </p>        
        
        <p>
            After that section comes the <strong>Special Pricing Rules</strong>
            section, which will let you set the pricing on using a number of
            different rules. Don't forget that the rules are processed
            in order, where the last matching rule always gets used.
        </p> 
        
        <p><strong>Note: If you don't set a price for a specific term (day, week, month, year),
                that option will not be available to the end user.</strong> If you set a
                price of 0, it will be free for the user. See the <a href="?page=Selfie-Tips">tips and tricks</a>
                guide for more about freebies. All prices are in USD.
        </p>
        
        <p><strong>Tip:</strong> It's a good idea to tier pricing to make long-term
            buying more attractive. Or maybe, make pricing for articles published
            very recently less expensive to promote buying today as opposed to
            later.
        </p>
        
        <img class="figure" alt="Set Pricing" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/set-pricing.png" />
        
        <h3>3. Set the Look and Feel</h3>
        
        <p>
            You can set how Selfie looks when it gets displayed in your article,
            or anywhere else on your site. This is handy for making sure readers
            know that it's a separate message and not part of the content.            
        </p>
        
        <img class="figure" alt="Look and Feel" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/look-feel.png" />
        
        <p>
            You can also use the <strong>Prefix</strong> option to help brand
            Selfie as your own product. For instance, RedBankGreen.com calls
            it "<a target="_blank" href="http://redbankgreen.com">Greenliner</a>", 
            and <a target="_blank" href="http://sanangelolive.com">SanAngeloLive.com</a>
            calls it a "Shout Out."
        </p>
        
        <img class="figure" alt="Prefix" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/prefix.png" />
        
        <h3>4. Insert Selfie Into Past Posts</h3>
        
        <p>
            If you want to monetize all of the content you've written in the past,
            you can have Selfie automatically inserted into all of your posts.
            This won't actually modify the post content &minus; it will just
            make sure a Selfie is inserted into a post when it is viewed,
            <strong>if the post doesn't already contain a Selfie.</strong>
        </p>
        
        <p><strong>Just a note:</strong>Once a user purchases a Selfie, 
            the transaction is final. Don't add or remove Selfies to posts 
            and pages where a post has been purchased.
        </p>
        
        <p>Be sure to enter a "Message to Place" too. This is the default
            message that will be rendered when the Selfie is displayed. It
            should be a personal pitch to the user. The quality of your pitch 
            is very important! Don't settle for "Your ad here." In fact, don't
            use the word "ad!"
        </p>
        
        <p>
            <strong>The is the most important feature of Selfie</strong>. 
            When you are editing a new post, you can insert a Selfie
            anywhere in the post that you would like by using the
            <strong>[selfie]</strong> shortcode. Again, the quality of the pitch that
            you make in your post is extremely important to your success.
        </p>        
        
        <img class="figure" alt="Auto Selfie" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/auto-selfie.png" />
        
        <h3>5. Insert Selfie Into New Posts</h3>
        
        <img class="figure" alt="Enter Shortcode" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/selfie-custom.png" />
        
        <p>This is what the Selfie will look like when viewing the post:</p>
        
        <img class="figure" alt="Rendered Selfie" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/selfie-rendered.png" />        
        
        <p>If you're feeling really lazy, you can just enter <strong>[selfie]</strong>
            without the enclosed text. The default message that renders is the
            one you set in step 4 above.
        </p>
        
        <p>If you scroll down the post edit page, you'll see an area where 
            you can disable Selfie for a specific post, or set custom pricing
            on that post.</p>
        
        <img class="figure" alt="Post Pricing" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/post-pricing.png" />        
        
        <p>Now you're primed for using Selfie! Head over to the <a href="?page=Selfie-Tips">tips and tricks</a>
            page for ideas on how to maximize the number of users who buy.
            Oh, and <strong>don't forget to tell your friends and colleagues</strong>!
        </p>
    </div>
          

    <div class="right_column">
        <?php Selfie_View::load('admin/global/sidebar') ?>
    </div>
    
</div>
<div class="clearfix"></div>