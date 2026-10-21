<?php
/**
 * Sitemap propio: /sitemap-zalbi.xml
 *
 * XML plano (sin hoja de estilos ni cabecera noindex) con las páginas, los
 * hinchables y los eventos publicados, en los dos idiomas, con sus
 * alternativas hreflang. Deja fuera los archivos de "tipo" (contenido muy
 * escaso) y la página de privacidad por defecto de WordPress.
 *
 * @package zalbi-theme
 */

/**
 * Atiende la petición /sitemap-zalbi.xml sin necesidad de reglas de reescritura.
 */
function zalbi_serve_sitemap() {
	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';

	if ( '/sitemap-zalbi.xml' !== $path ) {
		return;
	}

	$exclude = array();
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy ) {
		$exclude[] = $privacy;
		if ( function_exists( 'pll_get_post_translations' ) ) {
			$exclude = array_merge( $exclude, array_values( pll_get_post_translations( $privacy ) ) );
		}
	}

	$posts = get_posts(
		array(
			'post_type'        => array( 'page', 'hinchable', 'evento' ),
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'orderby'          => 'post_type title',
			'order'            => 'ASC',
			'post__not_in'     => $exclude,
			'suppress_filters' => true,
			'lang'             => '', // Polylang: todos los idiomas.
		)
	);

	status_header( 200 );
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'Cache-Control: public, max-age=3600' );

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

	// Portadas (no son entradas de WordPress): una por idioma, con sus alternativas.
	$homes = array();
	if ( function_exists( 'pll_languages_list' ) && function_exists( 'pll_home_url' ) ) {
		foreach ( pll_languages_list() as $lang ) {
			$homes[ $lang ] = pll_home_url( $lang );
		}
	} else {
		$homes[''] = home_url( '/' );
	}

	foreach ( $homes as $lang => $home_url ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_xml( $home_url ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_xml( gmdate( 'c' ) ) . "</lastmod>\n";
		if ( count( $homes ) > 1 ) {
			foreach ( $homes as $alt_lang => $alt_url ) {
				echo "\t\t" . '<xhtml:link rel="alternate" hreflang="' . esc_attr( $alt_lang ) . '" href="' . esc_url( $alt_url ) . '" />' . "\n";
			}
		}
		echo "\t</url>\n";
	}

	foreach ( $posts as $p ) {
		$url = get_permalink( $p );
		if ( ! $url ) {
			continue;
		}

		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_xml( $url ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_xml( gmdate( 'c', strtotime( $p->post_modified_gmt . ' UTC' ) ) ) . "</lastmod>\n";

		if ( function_exists( 'pll_get_post_translations' ) ) {
			$translations = pll_get_post_translations( $p->ID );
			if ( count( $translations ) > 1 ) {
				foreach ( $translations as $lang => $tid ) {
					if ( 'publish' !== get_post_status( $tid ) ) {
						continue;
					}
					echo "\t\t" . '<xhtml:link rel="alternate" hreflang="' . esc_attr( $lang ) . '" href="' . esc_url( get_permalink( $tid ) ) . '" />' . "\n";
				}
			}
		}

		echo "\t</url>\n";
	}

	echo "</urlset>\n";
	exit;
}
add_action( 'template_redirect', 'zalbi_serve_sitemap', 0 );
