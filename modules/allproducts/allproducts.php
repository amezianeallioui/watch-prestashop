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
		// if(!Configuration::get('ALLPRODUCTS_CFG'))
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
			// || !Configuration::updateValue('ALLPRODUCTS_CFG', 'my config')
		)	
			return false; // si non, on renvoie false

		return true;
	}
	
	//methode de desinstallation
	public function uninstall()
	{
		//Script SQL de suppression
		// include(dirname(__FILE__).'/sql/uninstall.php');
		// foreach ($sql as $s)
		// 	if (!Db::getInstance()->execute($s))
		// 		return false;
				
		//On ne degreffe pas, mais on supprime les configs
		if (!parent::uninstall() ||
			!Configuration::deleteByName('ALLPRODUCTS_CFG')
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

	/**
	* Récupérer tous les produits
	*/

	public function getAllProducts()
	{

		$context = $this->context;
		$id_lang = $context->language->id;

		// $context = Context::getContext();

		$page_number = 0;
		$nb_products = 10;

		$sql = '
		SELECT
			p.id_product,  MAX(product_attribute_shop.id_product_attribute) id_product_attribute, pl.`link_rewrite`, pl.`name`, pl.`description_short`, product_shop.`id_category_default`,
			MAX(image_shop.`id_image`) id_image, il.`legend`,
			ps.`quantity` AS sales, p.`ean13`, p.`upc`, cl.`link_rewrite` AS category, p.show_price, p.available_for_order, IFNULL(stock.quantity, 0) as quantity, p.customizable,
			IFNULL(pa.minimal_quantity, p.minimal_quantity) as minimal_quantity, stock.out_of_stock,
			product_shop.`date_add` > "'.date('Y-m-d', strtotime('-'.(Configuration::get('PS_NB_DAYS_NEW_PRODUCT') ? (int)Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20).' DAY')).'" as new,
			product_shop.`on_sale`, MAX(product_attribute_shop.minimal_quantity) AS product_attribute_minimal_quantity
		FROM `'._DB_PREFIX_.'product_sale` ps
		LEFT JOIN `'._DB_PREFIX_.'product` p ON ps.`id_product` = p.`id_product`
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa
			ON (p.`id_product` = pa.`id_product`)
		'.Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1').'
		'.Product::sqlStock('p', 'product_attribute_shop', false, $context->shop).'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl
			ON p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('pl').'
		LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product`)'.
		Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1').'
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
		LEFT JOIN `'._DB_PREFIX_.'category_lang` cl
			ON cl.`id_category` = product_shop.`id_category_default`
			AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl');

		if (Group::isFeatureActive())
		{
			$groups = FrontController::getCurrentCustomerGroups();
			$sql .= '
				JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_product` = p.`id_product`)
				JOIN `'._DB_PREFIX_.'category_group` cg ON (cp.id_category = cg.id_category AND cg.`id_group` '.(count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1').')';
		}

		$sql.= '
		WHERE product_shop.`active` = 1
		AND p.`visibility` != \'none\'
		GROUP BY product_shop.id_product
		ORDER BY p.id_product ASC
		LIMIT '.(int)($page_number * $nb_products).', '.(int)$nb_products;

		if (!$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql))
			return false;

		return Product::getProductsProperties($id_lang, $result);
	}

	
	/*//methode pour ajouter une page de configuration
	public function getContent()
	{
		$output = null;

		//traitement du formulaire
		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_config = strval(Tools::getValue('ALLPRODUCTS_CFG'));
			if (!$my_config
				|| empty($my_config)
				|| !Validate::isGenericName($my_config))
			$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('ALLPRODUCTS_CFG', $my_config);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		//affichage du formulaire
		return $output.$this->displayForm();
	}*/
	
	//methode d'affichage du formulaire de configuration
	/*public function displayForm()
	{
		//On recupere la langue par defaut
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		//On cree un tableau avec les champs du formulaire
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Configuration value'),
					'name' => 'ALLPRODUCTS_CFG',
					'size' => 20,
					'required' => true
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

		//On recupere la valeur actuelle
		$helper->fields_value['ALLPRODUCTS_CFG'] = Configuration::get('ALLPRODUCTS_CFG');

		//on genere le formulaire
		return $helper->generateForm($fields_form);
	}*/
	
	/*//methode appelée si le module est greffé à la home (hors header et colonnes)
	public function hookDisplayCenterColumn($params)
	{
		//on envoie des variables à smarty
		$this->context->smarty->assign(array(
			'my_config' => Configuration::get('ALLPRODUCTS_CFG'),
			'my_link' => $this->context->link->getModuleLink('mywatch', 'display')
		)); 
		
		//on appel le template correspondant
		return $this->display(__FILE__, 'mywatch.tpl');
	}*/
	
/*	//ici, on veut le meme comportement dans la colonne de droite
	public function hookDisplayRightColumn($params)
	{
		return $this->hookDisplayLeftColumn($params);
	}*/
	
	

	//Navigation -> on affiche le lien 'Ma montre' avec le bon lien
	/*public function hookDisplayNav($params)
	{
		//on envoie des variables à smarty
		$this->context->smarty->assign(array(
			'my_link' => $this->context->link->getModuleLink('mywatch', 'display')
		)); 
		
		//on appel le template correspondant
		return $this->display(__FILE__, 'nav.tpl');
	}*/


}


