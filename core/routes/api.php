<?php

use App\Http\Controllers\Api\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
	\Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::group([], function () {
	Route::get('general-setting', 'BasicController@generalSetting');
	Route::get('all-products', 'BasicController@allProducts');
	Route::get('unauthenticate', 'BasicController@unauthenticate')->name('unauthenticate');
	Route::get('languages', 'BasicController@languages');
	Route::get('language-data/{code}', 'BasicController@languageData');



	Route::group(['middleware' => 'auth.api:sanctum'], function () {
        Route::get('user/{id}', 'BasicController@user');
		Route::apiResource('products', 'ProductController');
		Route::get('seller-products', 'ProductController@sellerProducts');
		Route::apiResource('carts', 'CartController');
		Route::apiResource('checkout', 'CheckoutController');
		Route::apiResource('rate', 'RateController');

        //user review
		Route::apiResource('review', 'ReviewController');

		Route::post('pay', 'HandlePaymentController');
		Route::post('direct-pay', 'OrderPaymentController');

        //kyc
        Route::apiResource('kyc', 'KycController');
        Route::get('kyc-data', 'KycController@kycData');


        // Deposit
        Route::get('deposit/methods', 'PaymentController@depositMethods');
        Route::post('deposit/insert', 'PaymentController@store');
        Route::get('deposit/confirm', 'PaymentController@depositConfirm');

        Route::get('deposit/manual', 'PaymentController@manualDepositConfirm');
        Route::post('deposit/manual', 'PaymentController@manualDepositUpdate');

        Route::get('deposit/history', 'UserController@depositHistory');

        //transactions
        Route::get('transactions', 'TransactionController@transactions');
        Route::post('withdraw', 'TransactionController@withdraw');
        Route::get('withdrawal-method', 'TransactionController@withdrawalMethod');

        //escrow
        Route::post('escrow-accept/{escrow}', 'TransactionController@escrowAccept');
        Route::post('escrow-complete/{escrow}', 'TransactionController@escrowComplete');
        Route::post('escrow-reject/{escrow}', 'TransactionController@escrowReject');
        Route::get('get-escrow', 'TransactionController@escrows');

        //Campaigns
        Route::apiResource('campaigns', 'CampaignController')->only('store', 'update');
        Route::get('campaign-data', 'CampaignController@campaignData');
        Route::get('plans', 'BasicController@plans');

        //support ticket
        Route::apiResource('support', 'SupportTicketController');
        Route::apiResource('profile', 'ProfileController');

        //shops
        Route::apiResource('shops', 'ShopController');
        Route::get('shop-stats', 'ShopController@stat');
        Route::apiResource('messages', 'MessagesController');

        //orders
        Route::apiResource('order', 'OrderController');
        Route::get('orders/{type}', 'OrderController@pendingOrders');

        //sellers orders
        Route::get('orders/{type}/{status}', 'OrderController@pendingOrders');

        //offer to accept or reject
        Route::post('offer/{type}', 'CartController@offer');

        //user
        Route::get('notifications', 'UserController@notifications');
        Route::post('notifications', 'UserController@mark_notifications');

        //wishlist
        Route::apiResource('wishlist', 'WishlistController');

        //plans
        Route::apiResource('subscription', 'SubscriptionController');

        //subscription payment
        Route::post('subscription-payment', 'SubscriptionPaymentController');
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
