<?php
/* Template Name: Plantilla Contacto */
get_header();

$es_euskera = zalbi_is_eu();
$b          = zalbi_business();
$wa         = get_theme_mod( 'zalbi_whatsapp_number' );

$t = $es_euskera ? array(
	'sub'    => 'Kontatu gurekin eta aurrekontua prestatuko dizugu, konpromisorik gabe.',
	'info'   => 'Datuak',
	'addr'   => 'Helbidea',
	'phone'  => 'Telefonoa',
	'mail'   => 'Posta elektronikoa',
	'area'   => 'Bizkaia eta Euskadi osoan ibiltzen gara.',
	'wa'     => 'Idatzi WhatsApp bidez',
	'wa_msg' => 'Kaixo, informazioa nahi nuke',
) : array(
	'sub'    => 'Cuéntanos tu evento y te preparamos un presupuesto sin compromiso.',
	'info'   => 'Datos de contacto',
	'addr'   => 'Dirección',
	'phone'  => 'Teléfono',
	'mail'   => 'Email',
	'area'   => 'Nos movemos por toda Bizkaia y Euskadi.',
	'wa'     => 'Escríbenos por WhatsApp',
	'wa_msg' => 'Hola, quisiera información',
);
?>

<main id="primary" class="site-main">

	<section class="hero-catalog">
		<div class="container">
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html( $t['sub'] ); ?></p>
		</div>
	</section>

	<div class="container contact-layout">

		<div class="contact-form-col">
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</div>

		<aside class="contact-info-card">
			<h2><?php echo esc_html( $t['info'] ); ?></h2>
			<ul class="contact-info-list">
				<li>
					<i class="fas fa-map-marker-alt" aria-hidden="true"></i>
					<span><strong><?php echo esc_html( $t['addr'] ); ?></strong><br><?php echo esc_html( $b['street'] . ', ' . $b['postal'] . ' ' . $b['city'] ); ?></span>
				</li>
				<li>
					<i class="fas fa-phone-alt" aria-hidden="true"></i>
					<span><strong><?php echo esc_html( $t['phone'] ); ?></strong><br><a href="tel:<?php echo esc_attr( $b['phone'] ); ?>">658 88 73 58</a></span>
				</li>
				<li>
					<i class="fas fa-envelope" aria-hidden="true"></i>
					<span><strong><?php echo esc_html( $t['mail'] ); ?></strong><br><a href="mailto:<?php echo esc_attr( $b['email'] ); ?>"><?php echo esc_html( $b['email'] ); ?></a></span>
				</li>
			</ul>
			<p class="contact-area"><?php echo esc_html( $t['area'] ); ?></p>
			<?php if ( ! empty( $wa ) ) : ?>
				<a class="btn btn-pink" target="_blank" rel="noopener noreferrer"
				   href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( $t['wa_msg'] ); ?>">
					<i class="fab fa-whatsapp" aria-hidden="true"></i> <?php echo esc_html( $t['wa'] ); ?>
				</a>
			<?php endif; ?>
		</aside>

	</div>
</main>

<?php
get_footer();
