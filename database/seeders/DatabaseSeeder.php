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
            ['key' => 'credit_interest_rate', 'value' => '0.30', 'description' => 'Interet applique aux transactions a credit.'],
            ['key' => 'commodity_rate_fcfa_per_kg', 'value' => '1000', 'description' => 'Taux de conversion FCFA par kg de marchandise.'],
        ]);

        $categories = [];
        foreach ([
            'Pesticides' => ['Herbicides', 'Insecticides', 'Fungicides'],
            'Engrais' => ['NPK', 'Uree', 'Engrais organiques'],
            'Semences' => ['Mais', 'Riz', 'Semences maraicheres'],
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
            ['Herbicide Glypho 1L', 'Herbicides', 4500, 'Herbicide a large spectre pour le controle des mauvaises herbes avant semis.'],
            ['Herbicide Selectif 500ml', 'Herbicides', 3500, 'Herbicide selectif pour les parcelles maraicheres.'],
            ['Insecticide CacaoProtect 1L', 'Insecticides', 6000, 'Insecticide pour la protection des plantations de cacao.'],
            ['Fungicide Plantation Plus', 'Fungicides', 5500, 'Fungicide adapte aux conditions humides des plantations.'],
            ['NPK 15-15-15 50kg', 'NPK', 22000, 'Engrais equilibre pour les cultures de plein champ.'],
            ['Uree 46% 50kg', 'Uree', 24000, 'Engrais azote pour soutenir la croissance des cultures.'],
            ['Compost Bio 25kg', 'Engrais organiques', 8000, 'Compost organique pour ameliorer la fertilite du sol.'],
            ['Semences Mais Jaune 5kg', 'Mais', 12000, 'Semences ameliorees de mais jaune.'],
            ['Semences Riz Nerica 10kg', 'Riz', 15000, 'Semences de riz NERICA pour la production locale.'],
            ['Semences Tomate 100g', 'Semences maraicheres', 3000, 'Sachet de semences de tomate pour le maraichage.'],
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
