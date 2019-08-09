<?php
/**
 * Page template that grabs all MailChimp campaigns
 * Template Name: MailChimp News Digest
 * @package Citris
 * @since 0.1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div class="col-full page-default">
			<div class="grid">
				<main id="main" class="site-main" role="main">


				<?php while ( have_posts() ) : the_post(); ?>

					<?php get_template_part( 'content', 'single' ); ?>

                <?php endwhile; ?>
                

				</main><!-- #main -->
            </div><!-- .grid -->
            
            <!-- begin MailChimp API -->

            <?php
                $json = file_get_contents("https://us2.api.mailchimp.com/3.0/campaigns?apikey=ec64244760a10ab3931fda6eab6b4e25-us2&sort_field=send_time&sort_dir=DESC&count=500&status=sent&since_send_time=2015-01-01&folder_id=afaf13f4ac");
                $json = json_decode($json, true);
                $campaigns = $json['campaigns'];

                echo "<div class='mc-newsletters'>";
                foreach ($campaigns as $key => $value) {
                    echo "<div class='mc-newsletter'>";
                    echo "<a href='" . $value['archive_url'] . "' target='_blank'> <h2>" . $value['settings']['title'] . "</h2> </a>";
                    echo  "<br />";
                    echo "<h3>" . $value['settings']['subject_line'] . "</h3>";
                    echo  "<br />";
                    $texthtml = file_get_contents($value['archive_url']);

                    preg_match_all('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $texthtml, $image);
                    echo $image[0][1];
                    echo  "<br />";
                    // echo '<pre>' . print_r($image[0], true) . '</pre>';
                    echo  "<br />";
                    echo "</div>";
                }
                echo "</div>";

                // echo '<pre>' . print_r($json, true) . '</pre>';
                ?>

                <!-- end MailChimp API -->
		</div><!-- .col-full -->
		
		
		<?php $recent_query = new WP_Query( array( 'posts_per_page' => 4, 'post_type' => 'post', 'post_status' => 'publish' ) ); ?>
		<?php if ( $recent_query->have_posts() ) : ?>
			<div class="home-news">
				<div class="col-full">
					<h2 class="section-heading">News</h2>

					<?php while ( $recent_query->have_posts() ) : $recent_query->the_post(); ?>
						<div class="col-1-4">
							<?php get_template_part( 'content', 'home' ); ?>
						</div>
					<?php endwhile; wp_reset_postdata(); ?>

					<div class="clear"></div>
					<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="more com-btn">More News</a>
				</div><!-- .col-full -->
			</div><!-- .home-news -->
		<?php endif; ?>
		

	</div><!-- #primary -->

	<div class="home-sidebar">
		<div class="col-full">
			<?php get_template_part( 'home', 'sidebar' ); ?>
		</div><!-- .col-full -->
	</div><!-- home-sidebar -->

<?php get_footer(); ?>