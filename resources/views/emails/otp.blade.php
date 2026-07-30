<!DOCTYPE html>
<html>
<body style="margin:0;background:#F5F8F7;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;color:#1F2A28;">
  <div style="max-width:520px;margin:0 auto;padding:32px 16px;">
    <div style="background:#0f766e;color:#fff;padding:20px 24px;border-radius:12px 12px 0 0;">
      <table role="presentation" cellpadding="0" cellspacing="0">
        <tr>
          <td style="vertical-align:middle;padding-right:12px;">
            <img src="{{ logo_url() }}" alt="" width="32" height="32" style="display:block;border-radius:8px;" />
          </td>
          <td style="vertical-align:middle;">
            <h1 style="margin:0;font-size:22px;font-weight:800;letter-spacing:-0.02em;">{{ $siteName }}</h1>
          </td>
        </tr>
      </table>
    </div>
    <div style="background:#fff;padding:28px 24px;border-radius:0 0 12px 12px;border:1px solid #e2e8e6;border-top:0;">
      <p style="margin:0 0 12px;font-size:15px;">Hi{{ $name ? ' ' . $name : '' }},</p>
      <p style="margin:0 0 20px;font-size:15px;color:#5a6b67;">Use the verification code below to continue. It expires in {{ $minutes }} minutes.</p>
      <div style="text-align:center;margin:24px 0;">
        <span style="display:inline-block;font-size:34px;font-weight:bold;letter-spacing:10px;background:#E6F3F1;color:#0f766e;padding:14px 24px;border-radius:10px;">{{ $code }}</span>
      </div>
      <p style="margin:0;font-size:13px;color:#8a9a96;">If you didn't request this, you can safely ignore this email.</p>
    </div>
    <p style="text-align:center;font-size:12px;color:#8a9a96;margin-top:16px;">&copy; {{ date('Y') }} {{ $siteName }}</p>
  </div>
</body>
</html>
