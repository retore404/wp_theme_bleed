<?php
/**
 * PhotoSoushi WordPress Theme
 *
 * @package WordPress
 * @subpackage PhotoSoushi
 * @author retore
 * @link https://github.com/retore404/PhotoSoushi
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */

/**
 * 記事中1枚目の画像をアイキャッチ化する.
 */
function catch_first_image() {
	global $post, $posts;
	$first_img = '';
	ob_start();
	ob_end_clean();
	$output    = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
	$first_img = $matches [1] [0];
	if ( empty( $first_img ) ) { // Defines a default image.
		$dir       = get_template_directory_uri();
		$first_img = "$dir/images/thumbnail.svg";
	}
	return $first_img;
}

/**
 * リサイズ画像の自動生成を停止する.
 *
 * @param array $new_sizes 生成される画像サイズを定義する配列.
 * @return array $new_sizes 生成される画像サイズを定義する配列.
 */
function disable_image_sizes( $new_sizes ) {
	unset( $new_sizes['thumbnail'] );
	unset( $new_sizes['medium'] );
	unset( $new_sizes['large'] );
	unset( $new_sizes['medium_large'] );
	unset( $new_sizes['1536x1536'] );
	unset( $new_sizes['2048x2048'] );
	return $new_sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'disable_image_sizes' );
add_filter( 'big_image_size_threshold', '__return_false' );

/**
 * 画像のwidth/height自動指定を除去.
 *
 * @param string $html サムネイルのhtml.
 * @return string $html サムネイルのhtml(width/heightの指定削除).
 */
function remove_width_attribute( $html ) {
	$html = preg_replace( '/(width|height)="\d*"\s/', '', $html );
	return $html;
}
add_filter( 'post_thumbnail_html', 'remove_width_attribute', 10 );
add_filter( 'image_send_to_editor', 'remove_width_attribute', 10 );
add_filter( 'wp_img_tag_add_width_and_height_attr', '__return_false' ); // Gutenberg対応.

/**
 * タイトルタグを自動生成.
 *
 * @param array $results タイトルタグの内容配列.
 * @return array $results タイトルタグの内容配列（トップページにおいてサイトディスクリプションが空・全ページにおいてページ数が空）.
 */
function custom_title_text( $results ) {
	if ( is_home() ) {
		$results['tagline'] = '';
	}
	$results['page'] = '';
	return $results;
}
add_theme_support( 'title-tag' );
add_filter( 'document_title_parts', 'custom_title_text', 11 );

// ウィジェット.
register_sidebar(
	array(
		'name'          => __( 'MainWidget1' ),
		'id'            => 'main_widget1',
		'before_widget' => '<div class="widget-wrapper">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	)
);

register_sidebar(
	array(
		'name'          => __( 'Footer Widget1' ),
		'id'            => 'footer_widget1',
		'before_widget' => '<div class="widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	)
);

register_sidebar(
	array(
		'name'          => __( 'Footer Widget2' ),
		'id'            => 'footer_widget2',
		'before_widget' => '<div class="widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3>',
		'after_title'   => '</h3>',
	)
);

/** ページネーション. */
function the_pagination() {
	global $wp_query;
	$bignum = 999999999;
	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}
	echo '<nav class="pagination">';
	echo wp_kses_post(
		paginate_links(
			array(
				'base'      => str_replace( $bignum, '%#%', esc_url( get_pagenum_link( $bignum ) ) ),
				'format'    => '',
				'current'   => max( 1, get_query_var( 'paged' ) ),
				'total'     => $wp_query->max_num_pages,
				'prev_text' => '«',
				'next_text' => '»',
				'type'      => 'list',
				'end_size'  => 3,
				'mid_size'  => 3,
			)
		)
	);
	echo '</nav>';
}

/** コメントフォームの順序変更.
 *
 * @param array $fields コメントフィールド.
 * @return array $fields コメントフィールド（コメント部の順序逆転）.
 */
function move_comment_field_to_bottom( $fields ) {
	$comment_field = $fields['comment'];
	unset( $fields['comment'] );
	$fields['comment'] = $comment_field;
	return $fields;
}
add_filter( 'comment_form_fields', 'move_comment_field_to_bottom' );

// pタグの自動挿入を停止.
add_action(
	'init',
	function() {
		remove_filter( 'the_excerpt', 'wpautop' );
		remove_filter( 'the_content', 'wpautop' );
	}
);

add_filter(
	'tiny_mce_before_init',
	function( $init ) {
		$init['wpautop']                 = false;
		$init['apply_source_formatting'] = ture;
		return $init;
	}
);


/** タグ名の置き換え（アイコン化）.
 *
 * @param string $tag_name タグ名（アイコン置き換え前）.
 * @return string $tag_name タグ名（アイコン置き換え後）.
 */
function replace_tag_name( $tag_name ) {
	// タグ名の"Lens:"をアイコンに置き換える.
	$tag_name = str_replace( 'Lens:', '<i class="fas fa-camera"></i> ', $tag_name );
	// タグ名の"Location:"をアイコンに置き換える.
	$tag_name = str_replace( 'Location:', '<i class="fas fa-map-marker-alt"></i> ', $tag_name );
	// タグ名の"T*"を赤字にする.
	$tag_name = str_replace( 'T*', '<span class="t-star">T*</span>', $tag_name );
	return $tag_name;
}

/**
 * CSSの読み込み.
 */
function photo_soushi_enque_styles() {
	// FontAwesomeの読み込み.
	wp_enqueue_style(
		'font-awesome',
		'https://use.fontawesome.com/releases/v5.13.0/css/all.css',
		array(),
		'1.0.0',
		'all'
	);
	// PhotoSoushi style.cssの読み込み.
	wp_enqueue_style(
		'photo-soushi-css',
		get_stylesheet_uri(),
		array(),
		'1.0.0',
		'all'
	);
}
add_action( 'wp_enqueue_scripts', 'photo_soushi_enque_styles' );

/**
 * WebPファイルの許可
 *
 * @param array $mimes 許可するmimesの配列.
 * @return array $mimes 許可するmimesの配列(カスタムで許可するmimes追加済).
 */
function permit_mime_types( $mimes ) {
	$mimes['webp'] = 'image/webp';
	return $mimes;
}
add_filter( 'upload_mimes', 'permit_mime_types' );

/**
 * テーマ設定の追加
 */
function photo_soushi_theme_option() {
  add_options_page( 'テーマ設定', 'テーマ設定', 'edit_themes','theme_option','photo_soushi_theme_option_file' );
}
add_action('admin_menu', 'photo_soushi_theme_option');

/**
 * テーマ設定ページの定義
 */
function photo_soushi_theme_option_file(){
    require_once ( get_template_directory() . '/theme-options.php' );
}
add_action('admin_init', 'photo_soushi_theme_option_file' );