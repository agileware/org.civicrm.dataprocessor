{crmScope extensionKey='dataprocessor'}
    <h3>{ts}Exposed Filters{/ts}</h3>
    <div class="crm-block crm-form-block crm-data-processor_source-block">
        <table>
            <tr>
                <th>{ts}Title{/ts}</th>
                <th>{ts}Name{/ts}</th>
                <th>{ts}Required{/ts}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            {foreach from=$filters item=filter}
                <tr>
                    <td>{$filter.title}</td>
                    <td>{$filter.name}</td>
                    <td>{$filter.is_required}</td>
                    <td>
                        {if $filter.configuration_link}
                            <a href="{$filter.configuration_link}">{ts}Configure Filter{/ts}</a>
                        {/if}
                    </td>
                    <td>
                        <a href="{crmURL p="civicrm/dataprocessor/form/filter" q="reset=1&action=update&data_processor_id=`$filter.data_processor_id`&id=`$filter.id`"}">{ts}Edit{/ts}</a>
                    </td>
                    <td>
                        <a href="{crmURL p="civicrm/dataprocessor/form/filter" q="reset=1&action=delete&data_processor_id=`$filter.data_processor_id`&id=`$filter.id`"}">{ts}Remove{/ts}</a>
                    </td>
                </tr>
            {/foreach}
        </table>

        <div class="crm-submit-buttons">
            <a class="add button" title="{ts}Add Filter{/ts}" href="{$addFilterUrl}">
                <span><div class="icon add-icon ui-icon-circle-plus"></div>{ts}Add Filter{/ts}</span></a>
        </div>
    </div>
{/crmScope}