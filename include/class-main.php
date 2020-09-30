<?php

// Block direct access to file
defined( 'ABSPATH' ) or die( 'Not Authorized!' );

class Asodiabetes_Donations {

	private $settings;

	private $metaboxes = array();

	private $widgets = array();

	private $shortcodes = array();

	private $toolbars = array();

	private $taxonomies = array();

	private $wc_payu_latam = null;

	public function __construct() {

		// Plugin uninstall hook
		register_uninstall_hook( ASODIABETES_DONATIONS_FILE, array(__CLASS__, 'plugin_uninstall') );

		// Plugin activation/deactivation hooks
		register_activation_hook( ASODIABETES_DONATIONS_FILE, array($this, 'plugin_activate') );
		register_deactivation_hook( ASODIABETES_DONATIONS_FILE, array($this, 'plugin_deactivate') );

		// Plugin Actions
		add_action( 'plugins_loaded', array($this, 'plugin_init') );

		// User
		add_action( 'wp_enqueue_scripts', array($this, 'plugin_enqueue_scripts') );

		// Admin
		add_filter( 'mce_css', array($this, 'plugin_add_editor_style') );
		add_action( 'admin_enqueue_scripts', array($this, 'plugin_enqueue_admin_scripts') );
		add_action( 'admin_init', array($this, 'plugin_register_settings') );
		add_action( 'admin_menu', array($this, 'plugin_add_settings_pages') );

		// Register plugin widgets
		add_action( 'widgets_init', function(){
			foreach ($this->widgets as $widgetName => $widgetPath) {
				include_once( ASODIABETES_DONATIONS_INCLUDE_DIR . $widgetPath );
				register_widget( $widgetName );
			}
		});

		// Init plugin shortcodes
		foreach ($this->shortcodes as $className => $path) {
			include_once( ASODIABETES_DONATIONS_INCLUDE_DIR . $path );
			new $className();
		}

		// Init plugin metaboxes
		foreach ($this->metaboxes as $className => $path) {
			include_once( ASODIABETES_DONATIONS_INCLUDE_DIR . $path );
			new $className();
		}

		// Init plugin taxonomies
		foreach ($this->taxonomies as $className => $path) {
			include_once( ASODIABETES_DONATIONS_INCLUDE_DIR . $path );
			new $className();
		}

		// Init plugin toolbars
		foreach ($this->toolbars as $className => $path) {
			include_once( ASODIABETES_DONATIONS_INCLUDE_DIR . $path );
			new $className();
		}

	}

	/**
	* Plugin uninstall function
	* called when the plugin is uninstalled
	* @method plugin_uninstall
	*/
	public static function plugin_uninstall() { }

	/**
	* Plugin activation function
	* called when the plugin is activated
	* @method plugin_activate
	*/
	public function plugin_activate() {

		if( !class_exists( 'WooCommerce' )) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'Instale y active woocommerce para poder usar este complemento.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
		if(!class_exists('WC_Payu_Latam')){
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( __( 'Instale y active PayU woocommerce para poder usar este complemento.', 'woocommerce-addon-slug' ), 'Plugin dependency check', array( 'back_link' => true ) );
		}
	}

	/**
	* Plugin deactivate function
	* is called during plugin deactivation
	* @method plugin_deactivate
	*/
	public function plugin_deactivate() { }

	/**
	* Plugin init function
	* init the polugin textDomain
	* @method plugin_init
	*/
	function plugin_init() {
		if(!isset($this->wc_payu_latam)){
			$this->wc_payu_latam = new WC_Payu_Latam();
		}
		load_plugin_textDomain( 'asodiabetes-donations', false, dirname(ASODIABETES_DONATIONS_DIR_BASENAME) . '/languages' );

		add_action('wp_footer', array($this, 'loadBotton')); //cargar en el foofter
	}

