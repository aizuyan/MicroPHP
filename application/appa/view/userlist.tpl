[
	{foreach from=$users item=user name=users}
		{
			"name" : "{$user.name}",
			"age" : "{$user.age}"
		}
	{if $smarty.foreach.users.last}{else},{/if}
	{/foreach}
]
