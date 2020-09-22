<!-- Nemops.com OpenGraph Module -->

{if $og_type}

	<meta property="og:type"                   content="{$og_type}" />
	{if $remove_site_name_title}
	<meta property="og:title"                  content="{$meta_title|replace:{' - '|cat:$og_site_name}:''|escape:'htmlall':'UTF-8'}" />
	{else}
	<meta property="og:title"                  content="{$meta_title|escape:'htmlall':'UTF-8'}" />
	{/if}
	
	{if isset($og_product_image) && $og_product_image}{* Product Image or Category Image*}
	<meta property="og:image"                  content="{$og_product_image}" />
	{else if $og_image}
	<meta property="og:image"                  content="{$og_image}" />	
	{else}
	<meta property="og:image"                  content="http://{$smarty.server.HTTP_HOST}{$logo_url}" />	<!-- Shop Logo if no specific Image is set -->
	
	{/if}

	<meta property="og:site_name"              content="{$og_site_name|escape:html:'UTF-8'}" />

	<meta property="og:description"            content="{$meta_description|escape:html:'UTF-8'}" />
	<meta property="og:url"                    content="http://{$smarty.server.HTTP_HOST}{$smarty.server.REQUEST_URI}" />
	{if $og_type == 'og:product'}
	<meta property="product:price:amount"      content="{$og_product_price_amount}"/>
	<meta property="product:price:currency"    content="{$og_product_price_currency}"/>
	{/if}


{/if}


<!-- /end Nemops.com OpenGraph Module -->