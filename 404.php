<?php
/**
 * Página 404 (no encontrada), en castellano y euskera.
 *
 * @package zalbi-theme
 */

get_header();

$es_euskera = function_exists( 'zalbi_is_eu' ) ? zalbi_is_eu() : ( function_exists( 'pll_current_language' ) && 'eu' === pll_current_language() );

if ( $es_euskera ) {
	$t = array(
		'h1'   => 'Ez dugu orri hau aurkitu',
		'p'    => 'Baliteke esteka zaharra izatea edo orria aldatu izana. Hemendik jarraitu dezakezu:',
		'home' => 'Hasiera',
		'cat'  => 'Katalogoa ikusi',
		'cont' => 'Kontaktua',
		'u_h'  => home_url( '/eu/' ),
		'u_c'  => home_url( '/eu/katalogoa/' ),
		'u_k'  => home_url( '/eu/kontaktua/' ),
	);
} else {
	$t = array(
		'h1'   => 'No hemos encontrado esta página',
		'p'    => 'Puede que el enlace sea antiguo o que la página haya cambiado. Puedes seguir desde aquí:',
		'home' => 'Ir al inicio',
		'cat'  => 'Ver el catálogo',
		'cont' => 'Contacto',
		'u_h'  => home_url( '/' ),
		'u_c'  => home_url( '/catalogo/' ),
		'u_k'  => home_url( '/contacto/' ),
	);
}
?>

<main id="primary" class="site-main">
	<section class="hero-catalog">
		<div class="container">
			<h1><?php echo esc_html( $t['h1'] ); ?></h1>
			<p><?php echo esc_html( $t['p'] ); ?></p>
		</div>
	</section>

	<div class="container error-404-actions">
		<a class="btn btn-pink" href="<?php echo esc_url( $t['u_h'] ); ?>"><?php echo esc_html( $t['home'] ); ?></a>
		<a class="btn btn-outline" href="<?php echo esc_url( $t['u_c'] ); ?>"><?php echo esc_html( $t['cat'] ); ?></a>
		<a class="btn btn-outline" href="<?php echo esc_url( $t['u_k'] ); ?>"><?php echo esc_html( $t['cont'] ); ?></a>
	</div>
</main>

<?php
get_footer();
