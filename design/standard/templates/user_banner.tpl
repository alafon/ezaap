{*ezaapservice( 'User', 'Banner' )*}
{include uri='design:user/account_box.tpl'}

{*if true()}
<h1>Debug</h1>
{def $current_user = fetch(user,current_user)}
<h2>Cookies</h2>
{foreach $current_user.contentobject.data_map.ezaapuserdata.content.cookies as $key => $value}
{$key}: {$value}<br />
{/foreach}
{/if*}