	function loadBotton(){	
		$this->settings = get_option( 'asodiabetes-donations_main_options' );

		if($this->settings){
			?>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/blueimp-md5/2.5.0/js/md5.min.js"></script>
			 <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"
        								integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
				<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

				<img src="<?= $this->settings['image_option'] ?>" alt="donaciones" id="asodiabetes_boton_docaciones" onclick='generateDonation()' style="display:none;">
				<script>
				jQuery(function ($) {
					setTimeout(function(){ 
						$('#asodiabetes_boton_docaciones').fadeIn(50);
						$("#asodiabetes_boton_docaciones").animate({ zoom: '170%' }, 1000);
						$("#asodiabetes_boton_docaciones").animate({ zoom: '40%' }, 700);
						$("#asodiabetes_boton_docaciones").animate({ zoom: '100%' }, 300);

					}, 2000);
				 });	
					const options_donations = "<?php echo $this->settings['first_option']; ?>".split(' ');
					const merchantId = "<?= $this->wc_payu_latam->merchant_id ?>";
					const accountId = "<?= $this->wc_payu_latam->account_id ?>";
					const responseUrl = location.origin + '/gracias';
					const confirmationUrl = location.origin + '/gracias';
					const currency ="COP";
					const description = "Donación para asodiabetes";
					let buyerEmail = "";
					const url_pay = "<?= $this->wc_payu_latam->gateway_url ?>";
					let amountDonation = 0;
					const ApiKey = "<?= $this->wc_payu_latam->api_key ?>";

					function generateDonation(){
						Swal.fire({
							title: '<?= $this->settings["title_option"] ?>',
							//icon: 'info',
							html: '<?= $this->settings["message_option"] ?>',
							showCloseButton: true,
							showCancelButton: false,
							focusConfirm: false,
							confirmButtonText:'<i class="fa fa-thumbs-up"></i> ¡QUIERO DONAR!',
							confirmButtonAriaLabel: 'Thumbs up, great!',
							cancelButtonText:
								'No, por el momento',
							cancelButtonAriaLabel: 'No, por ahora.',
							confirmButtonColor: '#3085d6',
							cancelButtonColor: '#d33',
						}).then((result) => {
							if (result.isConfirmed) {

									/* INIT */

										Swal.fire({
											title: '<?= $this->settings["title_option"] ?>',
											html : '<?= $this->settings["message_option"] ?>',
											showCloseButton: true,
											reverseButtons : true,
											showCancelButton: false,
											cancelButtonText : "CANCELAR",
											confirmButtonText: "SIGUIENTE",
											validationMessage : "Seleccione un monto de donación",
											inputClass : "form-control",
											input: "select",
											inputOptions: options_donations,
											inputPlaceholder: "Monto de su donación",
											confirmButtonColor: '#3085d6',
											cancelButtonColor: '#d33',
											allowOutsideClick: () => !Swal.isLoading(),
											preConfirm: (test) => {
												if(test == "") {
													Swal.showValidationMessage("Seleccione un monto");
												}
											}
										}).then((result2) => {
											if (result2.value) {
												
												amountDonation = options_donations[parseInt(result2.value)];
												Swal.fire({
													title: '<?= $this->settings["title_option"] ?>',
													html : '<h4>Ingrese su email</h4>',
													input: 'text',
													inputAttributes: {
															autocapitalize: 'off'
													},
													showCloseButton: true,
													showCancelButton: false,
													confirmButtonText:'<i class="fa fa-thumbs-up"></i> ¡QUIERO DONAR!',
													showLoaderOnConfirm: true,
													confirmButtonColor: '#3085d6',
													cancelButtonColor: '#d33',
													preConfirm: (email) => {
														if(email == "") {
															Swal.showValidationMessage("Indique su email por favor");
														}
														if(!validateEmail(email)){
															Swal.showValidationMessage("Indique su email correctamente");
														}
														
													},
													allowOutsideClick: () => !Swal.isLoading()
													}).then((result3) => {
														if (result3.value) {

															buyerEmail = result3.value;
															

															/* FORM */
															var form = document.createElement("form"); 
															form.method = "POST";
    														form.action = url_pay;
															form.style.display = 'none';


															/*
					const options_donations = "<?php echo $this->settings['first_option']; ?>".split(' ');
					const merchantId = "<?= $this->wc_payu_latam->merchant_id ?>";
					const accountId = "<?= $this->wc_payu_latam->accout_id ?>";
					const responseUrl = location.origin + '/gracias';
					const confirmationUrl = location.origin + '/gracias';
					const currency ="COP";
					const description = "Donación para asodiabetes";
					let buyerEmail = "";
					const url_pay = "<?= $this->wc_payu_latam->gateway_url ?>";
					let amountDonation = 0;
															*/
															var element1 = document.createElement("input"); 
															element1.name="merchantId";
															element1.value=merchantId;
															form.appendChild(element1); 

															var element2 = document.createElement("input"); 
															element2.name="accountId";
															element2.value=accountId;
															form.appendChild(element2); 

															var element3 = document.createElement("input"); 
															element3.name="responseUrl";
															element3.value=responseUrl;
															form.appendChild(element3); 

															var element4 = document.createElement("input"); 
															element4.name="confirmationUrl";
															element4.value=confirmationUrl;
															form.appendChild(element4);

															var element5 = document.createElement("input"); 
															element5.name="currency";
															element5.value=currency;
															form.appendChild(element5);

															var element6 = document.createElement("input"); 
															element6.name="description";
															element6.value=description;
															form.appendChild(element6);

															var element7 = document.createElement("input"); 
															element7.name="buyerEmail";
															element7.value=buyerEmail;
															form.appendChild(element7);

															var element8 = document.createElement("input"); 
															element8.name="amount";
															element8.value=amountDonation;
															form.appendChild(element8);

															var d = new Date();
															var referenceCode = 'dona-' + d.getTime();

															var element9 = document.createElement("input"); 
															element9.name="referenceCode";
															element9.value=referenceCode;
															form.appendChild(element9);

															var signature = md5(ApiKey +"~"+ merchantId +"~"+ referenceCode +"~"+ amountDonation +"~"+ currency);
															
															var element10 = document.createElement("input"); 
															element10.name="signature";
															element10.value=signature;
															form.appendChild(element10);

															var element11 = document.createElement("input"); 
															element11.name="tax";
															element11.value=0;
															form.appendChild(element11);


															document.body.appendChild(form);

															form.submit();

														
														}
												});

											}
										});





									/* END */






								/*var newform2 = $('#frm_ePaycoCheckoutOpen').clone(); //Clone form 1
								newform2.filter('form').prop('id', 'form2'); //Update formID
								$('#frm_ePaycoCheckoutOpen').remove();
								$('body').html(newform2);
								$('#form2').submit();*/
							}
						})
						
					}

					function validateEmail(email) {
						const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
						return re.test(String(email).toLowerCase());
					}
				</script>


			<?php
			//echo $this->settings['first_option'];
		}

	}



