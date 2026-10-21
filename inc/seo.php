<?php
/**
 * SEO helpers for zalbi-theme.
 *
 * Works on top of All in One SEO (titles, descriptions, sitemap) and Polylang
 * (ES/EU). Everything here is defensive: if a plugin is missing the theme keeps
 * working and simply skips the enhancement.
 *
 * @package zalbi-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the current request in Basque?
 *
 * @return bool
 */
function zalbi_is_eu() {
	return function_exists( 'pll_current_language' ) && 'eu' === pll_current_language();
}

/**
 * Business data used by the structured data. One place to edit.
 *
 * @return array
 */
function zalbi_business() {
	return array(
		'name'   => 'Zalbi Aisia eta Abentura',
		'phone'  => '+34658887358',
		'email'  => 'aritzzalbidea@yahoo.es',
		'street' => 'Telleri 11-A',
		'postal' => '48600',
		'city'   => 'Sopela',
		'region' => 'Bizkaia',
		'social' => array(
			'https://www.instagram.com/zalbi_aisia_abentura/',
			'https://www.facebook.com/osipark.aisialdirakoekintzak/',
		),
	);
}

/**
 * Title and description for the home page, per language.
 *
 * @return array
 */
function zalbi_home_meta() {
	if ( zalbi_is_eu() ) {
		return array(
			'title'       => 'Puzgarrien alokairua Bizkaian | Zalbi Aisia',
			'description' => 'Sopelako enpresa: puzgarriak, uretako atrakzioak eta jokoak alokatzen ditugu Bizkaian eta Euskadin. 15 urte baino gehiagoko esperientzia. Aurrekontua konpromisorik gabe.',
		);
	}

	return array(
		'title'       => 'Alquiler de hinchables en Bizkaia | Zalbi Aisia',
		'description' => 'Empresa de Sopela: alquiler de hinchables, juegos y atracciones acuáticas en Bizkaia y Euskadi. Más de 15 años de experiencia. Presupuesto sin compromiso.',
	);
}

/* -------------------------------------------------------------------------
 * Titles and descriptions (All in One SEO filters)
 * ---------------------------------------------------------------------- */

/**
 * Home: fixed title per language. Catalogue and event items: if the title is
 * still AIOSEO's default ("Spiderman - Zalbi") build a keyword title. A title
 * written by hand in the editor is never touched.
 *
 * @param string $title Title computed by AIOSEO.
 * @return string
 */
function zalbi_filter_seo_title( $title ) {
	if ( is_admin() ) {
		return $title;
	}

	if ( is_front_page() ) {
		$meta = zalbi_home_meta();
		return $meta['title'];
	}

	if ( is_singular( array( 'hinchable', 'evento' ) ) ) {
		$post_title = wp_strip_all_tags( get_the_title() );
		$site       = get_bloginfo( 'name' );
		$decoded    = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES, 'UTF-8' );
		$default    = '/^' . preg_quote( html_entity_decode( $post_title, ENT_QUOTES, 'UTF-8' ), '/' ) . '\s+[\-\x{2013}\x{2014}|\x{00B7}]\s+' . preg_quote( $site, '/' ) . '$/u';

		if ( '' !== $post_title && preg_match( $default, $decoded ) ) {
			$is_event = is_singular( 'evento' );

			if ( zalbi_is_eu() ) {
				$suffix = $is_event ? 'animazioa Bizkaian' : 'alokairua Bizkaian';
			} else {
				$suffix = $is_event ? 'animación y eventos en Bizkaia' : 'alquiler en Bizkaia';
			}

			return $post_title . ': ' . $suffix . ' | Zalbi Aisia';
		}
	}

	return $title;
}
add_filter( 'aioseo_title', 'zalbi_filter_seo_title', 20 );

/**
 * Home: meta description per language.
 *
 * @param string $description Description computed by AIOSEO.
 * @return string
 */
function zalbi_filter_seo_description( $description ) {
	if ( ! is_admin() && is_front_page() ) {
		$meta = zalbi_home_meta();
		return $meta['description'];
	}

	return $description;
}
add_filter( 'aioseo_description', 'zalbi_filter_seo_description', 20 );

/* -------------------------------------------------------------------------
 * Language signals
 * ---------------------------------------------------------------------- */

/**
 * AIOSEO falls back to en_US for the Basque locale ("eu"). Rewrite og:locale
 * on Basque pages so social networks and crawlers see eu_ES.
 */
function zalbi_start_locale_buffer() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_robots() || ! zalbi_is_eu() ) {
		return;
	}
	ob_start( 'zalbi_fix_og_locale' );
}
add_action( 'template_redirect', 'zalbi_start_locale_buffer', 0 );

