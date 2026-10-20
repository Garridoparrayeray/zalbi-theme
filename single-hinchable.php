<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); 
    // DETECTAR IDIOMA
    $es_euskera = (function_exists('pll_current_language') && pll_current_language() == 'eu');

    // Recuperacion campos ACF (Protegido con get_the_ID)
    $medidas = get_field('medidas', get_the_ID());
    $capacidad = get_field('capacidad', get_the_ID());
    $tag_color = get_field('etiqueta_color', get_the_ID());
    
    // Traductor de Colores -> hay que traducir en todos si se mete colores o nuevas categorias, no he podido implementar un entrelazado entre todo
    //falta de tiempo y dinero
    if ($es_euskera) {
        $nombres_visuales = array(
            'tag-orange' => 'Kirola',
            'tag-pink'   => 'Puzgarria',
            'tag-blue'   => 'Uretakoa',
            'tag-green'  => 'Ekitaldia',
            'tag-purple' => 'Jokoa' 
        );
        $texto_defecto = 'Puzgarria';
        
        // Textos fijos traducidos
        $txt_inicio = 'Hasiera';
        $txt_catalogo = 'Katalogoa';
        $txt_categoria = 'Kategoria';
        $txt_ficha = 'Fitxa Teknikoa';
        $txt_medidas = 'Neurriak';
        $txt_capacidad = 'Edukiera';
        $txt_interesa = 'Eredu hau interesatzen zaizu?';
        $txt_btn_presu = 'Eskatu Aurrekontua';
        $txt_tambien = 'Interesgarria izan daiteke ere';
        $txt_ver_mas = 'Ikusi gehiago';
        
        $url_catalogo = home_url('/eu/katalogoa');
        $url_contacto = home_url('/eu/kontaktua');

    } else {
        $nombres_visuales = array(
            'tag-orange' => 'Atracción deportiva',
            'tag-pink'   => 'Hinchable',
            'tag-blue'   => 'Acuático',
            'tag-green'  => 'Evento',
            'tag-purple' => 'Juego' 
        );
        $texto_defecto = 'Hinchable';

        // Textos fijos en Español
        $txt_inicio = 'Inicio';
        $txt_catalogo = 'Catálogo';
        $txt_categoria = 'Categoría';
        $txt_ficha = 'Ficha Técnica';
        $txt_medidas = 'Medidas';
        $txt_capacidad = 'Capacidad';
        $txt_interesa = '¿Te interesa este modelo?';
        $txt_btn_presu = 'Solicitar Presupuesto';
        $txt_tambien = 'También te puede interesar';
        $txt_ver_mas = 'Ver más';

        $url_catalogo = home_url('/catalogo');
        $url_contacto = home_url('/contacto');
    }
    
    // Imágenes metidas en la descripción: pasan a la galería (columna izquierda).
    $contenido_html = apply_filters( 'the_content', get_the_content() );
    $galeria_imgs   = array();
    if ( preg_match_all( '/<img\b[^>]*>/i', $contenido_html, $m_img ) ) {
        $galeria_imgs = $m_img[0];
    }
    $contenido_html = preg_replace( '#<figure\b[^>]*>.*?</figure>#is', '', $contenido_html );
    $contenido_html = preg_replace( '/<img\b[^>]*>/i', '', $contenido_html );
    $contenido_html = preg_replace( '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $contenido_html );

    // Datos de la ficha: se ocultan si están vacíos o son de relleno.
    $es_dato_valido = function ( $v ) {
        $v = trim( wp_strip_all_tags( (string) $v ) );
        return '' !== $v && ! in_array( mb_strtolower( $v ), array( 'sin determinar', 'zehaztu gabe', '-', 'n/a' ), true );
    };
    $mostrar_medidas   = $es_dato_valido( $medidas );
    $mostrar_capacidad = $es_dato_valido( $capacidad );

    $nombre_categoria_visual = isset($nombres_visuales[$tag_color]) ? $nombres_visuales[$tag_color] : $texto_defecto;
?>

    <div class="container section-pad">
        <p class="breadcrumbs">
            <a href="<?php echo home_url(); ?>"><?php echo $txt_inicio; ?></a> > 
            <a href="<?php echo $url_catalogo; ?>"><?php echo $txt_catalogo; ?></a> > 
            <span><?php the_title(); ?></span>
        </p>

        <div class="product-detail-layout">
            
            <div class="product-gallery">
                <?php if ( has_post_thumbnail() ) {
                    the_post_thumbnail( 'large', array( 'class' => 'gallery-main', 'id' => 'gallery-main-img' ) );
                } else { ?>
                    <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/img/logo.webp" alt="<?php echo esc_attr( get_the_title() ); ?>" class="gallery-main" id="gallery-main-img" style="background:#eee;">
                <?php } ?>

                <?php if ( ! empty( $galeria_imgs ) ) : ?>
                    <div class="gallery-thumbs" id="gallery-thumbs">
                        <?php foreach ( $galeria_imgs as $img_html ) {
                            echo '<button type="button" class="gallery-thumb">' . $img_html . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h1 style="color: var(--c-purple); margin-bottom: 10px;"><?php the_title(); ?></h1>
                
                <span class="card-tag <?php echo $tag_color; ?>"><?php echo $txt_categoria; ?>: <?php echo $nombre_categoria_visual; ?></span>
                
                <div class="product-desc" style="margin-top: 20px;">
                    <?php echo $contenido_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>

                <?php if ( $mostrar_medidas || $mostrar_capacidad ) : ?>
                <div class="spec-box">
                    <h2 class="spec-title"><?php echo $txt_ficha; ?></h2>

                    <?php if ( $mostrar_medidas ) : ?>
                    <div class="spec-row">
                        <span><i class="fas fa-ruler-combined" aria-hidden="true"></i> <?php echo $txt_medidas; ?></span>
                        <strong><?php echo esc_html( $medidas ); ?></strong>
                    </div>
                    <?php endif; ?>

                    <?php if ( $mostrar_capacidad ) : ?>
                    <div class="spec-row">
                        <span><i class="fas fa-users" aria-hidden="true"></i> <?php echo $txt_capacidad; ?></span>
                        <strong><?php echo esc_html( $capacidad ); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="cta-box">
                    <p style="margin-bottom: 10px; font-weight: bold;"><?php echo $txt_interesa; ?></p>
                    <a href="<?php echo esc_url( add_query_arg( 'modelo', rawurlencode( get_the_title() ), $url_contacto ) ); ?>" class="btn" style="background: var(--c-lime); color: var(--c-purple); width: 100%;"><?php echo $txt_btn_presu; ?></a>
                </div>
            </div>
        </div>
    </div>

    <section class="section-pad" style="background: white; border-top: 1px solid #eee;">
        <div class="container">
            <h3 style="margin-bottom: 30px; color: var(--c-purple); text-align: center"><?php echo $txt_tambien; ?></h3>

            <div class="options" style = "justify-content: center;">
                <?php
                $related_args = array(
                    'post_type' => 'hinchable',
                    'posts_per_page' => 3,
                    'post__not_in' => array(get_the_ID()), 
                    'orderby' => 'rand'
                );
                $related = new WP_Query($related_args);

                if( $related->have_posts() ) {
                    while( $related->have_posts() ) {
                        $related->the_post();
                        
                        $r_tag_color = get_field('etiqueta_color', get_the_ID());
                        $r_nombre = isset($nombres_visuales[$r_tag_color]) ? $nombres_visuales[$r_tag_color] : $texto_defecto;
                ?>
                    
                    <a href="<?php the_permalink(); ?>" class="product-card" style="text-decoration: none; color: inherit; max-width: 300px; margin: 0;">
                        <div style="height: 200px; overflow: hidden;">
                            <?php the_post_thumbnail('medium', array('style' => 'width:100%; height:100%; object-fit:contain; background: #f0f0f0;')); ?>
                        </div>
                        <div class="card-content" style="padding: 15px;">
                            <span class="card-tag <?php echo $r_tag_color; ?>" style="font-size: 11px;"><?php echo $r_nombre; ?></span>
                            <h4 style="margin: 10px 0; font-size: 18px; color: var(--text-main);"><?php the_title(); ?></h4>
                            <span class="btn btn-outline" style="font-size: 12px; padding: 5px 15px; width: 100%; display:block; text-align:center;"><?php echo $txt_ver_mas; ?></span>
                        </div>
                    </a>

                <?php
                    }
                    wp_reset_postdata(); 
                }
                ?>
            </div>
        </div>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var main = document.getElementById('gallery-main-img');
    var thumbs = document.querySelectorAll('#gallery-thumbs .gallery-thumb img');
    if (!main || !thumbs.length) { return; }
    thumbs.forEach(function (t) {
        t.parentNode.addEventListener('click', function () {
            var prev = main.currentSrc || main.src;
            main.removeAttribute('srcset');
            main.removeAttribute('sizes');
            main.src = t.currentSrc || t.src;
            t.removeAttribute('srcset');
            t.removeAttribute('sizes');
            t.src = prev;
        });
    });
});
</script>

<?php endwhile; ?>

<?php get_footer(); ?>