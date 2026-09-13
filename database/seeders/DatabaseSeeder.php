<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            EmployeeCategorySeeder::class,
            ProvinceSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            StoreSeeder::class,
            PaymentMethodSeeder::class,
            VenueSeeder::class,
            SponsorSeeder::class,
            CitySeeder::class,
            PurchaseSeeder::class,
            EventSeeder::class,
            EmployeeSeeder::class,
            CustomerSeeder::class,
            PurchaseDetailSeeder::class,
            EventSponsorSeeder::class,
            InvoiceSeeder::class,
            ReservationSeeder::class,
            AttendanceSeeder::class,
            InvoiceDetailSeeder::class,
            PaymentDetailSeeder::class,
        ]);
    }
}
