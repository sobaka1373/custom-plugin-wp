<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function admin_page_open(): void
{
    ob_start();
    ?>
    <p>Start parse DB and generate Posts</p>
    <input type="button" class="button button-parse-plugin" name="parse" value="Convert data to post" />
    <input type="button" class="button button-parse-post-plugin" name="parse_post" value="Parse post" />
    <span class="spinner"></span>
    <p class="message-parse hidden">There are still posts to be processed.</p>
    <p class="message-parse-end hidden">All processed.</p>
    <?php
    $output = ob_get_clean();
    echo $output;
}

function get_data_from_db(): array
{
    global $wpdb;
    $sql = "SELECT * FROM jos_content WHERE sectionid <> 0 AND sectionid <> 1
    AND sectionid <> 2 AND sectionid <> 8 AND sectionid <> 9 AND sectionid <> 11 AND sectionid <> 12 
    AND sectionid <> 14 AND sectionid <> 17 AND sectionid <> 19 AND sectionid <> 36;";
    return $wpdb->get_results($sql);
}

function get_category_post($item): array
{
    $category_array = [];
    $string_lower = mb_strtolower($item->title, "UTF-8");

    if ((int) $item->sectionid === 3 || (int) $item->sectionid === 15
        || (int) $item->sectionid === 16 || (int) $item->sectionid === 27) {
	      $category_array[] = get_category_id('studentstvo');
    }

    if ((int) $item->sectionid === 13) {
      if (str_contains($string_lower, 'фоторепортаж') || str_contains($string_lower, 'звіт')) {
	      $category_array[] = get_category_id('studentstvo');
      }
    }

    if ((int) $item->sectionid === 4) {
	    $category_array[] = get_category_id('mayster-klas');
    }

    if ((int) $item->sectionid === 5) {
	    $category_array[] = get_category_id('konferentsii');
    }

    if ((int) $item->sectionid === 7) {
        if (str_contains($string_lower, 'ректор') || str_contains($string_lower, 'універ')
            || str_contains($string_lower, 'КУП НАН') || str_contains($string_lower, 'аукціон')
            || str_contains($string_lower, 'співпрац') || str_contains($string_lower, 'виставк')
            || str_contains($string_lower, 'план') || str_contains($string_lower, 'угод')
            || str_contains($string_lower, 'часопис') || str_contains($string_lower, 'бакалавр')
            || str_contains($string_lower, 'магістр') || str_contains($string_lower, 'колег')
            || str_contains($string_lower, 'студент') || str_contains($string_lower, 'аспірант')) {
	        $category_array[] = get_category_id('universitet');
        }
    }

    if (str_contains($string_lower, 'круглий стіл')) {
	    $category_array[] = get_category_id('kruhly-stoly');
    }

    if (str_contains($string_lower, 'семінар')) {
	    $category_array[] = get_category_id('seminary');
    }

    if (str_contains($string_lower, 'увага')) {
	    $category_array[] = get_category_id('ogoloshennya');
    }

    if (str_contains($string_lower, 'абітур') || str_contains($string_lower, 'вступ')) {
	    $category_array[] = get_category_id('abituriientu');
    }

    if (str_contains($string_lower, 'студент')) {
	    $category_array[] = get_category_id('studentstvo');
    }

    if (str_contains($string_lower, 'конференц')) {
	    $category_array[] = get_category_id('konferentsii');
    }

    $category_array = array_unique($category_array);
    if (empty($category_array)) {
	    $category_array[] = 1;
      return $category_array;
    }

	return $category_array;
}

function get_category_id($slug): int
{
	return get_category_by_slug($slug)->term_id;
}
function parse_article($old_post_content): string
{

	$new_post_content = str_replace("div", "p", $old_post_content );

	$new_post_content = preg_replace("~<p\s+.*?>~i",'<p>', $new_post_content);
	$new_post_content = preg_replace("~<span\s+.*?>~i",'<span>', $new_post_content);

	$new_post_content = str_replace( "http://kul.kiev.ua", "", $new_post_content );
	$new_post_content = str_replace( "//", "/", $new_post_content );

	//Table
	$pos1 = stripos($new_post_content, '<table');
	$pos2 = stripos($new_post_content, '</table>');
	if ($pos1) {
		$table = substr($new_post_content, $pos1, $pos2 - $pos1);
		$image_flag = str_contains($table, "<img");
		if ($image_flag) {
			$new_post_content = preg_replace("~<table\s+.*?>~i",'<div class="data">', $new_post_content);
			$new_post_content = str_replace("</table>", "</div>", $new_post_content);
			$new_post_content = str_replace( array( "<tbody>", "</tbody>", "<tr>", "</tr>", "<td>", "</td>" ), "", $new_post_content );
		}
	}

	$doc = new DOMDocument();
	$doc->loadHTML(mb_convert_encoding($new_post_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
	$elements = $doc->getElementsByTagName('script');
	for ($i = $elements->length; --$i >= 0; ) {
		$script = $elements->item($i);
    if ($script) {
	    $script->parentNode->removeChild($script);
    }
	}
	$images = $doc->getElementsByTagName('img');
	for ($i = $images->length; --$i >= 0; ) {
	  $image = $images->item($i);
		if ( $image && $image->parentNode->tagName === 'a' ) {
      $image->parentNode->setAttribute('class', 'zoom');
    }
	}

	return $doc->saveHTML();
}

function check_posts_consist_meta(): void
{
	$args = array(
		'meta_key' => 'jos_table',
		'meta_value' => '1',
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 1
	);
	$posts = get_posts($args);

	if (empty($posts)) {
		echo 1;
	} else {
		echo 2;
	}
}