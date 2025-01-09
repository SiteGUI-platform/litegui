<!doctype html>
<html>
<head>
    <title>{$data.subject}</title>
</head>    
<body bgcolor="#f5f5f5">
  <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; max-width: 620px;">
    <tbody>
      <tr>
        <td align="left" valign="top" style="padding: 0;">
          <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td height="20" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
              </tr>
            </tbody>
          </table>
          {block name="header"}  
          <!-- BEGIN MODULE: Menu 1 -->
          <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td bgcolor="#f5f5f5" valign="top" style="padding: 0px; background-color: #f5f5f5; border-radius: 8px">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                    <tbody>
                      <tr>
                        <td align="center" valign="top" style="padding: 10px;">
                          <a href="https://{$site.url}" style="text-decoration: none;"><img src="{$site.logo}" height="49" alt="" style="height: auto; max-width: 60%; border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff; font-size: 14px;"></a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- END MODULE: Menu 1 -->
          {/block}  
          {block name="content"}
          <!-- BEGIN MODULE: Transactional 1 -->
          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
            <tbody>
              <tr>
                <td height="0" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
              </tr>
            </tbody>
          </table>
          <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
            <tbody>
              <tr>
                <td style="padding: 20px; background: #ffffff; border-radius: 8px" bgcolor="#ffffff" valign="top">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                    {if $data.title}
                    <tbody>
                      <tr>
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 26px; font-weight: 800; line-height: 46px; letter-spacing: -0.6px; color: #151515; padding: 0" valign="top">
                          {$data.title}
                        </td>
                      </tr>
                      <tr>
                        <td height="15" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                      </tr>
                    </tbody>
                    {/if}
                    <tbody>
                      <tr>
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0" valign="top">
                          Hello {$data.recipient},<br><br>
                          {if $data.body}{$data.body}{else}{$data.content}{/if}
                        </td>
                      </tr>
                      <tr>
                        <td height="25" style="line-height: 1px; font-size: 1px;">&nbsp;</td>
                      </tr>
                    </tbody>
                    {if $data.cta_text}
                    <tbody>
                      <tr>
                        <td style="padding: 5px 10px;" valign="top" align="center">
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tbody>
                              <tr>
                                <td style="text-align: center; border-radius: 8px; padding: 14px 19px; background-color: #0f35b2" bgcolor="#0f35b2" valign="top" align="center">
                                  <a href="{$data.cta_url}" style="text-decoration: none; line-height: 24px; letter-spacing: -0.2px; font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; color: #ffffff; word-break: break-word; display: block;">{$data.cta_text}</a>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>  
                      </tr>
                      <tr>  
                        <td>
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tbody>                              
                              <tr>
                                <td style="display: none; font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0" valign="top" align="center">
                               or copy and paste the following link into your web browser:<br>
                               <textarea style="background-color: beige; word-break:break-all;">{$data.cta_url}</textarea>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                    {/if}
                    {if $data.signature}
                    <tbody>
                      <tr>
                        <td height="25" style="line-height: 1px; font-size: 1px;">&nbsp;</td>
                      </tr>
                      <tr>
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0" valign="top">
                          {$data.signature}
                        </td>
                      </tr>
                    </tbody>
                    {/if}
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- END MODULE: Transactional 1 -->
          {/block}  
          {block name="footer"}
          <!-- BEGIN MODULE: Footer 1 -->
          <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
            <tbody>
              <tr>
                <td height="8" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
              </tr>
            </tbody>
          </table>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
            <tbody>
              <tr>
                <td style="padding: 10px 0px; background-color: #f5f5f5; border-radius: 8px" valign="top" bgcolor="#f5f5f5" role="presentation">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    <tbody>
                      <tr>
                        <td style="font-size: 0;" valign="top">
                          <!--[if (gte mso 9)|(IE)]><table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation"><tr><td width="280" valign="top"><![endif]-->
                          <div style="display: inline-block; width: 50%; vertical-align: top;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                              <tbody>
                                <tr>
                                  <td style="padding: 20px;" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 500; line-height: 18px; letter-spacing: -0.2px; color: #000000" valign="top">
                                            Follow Us
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="11" style="line-height: 1px; font-size: 1px;">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: Arial, sans-serif; font-size: 19px;" valign="top">
                                            {if $site.social.facebook}
                                            <a href="https://facebook.com/{$site.social.facebook}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/facebook.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                            {if $site.social.youtube}
                                            <a href="https://www.youtube.com/@{$site.social.youtube}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/youtube.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                            {if $site.social.instagram}
                                            <a href="https://instagram.com/{$site.social.instagram}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/instagram.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                            {if $site.social.tiktok}
                                            <a href="https://tiktok.com/@{$site.social.tiktok}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/tiktok.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                            {if $site.social.twitter}
                                            <a href="https://twitter.com/{$site.social.twitter}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/twitter-x.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                            {if $site.social.linkedin}
                                            <a href="https://linkedin.com/company/{$site.social.linkedin}" style="text-decoration: none;"><img src="https://cdn.sitegui.com/public/uploads/global/img/linkedin.png" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            {/if}
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                         <!--[if (gte mso 9)|(IE)]></td><td width="280" valign="top"><![endif]-->
                          <div style="display: inline-block; width: 50%; vertical-align: top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                              <tbody>
                                <tr>
                                  <td style="padding: 20px;" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500; line-height: 24px;" valign="top" align="">
                                            <img src="https://cdn.sitegui.com/public/uploads/global/img/house-lock.png" alt="" style="border: 0; line-height: 100%; outline: 0; vertical-align:text-bottom;"><span>&nbsp;&nbsp;</span>
                                            <a href="https://{$site.account_url}/account" style="text-decoration: none; color: #1595E7;">Account Center</a>
                                          </td>
                                        </tr>
                                      </tbody>
                                      {if $site.social.phone}
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500; line-height: 24px;" valign="top" align="">
                                            <img src="https://cdn.sitegui.com/public/uploads/global/img/phone.png" alt="" style="border: 0; line-height: 100%; outline: 0; vertical-align:text-bottom;"><span>&nbsp;&nbsp;</span>
                                            <a href="tel:{$site.social.phone}" style="text-decoration: none; color: #1595E7;">{$site.social.phone}</a>
                                          </td>
                                        </tr>
                                      </tbody>
                                      {/if}
                                      {if $site.social.email}
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500; line-height: 24px;" valign="top" align="">
                                            <img src="https://cdn.sitegui.com/public/uploads/global/img/email.png" alt="" style="border: 0; line-height: 100%; outline: 0; vertical-align:text-bottom;"><span>&nbsp;&nbsp;</span>
                                            <a href="mailto:{$site.social.email}" style="text-decoration: none; color: #1595E7;">{$site.social.email}</a>
                                          </td>
                                        </tr>
                                      </tbody>
                                      {/if}
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>                       
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- END MODULE: Footer 1 -->
          {/block}  
          <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td height="20" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
  </table>
  </body>