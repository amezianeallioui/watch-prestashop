<?php

include_once "../config/autoload.php";

class mywatchdisplayModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();
		// $this->setTemplate('display.tpl');

		// $id_lang = 1;

		// $product = new Product ($product['id_product'], $id_lang);
		// $attributes = $product->getAttributeCombinations($id_lang);

		//on récupère les différentes catégories, caractéristiques des produits

		// $query_attributes = "SELECT psm_al.name FROM " . _DB_PREFIX_ . "attribute_lang psm_al LEFT JOIN " . _DB_PREFIX_ . "attribute_group_lang psm_agl ON (psm_al.id_lang = psm_agl.id_lang) LEFT JOIN " . _DB_PREFIX_ . "attribute psm_a ON (psm_a.id_attribute_group = psm_agl.id_lang) WHERE psm_al.id_lang = " . (int) Configuration::get('PSm_LANG_DEFAULT'); $attributes = Db::getInstance()->ExecuteS($query_attributes); $existent_attributes = array(); foreach($attributes as $attr) { $existent_attributes[] = $attr['name']; }

		$this->context->smarty->assign(array(
			'my_config' => Configuration::get('MYWATCH_CFG'),
			'categories' => Category::getCategories( $this->context->language->id, true, false ),
			// 'product' => $myproduct = new Product(Tools::getValue(50)),
			// 'product' => new Product (30, $this->context->language->id),
			// 'attr' => Attribute::getAttributes($id_lang, $not_null = false)
			'attributes' => Attribute::getAttributes($id_lang, $not_null = false)
		)); 


		
		//on appel le template correspondant
		return $this->setTemplate('display.tpl');
	}
}