	/**
	* Add the plugin menu page(s)
	* @method plugin_add_settings_pages
	*/
	function plugin_add_settings_pages() {

		add_menu_page(
			__('Asodiabetes Donations', 'asodiabetes-donations'),
			__('Asodiabetes Donations', 'asodiabetes-donations'),
			'administrator', // Menu page capabilities
			'asodiabetes-donations-settings', // Page ID
			array($this, 'plugin_settings_page'), // Callback
			'dashicons-admin-generic',
			null
		);

	}

	/**
	* Register the main Plugin Settings
	* @method plugin_register_settings
	*/
	function plugin_register_settings() {

		register_setting( 'asodiabetes-donations-settings-group', 'asodiabetes-donations_main_options', array($this, 'plugin_sanitize_settings') );

		add_settings_section( 'main', __('Main Settings', 'asodiabetes-donations'), array( $this, 'main_section_callback' ), 'asodiabetes-donations-settings' );

		add_settings_field( 'first_option', 'Lista de montos posibles para donacion. Ingrese el monto + un espacio para agregar otro', array( $this, 'first_option_callback' ), 'asodiabetes-donations-settings', 'main' );
		
		
		add_settings_field( 'image_option', 'Imagen del boton', array( $this, 'image_option_callback' ), 'asodiabetes-donations-settings', 'main' );

		add_settings_field( 'title_option', 'Titulo del popUp', array( $this, 'title_option_callback' ), 'asodiabetes-donations-settings', 'main' );

		add_settings_field( 'message_option', 'Cuerpo del mensaje', array( $this, 'message_option_callback' ), 'asodiabetes-donations-settings', 'main' );

	}

	/**
	* The text to display as description for the main section
	* @method main_section_callback
	*/
	function main_section_callback() {
		return _e( 'Plugin settings.', 'asodiabetes-donations' );
	}

