# Dynamic Hostinger Website — SmartOps AI Solutions

This is a PHP dynamic version of the portfolio website.

## Dynamic features
- Public service website powered by `data/content.json`
- Enquiry/contact form
- Leads saved to `data/leads.csv`
- Admin setup page to create password
- Admin login/dashboard
- View latest leads
- Export leads as CSV
- Edit website content from admin as JSON
- Protected `data/` folder using `.htaccess`

## Hostinger setup
1. Log in to Hostinger hPanel.
2. Open File Manager → `public_html`.
3. Upload and extract `smartops-ai-dynamic-hostinger.zip` inside `public_html`.
4. Make sure `index.php` is directly inside `public_html`.
5. Visit `https://yourdomain.com/setup.php`.
6. Create a strong admin password.
7. After setup, log in at `https://yourdomain.com/admin/login.php`.
8. Edit website content from the admin dashboard if needed.

## Important security notes
- Do not use a weak admin password.
- The setup page locks itself after the first password is created.
- The `data/` folder has `.htaccess` protection.
- Do not delete `data/config.local.php` unless you want to reset admin setup.
- Replace `hello@yourdomain.com` in admin content editor with your real business email.

## Current contacts
- WhatsApp/Calls: 08136377667, 07060630333
- WhatsApp CTA: 08136377667

## If the contact form does not save leads
Check that Hostinger file permissions allow PHP to write to the `data/` folder.
