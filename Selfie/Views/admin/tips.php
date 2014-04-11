<script src="https://broadstreet-common.s3.amazonaws.com/broadstreet-net/init.js"></script>
<div id="main">
    
      <?php Selfie_View::load('admin/global/header') ?>
    
    <div class="left_column help-col">
        <h1>Tips and Tricks for Crushing it with Selfie</h1>
        
        <p>
            There's a lot you can do to maximum the number of Selfie purchases
            you get. Your website's brand is probably a powerful one, and perhaps
            you have hundreds or thousands of loyal readers. However, they 
            won't book a message on your site without human appeal, strong
            encouragement, and awareness that Selfie is an option to them.            
        </p>
        
        
        <h3>Tip #1: Introduce Selfie to Readers</h3>
        
        <p>
            Your readers probably aren't going to try something new if they
            are unfamiliar with it. Think about writing a post or an announcement 
            that calls out Selfie, and how valuable it can be for announcing 
            birthdays, events, messages from companies, and more. It's not 
            just for <i>advertisers</i>, it's for readers.
        </p>
        
        <h3>Tip #2: Pitch, Pitch, Pitch</h3>
        
        <p>
            When you insert a Selfie into a post, your pitch to the reader is 
            the single most important aspect of whther they'll convert and book
            an ad. Ever see those "Your Ad Here" boxes? They're boring and
            pathetic.
        </p>
        
        <p>
            Make a new pitch every time and learn what works for your 
            particular publication. If other publishers can do it, you can too.
        </p>
        
        <h3>Tip #3: Offer Freebies When Starting Out</h3>
        
        <p>
            In step #5 on the <a href="?page=Selfie-Tips">getting start page</a>,
            you saw that you can set individual pricing on a post. Why not
            write a post announcing Selfie, and then offer free messages for the
            first week? Get your users primed to become future customers.
        </p>
        
        <h3>Tip #4: Insert A Lot of Selfies</h3>
        
        <p>
            The more Selfies you place, the higher your revenue potential is.
            If you inserted 3 Selfies per post, and you had 5 new posts per day 
            at a monthly price of $50, that would be $22,500 in potential revenue.            
            Don't throttle your potential.
        </p>
        
        <h3>Tip #5: Mention the Price Inside the Selfie Pitch</h3>
        
        <p>
            See the example below. Notice how there's a special code for inserting
            the price of the Selfie?
        </p>
        
        <img class="figure" alt="Price Macro" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/price-macro.png" />
        
        <p>That will automatically insert the current price of that Selfie for
            the term. You can do this with ${day_price}, ${week_price}, 
            ${month_price} or ${year_price}</p>
        
        <p>This is what it looks like when the post is viewed:</p>
        
        <img class="figure" alt="Price Macro Rendered" src="<?php echo Selfie_Utility::getImageBaseURL() ?>help/price-macro-rendered.png" />
        
        <h3>Tip #6: Insert Selfie Into Old Posts</h3>
        
        <p>Selfie isn't only about tapping into readers of today's news. There 
            are probably some search terms that your website is ranked #1 in
            Google for. For example, RedBankGreen.com is listed as #1 for the
            Google search for "Peruvian Red Bank."
        </p>
        
        <p>Your content is valuable to all kinds of advertisers. Maybe they want
            to post a message right on that page to announce something relevant.
            They can only do that if you enable Selfie for old posts! Check out 
            step #4 on the <a href="?page=Selfie-Tips">getting start page</a> 
            page to set that up.
        </p>
        
        <h3>Tip #7: Add Selfies Outside of the Post</h3>
        
        <p>
            We think Selfies are most valuable inside the post content, because
            advertisers will see the best performance. But you can actually
            do much more.
        </p>
        
        <p>            
            Selfies can actually go anywhere on your site. A developer
            can modify your theme to put a Selfie before a post, after a post,
            in the header, or anywhere. They'll just need this snippet of 
            code. It will use your default site pricing.
        </p>
        
        <code>&lt;?php echo do_shortcode('[selfie]'); ?&gt;</code>
        
        <h3>Tip #8: CSS Classes</h3>
        
        <p>
            All in-post Selfies are wrapped in a paragraph tag with the CSS class 
            "selfie-paragraph", so you could use that to style it from your site's 
            stylesheet. Example:
        </p>

        <pre>
.selfie-paragraph { padding-left: 10px; border-left: 4px solid lightgreen; }
        </pre>

        <p>That would give the paragraph a slight indent with a thick light green left border.</p>
        
    </div>
          

    <div class="right_column">
        <?php Selfie_View::load('admin/global/sidebar') ?>
    </div>
    
</div>
<div class="clearfix"></div>