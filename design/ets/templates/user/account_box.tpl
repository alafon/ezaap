{def $current_user = fetch( 'user', 'current_user' )
     $selected_business = $current_user.contentobject.data_map.ezsfuserdata.content.business
     $business_data = ezsfservice('Account','BusinessList')
     $business_list = $business_data.business_list}

    <form method="post" action={$business_data.form_url|ezurl}>
        <p>{"Hello %username"|i18n('account/box',,hash('%username', $current_user.contentobject.data_map.ezsfuserdata.content.username|wash))}</p>
        {if $business_data.business_list|count}
            {if $business_data.selected_business}
            <p>{"Logged in as %selected_role"|i18n('account/box',,hash('%selected_role', $business_data.selected_business_name))}</p>
            {else}
            <p>{"Please choose a role within the following list"|i18n('account/box')}</p>
            {/if}
            <select name="ecom_config[business]">
                <option value="0" {$selected_business|eq(0)|choose('','selected="selected"')}>My self</option>
                {foreach $business_list as $business_id => $business}
                <option value="{$business_id}"{$selected_business|eq($business_id)|choose('',' selected="selected"')}>{$business.label|wash}</option>
                {/foreach}
            </select>
            <button type="submit">{"Submit"|i18n('account/box')}</button>
        {/if}
    </form>