<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">

# Stack

- [Laravel](https://laravel.com/)
- [Inertia](https://inertiajs.com/)
- [Vue](https://vuejs.org/)
- [Tailwind](https://tailwindcss.com/)

## PHP Packages

- [Laravel Jetstream](https://jetstream.laravel.com/)
- [Laravel Inertia](https://inertiajs.com/)
- [Laravel Pint](https://laravel.com/docs/11.x/pint)
- [Laravel Pulse](https://pulse.laravel.com/)
- [Laravel Sanctum](https://laravel.com/docs/11.x/sanctum)
- [Laravel Telescope](https://laravel.com/docs/11.x/telescope)
- [Laravel Permissions](https://spatie.be/docs/laravel-permission/v6/introduction)
- [Laravel Excel](https://laravel-excel.com/)
- [Laravel Lang](https://laravel-lang.com/)

## Local Installation

```bash
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
```

## Prepare to Production Mac

```bash
composer clear-cache &&
composer install &&
composer pint &&
npm cache verify &&
npm install &&
npm run format &&
npm run build &&
rm -rf node_modules &&
rm -rf public/storage &&
rm -rf storage/logs/laravel.log
cd .. &&
zip -q -r labdopingv2.zip labdopingv2 -x ".DS_Store" -x "__MACOSX"
```

## Prepare to Production Win (CMD)

```bash
composer clear-cache && ^
composer install && ^
composer pint && ^
npm cache verify && ^
npm install && ^
npm run format && ^
npm run build && ^
rmdir /s /q node_modules && ^
rmdir /s /q public/storage && ^
rmdir /s /q storage/logs/laravel.log && ^
cd .. && ^
powershell Compress-Archive -Path servicios -DestinationPath servicios.zip -Force
```

## QAS Deployment

```bash
rm -f servicios &&
unzip -qo servicios.zip &&
rm -f servicios.zip &&
rm -rf public_html &&
ln -s /home/qasservicios/servicios/public /home/qasservicios/public_html &&
cd servicios &&
find /home/qasservicios/servicios/ -name ".DS_Store" -type f -delete &&
rm -rf public/storage &&
composer clear-cache &&
composer install &&
php artisan storage:link &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan config:clear &&
php artisan event:cache &&
php artisan route:cache &&
php artisan view:cache &&
composer install --optimize-autoloader --no-dev &&
php artisan config:cache &&
php artisan about

## Production Deployment

```bash
cd labdopingv2 &&
php artisan down &&
cd .. &&
date=$(date +'%d-%m-%Y') && mv /home/dev/labdopingv2 /home/dev/labdopingv2-$date &&
unzip -qo labdopingv2.zip &&
rm -f labdopingv2.zip &&
rm -rf public_html &&
ln -s /home/dev/labdopingv2/public /home/dev/public_html &&
cd labdopingv2 &&
find /home/dev/labdopingv2/ -name ".DS_Store" -type f -delete &&
rm -rf public/storage &&
composer clear-cache &&
composer install &&
php artisan storage:link &&
php artisan event:clear &&
php artisan route:clear &&
php artisan view:clear &&
php artisan config:clear &&
php artisan event:cache &&
php artisan route:cache &&
php artisan view:cache &&
composer install --optimize-autoloader --no-dev &&
php artisan config:cache &&
php artisan about
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
