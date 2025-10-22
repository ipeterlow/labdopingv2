php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &

composer clear-cache &&
composer install &&
composer pint &&
npm cache verify &&
npm install &&
npm run format &&
rm -rf public/storage &&
php artisan storage:link &&
php artisan config:clear &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan about
