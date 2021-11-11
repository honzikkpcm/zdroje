<?php
	$container = get_theme_mod( 'understrap_container_type' );
?>
<!DOCTYPE html>
<html <?php language_attributes() ?> >
<head>
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-120543819-1"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', 'UA-120543819-1');
	</script>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-title" content="<?php bloginfo( 'name' ); ?> - <?php bloginfo( 'description' ); ?>">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php wp_head(); ?>

	<link rel='stylesheet' id='style-css'  href='<?php echo get_template_directory_uri(); ?>/css/slick.css?ver=4.9.7' type='text/css' media='all' />
	<link rel='stylesheet' id='style-css'  href='<?php echo get_template_directory_uri(); ?>/css/style.css?ver=8' type='text/css' media='all' />
	<script type='text/javascript' id='script-css'  src='<?php echo get_template_directory_uri(); ?>/js/jquery.inview.min.js?ver=4.9.5'></script>
	<script type='text/javascript' id='script-css'  src='<?php echo get_template_directory_uri(); ?>/js/jquery.selectBox.js?ver=4.9.5'></script>	
	<script type='text/javascript' id='script-css'  src='<?php echo get_template_directory_uri(); ?>/js/slick.min.js?ver=4.9.5'></script>
	<script type='text/javascript' id='script-css'  src='<?php echo get_template_directory_uri(); ?>/js/app.js?ver=4.9.8'></script>
	<!-- Facebook Pixel Code -->
	<script>
	!function(f,b,e,v,n,t,s)
	{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
	n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s)}(window,document,'script',
	'https://connect.facebook.net/en_US/fbevents.js');
	 fbq('init', '379913238882356'); 
	fbq('track', 'PageView');
	</script>
	<noscript>
	 <img height="1" width="1" 
	src="https://www.facebook.com/tr?id=379913238882356&ev=PageView
	&noscript=1"/>
	</noscript>
	<!-- End Facebook Pixel Code -->
</head>

<body <?php body_class(); ?>>

<div class="hfeed site" id="page">

<section class="header wrapper-fluid wrapper-navbar" id="wrapper-navbar" itemscope itemtype="http://schema.org/WebSite">

	<a class="skip-link screen-reader-text sr-only" href="#content"><?php esc_html_e( 'Skip to content', 'understrap' ); ?></a>

	<nav class="navbar navbar-expand-md navbar-dark bg-dark">

		<?php if ( 'container' == $container ) : ?>
			<div class="container" >
		<?php endif; ?>

		<?php if ( ! has_custom_logo() ) { ?>
			<a rel="home" class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" itemprop="url">
				<img src="<?php echo get_template_directory_uri(); ?>/img/only-for-you-logo.svg" width="300" height="90" alt="Only FY">
			</a>
		<?php } else {
			the_custom_logo();
		} ?>

		<button class="navbar-toggler header_menu_wrap" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
			<span class="header_menu">
       			<span class="header_menu_icon"></span>
    		</span>
			<span class="menu-label">MENU</span>
		</button>

		<!-- The WordPress Menu goes here -->
		<?php wp_nav_menu(
			array(
				'theme_location'  => 'primary',
				'container_class' => 'collapse navbar-collapse',
				'container_id'    => 'navbarNavDropdown',
				'menu_class'      => 'navbar-nav',
				'fallback_cb'     => '',
				'menu_id'         => 'main-menu',
				'walker'          => new understrap_WP_Bootstrap_Navwalker(),
			)
		); ?>

		<?php if ( 'container' == $container ) : ?>
			</div><!-- .container -->
		<?php endif; ?>

	</nav><!-- .site-navigation -->

</section><!-- .wrapper-navbar end -->
