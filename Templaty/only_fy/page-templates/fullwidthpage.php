<?php
	get_header();
	$container = get_theme_mod( 'understrap_container_type' );
?>
<section class="login-wrapper">
	<div class="shader"></div>
	<div class="login-field">
		<form id="login" name="login" action="/Account/Login" method="post">
			<img src="<?php echo get_template_directory_uri(); ?>/img/only-for-you-logo.svg" class="logo-small" alt="Car Service only FY" width="290">
			<h3>Přihlášení</h3>
			<input type="email" name="UserName" placeholder="E-mail">
			<input type="password" name="Password" placeholder="Heslo">
			<input name="__RequestVerificationToken" type="hidden" value="3tLYNuCjigGelqQiFZrn_44-36mkHvxKbCMCTeytoClD6aB8alWPjqNfD79NwyPTQMpT5d340LVMo9PKAOtcMOoOvVRhnVl_An1ND1krv-41">
			<button type="submit" class="button transparent">PŘIHLÁSIT SE</button>
		</form>	
	</div>
</section>

<section class="cookies wrapper">
	<div class="<?php echo esc_attr( $container ); ?>">
		<div class="row">				
			<div class="col-md-12 content-area">	
				<h2 style="text-align: center;">Snažíme se poskytovat služby v co nejvyšší kvalitě, proto naše stránky využívají technologii cookies. Většina internetových prohlížečů je automaticky nastavena tak, aby byly soubory cookies příjímány. Změnu můžete provést v nastavení svého prohlížeče. Více informací o ochraně osobních údajů
				</h2>
				<a class="button transparent" href="/kontaktovat">BERU NA VÉDOMÍ</a>
			</div>
		</div>
	</div>
</section>	

<main class="site-main" id="content" role="main">
	<div id="full-width-page-wrapper">
		<section class="how wrapper" id="jak-to-funguje">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">
					<div class="col-md-12 content-area">
						<?php
							$post_id = 1;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
						<img src="<?php echo get_template_directory_uri(); ?>/img/only-for-you-logo-small.svg" class="logo-small" alt="Car Service only FY" width="300" height="119">
					</div>
				</div>
			</div>
		</section>
		<section class="how_promo wrapper">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">				
					<div class="col-md-12 content-area">	
						<?php
							$post_id = 30;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
				</div>
			</div>
		</section>
		<section class="map wrapper" id="nase-auta">
			<div id="map"></div>
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">				
					<div class="col-md-12 content-area">	
						<?php
							$post_id = 33;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
				</div>
				
				<div class="row mw520 selecty">
					<a href="#locateMe" id="locateMe">Chci najít svojí polohu</a>
	
					<div class="col-md-4">
						<form action="return.php" class="carSelector" method="post" accept-charset="utf-8">
							<!--
							<select name="form[lokalita]" class="carSelect">
								<option value="">Podle lokality</option>
								<option value="Praha 1">Praha 1</option>
								<option value="Praha 2">Praha 2</option>
								<option value="Praha 3">Praha 3</option>
								<option value="Praha 4">Praha 4</option>
								<option value="Praha 5">Praha 5</option>
								<option value="Praha 6">Praha 6</option>
								<option value="Praha 7">Praha 7</option>
								<option value="Praha 8">Praha 8</option>
								<option value="Praha 9">Praha 9</option>
								<option value="Praha 10">Praha 10</option>
								<option value="Praha 11">Praha 11</option>
								<option value="Praha 12">Praha 12</option>
							</select>
						</div>
						<div class="col-md-4">
							<select name="form[velikost]" class="carSelect">
								<option value="">Podle velikosti</option>
								<option value="S">Malý</option>
								<option value="M">Štřední</option>
								<option value="L">Velký</option>
							</select>
						</div>					
						<div class="col-md-4">
							<select name="form[velikost]" class="carSelect">
								<option value="">Speciální vozy</option>
								<option value="XL">Dodávka</option>
							</select>
						</div>-->		
					</form>

				</div>

				<script async defer
				src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAu2tF4DafBOYkmyqREO0LOywsfuDLa0LA&callback=initMap">
				</script>
			</div>
		</section>
		<section class="map_promo wrapper">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">				
					<div class="col-md-12 content-area">	
						<?php
							$post_id = 45;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
				</div>
			</div>
		</section>		
		<section class="aplications wrapper" id="aplikace">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">
					<div class="col-md-12 content-area">
						<?php
							$post_id = 47;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
				</div>
			</div>
		</section>
		<section class="aplications_promo wrapper">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">				
					<div class="col-md-12 content-area">	
						<?php
							$post_id = 60;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
				</div>
			</div>
		</section>		
		<section class="cars wrapper" id="ceny">
			<div class="col-md-12 content-area">
				<?php
					$post_id = 63;
					$queried_post = get_post($post_id);
					echo $queried_post->post_content;
				?>
			</div>
		</section>
			<?php /*
				$post_id = 81;
				$queried_post_parent = get_post($post_id);

				if($queried_post_parent->post_content == "1"){
					$postid = 1;
					$queried_post = get_post(79);
				}
				if($queried_post_parent->post_content == "2"){
					$postid = 2;
					$queried_post = get_post(99);
				}
				if($queried_post_parent->post_content == "3"){
					$postid = 3;
					$queried_post = get_post(103);
				}
				if($queried_post_parent->post_content == "4"){
					$postid = 4;
					$queried_post = get_post(105);
				}
				if($queried_post_parent->post_content == "5"){
					$postid = 5;
					$queried_post = get_post(109);
				}

				if($queried_post_parent->post_content == ""){
					$postid = mt_rand(1,5);
					if($postid == 1){
						$queried_post = get_post(79);
					}
					if($postid == 2){
						$queried_post = get_post(99);
					}
					if($postid == 3){
						$queried_post = get_post(103);
					}										
					if($postid == 4){
						$queried_post = get_post(105);                                                          
					}
					if($postid == 5){
						$queried_post = get_post(109);
					}					
				}				

				echo '<section class="advert wrapper advert-'.$postid.'">
						<div class="'.esc_attr( $container ). '">
							<div class="row">				
								<div class="col-md-12 content-area">';	
									echo $queried_post->post_content;
				echo '			</div>
							</div>
						</div>
					</section>';
			*/ ?>		
		<section class="contact wrapper" id="kontakty">
			<div class="<?php echo esc_attr( $container ); ?>">
				<div class="row">				
					<div class="tabs-wrapper col-md-6 content-area order-12">	
						<?php
							$post_id = 69;
							$queried_post = get_post($post_id);
							echo $queried_post->post_content;
						?>
					</div>
					<div class="contact col-md-6 content-area order-1">	
						<img src="<?php echo get_template_directory_uri(); ?>/img/only-for-you-logo.svg" width="300" height="90" alt="Only FY">

						<h3>Kontaktujte nás</h3>
						<?php echo do_shortcode('[contact-form-7 id="76" title="Kontaktujte nás"]'); ?>
					</div>
				</div>
			</div>
		</section>					
	</div>
</main>

<?php get_footer(); ?>
