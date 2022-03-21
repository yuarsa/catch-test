## Installations

```
git clone https://github.com/yuarsa/catch-test.git
cd catch-nest
composer install --prefer-dist
php artisan storage:link
```

## Generate file and convert data
```
php artisan catch:convert-data
```

The generated file are in the public folder with name 'output.csv'