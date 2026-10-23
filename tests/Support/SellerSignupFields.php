<?php

namespace Tests\Support;

trait SellerSignupFields
{
    private function sellerSignupFields(): array
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $category = \App\Models\Category::firstOrCreate(['slug' => 'signup-test'], ['name' => 'Test category', 'is_active' => true]);
        return [
            'shop_name' => 'Test Shop', 'shop_category' => $category->id,
            'seller_type' => 'individual', 'tin_number' => '123456789000',
            'region' => 'NCR', 'province' => 'Metro Manila', 'city' => 'Manila',
            'barangay' => 'Barangay 1', 'street_address' => '12 Main Street', 'postal_code' => '1000',
            'id_photo' => \Illuminate\Http\UploadedFile::fake()->create('id.pdf', 20, 'application/pdf'),
        ];
    }
}
