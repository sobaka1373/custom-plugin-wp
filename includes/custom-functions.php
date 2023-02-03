<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function admin_page_open() {
    ob_start();
    ?>
    <p>Start generation</p>
    <input type="button" class="button button-generate-posts" name="parse" value="Generate posts" />
    <span class="spinner"></span>
    <?php
    $output = ob_get_clean();
    echo $output;
}

function parse_city_file(string $file): array
{
  $data_array = [];
  $row = 0;
  if (($handle = fopen($file, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
      $num = count($data);
      $row++;
      for ($c=0; $c < $num; $c++) {
        $data_array[$row][$c] = $data[$c];
      }
    }
    fclose($handle);
  }
  return $data_array;
}

function generate_region($region)
{
  $region_data = array(
      's'    => $region,
      'post_type'     => 'region',
      'post_status'   => 'publish',
      'post_author'   => 1
  );
  $flag = get_posts($region_data);
  if (empty($flag)) {
      unset($region_data['s']);
      $region_data['post_title'] = $region;
      $region_data['post_name'] = sanitize_title(generate_slug($region));
      return wp_insert_post(wp_slash($region_data));
  }

  return $flag[0]->ID;
}

function generate_district($district, $region_id)
{
    $district_data = array(
        's'             => $district,
        'post_type'     => 'district',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_parent'   => $region_id
    );
    $flag = get_posts($district_data);
    if (empty($flag)) {
        unset($district_data['s']);
        $district_data['post_title'] = $district;
        $district_data['post_name'] = sanitize_title(generate_slug($district));
        return wp_insert_post(wp_slash($district_data));
    }

    return $flag[0]->ID;
}

function generate_townname($townname, $id_towntype)
{
    $townname_data = array(
        's'             => $townname,
        'post_type'     => 'townname',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_parent'   => $id_towntype
    );
    $flag = get_posts($townname_data);
    if (empty($flag)) {
        unset($townname_data['s']);
        $townname_data['post_title'] = $townname;
        $townname_data['post_name'] = sanitize_title(generate_slug($townname));
        return wp_insert_post(wp_slash($townname_data));
    }

    return $flag[0]->ID;
}

function generate_streetname($streetname, $id_streettype)
{
    $streetnam_data = array(
        's'             => $streetname,
        'post_type'     => 'streetname',
        'post_status'   => 'publish',
        'post_author'   => 1,
        'post_parent'   => $id_streettype
    );
    $flag = get_posts($streetnam_data);
    if (empty($flag)) {
        unset($streetnam_data['s']);
        $streetnam_data['post_title'] = $streetname;
        $streetnam_data['post_name'] = sanitize_title(generate_slug($streetname));
        return wp_insert_post(wp_slash($streetnam_data));
    }

    return $flag[0]->ID;
}

function generate_site_map()
{
    $map_titles = [];
//    $map_slug = [];
    $map_type = [];
    $main_post = get_post();
    while ($main_post->post_parent !== 0 || $main_post === null) {
      $map_titles[] = $main_post->post_title;
      $map_slug[] = $main_post->post_name;
      $map_type[] = $main_post->post_type;
      $main_post = get_post($main_post->post_parent);
    }
    if ($main_post !== null) {
        $map_titles[] = $main_post->post_title;
        $map_slug[] = $main_post->post_name;
        $map_type[] = $main_post->post_type;
    }
    $map_titles = array_reverse($map_titles);
    $map_slug = array_reverse($map_slug);
    $map_type = array_reverse($map_type);
    foreach ($map_titles as $key => $item) {
        echo "<a href=?" . $map_type[$key] . "=" . $map_slug[$key] .">" . $item . "</a>";
        if ($key !== count($map_titles) - 1) {
            echo "-";
        }
    }

}

function generate_post_content() {
    $main_post = get_post();
    $arg = array(
        'post_parent'   => $main_post->ID
    );
    $ref_posts = get_children($arg);
    if ($main_post->post_type === "townname") {
        echo "<table>";
        echo "<tr>";
        echo "<td>Название улицы</td>";
        echo "<td>Номера домов</td>";
        echo "<td>Почтовый инддекс</td>";
        echo "</tr>";
        if (isset($ref_posts) && !empty($ref_posts)) {
            foreach ($ref_posts as $ref_post) {
                $street_type = get_post_meta($ref_post->ID,'street_type');
                $street_type = $street_type[0];

                $buildings = get_post_meta($ref_post->ID,'buildings');
                $buildings = $buildings[0];
                $post_code = get_post_meta($ref_post->ID,'post_code');
                $post_code = $post_code[0];
                echo "<tr>";
                echo "<td>" . "<a href=?" . $ref_post->post_type . "=" . $ref_post->post_name .">" . $street_type . " " . $ref_post->post_title . "</a>" . "</td>";
                echo "<td>" . $buildings . "</td>";
                echo "<td>" . $post_code . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } elseif ($main_post->post_type === "district") {
        foreach ($ref_posts as $ref_post) {
            echo "<br>";
            $town_type = get_post_meta($ref_post->ID, 'towm_type');
            $town_type = $town_type[0];
            echo "<a href=?" . $ref_post->post_type . "=" . $ref_post->post_name .">" . $town_type . " " . $ref_post->post_title . "</a>";
        }
    } elseif ($main_post->post_type === "streetname") {
        $street_type = get_post_meta($main_post->ID,'street_type');
        $street_type = $street_type[0];
        $buildings = get_post_meta($main_post->ID,'buildings');
        $buildings = $buildings[0];
        $post_code = get_post_meta($main_post->ID,'post_code');
        $post_code = $post_code[0];
        $region_name = '';
        $district_name = '';
        $town_name = '';
        $street_name = '';

        while ($main_post->post_parent !== 0  || $main_post === null ) {

            if ($main_post->post_type === 'district') {
                $district_name = $main_post->post_title;
            } elseif ($main_post->post_type === 'townname') {
                $town_name = $main_post->post_title;
            } elseif ($main_post->post_type === 'streetname') {
                $street_name = $main_post->post_title;
            }
            $main_post = get_post($main_post->post_parent);
        }
        if ($main_post->post_type === 'region') {
            $region_name = $main_post->post_title;
        }

        echo "Почтовый индекс: " . $post_code;
        echo "<br>";
        echo "Населенный пункт: " . $town_name;
        echo "<br>";
        echo "Область: " . $region_name;
        echo "<br>";
        echo "Район: " . $district_name;
        echo "<br>";
        echo "Улица: " . $street_type . ' ' . $street_name;
        echo "<br>";
        echo "Дома: " . $buildings;
    }
    elseif (isset($ref_posts) && !empty($ref_posts)) {
        foreach ($ref_posts as $ref_post) {
            echo "<br>";
            echo "<a href=?" . $ref_post->post_type . "=" . $ref_post->post_name .">" . $ref_post->post_title . "</a>";
        }
    }
}

function generate_slug($string) {

    $rus=array('А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
    $lat=array('a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');

    $string = str_replace(array(...$rus, '-'), array(...$lat, ''), $string);
    return preg_replace('/[^A-Za-z0-9-]+/', '_', $string);
}