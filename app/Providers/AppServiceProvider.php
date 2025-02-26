<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Blade directive for view file
        Blade::directive('viewfile', function ($fullpath) {
            $temp = explode(',', $fullpath);
            $fullpath = $temp[0];
            $type = $temp[1] ?? 'private';
            return "<?php
                \$path = explode('/', $fullpath);
                \$file = end(\$path);
                array_pop(\$path);
                \$path = implode('/', \$path);
                \$temp_key = encrypt(md5(\$path));
                \$type = '$type';
                echo route('web.view.file', [
                    'filename' => \$file,
                ]) . '?type=$type&path=' . urlencode(\$path) . (\$type == 'private' ? '' : '&temp_key=' . \$temp_key);
            ?>";
        });


        // Blade directive for download file
        Blade::directive('downloadfile', function ($fullpath) {
            $temp = explode(',', $fullpath);
            $fullpath = $temp[0];
            $type = $temp[1] ?? 'private';
            return "<?php
                \$path = explode('/', $fullpath);
                \$file = end(\$path);
                array_pop(\$path);
                \$path = implode('/', \$path);
                \$temp_key = encrypt(md5(\$path));
                echo route('web.download.file', [
                    'filename' => \$file,
                ]) . '?type=$type&path=' . urlencode(\$path) . (\$temp_key == 'private' ? '' : '&temp_key=' . \$temp_key);
            ?>";
        });

        // Blade directive for currency
        Blade::directive('currency', function ( $expression ) { return "Rp<?php echo number_format($expression,0,',','.'); ?>"; });
    }
}
