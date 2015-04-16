<?php

//permet de tester le contexte et empecher de charger le module en dehors de Prestashop
if (!defined('_PS_VERSION_'))
	exit;
	
class AllProducts extends Module
{
	//méthode appelée à l'instanciation de l'objet
	public function __construct()
	{
		// Nom du répertoire
		$this->name = 'allproducts';
		// Titre de la section qui contiendra le module dans le back-office
		$this->tab = 'front_office_features';
		$this->version = '1.6.0';
		$this->author = 'Ameziane Allioui';
		
		//charge le module à l'ouverture du back-office, utile pour les avertissements
		$this->need_instance = 0;
		//compatibilité avec les versions de Prestashop
		$this->ps_versions_compliancy = array('min' => '1.6');
		//active bootstrap dans les templates du module ainsi que dans la configuration en back-office
		$this->bootstrap = true;

		//appel au constructeur parent
		parent::__construct();

		//infos du module en back-office
		$this->displayName = $this->l('Bloc tous les produits');
		$this->description = $this->l('Ajoute un bloc qui affiche tous vos produits en page d\'accueil');

		//message d'avertissement en cas de désinstallation
		$this->confirmUninstall = $this->l('Attention, vous allez supprimer le module !');

		//On teste l'existence d'une variable de configuration propre au module
		if(!Configuration::get('ALLPRODUCTS_NUMBER') || !Configuration::get('ALLPRODUCTS_ONLY_ACTIVE'))
		$this->warning = $this->l('La configuration n\'est pas valide.');
	}
	
	//methode appelée à l'installation du module
	public function install()
	{

		//On test le multiboutique
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		//On greffe le module à quelques hook, on crée une config et on lance l'installation classique
		// Regarde si le module a bien été installé
		if(!parent::install() 
			|| !$this->registerHook('header') 
			|| !$this->registerHook('displayHomeTab')
			|| !$this->registerHook('displayHomeTabContent')
			|| !Configuration::updateValue('ALLPRODUCTS_NUMBER', 1000)
			|| !Configuration::updateValue('ALLPRODUCTS_ONLY_ACTIVE', true)
		)	
			return false; // si non, on renvoie false

		return true;
	}
	
	//methode de desinstallation
	public function uninstall()
	{
				
		//On ne degreffe pas, mais on supprime les configs
		if (!parent::uninstall() ||
			!Configuration::deleteByName('ALLPRODUCTS_NUMBER') ||
			!Configuration::deleteByName('ALLPRODUCTS_ONLY_ACTIVE')
		)
			return false;

		return true;
	}

	// Dans le header, on injecte le css
	public function hookHeader()
	{
		$this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
	}

	// Menu 
	public function hookDisplayHomeTab($params){
		return $this->display(__FILE__, 'tab.tpl');
	}

	public function hookDisplayHomeTabContent($params)
	{
		$this->smarty->assign(array(
			'products' => $this->getAllProducts(),
			'homeSize' => Image::getSize(ImageType::getFormatedName('home'))
		));

		return $this->display(__FILE__, 'allproducts-home.tpl');
	}

	//methode pour ajouter une page de configuration
	public function getContent()
	{
		$output = null;

		//traitement du formulaire
		if (Tools::isSubmit('submit'.$this->name))
		{
			$number_of_products = strval(Tools::getValue('ALLPRODUCTS_NUMBER'));
			$only_active = strval(Tools::getValue('ALLPRODUCTS_ONLY_ACTIVE'));
			echo 'test : '.$only_active;

			if(
				(!$number_of_products && !$only_active)
				// || (empty($number_of_products) || empty($only_active))
				// || (!Validate::isGenericName($number_of_products) || !Validate::isBool($only_active))
			)
			$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('ALLPRODUCTS_NUMBER', $number_of_products);
				Configuration::updateValue('ALLPRODUCTS_ONLY_ACTIVE', $only_active);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		//affichage du formulaire
		return $output.$this->displayForm();
	}
	
	//methode d'affichage du formulaire de configuration
	public function displayForm()
	{
		//On recupere la langue par defaut
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$options = array(
		  array(
		    'id_option' => 1,              // The value of the 'value' attribute of the <option> tag.
		    'name' => $this->l('Enabled')              // The value of the text content of the  <option> tag.
		  ),
		  array(
		    'id_option' => 0,
		    'name' => $this->l('Disabled')
		  )
		);

		//On cree un tableau avec les champs du formulaire
		$fields_form[0]['form'] = array(
			'legend' => array(
				// 'title' => $this->l('Configuration du module'),
				'title' => 'Configuration du module',
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => 'Nombre de produits à afficher',
					'name' => 'ALLPRODUCTS_NUMBER',
					'size' => 5,
					'required' => true,
				),
				array(
				  'type' => 'select',                              // This is a <select> tag.
				  'label' => 'Afficher uniquement les produits activés',         // The <label> for this <select> tag.
				  'name' => 'ALLPRODUCTS_ONLY_ACTIVE',                     // The content of the 'id' attribute of the <select> tag.
				  'required' => true,                              // If set to true, this option must be set.
				  'options' => array(
				    'query' => $options,                           // $options contains the data itself.
				    'id' => 'id_option',                           // The value of the 'id' key must be the same as the key for 'value' attribute of the <option> tag in each $options sub-array.
				    'name' => 'name'                               // The value of the 'name' key must be the same as the key for the text content of the <option> tag in each $options sub-array.
				  )
				)
			),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
			)
		);

		//on traite ensuite ce tableau avec les helper
		$helper = new HelperForm();

		//Module, token et currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		//Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		//Title et toolbar
		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' => array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		//On recupere les valeurs actuelles pour la configuration (nombre de produits à afficher + affichage des produits uniquement activés)
		$helper->fields_value['ALLPRODUCTS_NUMBER'] = Configuration::get('ALLPRODUCTS_NUMBER');
		$helper->fields_value['ALLPRODUCTS_ONLY_ACTIVE'] = Configuration::get('ALLPRODUCTS_ONLY_ACTIVE');

		//on genere le formulaire
		return $helper->generateForm($fields_form);
	}
	


	/**
	* Récupérer tous les produits
	*/

	public function getAllProducts()
	{
		// Paramètres pour la fonction permettant de récupérer tous les produits
		$context = Context::getContext();
		$id_lang = $context->language->id; // id de la langue active
		$start = 0;
		// On regarde si un nombre de produits à afficher a été indiqué
		Configuration::get('ALLPRODUCTS_NUMBER') ? $limit = Configuration::get('ALLPRODUCTS_NUMBER') : $limit = 1000;
		$order_by = 'id_product';
		$order_way = 'ASC';
		$id_category = false;
		// Afficher uniquement les produits activés ou non

		$only_active = Configuration::get('ALLPRODUCTS_ONLY_ACTIVE');

		// On récupère tous les produits
		$products = Product::getProducts($id_lang, $start, $limit, $order_by, $order_way, $id_category,
		$only_active, null);

		// On récupère les propriété pour chaque produit (image, activé ou non)
		$products = Product::getProductsProperties($id_lang, $products);

		return $products;
	}

}