	/**
	* Create the option html input
	* @return html
	*/
	function first_option_callback() {
		return printf(
			'<textarea id="asodiabetes_options_donations" name="asodiabetes-donations_main_options[first_option]" cols="80" rows="1" >%s</textarea>',
			isset( $this->settings['first_option'] ) ? esc_attr( $this->settings['first_option']) : ''
		);
	}

	/**
	* Create the option html input
	* @return html
	*/
	function image_option_callback() {
		return printf(
			'<input type="text" id="image_options" name="asodiabetes-donations_main_options[image_option]"  value="%s" style="width: 510px"/>',
			isset( $this->settings['image_option'] ) ? esc_attr( $this->settings['image_option']) : ''
		);
	}

	/**
	* Create the option html input
	* @return html
	*/
	function title_option_callback() {
		return printf(
			'<input type="text" id="title_option" name="asodiabetes-donations_main_options[title_option]"  value="%s" style="width: 510px"/>',
			isset( $this->settings['title_option'] ) ? esc_attr( $this->settings['title_option']) : ''
		);
	}

	/**
	* Create the option html input
	* @return html
	*/
	function message_option_callback() {
		return printf(
			'<input type="text" id="message_option" name="asodiabetes-donations_main_options[message_option]"  value="%s" style="width: 510px"/>',
			isset( $this->settings['message_option'] ) ? esc_attr( $this->settings['message_option']) : ''
		);
	}

	/**
	* Sanitize the settings values before saving it
	* @param  mixed $input The settings value
	* @return mixed        The sanitized value
	*/
	function plugin_sanitize_settings($input) {
		return $input;
	}

	/**
	* Enqueue the main Plugin admin scripts and styles
	* @method plugin_enqueue_scripts
	*/
	function plugin_enqueue_admin_scripts() {

		wp_register_style(
			'asodiabetes-donations_admin_style',
			ASODIABETES_DONATIONS_DIR_URL . '/assets/dist/admin.css',
			array(),
			null
		);

		wp_register_script(
			'asodiabetes-donations_admin_script',
			ASODIABETES_DONATIONS_DIR_URL . "/assets/dist/admin.js",
			array('jquery'),
			null,
			true
		);

		wp_enqueue_style('asodiabetes-donations_admin_style');
		wp_enqueue_script('asodiabetes-donations_admin_script');

	}

	/**
	* Enqueue the main Plugin user scripts and styles
	* @method plugin_enqueue_scripts
	*/
	function plugin_enqueue_scripts() {

		wp_register_style(
			"asodiabetes-donations_user_style",
			ASODIABETES_DONATIONS_DIR_URL . "/assets/dist/user.css",
			array(),
			null
		);

		wp_register_script(
			"asodiabetes-donations_user_script",
			ASODIABETES_DONATIONS_DIR_URL . "/assets/dist/user.js",
			array('jquery'),
			null,
			true
		);

		wp_enqueue_style('asodiabetes-donations_user_style');
		wp_enqueue_script('asodiabetes-donations_user_script');

	}

	/**
	* Add the plugin style to tinymce editor
	* @method plugin_add_editor_style
	*/
	function plugin_add_editor_style($styles) {
		if ( !empty( $styles ) ) {
			$styles .= ',';
		}
		$styles .= ASODIABETES_DONATIONS_DIR_URL . '/assets/dist/editor-style.css';
		return $styles;
	}

	/**
	* Plugin main settings page
	* @method plugin_settings_page
	*/
	function plugin_settings_page() {

		ob_start(); ?>

		<div class="wrap" style="max-width: 860px;">

			<div class="card" style="max-width: 860px;">

				<h1><?php _e( 'Asodiabetes Donations', 'asodiabetes-donations' ); ?></h1>

				<p><?php _e( 'Opciones de donación. Debe colocar cada monto y un espacio', 'asodiabetes-donations' ); ?></p>

			</div>

			<div class="card" style="max-width: 860px;">

				<?php 
				$this->settings = get_option( 'asodiabetes-donations_main_options' ); 
				?>

				<form method="post" action="options.php">

					<?php settings_fields( 'asodiabetes-donations-settings-group' ); ?>
					<?php do_settings_sections( 'asodiabetes-donations-settings' ); ?>

					<?php submit_button(); ?>

				</form>

			</div>

		</div><?php

		return print( ob_get_clean() );

	}

}

new Asodiabetes_Donations;
