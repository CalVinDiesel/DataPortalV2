DataPortalV2 — Deployment Files
=================================

This folder contains everything needed for production deployment.
No code changes are required when deploying — just follow the checklist.

Files:
  DEPLOYMENT_CHECKLIST.md  → Step-by-step deployment guide
  nginx.conf               → Copy to /etc/nginx/sites-available/dataportal
  php-fpm.conf             → Copy to /etc/php/8.2/fpm/pool.d/dataportal.conf

The Nitro 16-lane upload engine switches to production mode automatically
when APP_ENV=production is set in .env. No manual steps needed.
