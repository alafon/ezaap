{def $current_user = fetch( 'user', 'current_user' )}
{if $current_user.is_logged_in}

{def $selected_business = $current_user.contentobject.data_map.ezaapuserdata.content.business
     $business_data = ezaapservice('Account','BusinessList')
     $business_list = $business_data.business_list
     $role = $current_user.contentobject.data_map.ezaapuserdata.content.roles.0
     $role_names = hash( 'ROLE_ADMIN', 'Account administrator'|i18n('account/box'),
                         'ROLE_PURCHASER', 'Purchaser'|i18n('account/box'),
                         'ROLE_LEGAL_REPRESENTATIVE', 'Legal representative'|i18n('account/box'),
                         'ROLE_ACCOUNTANT', 'Accountant'|i18n('account/box'),
                         'ROLE_CREATOR', 'Account creator'|i18n('account/box'),
                         'ROLE_TCA', 'Test center administrator'|i18n('account/box'),
                         'ROLE_USER', 'ROLE_USER'|i18n('account/box'))
     $role_name = $role_names[$role]}

    <form method="post" action={$business_data.form_url|ezurl}>
        <p>{"Hello %username"|i18n('account/box',,hash('%username', $current_user.contentobject.data_map.ezaapuserdata.content.username|wash))}</p>
        {if $role|ne('ROLE_USER')}
        <p>{"Logged in as %role for %business"|i18n('account/box',,hash('%role', concat( '<b>', $role_name, '</b>' ), '%business', concat( '<b>', $business_list[$selected_business].label|wash, '</b>' )))}</p>
        {/if}
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