<?php
class MonmoduleDisplayModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();
		$this->setTemplate('display.tpl');
	}
}

