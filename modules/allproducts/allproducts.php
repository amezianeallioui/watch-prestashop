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

		//On test l'existence d'une variable de configuration propre au module
		// if(!Configuration::get('ALLPRODUCTS_COLOR'))
		// $this->warning = $this->l('La configuration 1 n\'est pas valide.');
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
			// || !$this->registerHook('home') 
			|| !$this->registerHook('header') 
			|| !$this->registerHook('displayHomeTab')
			|| !$this->registerHook('displayHomeTabContent')
			|| !Configuration::updateValue('ALLPRODUCTS_COLOR', 1000)
		)	
			return false; // si non, on renvoie false

		return true;
	}
	
	//methode de desinstallation
	public function uninstall()
	{
				
		//On ne degreffe pas, mais on supprime les configs
		if (!parent::uninstall() ||
			!Configuration::deleteByName('ALLPRODUCTS_COLOR')
		)
			return false;

		return true;
	}

	// Dans le header, on injecte le css
	public function hookHeader()
	{
		$this->context->controller->addCSS(_THEME_CSS_DIR_.'product_list.css');
		$this->context->controller->addCSS($this->_path.'allproducts.css', 'all');
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
			$my_config = strval(Tools::getValue('ALLPRODUCTS_COLOR'));
			if (!$my_config
				|| empty($my_config)
				|| !Validate::isGenericName($my_config))
			$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('ALLPRODUCTS_COLOR', $my_config);
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

		//On cree un tableau avec les champs du formulaire
		$fields_form[0]['form'] = array(
			'legend' => array(
				// 'title' => $this->l('Configuration du module'),
				'title' => 'Configuration du module',
			),
			'input' => array(
				array(
					'type' => 'text',
					// 'label' => $this->l('Configuration value'),
					'label' => 'Nombre de produits à afficher',
					'name' => 'ALLPRODUCTS_COLOR',
					'size' => 5,
					'required' => true
				)
			),
			'submit' => array(
				'title' => $this->l('Enregistrer'),
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

		//On recupere la valeur actuelle
		$helper->fields_value['ALLPRODUCTS_COLOR'] = Configuration::get('ALLPRODUCTS_COLOR');

		//on genere le formulaire
		return $helper->generateForm($fields_form);
	}
	


	/**
	* Récupérer tous les produits
	*/

	public function getAllProducts()
	{

		$context = Context::getContext();

		$id_lang = $context->language->id;

		$start = 0;

		Configuration::get('ALLPRODUCTS_COLOR') ? $limit = Configuration::get('ALLPRODUCTS_COLOR') : $limit = 1000;

		$order_by = 'id_product';
		$order_way = 'ASC';
		$id_category = false;
		$only_active = false;

		$result = Product::getProducts($id_lang, $start, $limit, $order_by, $order_way, $id_category,
		$only_active, null);

		return Product::getProductsProperties($id_lang, $result);


	}

}


