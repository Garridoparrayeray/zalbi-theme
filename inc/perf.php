<?php
/**
 * Rendimiento de imágenes.
 *
 * @package zalbi-theme
 */

/**
 * Imprime la miniatura de la entrada actual para una tarjeta de listado.
 * Las primeras $eager tarjetas se cargan de inmediato (visibles al abrir la
 * página); el resto usa carga diferida. Siempre decoding="async" y `sizes`
 * para que el navegador elija el tamaño adecuado en móvil.
 *
 * @param string $size  Tamaño de imagen de WordPress.
 * @param int    $eager Cuántas tarjetas cargan sin diferir.
 * @param string $group Contador independiente por listado.
 */
function zalbi_card_thumbnail( $size = 'medium_large', $eager = 0, $group = 'cards' ) {
	static $count = array();

	$count[ $group ] = isset( $count[ $group ] ) ? $count[ $group ] + 1 : 1;
	$is_eager        = $count[ $group ] <= $eager;

	$attr = array(
		'style'    => 'width:100%; height:100%; object-fit:contain; background: #f0f0f0;',
		'loading'  => $is_eager ? 'eager' : 'lazy',
		'decoding' => 'async',
		'sizes'    => '(max-width: 600px) 92vw, (max-width: 1024px) 45vw, 360px',
	);

	if ( $is_eager && 1 === $count[ $group ] ) {
		$attr['fetchpriority'] = 'high';
	}

	the_post_thumbnail( $size, $attr );
}

/**
 * Hinchables relacionados: mismo tipo primero, luego el resto. Se recorre la
 * lista en orden alfabético empezando justo después de la ficha actual, de
 * modo que el reparto de enlaces internos es parejo y estable (sin azar).
 *
 * @param int $post_id Hinchable actual.
 * @param int $limit   Cuántos devolver.
 * @return int[]
 */
function zalbi_related_hinchables( $post_id, $limit = 3 ) {
	$base = array(
		'post_type'        => 'hinchable',
		'post_status'      => 'publish',
		'posts_per_page'   => -1,
		'fields'           => 'ids',
		'orderby'          => 'title',
		'order'            => 'ASC',
		'suppress_filters' => false,
	);

	if ( function_exists( 'pll_get_post_language' ) ) {
		$lang = pll_get_post_language( $post_id );
		if ( $lang ) {
			$base['lang'] = $lang;
		}
	}

	$all   = get_posts( $base );
	$terms = wp_get_post_terms( $post_id, 'tipo_hinchable', array( 'fields' => 'ids' ) );
	$same  = array();

	if ( ! is_wp_error( $terms ) && $terms ) {
		$same = get_posts(
			$base + array(
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'tipo_hinchable',
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				),
			)
		);
	}

	$cycle = function ( $ids ) use ( $post_id ) {
		// Orden circular a partir de la posición de la ficha actual.
		$pos = 0;
		foreach ( $ids as $i => $id ) {
			if ( (int) $id === (int) $post_id ) {
				$pos = $i;
				break;
			}
			if ( get_the_title( $id ) > get_the_title( $post_id ) ) {
				$pos = $i;
				break;
			}
			$pos = $i + 1;
		}
		$ids = array_values( array_diff( $ids, array( $post_id ) ) );
		$n   = count( $ids );
		if ( 0 === $n ) {
			return array();
		}
		$pos %= $n;
		$out = array();
		for ( $k = 0; $k < $n; $k++ ) {
			$out[] = $ids[ ( $pos + $k ) % $n ];
		}
		return $out;
	};

	$result = array_merge( $cycle( $same ), $cycle( $all ) );
	return array_slice( array_values( array_unique( $result ) ), 0, $limit );
}
