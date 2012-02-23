{def $current_user = fetch( 'user', 'current_user' )}
{if $current_user.is_logged_in}

{def $selected_business = $current_user.contentobject.data_map.ezaapuserdata.content.business
     $business_data = ezaapservice('Account','BusinessList')
     $business_list = $business_data.business_list
     $role = $current_user.contentobject.data_map.ezaapuserdata.content.roles|implode(' and ')}

    <form method="post" action={$business_data.form_url|ezurl}>
        <p>{"Hello %username"|i18n('account/box',,hash('%username', $current_user.contentobject.data_map.ezaapuserdata.content.username|wash))}</p>
        <p>{"Logged in as %role"|i18n('account/box',,hash('%role', concat( '<b>', $role, '</b>' )))}</p>
        {if $business_data.business_list|count}
            <p>{"Available business list : "|i18n('account/box')}</p>
            <select name="ecom_config[business]">
                {foreach $business_list as $business_id => $business}
                <option value="{$business_id}"{$selected_business|eq($business_id)|choose('',' selected="selected"')}>{$business.label|wash}</option>
                {/foreach}
            </select>
            <button type="submit">{"Submit"|i18n('account/box')}</button>
        {/if}
    </form>

{/if}