<ul $AttributesHTML>
	<% loop $Options %>
        <li class="{$Class}">
            <input id="{$ID}" class="radio" name="{$Name}" type="radio" data-brightness="{$Top.getColourBrightness($Value)}" value="{$Value}"<% if $isChecked %> checked<% end_if %><% if $isDisabled %> disabled<% end_if %> />
            <label for="{$ID}" title="{$Top.getColourName($Value)}" style="background: {$Title}"></label>
        </li>
	<% end_loop %>
</ul>
