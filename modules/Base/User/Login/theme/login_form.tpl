{$form_data.javascript}

<form {$form_data.attributes}>
    {$form_data.hidden}
    <!-- Display the fields -->
    <center>

        <div class="layer" style="padding: 9px; width: 552px;">
            <div class="css3_content_shadow">

                <table id="Base_User_Login" cellspacing="0" cellpadding="0" border="0" style="height: 507px;">
                    <tbody>
                    <tr>
                        <td colspan="2" class="header_tail">{$logo}</td>
                    </tr>
                    <tr>
                        <td class="gradient">
                            <table cellspacing="0" cellpadding="0" border="0" style="width:100%;table-layout: auto;">
                                <tbody>
                                {if $is_demo}
                                    <tr>
                                        <td colspan="2" align="center"><strong>EPESI DEMO APPLICATION</strong></td>
                                    </tr>
                                {/if}
                                {if isset($message)}
                                    <tr>
                                        <td class="message">
                                            {$message}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="autologin"></td>
                                    </tr>
                                {else}
                                    {if $mode=='recover_pass'}
                                        <tr>
                                            <td colspan="2" class="error"><span
                                                        class="error">{$form_data.username.error}</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="error"><span
                                                        class="error">{$form_data.mail.error}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="label">{$form_data.username.label}&nbsp;&nbsp;</td>
                                            <td class="input">{$form_data.username.html}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">{$form_data.mail.label}&nbsp;&nbsp;</td>
                                            <td class="input">{$form_data.mail.html}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="submit_button">{$form_data.buttons.html}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="autologin"></td>
                                        </tr>
                                    {else}
                                        <tr>
                                            <td colspan="2" class="error"><span
                                                        class="error">{$form_data.username.error}</span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="error"><span
                                                        class="error">{$form_data.password.error}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="label">{$form_data.username.label}&nbsp;&nbsp;</td>
                                            <td class="input">{$form_data.username.html}</td>
                                        </tr>
                                        <tr>
                                            <td class="label">{$form_data.password.label}&nbsp;&nbsp;</td>
                                            <td class="input">{$form_data.password.html}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="submit_button">{$form_data.submit_button.html}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="autologin">{$form_data.autologin.html}</td>
                                        </tr>
                                    {/if}
                                {/if}
                                <tr>
                                    <td colspan="2" class="autologin">{$form_data.warning.html}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="recover_password">{$form_data.recover_password.html}</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                                {if isset($donation_note)}
                                    <tr>
                                        <td colspan="2" class="donation_notice">{$donation_note}</td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td colspan="2" class="footer">
                                        <!-- Epesi Terms of Use require line below - do not remove it! -->
                                        Copyright &copy; {php}echo date("Y"){/php} &bull; <a
                                                href="http://www.telaxus.com">Telaxus LLC</a> &bull; Managing Business
                                        Your Way<sup>TM</sup>
                                        <!-- Epesi Terms of Use require line above - do not remove it! -->
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
        </div>

        <!-- Epesi Terms of Use require line below - do not remove it! -->
        <a href="http://epe.si/"><img src="images/epesi-powered.png" alt="EPESI powered"/></a>
        <!-- Epesi Terms of Use require line above - do not remove it! -->

    </center>
</form>