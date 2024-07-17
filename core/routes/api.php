<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
	\Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::group([], function () {
	Route::get('general-setting', 'BasicController@generalSetting');
	Route::get('unauthenticate', 'BasicController@unauthenticate')->name('unauthenticate');
	Route::get('languages', 'BasicController@languages');
	Route::get('language-data/{code}', 'BasicController@languageData');

	//products
	Route::resource('products', 'ProductController')->only('index');

	Route::group(['middleware' => 'auth.api:sanctum'], function () {
		Route::apiResource('products', 'ProductController')->except('index');
		Route::get('seller-products', 'ProductController@sellerProducts');
		Route::apiResource('carts', 'CartController');
		Route::apiResource('checkout', 'CheckoutController');
		Route::apiResource('rate', 'RateController');
		Route::post('pay', 'HandlePaymentController');

        Route::apiResource('kyc', 'KycController');
        Route::get('kyc-data', 'KycController@kycData');


        // Deposit
        Route::get('deposit/methods', 'PaymentController@depositMethods');
        Route::post('deposit/insert', 'PaymentController@store');
        Route::get('deposit/confirm', 'PaymentController@depositConfirm');

        Route::get('deposit/manual', 'PaymentController@manualDepositConfirm');
        Route::post('deposit/manual', 'PaymentController@manualDepositUpdate');

        Route::get('deposit/history', 'UserController@depositHistory');

        Route::get('transactions', 'TransactionController@transactions');
        Route::post('withdraw', 'TransactionController@withdraw');
        Route::get('withdrawal-method', 'TransactionController@withdrawalMethod');

        //escrow
        Route::post('escrow-accept', 'TransactionController@escrowAccept');
        Route::post('escrow-reject', 'TransactionController@escrowReject');
        Route::get('get-escrow', 'TransactionController@escrows');

        //support ticket
        Route::apiResource('support', 'SupportTicketController');
        Route::apiResource('profile', 'ProfileController');

        //shops
        Route::apiResource('shops', 'ShopController');
        Route::get('shop-stats', 'ShopController@stat');
        Route::apiResource('messages', 'MessagesController');

        Route::apiResource('order', 'OrderController');

        //user
        Route::get('notifications', 'UserController@notifications');
	});


	Route::namespace('Auth')->group(function () {
		Route::post('login', 'LoginController@login');
        Route::post('logout', 'LoginController@logout');
		Route::post('register', 'RegisterController@register');

		Route::post('password/email', 'ForgotPasswordController@sendResetCodeEmail');
		Route::post('password/verify-code', 'ForgotPasswordController@verifyCode');

		Route::post('password/reset', 'ResetPasswordController@reset');
	});


	Route::middleware('auth.api:sanctum')->name('user.')->prefix('user')->group(function () {
		Route::get('logout', 'Auth\LoginController@logout');
		Route::get('authorization', 'AuthorizationController@authorization')->name('authorization');
		Route::get('resend-verify', 'AuthorizationController@sendVerifyCode')->name('send.verify.code');
		Route::post('verify-email', 'AuthorizationController@emailVerification')->name('verify.email');
		Route::post('verify-sms', 'AuthorizationController@smsVerification')->name('verify.sms');
		Route::post('verify-g2fa', 'AuthorizationController@g2faVerification')->name('go2fa.verify');

		Route::middleware(['checkStatusApi'])->group(function () {
			Route::get('dashboard', function () {
				return auth()->user();
			});

			Route::post('profile-setting', 'UserController@submitProfile');
			Route::post('change-password', 'UserController@submitPassword');

			// Withdraw
			Route::get('withdraw/methods', 'UserController@withdrawMethods');
			Route::post('withdraw/store', 'UserController@withdrawStore');
			Route::post('withdraw/confirm', 'UserController@withdrawConfirm');
			Route::get('withdraw/history', 'UserController@withdrawLog');



		});
	});
});
