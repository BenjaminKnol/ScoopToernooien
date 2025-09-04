<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $teams = [
            ['name' => 'Biggetjes', 'color_name' => 'roze', 'color_hex' => '#ff66b2'],
            ['name' => 'Schapen',   'color_name' => 'wit',  'color_hex' => '#ffffff'],
            ['name' => 'Koeien',    'color_name' => 'zwart','color_hex' => '#000000'],
            ['name' => 'Kuiken',    'color_name' => 'geel', 'color_hex' => '#ffd60a'],
            ['name' => 'Haaien',    'color_name' => 'blauw','color_hex' => '#1e90ff'],
            ['name' => 'Kikker',    'color_name' => 'groen','color_hex' => '#22c55e'],
            ['name' => 'Vos',       'color_name' => 'rood', 'color_hex' => '#ef4444'],
            ['name' => 'Goudvis',   'color_name' => 'oranje','color_hex' => '#fb923c'],
        ];

        foreach ($teams as $data) {
            Team::updateOrCreate(
                ['name' => $data['name']],
                [
                    'points' => 0,
                    'color_name' => $data['color_name'],
                    'color_hex' => $data['color_hex'],
                ]
            );
        }
    }
}
