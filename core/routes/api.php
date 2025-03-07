<?php

use App\Http\Controllers\Api\DisputeController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

Route::group([], function () {
    Route::get('general-setting', 'BasicController@generalSetting');
    Route::get('banks', 'BasicController@banks');
    Route::post('verify-account-number', 'BasicController@verifyAccountNumber');
    Route::get('all-products', 'BasicController@allProducts');
    Route::get('unauthenticate', 'BasicController@unauthenticate')->name('unauthenticate');
    Route::get('languages', 'BasicController@languages');
    Route::get('language-data/{code}', 'BasicController@languageData');

    //search
    Route::post('search', 'SearchController@search');



    Route::group(['middleware' => 'auth.api:sanctum'], function () {
        Route::get('user/{id}', 'BasicController@user');
        //products
        Route::apiResource('products', 'ProductController');
        Route::get('seller-products', 'ProductController@sellerProducts');
        Route::get('stats/{id}', 'ProductController@stats');

        //delist
        Route::get('delist/{id}', 'ProductController@delist');
        Route::get('relist/{id}', 'ProductController@relist');

        Route::apiResource('carts', 'CartController');
        Route::apiResource('checkout', 'CheckoutController');
        Route::apiResource('rate', 'RateController');

        //user review
        Route::apiResource('review', 'ReviewController');

        //user review
        Route::apiResource('reply', 'ReplyController');

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

        //withdraw
        Route::resource('bank_accounts', 'BankAccountController');
        //        Route::post('withdraw-detail', 'TransactionController@withdrawDetails');
        //        Route::post('withdraw', 'TransactionController@withdraw');
        Route::post('withdraw', 'WithdrawalController@store');

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

        //profile
        Route::apiResource('profile', 'ProfileController');

        //shops
        Route::apiResource('shops', 'ShopController');
        Route::get('shop-stats', 'ShopController@stat');
        Route::apiResource('messages', 'MessagesController');

        //conversation
        Route::apiResource('conversations', 'ConversationController');

        Route::get('conversations/message/{id}', 'ConversationController@message');

        //orders
        Route::apiResource('order', 'OrderController');
        Route::get('orders/{type}', 'OrderController@pendingOrders');

        //sellers orders
        Route::get('orders/{type}/{status}', 'OrderController@pendingOrders');

        //offer to accept or reject
        Route::post('offer/{type}/{id}', 'CartController@offer');

        //user
        Route::get('notifications', 'UserController@notifications');
        Route::post('notifications', 'UserController@mark_notifications');

        //wishlist
        Route::apiResource('wishlist', 'WishlistController');

        //kyc
        Route::apiResource('kyc', 'KycController');

        //plans
        Route::apiResource('subscription', 'SubscriptionController');

        //subscription payment
        Route::post('subscription-payment', 'SubscriptionPaymentController');

        //Dispute & Refund
        Route::get('/order-refund', [RefundController::class, 'refund']);
        Route::get('/refund/{refund}', [RefundController::class, 'show']);
        Route::post('/orders/{order}/refund', [RefundController::class, 'requestRefund']);
        Route::put('/refunds/{refund}/close', [RefundController::class, 'closeRefund']);

        // Dispute routes
        Route::post('/orders/{order}/dispute', [DisputeController::class, 'createDispute']);
        Route::post('/disputes/{dispute}/reply', [DisputeController::class, 'replyToDispute']);
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
        Route::post('change-password', 'UserController@submitPassword');
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

            // Withdraw
            Route::get('withdraw/methods', 'UserController@withdrawMethods');
            Route::post('withdraw/store', 'UserController@withdrawStore');
            Route::post('withdraw/confirm', 'UserController@withdrawConfirm');
            Route::get('withdraw/history', 'UserController@withdrawLog');
        });
    });
});
