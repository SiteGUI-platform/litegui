<!doctype html>
<html>
<head>
    <title>{$data.subject}</title>
</head>    
<body bgcolor="#f5f5f5">
  <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto; max-width: 620px;">
    <tbody>
      <tr>
        <td align="left" valign="top" style="padding: 0 10px;">
          <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td height="20" style="font-size: 1px; line-height: 1px;">&nbsp;</td>
              </tr>
            </tbody>
          </table>
          <!-- BEGIN MODULE: Menu 1 -->
          <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
            <tbody>
              <tr>
                <td bgcolor="#f5f5f5" valign="top" style="padding: 0px; background-color: #f5f5f5; border-radius: 8px">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                    <tbody>
                      <tr>
                        <td align="center" valign="top" style="padding: 10px;">
                          <a href="https://{$site.url}" style="text-decoration: none;"><img src="{$site.logo}" height="49" alt="" style="height: auto; max-width: 100%; border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff; font-size: 14px;"></a>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- END MODULE: Menu 1 -->
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
                <td style="padding: 30px; background: #ffffff; border-radius: 8px" bgcolor="#ffffff" valign="top">
                  <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                    {if $data.title}
                    <tbody>
                      <tr>
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 26px; font-weight: 800; line-height: 46px; letter-spacing: -0.6px; color: #151515; padding: 0 10px" valign="top">
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
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0 10px" valign="top">
                          Xin chào {$data.recipient},<br><br>
                          {$data.content}
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
                      <tr>
                        <td height="20" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                      </tr>  
                        <td>
                          <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                            <tbody>                              
                              <tr>
                                <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0 10px" valign="top" align="center">
                               or copy and paste the following link into your web browser:<br>
                               <span style="background-color: beige; word-break:break-all;">{$data.cta_url}</span>
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
                        <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 30px; letter-spacing: -0.2px; color: #777777; padding: 0 10px" valign="top">
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
                <td style="padding: 20px 20px 14px 20px; background-color: #f5f5f5; border-radius: 8px" valign="top" bgcolor="#f5f5f5" role="presentation">
                  <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    <tbody>
                      <tr>
                        <td style="font-size: 0;" valign="top">
                          <!--[if (gte mso 9)|(IE)]><table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation"><tr><td width="280" valign="top"><![endif]-->
                          <div style="display: inline-block; width: 100%; max-width: 280px; vertical-align: top;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                              <tbody>
                                <tr>
                                  <td style="padding: 20px;" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500; line-height: 24px; letter-spacing: -0.2px; color: #000000" valign="top">
                                            Follow Us.
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="11" style="line-height: 1px; font-size: 1px;">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; letter-spacing: -0.2px; color: #D8D8D8;" valign="top"></td>
                                        </tr>
                                        <tr>
                                          <td height="15" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: Arial, sans-serif; font-size: 19px;" valign="top">
                                            <a href="https://facebook.com/{$site.url}" style="text-decoration: none;"><img src="https://designmodo.com/postcards/app/images/facebook-dark-gray.png" width="20" height="20" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            <a href="https://twitter.com/{$site.url}" style="text-decoration: none;"><img src="https://designmodo.com/postcards/app/images/twitter-dark-gray.png" width="21" height="18" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
                                            <span>&nbsp;&nbsp;</span>
                                            <a href="https://instagram.com/{$site.url}" style="text-decoration: none;"><img src="https://designmodo.com/postcards/app/images/instagram-dark-gray.png" width="21" height="20" alt="" style="border: 0; line-height: 100%; outline: 0; -ms-interpolation-mode: bicubic; color: #ffffff;"></a>
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
                          <div style="display: inline-block; width: 100%; max-width: 280px; vertical-align: top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                              <tbody>
                                <tr>
                                  <td style="padding: 20px;" valign="top">
                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500; line-height: 24px; letter-spacing: -0.2px; color: #000000" valign="top" align="right">
                                            Contact us.
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="8" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; line-height: 20px; letter-spacing: -0.2px; color: #000000" valign="top"></td>
                                        </tr>
                                        <tr>
                                          <td height="0" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 500; line-height: 24px; letter-spacing: -0.2px;" valign="top" align="right">
                                            <a href="tel:" style="text-decoration: none; color: #000000; font-size: 15px"></a>
                                          </td>
                                        </tr>
                                        <tr>
                                          <td height="0" style="line-height: 1px; font-size: 1px">&nbsp;</td>
                                        </tr>
                                      </tbody>
                                      <tbody>
                                        <tr>
                                          <td style="font-family: 'Fira Sans', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 500; line-height: 24px;" valign="top" align="right">
                                            <a href="mailto:{$data.from_mail}" style="text-decoration: none; color: #1595E7;">{$data.from_mail}</a>
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                          <!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- END MODULE: Footer 1 -->
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