<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Debt;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->create([
            'name' => 'Admin Agri CI',
            'email' => 'admin@agrici.ci',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $supervisorAbidjan = User::query()->create([
            'name' => 'Awa Kouame',
            'email' => 'supervisor.abidjan@agrici.ci',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
        ]);

        $supervisorYamoussoukro = User::query()->create([
            'name' => 'Moussa Traore',
            'email' => 'supervisor.yamoussoukro@agrici.ci',
            'password' => Hash::make('password'),
            'role' => User::ROLE_SUPERVISOR,
        ]);

        $operatorAbidjan = User::query()->create([
            'name' => "Koffi N'Guessan",
            'email' => 'operator.abidjan@agrici.ci',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OPERATOR,
            'supervisor_id' => $supervisorAbidjan->id,
        ]);

        User::query()->create([
            'name' => 'Mariam Bamba',
            'email' => 'operator.yamoussoukro@agrici.ci',
            'password' => Hash::make('password'),
            'role' => User::ROLE_OPERATOR,
            'supervisor_id' => $supervisorYamoussoukro->id,
        ]);

        Setting::query()->insert([
            ['key' => 'credit_interest_rate', 'value' => '0.30', 'description' => 'Interest applied to credit transactions.'],
            ['key' => 'commodity_rate_fcfa_per_kg', 'value' => '1000', 'description' => 'FCFA conversion rate per kg of commodity.'],
        ]);

        $categories = [];
        foreach ([
            'Pesticides' => ['Herbicides', 'Insecticides', 'Fungicides'],
            'Fertilizers' => ['NPK', 'Urea', 'Organic Fertilizers'],
            'Seeds' => ['Maize', 'Rice', 'Vegetable Seeds'],
        ] as $parentName => $children) {
            $parent = Category::query()->create([
                'name' => $parentName,
                'slug' => str($parentName)->slug(),
            ]);

            foreach ($children as $childName) {
                $categories[$childName] = Category::query()->create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => str($childName)->slug(),
                ]);
            }
        }

        foreach ([
            ['Herbicide Glypho 1L', 'Herbicides', 4500, 'Broad-spectrum herbicide for pre-planting weed control.'],
            ['Herbicide Selectif 500ml', 'Herbicides', 3500, 'Selective herbicide for vegetable plots.'],
            ['Insecticide CacaoProtect 1L', 'Insecticides', 6000, 'Insecticide for cocoa plantation protection.'],
            ['Fungicide Plantation Plus', 'Fungicides', 5500, 'Fungicide for humid plantation conditions.'],
            ['NPK 15-15-15 50kg', 'NPK', 22000, 'Balanced fertilizer for field crops.'],
            ['Urea 46% 50kg', 'Urea', 24000, 'Nitrogen fertilizer for crop growth support.'],
            ['Compost Bio 25kg', 'Organic Fertilizers', 8000, 'Organic compost for soil improvement.'],
            ['Semences Mais Jaune 5kg', 'Maize', 12000, 'Improved yellow maize seeds.'],
            ['Semences Riz Nerica 10kg', 'Rice', 15000, 'NERICA rice seeds for local production.'],
            ['Semences Tomate 100g', 'Vegetable Seeds', 3000, 'Tomato seed sachet for market gardening.'],
        ] as [$name, $category, $price, $description]) {
            Product::query()->create([
                'category_id' => $categories[$category]->id,
                'name' => $name,
                'price_fcfa' => $price,
                'description' => $description,
                'is_active' => true,
            ]);
        }

        $farmers = collect([
            ['FCI-0001', 'Jean', 'Kouadio', '+2250701010101', 100000],
            ['FCI-0002', 'Aminata', 'Kone', '+2250502020202', 75000],
            ['FCI-0003', 'Yao', "N'Dri", '+2250103030303', 50000],
            ['FCI-0004', 'Fatou', 'Cisse', '+2250704040404', 120000],
        ])->map(fn (array $farmer) => Farmer::query()->create([
            'identifier' => $farmer[0],
            'firstname' => $farmer[1],
            'lastname' => $farmer[2],
            'phone' => $farmer[3],
            'credit_limit_fcfa' => $farmer[4],
        ]));

        $demoFarmer = $farmers->firstWhere('identifier', 'FCI-0002');

        foreach ([13000, 26000] as $amount) {
            $transaction = Transaction::query()->create([
                'farmer_id' => $demoFarmer->id,
                'operator_id' => $operatorAbidjan->id,
                'total_fcfa' => (int) round($amount / 1.3),
                'payment_method' => Transaction::PAYMENT_CREDIT,
                'interest_rate' => 0.30,
                'interest_amount_fcfa' => $amount - (int) round($amount / 1.3),
                'credited_total_fcfa' => $amount,
                'status' => 'open',
            ]);

            Debt::query()->create([
                'transaction_id' => $transaction->id,
                'farmer_id' => $demoFarmer->id,
                'original_amount_fcfa' => $amount,
                'paid_amount_fcfa' => 0,
                'remaining_amount_fcfa' => $amount,
                'status' => Debt::STATUS_OPEN,
            ]);
        }

        $admin->tokens()->delete();
    }
}
