<!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>@yield('title', config('app.name'))</title>
    <style media="all" type="text/css">
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            font-size: 16px;
            line-height: 1.5;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            background-color: #f0fdf4;
            margin: 0;
            padding: 0;
        }
        table { border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; }
        table td { font-family: inherit; font-size: 16px; vertical-align: top; }
        .body { background-color: #f0fdf4; width: 100%; }
        .container { margin: 0 auto !important; max-width: 600px; padding: 24px 12px; width: 100%; }
        .content { box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 0; }
        .main { background: #ffffff; border-radius: 12px; width: 100%; overflow: hidden; }
        .wrapper { box-sizing: border-box; padding: 16px 28px 24px; }
        .footer { padding: 16px 28px; text-align: center; font-size: 12px; color: #9ca3af; line-height: 1.6; }
        .footer td, .footer p, .footer span, .footer a { color: #9ca3af; font-size: 12px; text-align: center; }
        p { font-family: inherit; font-size: 15px; font-weight: normal; margin: 0; margin-bottom: 14px; color: #374151; }
        p:last-child { margin-bottom: 0; }
        a { color: #059669; text-decoration: underline; }
        .btn table { width: auto; margin: 0 auto; }
        .btn table td { background-color: #059669; border-radius: 8px; text-align: center; }
        .btn a { background-color: #059669; border: none; border-radius: 8px; box-sizing: border-box; color: #ffffff; cursor: pointer; display: inline-block; font-size: 14px; font-weight: 600; margin: 0; padding: 12px 28px; text-decoration: none; }
        .preheader { color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0; }
        @media only screen and (max-width: 640px) {
            .wrapper { padding: 12px 16px 18px !important; }
            .container { padding: 12px 4px !important; }
            .main { border-radius: 8px !important; }
            .btn a { font-size: 15px !important; width: 100% !important; }
            .footer { padding: 12px 16px !important; }
        }
        @media all {
            .ExternalClass { width: 100%; }
            .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
            .apple-link a { color: inherit !important; font-family: inherit !important; font-size: inherit !important; font-weight: inherit !important; line-height: inherit !important; text-decoration: none !important; }
            #MessageViewBody a { color: inherit; text-decoration: none; font-size: inherit; font-family: inherit; font-weight: inherit; line-height: inherit; }
            .btn a:hover { background-color: #047857 !important; }
            .btn table td:hover { background-color: #047857 !important; }
        }
    </style>
</head>
<body>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body">
        <tr>
            <td>&nbsp;</td>
            <td class="container">
                <div class="content">
                    <span class="preheader">@yield('preheader', config('app.name'))</span>
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main">
                        <tr>
                            <td style="padding: 20px 28px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="56" style="width: 56px; padding: 0 12px 0 0; vertical-align: middle;">
                                            <img src="https://i.imgur.com/g2HULiI.png" alt="CHMSU" width="56" height="56" style="display: block; border: 0;">
                                        </td>
                                        <td style="vertical-align: middle; padding: 0;">
                                            <p style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0; line-height: 1.3;">Carlos Hilado Memorial State University</p>
                                            <p style="font-size: 12px; font-weight: 500; color: #64748b; margin: 2px 0 0; line-height: 1.3;">Talisay Campus &bull; College of Computer Studies</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 28px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr><td style="height: 2px; background-color: #059669; font-size: 0; line-height: 0;"></td></tr>
                                    <tr><td style="height: 1px; background-color: #eab308; font-size: 0; line-height: 0;"></td></tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 20px 28px 0;">
                                <h2 style="color: #059669; margin: 0; font-size: 17px; font-weight: 700; line-height: 1.3;">@yield('header', config('app.name'))</h2>
                            </td>
                        </tr>
                        <tr>
                            <td class="wrapper">
                                @yield('content')
                            </td>
                        </tr>
                    </table>
                    <div class="footer">
                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="text-align: center; font-size: 12px; color: #9ca3af;">
                                    <p style="font-size: 12px; color: #9ca3af; margin-bottom: 3px;">Carlos Hilado Memorial State University &mdash; Talisay</p>
                                    <p style="font-size: 12px; color: #9ca3af; margin-bottom: 3px;">BSIS Program &bull; Internship Management System</p>
                                    <p style="font-size: 12px; color: #9ca3af; margin-bottom: 0;">This is an automated message. Please do not reply.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </td>
            <td>&nbsp;</td>
        </tr>
    </table>
</body>
</html>
