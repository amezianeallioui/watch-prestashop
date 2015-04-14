{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if isset($HOOK_HOME_TAB_CONTENT) && $HOOK_HOME_TAB_CONTENT|trim}

	<div class="row">
		<div class="col-sm-1"></div>
		<div class="col-sm-10 txtRecommend">
			<h2>Nous vous recommandons</h2>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam augue sapien, malesuada vel hendrerit eu, finibus eu magna. Nunc ulamcorper et diam viverra interdum.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam augue sapien, malesuada vel hendrerit eu, finibus eu magna. Nunc ulamcorper et diam viverra interdum.</p>
		</div>
		<div class="col-sm-1"></div>
	</div>

	{if isset($HOOK_HOME) && $HOOK_HOME|trim}
		<div class="clearfix">{$HOOK_HOME}</div>
	{/if}
    {if isset($HOOK_HOME_TAB) && $HOOK_HOME_TAB|trim}
    <div class='centerLineTitle'>
        <ul id="home-page-tabs" class="nav nav-tabs clearfix">
			{$HOOK_HOME_TAB}
		</ul>
	</div>
	{/if}
	<div class="tab-content">{$HOOK_HOME_TAB_CONTENT}</div>
{/if}