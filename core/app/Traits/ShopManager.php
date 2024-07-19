<?php

namespace App\Traits;

use App\Exceptions\ShopCreationError;
use App\Models\GeneralSetting;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use App\Rules\FileTypeValidate;
use Illuminate\Support\Facades\Hash;

trait ShopManager
{
    public function updateShop(Shop $shop, $request): Shop
    {
        $logoValidation = $coverValidation = 'required';

        $request->validate([
            'name'                  => 'required|string|max:40',
            'phone'                 => 'required|string|max:40',
            'address'               => 'required|string|max:600',
            'opening_time'          => 'nullable|date_format:H:i',
            'closing_time'          => 'nullable|date_format:H:i',
            'meta_title'            => 'nullable|string|max:191',
            'meta_description'      => 'nullable|string|max:191',
            'meta_keywords'         => 'nullable|array',
            'meta_keywords.array.*' => 'string',
            'social_links'          => 'nullable|array',
            'social_links.*.name'   => 'required_with:social_links|string',
            'social_links.*.icon'   => 'required_with:social_links|string',
            'social_links.*.link'   => 'required_with:social_links|string',

            'image'                 => ['nullable', 'image',new FileTypeValidate(['jpg','jpeg','png'])],
            'cover_image'           => ['nullable', 'image',new FileTypeValidate(['jpg','jpeg','png'])]
        ],[
            'firstname.required'                  => 'First name field is required',
            'lastname.required'                   => 'Last name field is required',
            'social_links.*.name.required_with'   => 'All specification name is required',
            'social_links.*.icon.required_with'   => 'All specification icon is required',
            'social_links.*.link.required_with'   => 'All specification link is required',
            'image.required'                      => 'Logo is required',
            'cover_image.required'                => 'Cover is required'
        ]);

        return $this->saveShop($shop, $request);
    }

    public function createNewShop($request): Shop
    {
        if($request->user()->seller_id){
            throw new ShopCreationError('User already own a shop');
        };

        $request->validate([
            'name'                  => 'required|string|max:40',
            'phone'                 => 'required|string|max:40',
            'address'               => 'required|string|max:600',
            'opening_time'          => 'nullable|date_format:H:i',
            'closing_time'          => 'nullable|date_format:H:i',
            'meta_title'            => 'nullable|string|max:191',
            'meta_description'      => 'nullable|string|max:191',
            'meta_keywords'         => 'nullable|array',
            'meta_keywords.array.*' => 'string',
            'social_links'          => 'nullable|array',
            'social_links.*.name'   => 'required_with:social_links|string',
            'social_links.*.icon'   => 'required_with:social_links|string',
            'social_links.*.link'   => 'required_with:social_links|string',

            'image'                 => ['required', 'image',new FileTypeValidate(['jpg','jpeg','png'])],
            'cover_image'           => ['nullable', 'image',new FileTypeValidate(['jpg','jpeg','png'])]
        ],[
            'firstname.required'                  => 'First name field is required',
            'lastname.required'                   => 'Last name field is required',
            'social_links.*.name.required_with'   => 'All specification name is required',
            'social_links.*.icon.required_with'   => 'All specification icon is required',
            'social_links.*.link.required_with'   => 'All specification link is required',
            'image.required'                      => 'Logo is required',
            'cover_image.required'                => 'Cover is required'
        ]);

        $shop = $this->saveShop(new Shop, $request);
        $request->user()->update([
            'seller_id' => $shop->seller_id
        ]);

        return $shop;
    }

    private function saveShop(Shop $shop, $request)
    {
        /** @var User $user */
        $user = $request->user();
        $seller = $user->seller;

        if(!$seller){
            $seller = $this->createSeller([
                'fullname' => $user->fullname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'address' => $user->address,
                'country' => $user->country
            ]);
        }

        if ($request->hasFile('image')) {
            $location       = imagePath()['seller']['shop_logo']['path'];
            $size           = imagePath()['seller']['shop_logo']['size'];
            $shop->logo     = uploadImage($request->image, $location, $size, @$shop->logo);
        }

        $shop->name              = $request->name;
        $shop->seller_id         = $seller->id;
        $shop->user_id         = $user->id;
        $shop->status         = 1;
        $shop->phone             = $request->phone;
        $shop->address           = $request->address;
        $shop->opens_at          = $request->opening_time;
        $shop->closed_at         = $request->closing_time;
        $shop->meta_title        = $request->meta_title;
        $shop->meta_description  = $request->meta_description;
        $shop->meta_keywords     = $request->meta_keywords??null;
        $shop->social_links      = $request->social_links??null;
        $shop->save();

        return $shop;
    }

    public function createSeller(array $data)
    {
        $seller = new Seller();
        $seller->fullname  = $data['fullname'];
        $seller->email        = strtolower(trim($data['email']));
        $seller->password     = ' ';
        $seller->username     = ' ';
        $seller->country_code = ' ';
        $seller->mobile = $data['mobile'];

        $seller->address = [
            'address'   => $data['address'],
            'state'     => '',
            'zip'       => '',
            'country'   => isset($data['country']) ? $data['country'] : null,
            'city'      => ''
        ];

        $general = GeneralSetting::first();

        $seller->status = 1;
        $seller->ev = $general->ev ? 0 : 1;
        $seller->sv = $general->sv ? 0 : 1;
        $seller->save();

        return $seller;
    }
}
