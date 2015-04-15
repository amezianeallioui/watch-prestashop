<?php

include_once "../config/autoload.php";

class mywatchdisplayModuleFrontController extends ModuleFrontController
{

	/*public function getAttributesGroups($id_lang,$id_product)
	{		
		return Db::getInstance()->ExecuteS('
			SELECT 	ag.`id_attribute_group`, 
					agl.`name` AS group_name, 
					agl.`public_name` AS public_group_name, 
					a.`id_attribute`, 
					al.`name` AS attribute_name,
					a.`color` AS attribute_color, 
					pa.`id_product_attribute`, 
					pa.`quantity`, 
					pa.`price`, 
					pa.`ecotax`, 
					pa.`weight`, 
					pa.`default_on`, 
					pa.`reference`
			FROM `'._DB_PREFIX_.'product_attribute` pa
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
			WHERE pa.`id_product` = '.intval($id_product).'
			AND al.`id_lang` = '.intval($id_lang).'
			AND agl.`id_lang` = '.intval($id_lang).'
			AND pa.`quantity` <> 0
			ORDER BY attribute_color');
	}*/

	public function initContent()
	{
		parent::initContent();

		// global $smarty;

		// $category = new Category(1, Configuration::get('PS_LANG_DEFAULT'));
		// echo $category;
		// $nb = (int)(Configuration::get('HOME_FEATURED_NBR'));
		// $current_id_lang = intval($params['cookie']->id_lang); // AJOUT
		// $products = $category->getProducts((int)($params['cookie']->id_lang), 1, ($nb ? $nb : 10));

		// $categ_desc = $category->description;
		// $categ_name = $category->name;

		//Boucle sur les produits pour récupérer leur déclinaisons		
		// for($i=0;$i<count($products);$i++) {
		// 	$attributesGroups = $this->getAttributesGroups($current_id_lang,$products[$i]['id_product']);
		// 	// Insere le tableau des combinaison dans le tableau des produits, en associant chaque produit a son tableau de combinaisons
		// 	$products[$i]["combinaisons"] = $attributesGroups; 
		// }

		// $image_array=array();
		// for($i=0;$i<count($products);$i++)
	 //   {
		// 	$image_array[$i]= $category->getProductsimg($products[$i]['id_product']);
	 //   }
/*
		$this->context->smarty->assign(array(
			'category' => $category,
			'nom_categ' => $categ_name[2], // Recupere le nom de la categorie
			'desc_categ' => $categ_desc[2], // Recupere la description de la categorie
			'products' => $products,
			'currency' => new Currency(intval($params['cart']->id_currency)),
			'lang' => Language::getIsoById(intval($params['cookie']->id_lang)),
			'productNumber' => sizeof($products),
			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
			'homeSize' => Image::getSize('home'),
			// 'productimg' => (isset($image_array) AND $image_array) ? $image_array : NULL ,
			'attributs' = AttributeGroup::getAttributesGroups($this->context->language->id)
		));*/
		
		//on appel le template correspondant
		return $this->setTemplate('display.tpl');
	}
}

