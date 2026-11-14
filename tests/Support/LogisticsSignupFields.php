<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;

// The company, service area, office address and document fields of the 7-step logistics partner sign-up.
trait LogisticsSignupFields
{
    private function logisticsSignupFields(): array
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::fake('local');
        return [
            'business_name' => 'Cavite Delivery', 'sec_registration_no' => 'cs 202012345',
            'tin_number' => '123-456-789-000', 'contact_position' => 'Operations Manager',
            'service_regions' => ['NCR', 'IV-A'],
            'office_region' => 'IV-A', 'office_province' => 'Cavite', 'office_city' => 'Imus',
            'office_barangay' => 'Alapan I-A', 'office_street' => '12 Aguinaldo Hwy', 'office_postal_code' => '4103',
            'sec_certificate' => UploadedFile::fake()->create('sec.pdf', 20, 'application/pdf'),
            'bir_certificate' => UploadedFile::fake()->create('bir.pdf', 20, 'application/pdf'),
            'business_permit' => UploadedFile::fake()->create('permit.pdf', 20, 'application/pdf'),
            'id_photo' => UploadedFile::fake()->create('id.pdf', 20, 'application/pdf'),
        ];
    }
}
