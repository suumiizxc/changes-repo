<?php


Route::group([ 'namespace' => '\JsLocalization\Http\Controllers' ], function()
{
    Route::get('/js-localization/messages', 'JsLocalizationController@createJsMessages');
    Route::get('/js-localization/config', 'JsLocalizationController@createJsConfig');
    Route::get('/js-localization/localization.js', 'JsLocalizationController@deliverLocalizationJS');

    Route::get('/js-localization/all.js', 'JsLocalizationController@deliverAllInOne');
});
