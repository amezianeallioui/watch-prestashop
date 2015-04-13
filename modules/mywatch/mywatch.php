<?php

//permet de tester le contexte et empecher de charger le module en dehors de Prestashop
if (!defined('_PS_VERSION_'))
	exit;
	
//Nom du module en CamelCase 
//On étend la classe Module
class MyWatch extends Module
{
	//méthode appelée à l'instanciation de l'objet
	public function __construct()
	{
		//meme nom que le repertoire
		$this->name = 'mywatch';
		//Titre de la section qui contiendra le module dans le back-office
		$this->tab = 'front_office_features';
		$this->version = '1.0';
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
		$this->displayName = $this->l('My watch');
		$this->description = $this->l('Faites votre montre personnalisée !');

		//message d'avertissement en cas de désinstallation
		$this->confirmUninstall = $this->l('Attention, vous allez supprimer le module!');

		//On test l'existence d'une variable de configuration propre au module
		if(!Configuration::get('MYWATCH_CFG'))
		$this->warning = $this->l('La configuration 1 n\'est pas valide.');
	}
	
	//methode appelée à l'installation du module
	public function install()
	{
		//On test le multiboutique
		if(Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);
			
		//On peut executer un script SQL si besoin
		include(dirname(__FILE__).'/sql/install.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;

		//On greffe le module à quelques hook, on crée une config et on lance l'installation classique
		// Regarde si le module a bien été installé
		if(!parent::install() ||
			!$this->registerHook('leftColumn') ||
			!$this->registerHook('header') ||
			!Configuration::updateValue('MYWATCH_CFG', 'my config')
		)	
			return false; // si non, on renvoie false

		return true;
	}
	
	//methode de desinstallation
	public function uninstall()
	{
		//Script SQL de suppression
		include(dirname(__FILE__).'/sql/uninstall.php');
		foreach ($sql as $s)
			if (!Db::getInstance()->execute($s))
				return false;
				
		//On ne degreffe pas, mais on supprime les configs
		if (!parent::uninstall() ||
			!Configuration::deleteByName('MYWATCH_CFG')
		)
			return false;

		return true;
	}
	
	//methode pour ajouter une page de configuration
	public function getContent()
	{
		$output = null;

		//traitement du formulaire
		if (Tools::isSubmit('submit'.$this->name))
		{
			$my_config = strval(Tools::getValue('MYWATCH_CFG'));
			if (!$my_config
				|| empty($my_config)
				|| !Validate::isGenericName($my_config))
			$output .= $this->displayError($this->l('Invalid Configuration value'));
			else
			{
				Configuration::updateValue('MYWATCH_CFG', $my_config);
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
				'title' => $this->l('Settings'),
			),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('Configuration value'),
					'name' => 'MYWATCH_CFG',
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
		$helper->fields_value['MYWATCH_CFG'] = Configuration::get('MYWATCH_CFG');

		//on genere le formulaire
		return $helper->generateForm($fields_form);
	}
	
	//methode appelée si le module est greffé à la colonne de gauche
	public function hookDisplayLeftColumn($params)
	{
		//on envoie des variables à smarty
		$this->context->smarty->assign(array(
			'my_config' => Configuration::get('MYWATCH_CFG'),
			'my_link' => $this->context->link->getModuleLink('mywatch', 'display')
		)); 
		
		//on appel le template correspondant
		return $this->display(__FILE__, 'display.tpl');
	}
	
	//ici, on veut le meme comportement dans la colonne de droite
	public function hookDisplayRightColumn($params)
	{
		return $this->hookDisplayLeftColumn($params);
	}
	
	//Dans le header, on injecte les css, les js, etc.
	public function hookDisplayHeader()
	{
		$this->context->controller->addCSS($this->_path.'css/mywatch.css', 'all');
	}
}


