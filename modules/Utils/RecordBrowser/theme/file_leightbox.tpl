<center>
    <br/>
    <table id="file_info" cellpadding="0" style="width:300px; border-spacing: 3px;">
        <tr>
            <td class="epesi_label" style="width:30%;">
                {$labels.filename}
            </td>
            <td class="epesi_data static_field" style="width:70%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                {$filename}
            </td>
        </tr>
        <tr>
            <td class="epesi_label" style="width:30%;">
                {$labels.file_size}
            </td>
            <td class="epesi_data static_field" style="width:70%;">
                {$file_size}
            </td>
        </tr>
    </table>
    <br/>
    <div id="{$download_options_id}">
        <table id="file_donwload" cellspacing="0" cellpadding="0">
            <tr>
                <!-- VIEW -->
                <td valign="top">
                    {$__link.view.open}
                    <div class="epesi_big_button">
                        <img src="{$theme_dir}/Utils/Attachment/view.png" alt="" align="middle" border="0" width="32" height="32">
                        <span>{$__link.view.text}</span>
                    </div>
                    {$__link.view.close}
                </td>
                <!-- DOWNLOAD -->
                <td valign="top">
                    {$__link.download.open}
                    <div class="epesi_big_button">
                        <img src="{$theme_dir}/Utils/Attachment/download.png" alt="" align="middle" border="0" width="32" height="32">
                        <span>{$__link.download.text}</span>
                    </div>
                    {$__link.download.close}
                </td>
                <td valign="top">
                    {$__link.delete_file.open}
                    <div class="epesi_big_button">
                        <img src="{$theme_dir}/Utils/Attachment/download.png" alt="" align="middle" border="0" width="32" height="32">
                        <span>{$__link.delete_file.text}</span>
                    </div>
                    {$__link.delete_file.close}
                </td>
                {if isset($__link.set_invoice)}
                    <td valign="top">
                    {$__link.set_invoice.open}
                    <div class="epesi_big_button">
                    <img src="" alt="" align="middle" border="0" width="32" height="32">
                    <span>{$__link.set_invoice.text}</span>
                    </div>
                    {$__link.set_invoice.close}
                    </td>
                {/if}
            </tr>
        </table>
</center>