/**
 * Output-buffer callback for zalbi_start_locale_buffer().
 *
 * @param string $html Full page HTML.
 * @return string
 */
function zalbi_fix_og_locale( $html ) {
	return preg_replace(
		'/(<meta\s+property=(["\'])og:locale\2\s+content=)(["\'])[^"\']*\3/i',
		'$1$3eu_ES$3',
		$html
	);
}

/**
 * Polylang prints hreflang for ES and EU. Add x-default pointing to Spanish.
 *
 * @param array $hreflangs hreflang => URL.
 * @return array
 */
function zalbi_hreflang_x_default( $hreflangs ) {
	if ( isset( $hreflangs['es'] ) && ! isset( $hreflangs['x-default'] ) ) {
		$hreflangs['x-default'] = $hreflangs['es'];
	}
	return $hreflangs;
}
add_filter( 'pll_rel_hreflang_attributes', 'zalbi_hreflang_x_default' );

/* -------------------------------------------------------------------------
 * Structured data (JSON-LD)
 * ---------------------------------------------------------------------- */

/**
 * LocalBusiness on the home page, Service on each catalogue / event item.
 * AIOSEO already prints Organization, WebSite, WebPage and BreadcrumbList.
 */
function zalbi_output_structured_data() {
	if ( is_admin() ) {
		return;
	}

	$b           = zalbi_business();
	$eu          = zalbi_is_eu();
	$business_id = home_url( '/#localbusiness' );
	$area        = array(
		array(
			'@type' => 'AdministrativeArea',
			'name'  => 'Bizkaia',
		),
		array(
			'@type' => 'AdministrativeArea',
			'name'  => 'Euskadi',
		),
	);
	$graph       = array();

	if ( is_front_page() ) {
		$logo_id = (int) get_theme_mod( 'custom_logo' );
		$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		$meta    = zalbi_home_meta();

		$graph[] = array_filter(
			array(
				'@type'         => 'EntertainmentBusiness',
				'@id'           => $business_id,
				'name'          => $b['name'],
				'url'           => home_url( '/' ),
				'description'   => $meta['description'],
				'image'         => $logo,
				'telephone'     => $b['phone'],
				'email'         => $b['email'],
				'address'       => array(
					'@type'           => 'PostalAddress',
					'streetAddress'   => $b['street'],
					'postalCode'      => $b['postal'],
					'addressLocality' => $b['city'],
					'addressRegion'   => $b['region'],
					'addressCountry'  => 'ES',
				),
				'areaServed'    => $area,
				'knowsLanguage' => array( 'es', 'eu' ),
				'sameAs'        => $b['social'],
			)
		);
	} elseif ( is_singular( array( 'hinchable', 'evento' ) ) ) {
		$post     = get_queried_object();
		$is_event = is_singular( 'evento' );

		if ( $eu ) {
			$type = $is_event ? 'Haurrentzako animazioa eta ekitaldiak' : 'Puzgarrien eta atrakzioen alokairua';
		} else {
			$type = $is_event ? 'Animación y eventos infantiles' : 'Alquiler de hinchables y atracciones';
		}

		$graph[] = array_filter(
			array(
				'@type'       => 'Service',
				'@id'         => get_permalink( $post ) . '#service',
				'name'        => wp_strip_all_tags( get_the_title( $post ) ),
				'serviceType' => $type,
				'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
				'url'         => get_permalink( $post ),
				'image'       => get_the_post_thumbnail_url( $post, 'large' ),
				'areaServed'  => $area,
				'provider'    => array(
					'@type' => 'EntertainmentBusiness',
					'@id'   => $business_id,
					'name'  => $b['name'],
					'url'   => home_url( '/' ),
				),
			)
		);

		// Migas de pan (las mismas que se ven en la página).
		if ( $is_event ) {
			$list_label = $eu ? 'Ekitaldiak' : 'Eventos';
			$list_url   = $eu ? home_url( '/eu/ekitaldiak/' ) : home_url( '/eventos/' );
		} else {
			$list_label = $eu ? 'Katalogoa' : 'Catálogo';
			$list_url   = $eu ? home_url( '/eu/katalogoa/' ) : home_url( '/catalogo/' );
		}

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => get_permalink( $post ) . '#breadcrumb',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => $eu ? 'Hasiera' : 'Inicio',
					'item'     => $eu ? home_url( '/eu/' ) : home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => $list_label,
					'item'     => $list_url,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => wp_strip_all_tags( get_the_title( $post ) ),
					'item'     => get_permalink( $post ),
				),
			),
		);
	}

	if ( empty( $graph ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'wp_head', 'zalbi_output_structured_data', 30 );

/* -------------------------------------------------------------------------
 * Content clean-up
 * ---------------------------------------------------------------------- */

/**
 * Pages print the title as <h1> (content-page.php) and the Contact Form 7
 * template repeats it as another <h1> inside the form. Drop a heading that
 * repeats the page title and demote any other <h1> in the content to <h2>.
 *
 * @param string $content Post content.
 * @return string
 */
function zalbi_clean_page_headings( $content ) {
	if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$title = trim( wp_strip_all_tags( get_the_title() ) );

	return preg_replace_callback(
		'#<h1\b[^>]*>(.*?)</h1>#is',
		function ( $m ) use ( $title ) {
			$text = trim( wp_strip_all_tags( $m[1] ) );

			if ( '' !== $title && 0 === strcasecmp( $text, $title ) ) {
				return '';
			}

			return str_ireplace( array( '<h1', '</h1>' ), array( '<h2', '</h2>' ), $m[0] );
		},
		$content
	);
}
add_filter( 'the_content', 'zalbi_clean_page_headings', 20 );

/**
 * Catalogue and event images were uploaded without alt text. Use the item
 * title when the media library has none, so every product image has one.
 *
 * @param array $attr Image attributes.
 * @return array
 */
function zalbi_image_alt_fallback( $attr ) {
	if ( isset( $attr['alt'] ) && '' !== trim( $attr['alt'] ) ) {
		return $attr;
	}

	if ( in_array( get_post_type(), array( 'hinchable', 'evento' ), true ) ) {
		$attr['alt'] = wp_strip_all_tags( get_the_title() );
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'zalbi_image_alt_fallback' );

/* -------------------------------------------------------------------------
 * Retired URLs
 * ---------------------------------------------------------------------- */

/**
 * The "Producto" template page was unpublished. Send its old URL to the
 * catalogue with a permanent redirect instead of leaving a 404.
 */
function zalbi_redirect_retired_urls() {
	if ( ! is_404() ) {
		return;
	}

	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );
	$map  = array(
		'producto'    => '/catalogo/',
		'eu/producto' => '/eu/katalogoa/',
	);

	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'zalbi_redirect_retired_urls' );

/* -------------------------------------------------------------------------
 * Legal links (footer)
 * ---------------------------------------------------------------------- */

/**
 * URL of a published page by slug, in the given language.
 *
 * @param string[] $slugs Candidate slugs, first match wins.
 * @param string   $lang  Polylang language slug.
 * @return string Empty string if no published page matches.
 */
function zalbi_find_page_url( $slugs, $lang ) {
	foreach ( $slugs as $slug ) {
		$args = array(
			'post_type'        => 'page',
			'name'             => $slug,
			'post_status'      => 'publish',
			'numberposts'      => 1,
			'suppress_filters' => false,
		);

		if ( function_exists( 'pll_current_language' ) ) {
			$args['lang'] = $lang;
		}

		$found = get_posts( $args );

		if ( ! empty( $found ) ) {
			// Polylang: no enlazar una página que esté en otro idioma.
			if ( function_exists( 'pll_get_post_language' ) && pll_get_post_language( $found[0]->ID ) !== $lang ) {
				continue;
			}
			return get_permalink( $found[0] );
		}
	}

	return '';
}

/**
 * Footer legal links. Only published pages are listed, so the footer never
 * links to a draft or a 404.
 *
 * @return array[] Each item: array( 'label' => string, 'url' => string ).
 */
function zalbi_legal_links() {
	$eu   = zalbi_is_eu();
	$lang = $eu ? 'eu' : 'es';

	$items = array(
		array(
			'label' => $eu ? 'Lege Oharra' : 'Aviso Legal',
			'slugs' => $eu ? array( 'lege-oharra', 'aviso-legal' ) : array( 'aviso-legal' ),
		),
		array(
			'label' => $eu ? 'Pribatutasun Politika' : 'Política de Privacidad',
			'slugs' => $eu ? array( 'politica-privacidad', 'pribatutasun-politika' ) : array( 'politica-privacidad' ),
		),
		array(
			'label' => $eu ? 'Cookie Politika' : 'Política de Cookies',
			'slugs' => $eu ? array( 'cookie-politika', 'politica-cookies' ) : array( 'politica-cookies' ),
		),
	);

	$links = array();

	foreach ( $items as $item ) {
		$url = zalbi_find_page_url( $item['slugs'], $lang );

		if ( '' !== $url ) {
			$links[] = array(
				'label' => $item['label'],
				'url'   => $url,
			);
		}
	}

	return $links;
}
