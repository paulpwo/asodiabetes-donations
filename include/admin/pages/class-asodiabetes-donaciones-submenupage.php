<?php

// Block direct access to file
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

/**
  * Asodiabetes_Donaciones_Submenu_Page
  *
  * @since 0.1.0
  *
  * @author Paul Osinga
  * @license GPL-2.0
  *
  */
class Asodiabetes_Donaciones_Submenu_Page {

  function __construct() {

    add_submenu_page(
      "",
      __("Asodiabetes donaciones", "asodiabetes-donations"),
      __("Asodiabetes donaciones", "asodiabetes-donations"),
      "administrator",
      "asodiabetes-donaciones",
      array($this, "print_page")
    );

  }

  public function print_page() {
    ob_start(); ?>

    <div class="wrap">

      <div class="card">
        <h1><?php _e( 'Asodiabetes donaciones', 'asodiabetes-donations' ); ?></h1>
        <p><?php _e( 'This is the Asodiabetes donaciones sub menu page start here to customize your template.', 'asodiabetes-donations' ); ?></p>
      </div>

    </div><?php

    return print(ob_get_clean());
  }

}
