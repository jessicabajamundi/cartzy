/**
 * Cartzy mail relay (Google Apps Script).
 *
 * Render's free plan blocks SMTP, so the live site sends email over HTTPS to this script,
 * which sends it from the Gmail account that deploys it (App\Mail\RelayTransport).
 *
 * Set up (signed in as the Gmail account that should send Cartzy emails):
 * 1. https://script.google.com > New project. Paste this file over Code.gs.
 * 2. Replace SECRET below with a long random value. Use the same value for MAIL_WEBHOOK_SECRET on Render.
 * 3. Deploy > New deployment > type "Web app". Execute as: Me. Who has access: Anyone. Deploy and allow access.
 * 4. Copy the Web app URL (ends in /exec) into MAIL_WEBHOOK_URL on Render.
 * 5. On Render also set MAIL_MAILER=relay, so every Cartzy email uses this relay.
 *
 * After changing this code, use Deploy > Manage deployments > Edit > New version, so the /exec URL stays the same.
 * A personal Gmail account can send about 100 emails a day this way.
 */
const SECRET = 'replace-with-the-same-value-as-MAIL_WEBHOOK_SECRET';

function doPost(e) {
  let body;
  try {
    body = JSON.parse(e.postData.contents);
  } catch (err) {
    return reply({ ok: false, error: 'Invalid JSON.' });
  }
  if (!SECRET || SECRET.indexOf('replace-with') === 0 || body.secret !== SECRET) {
    return reply({ ok: false, error: 'Unauthorized.' });
  }
  if (!body.to || !body.subject || !body.html) {
    return reply({ ok: false, error: 'to, subject and html are required.' });
  }
  try {
    MailApp.sendEmail({
      to: String(body.to),
      subject: String(body.subject),
      htmlBody: String(body.html),
      name: String(body.name || 'Cartzy'),
    });
  } catch (err) {
    return reply({ ok: false, error: String(err && err.message ? err.message : err) });
  }
  return reply({ ok: true });
}

// Opening the /exec URL in a browser shows this, which confirms the deployment works.
function doGet() {
  return reply({ ok: true, service: 'cartzy-mail-relay' });
}

function reply(data) {
  return ContentService.createTextOutput(JSON.stringify(data)).setMimeType(ContentService.MimeType.JSON);
}
