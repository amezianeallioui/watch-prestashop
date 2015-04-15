{if isset($products) && $products}
	{include file="$tpl_dir./product-list.tpl" products=$products class='blockallproducts tab-pane' id='blockallproducts'}
{else}
<ul id="blockallproducts" class="blockallproducts tab-pane">
	<li class="alert alert-info">{l s='No products for the moment, sorry !' mod='blockallproducts'}</li>
</ul>
{/if}