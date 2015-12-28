{* Get total number of fields to display *}
{assign var=count value=0}
{php}
	$this->_tpl_vars['multiselects'] = array();
{/php}
{foreach key=k item=f from=$fields name=fields}
	{if $f.type!="multiselect"}
		{assign var=count value=$count+1}
	{else}
		{php}
			$this->_tpl_vars['multiselects'][] = $this->_tpl_vars['f'];
		{/php}
	{/if}
{/foreach}
{php}
	$this->_tpl_vars['rows'] = ceil($this->_tpl_vars['count']/$this->_tpl_vars['cols']);
	$this->_tpl_vars['mss_rows'] = ceil(count($this->_tpl_vars['multiselects'])/$this->_tpl_vars['cols']);
	$this->_tpl_vars['no_empty'] = $this->_tpl_vars['count']-floor($this->_tpl_vars['count']/$this->_tpl_vars['cols'])*$this->_tpl_vars['cols'];
	if ($this->_tpl_vars['no_empty']==0) $this->_tpl_vars['no_empty'] = $this->_tpl_vars['cols']+1;
	$this->_tpl_vars['mss_no_empty'] = count($this->_tpl_vars['multiselects'])-floor(count($this->_tpl_vars['multiselects'])/$this->_tpl_vars['cols'])*$this->_tpl_vars['cols'];
	if ($this->_tpl_vars['mss_no_empty']==0) $this->_tpl_vars['mss_no_empty'] = $this->_tpl_vars['cols']+1;
	$this->_tpl_vars['cols_percent'] = 100 / $this->_tpl_vars['cols'];
{/php}

{if $main_page}
<table class="Utils_RecordBrowser__table" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="width:100px;">
				<div class="name">
					<img alt="&nbsp;" class="icon" src="{$icon}" width="32" height="32" border="0">
					<div class="label">{$caption}</div>
				</div>
			</td>
			<td class="required_fav_info">
				&nbsp;*&nbsp;{$required_note}
				{if isset($subscription_tooltip)}
					&nbsp;&nbsp;&nbsp;{$subscription_tooltip}
				{/if}
				{if isset($fav_tooltip)}
					&nbsp;&nbsp;&nbsp;{$fav_tooltip}
				{/if}
				{if isset($info_tooltip)}
					&nbsp;&nbsp;&nbsp;{$info_tooltip}
				{/if}
				{if isset($clipboard_tooltip)}
					&nbsp;&nbsp;&nbsp;{$clipboard_tooltip}
				{/if}
				{if isset($history_tooltip)}
					&nbsp;&nbsp;&nbsp;{$history_tooltip}
				{/if}
				{if isset($new)}
					{foreach item=n from=$new}
						&nbsp;&nbsp;&nbsp;{$n}
					{/foreach}
				{/if}
			</td>
		</tr>
	</tbody>
</table>

{if isset($click2fill)}
    {$click2fill}
{/if}

{/if}

<div class="row">

			{assign var=x value=1}
			{assign var=y value=1}
			{foreach key=k item=f from=$fields name=fields}
				{if $f.type!="multiselect"}
					{if !isset($focus) && $f.type=="text"}
						{assign var=focus value=$f.element}
					{/if}

					{if $y==1}
					<div class="col-xs-12 col-md-6">
						<div class="{if $action == 'view'}view{else}edit{/if}">
					{/if}
						<dl class="dl-horizontal">
							<dt>{$f.label}{if $f.required}*{/if}</dt>
							<dd class="data {$f.style}" id="_{$f.element}__data" style="position:relative;">
								{if $f.error}{$f.error}{/if}{$f.html}{if $action == 'view'}&nbsp;{/if}
							</dd>
						</dl>

					{if $y==$rows or ($y==$rows-1 and $x>$no_empty)}
						{if $x>$no_empty}
							<dl style="display:none;">
								<dt class="label">&nbsp;</dt>
								<dd class="data">&nbsp;</dd>
							</dl>
						{/if}
						{assign var=y value=1}
						{assign var=x value=$x+1}
						</div>
					</div>
					{else}
						{assign var=y value=$y+1}
					{/if}
				{/if}
			{/foreach}
		{if !empty($multiselects)}
			<div class="col-xs-12">
				{assign var=x value=1}
				{assign var=y value=1}
				{foreach key=k item=f from=$multiselects name=fields}
					{if $y==1}
					<div class="column" style="width: {$cols_percent}%;">
						<div class="multiselects {if $action == 'view'}view{else}edit{/if}" style="border-top: none;">
					{/if}
							<dl class="dl-horizontal">
								<dt>{$f.label}{if $f.required}*{/if}{$f.advanced}</dt>
								<dd class="data {$f.style}" id="_{$f.element}__data">
									<div style="position:relative;">
										{if isset($f.error)}{$f.error}{/if}{$f.html}{if $action == 'view'}&nbsp;{/if}
									</div>
								</dd>
							</dl>
					{if $y==$mss_rows or ($y==$mss_rows-1 and $x>$mss_no_empty)}
						{if $x>$mss_no_empty}
							<dl style="display:none;">
								<dt class="label">&nbsp;</dt>
								<dd class="data">&nbsp;</dd>
							</dl>
						{/if}
						{assign var=y value=1}
						{assign var=x value=$x+1}
						</div>
					</div>
					{else}
						{assign var=y value=$y+1}
					{/if}
				{/foreach}
			</div>
		{/if}

<div class="col-xs-12">
	<div class="longfields {if $action == 'view'}view{else}edit{/if}">
				{foreach key=k item=f from=$longfields name=fields}
					<dl class="dl-horizontal">
						<dt class="label long_label">{$f.label}{if $f.required}*{/if}</dt>
						<dd class="data long_data {if $f.type == 'currency'}currency{/if}" id="_{$f.element}__data">
							<div style="position:relative;">
								{if $f.error}{$f.error}{/if}{$f.html}{if $action == 'view'}&nbsp;{/if}
							</div>
						</dd>
					</dl>
				{/foreach}
			</div></div>

	</tbody>
</table>

{if $main_page}
{php}
	if (isset($this->_tpl_vars['focus'])) eval_js('focus_by_id(\''.$this->_tpl_vars['focus'].'\');');
{/php}
{/if}

</div>