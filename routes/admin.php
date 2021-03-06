<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/23
 * Time: 12:24
 */
\Illuminate\Support\Facades\Route::group([
    'prefix' => 'admin'
], function () {

    \Illuminate\Support\Facades\Route::get('/login', '\App\Dede\Controllers\LoginController@index');
    \Illuminate\Support\Facades\Route::post('/login', '\App\Dede\Controllers\LoginController@login')->name('admin.login');
    \Illuminate\Support\Facades\Route::get('/logout', '\App\Dede\Controllers\LoginController@logout');

    \Illuminate\Support\Facades\Route::group(['middleware' => 'auth:admin'], function () {
        \Illuminate\Support\Facades\Route::get('/index', '\App\Dede\Controllers\HomeController@index')->name('admin.index');
        \Illuminate\Support\Facades\Route::get('/catalog', '\App\Dede\Controllers\CatalogController@index')->name('admin.catalog');
        \Illuminate\Support\Facades\Route::get('/catalog/add/{parentArctype?}', '\App\Dede\Controllers\CatalogController@add')->name('admin.catalog.create');
        \Illuminate\Support\Facades\Route::post('/catalog/add/{parentArctype?}', '\App\Dede\Controllers\CatalogController@create')->name('admin.catalog.create');

        \Illuminate\Support\Facades\Route::get('/catalog/edit/{arctype}', '\App\Dede\Controllers\CatalogController@edit')->name('admin.catalog.update');
        \Illuminate\Support\Facades\Route::post('/catalog/edit/{arctype}', '\App\Dede\Controllers\CatalogController@update')->name('admin.catalog.update');

        \Illuminate\Support\Facades\Route::post('/catalog/move', '\App\Dede\Controllers\CatalogController@move')->name('admin.catalog.move');

        \Illuminate\Support\Facades\Route::post('/catalog/delete/', '\App\Dede\Controllers\CatalogController@delete')->name('admin.catalog.delete');
        \Illuminate\Support\Facades\Route::post('/catalog/sotrrank/', '\App\Dede\Controllers\CatalogController@sotrRank')->name('admin.catalog.sotrrank');

        \Illuminate\Support\Facades\Route::get('/uploader', '\App\Dede\Controllers\UploaderController@action')->name('admin.uploader');
        \Illuminate\Support\Facades\Route::post('/uploader', '\App\Dede\Controllers\UploaderController@index')->name('admin.uploader');
        \Illuminate\Support\Facades\Route::post('/uploader/stream', '\App\Dede\Controllers\UploaderController@stream')
            ->name('admin.uploader.stream');

        \Illuminate\Support\Facades\Route::get('/content', '\App\Dede\Controllers\ContentController@index')->name('admin.content');
        \Illuminate\Support\Facades\Route::get('/content/list', '\App\Dede\Controllers\ContentController@pageList')->name
        ('admin.content.list');
        \Illuminate\Support\Facades\Route::get('/content/add/', '\App\Dede\Controllers\ContentController@add')->name('admin.content.create');
        \Illuminate\Support\Facades\Route::post('/content/add/', '\App\Dede\Controllers\ContentController@create')->name('admin.content.create');


        \Illuminate\Support\Facades\Route::get('/siteConfig', '\App\Dede\Controllers\SiteConfigController@index')->name('admin.siteConfig');

//        \Illuminate\Support\Facades\Route::get('/content/edit/{}', '\App\Dede\Controllers\ContentController@edit')->name('admin.content.create');
//        \Illuminate\Support\Facades\Route::post('/content/add/', '\App\Dede\Controllers\ContentController@create')->name('admin.content.create');



    });

//    \Illuminate\Support\Facades\Route::group(['prefix' => 'tool'], function () {
//        \Illuminate\Support\Facades\Route::get('/modal/move', '\App\Dede\Controllers\ModalController@move')->name('admin.uploader');
//    });

//    \Illuminate\Routing\Route::$validators;
//    \Symfony\Component\Routing\Route::class;
//    \Symfony\Component\Routing\Annotation\Route::class;


});



