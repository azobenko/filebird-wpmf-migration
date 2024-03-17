<?php
// Place code into any part of the functions file
global $wpdb;
$folders = $wpdb->get_results(
  $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fbv" )
);

if (!empty($folders)) {
  $fb_to_wpmf = [];
  foreach ($folders as $f) {
    if ( $f->parent ){
      $parent_term_id = $fb_to_wpmf[intval($f->parent)] ?? 0;
    } else {
      $parent_term_id = 0;
    }
    if ( isset($_GET['start_migration']) ) {
      //Add folders as the terms
      $new = wp_insert_term( $f->name, 'wpmf-category', [ 'slug' => $f->slug, 'parent' => $parent_term_id ] );
      if ( is_array($new) ) {
        $fb_to_wpmf[intval($f->id)] = $new['term_id'];
        echo 'Added: '.$f->name.'</br>';
      } else {
        $fb_to_wpmf[intval($f->id)] = $new->error_data['term_exists'];
        echo 'Existed: '.$f->name.'</br>';
      }
    }
  }
  //Map media objects to new folders
  if ( !empty($fb_to_wpmf) && isset($_GET['start_migration'])) {
    $count = 0;
    $fb_map = $wpdb->get_results(
      $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fbv_attachment_folder" )
    );
    if (!empty($fb_map)) {
      foreach ($fb_map as $obj) {
        wp_set_object_terms( intval($obj->attachment_id), $fb_to_wpmf[intval($obj->folder_id)], 'wpmf-category' );
        $count++;
      }
    }
    echo '<br><b>Mapped media count: ' . $count . '</b>';
  }
}
