<!-- Block monmodule -->
<div id="mywatch_block_home" class="block">
	<h4>Bonjour!</h4>
	<div class="block_content">
	 <p>Hello,
       {if isset($my_module_name) && $my_module_name}
           {$my_module_name}
       {else}
           World
       {/if}
       !       
    </p>   
		<p>{if isset($my_config) && $my_config}{$my_config}{else}Hello World{/if} !</p>
		<ul>
			<li><a href="{$my_link}" title="Click this link">Click me!</a></li>
		</ul>
	</div>
</div>
<!-- /Block monmodule -->



