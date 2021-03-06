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
{include file="$tpl_dir./errors.tpl"}
{if isset($category)}
	{if $category->id AND $category->active}
		
		{if isset($subcategories)}
            
    		<!-- Subcategories -->
    		<div id="subcategories">
    			<ul class="row clearfix">
    			{foreach from=$subcategories item=subcategory}
    				<li class="col-xs-12 col-sm-4">
    					<a class="subcategory" href="{$link->getCategoryLink($subcategory.id_category, $subcategory.link_rewrite)|escape:'html':'UTF-8'}" title="{$subcategory.name|escape:'html':'UTF-8'}" >

                            <img alt="image catégorie" src="{if $subcategory.id_image}{$link->getCatImageLink($subcategory.link_rewrite, $subcategory.id_image)|escape:'html':'UTF-8'}{else}{$img_cat_dir}{$lang_iso}-default-medium_default.jpg{/if}">

                            <h3>{$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}</h3>
                            {*
                                {if $subcategory.description}
                                    <span>{$subcategory.description|truncate:25:'...'}</span>
                                {/if}
                            *}
    					</a>
				    </li>
    			{/foreach}
    			</ul>
    		</div>
		{else}

            {if $products}
    			<div class="content_sortPagiBar clearfix">
                	<div class="sortPagiBar row clearfix">
                		{include file="./product-sort.tpl"}
                    	{include file="./nbr-product-page.tpl"}
    				</div>
                    <div class="top-pagination-content row clearfix">
                    	{include file="./product-compare.tpl"}
    					{include file="$tpl_dir./pagination.tpl"}
                    </div>
    			</div>
    			{include file="./product-list.tpl" products=$products}
    			<div class="content_sortPagiBar row">
    				<div class="bottom-pagination-content clearfix">
    					{include file="./product-compare.tpl" paginationId='bottom'}
                        {include file="./pagination.tpl" paginationId='bottom'}
    				</div>
    			</div>
    		{else}
                <p class="empty-category">Il n'y a pas encore de produits dans cette catégorie !</p>
            {/if}
        {/if}
	{elseif $category->id}
		<p class="alert alert-warning">{l s='This category is currently unavailable.'}</p>
	{/if}
{/if}
