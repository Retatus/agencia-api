<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Country::insert([
            [
                'name' => 'Perú',
                'iso' => 'PE',
                'phone_code' => '51',
                'flag' => '🇵🇪',
            ],
            [
                'name' => 'Bolivia',
                'iso' => 'BO',
                'phone_code' => '591',
                'flag' => '🇧🇴',
            ],
            [
                'name' => 'Ecuador',
                'iso' => 'EC',
                'phone_code' => '593',
                'flag' => '🇪🇨',
            ],
            [
                'name' => 'Colombia',
                'iso' => 'CO',
                'phone_code' => '57',
                'flag' => '🇨🇴',
            ],
            [
                'name' => 'Chile',
                'iso' => 'CL',
                'phone_code' => '56',
                'flag' => '🇨🇱',
            ],
            [
                'name' => 'Brasil',
                'iso' => 'BR',
                'phone_code' => '55',
                'flag' => '🇧🇷',
            ],
            [
                'name' => 'México',
                'iso' => 'MX',
                'phone_code' => '52',
                'flag' => '🇲🇽',
            ],
            [
                'name' => 'Estados Unidos',
                'iso' => 'US',
                'phone_code' => '1',
                'flag' => '🇺🇸',
            ],
            [
                'name' => 'España',
                'iso' => 'ES',
                'phone_code' => '34',
                'flag' => '🇪🇸',
            ],
            [
                'name' => 'Francia',
                'iso' => 'FR',
                'phone_code' => '33',
                'flag' => '🇫🇷',
            ],
            [
                'name' => 'Alemania',
                'iso' => 'DE',
                'phone_code' => '49',
                'flag' => '🇩🇪',
            ],
            [
                'name' => 'China',
                'iso' => 'CN',
                'phone_code' => '86',
                'flag' => '🇨🇳',
            ],
            [
                'name' => 'Japón',
                'iso' => 'JP',
                'phone_code' => '81',
                'flag' => '🇯🇵',
            ],
            [
                'name' => 'Corea del Sur',
                'iso' => 'KR',
                'phone_code' => '82',
                'flag' => '🇰🇷',
            ],
        ]);
    }
}