<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\TaskReaction;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Team Members
        $janis = User::create([
            'name' => 'Jānis Bērziņš (Vadītājs)',
            'email' => 'janis@komanda.lv',
            'avatar' => '👨‍💼',
            'password' => Hash::make('password'),
        ]);

        $anna = User::create([
            'name' => 'Anna Ozola (Dizainere)',
            'email' => 'anna@komanda.lv',
            'avatar' => '👩‍🎨',
            'password' => Hash::make('password'),
        ]);

        $karlis = User::create([
            'name' => 'Kārlis Kalniņš (Izstrādātājs)',
            'email' => 'karlis@komanda.lv',
            'avatar' => '👨‍💻',
            'password' => Hash::make('password'),
        ]);

        $laura = User::create([
            'name' => 'Laura Liepiņa (Mārketings)',
            'email' => 'laura@komanda.lv',
            'avatar' => '👩‍💼',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Workspaces (Tenants)
        $acme = Tenant::create([
            'name' => 'Acme Komanda 🚀',
            'owner_id' => $janis->id,
            'invite_code' => 'ACME2026',
        ]);

        $personal = Tenant::create([
            'name' => 'Personīgā Darbavieta 💼',
            'owner_id' => $janis->id,
            'invite_code' => 'PERSONAL',
        ]);

        // Attach users to tenants
        $acme->users()->attach([
            $janis->id => ['role' => 'admin'],
            $anna->id => ['role' => 'member'],
            $karlis->id => ['role' => 'member'],
            $laura->id => ['role' => 'member'],
        ]);

        $personal->users()->attach([
            $janis->id => ['role' => 'admin'],
        ]);

        // Day calculations
        $now = Carbon::now();
        $friday = $now->copy()->next(Carbon::FRIDAY)->toDateString();
        if ($now->isFriday()) {
            $friday = $now->toDateString();
        } elseif ($now->isSaturday()) {
            $friday = $now->copy()->subDay()->toDateString();
        } elseif ($now->isSunday()) {
            $friday = $now->copy()->subDays(2)->toDateString();
        }
        $saturday = Carbon::parse($friday)->addDay()->toDateString();
        $sunday = Carbon::parse($friday)->addDays(2)->toDateString();

        // 3. Create Tasks in Backlog (scheduled_date = null)
        $task1 = Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $anna->id,
            'title' => 'Pārstrādāt mobilās saskarnes UX un navigāciju',
            'category' => 'projekti',
            'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80',
            'scheduled_date' => null,
            'is_completed' => false,
        ]);

        $task2 = Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $karlis->id,
            'title' => 'Serveru drošības audita un atjauninājumu izpilde',
            'category' => 'steidzami',
            'image_url' => 'https://images.unsplash.com/photo-1507925921958-8a62f3d1a50d?auto=format&fit=crop&w=600&q=80',
            'scheduled_date' => null,
            'is_completed' => false,
        ]);

        $task3 = Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $laura->id,
            'title' => 'Sagatavot ceturkšņa mārketinga kampaņas saturu',
            'category' => 'attistiba',
            'image_url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=600&q=80',
            'scheduled_date' => null,
            'is_completed' => false,
        ]);

        $task4 = Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $janis->id,
            'title' => 'Tikšanās ar jauno klientu par sadarbības līgumu',
            'category' => 'sanaksmes',
            'image_url' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=600&q=80',
            'scheduled_date' => null,
            'is_completed' => false,
        ]);

        $task5 = Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $karlis->id,
            'title' => 'Koda refaktorēšana un automātisko testu papildināšana',
            'category' => 'ikdienas',
            'image_url' => 'https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=600&q=80',
            'scheduled_date' => null,
            'is_completed' => false,
        ]);

        // Add reactions
        TaskReaction::create(['task_id' => $task1->id, 'user_id' => $janis->id, 'reaction' => 'thumbs_up']);
        TaskReaction::create(['task_id' => $task1->id, 'user_id' => $laura->id, 'reaction' => 'thumbs_up']);
        TaskReaction::create(['task_id' => $task2->id, 'user_id' => $janis->id, 'reaction' => 'thumbs_up']);

        // 4. Create Scheduled Tasks
        Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $janis->id,
            'title' => 'Komandas iknedēļas sinhronizācija un atskaites',
            'category' => 'sanaksmes',
            'scheduled_date' => $friday,
            'scheduled_time_slot' => '10:00',
            'is_completed' => true,
        ]);

        Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $karlis->id,
            'title' => 'Jaunās versijas izvietošana produkcijas vidē',
            'category' => 'steidzami',
            'scheduled_date' => $friday,
            'scheduled_time_slot' => '15:30',
            'is_completed' => false,
        ]);

        Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $anna->id,
            'title' => 'Projekta dizaina sistēmas dokumentācijas pabeigšana',
            'category' => 'projekti',
            'scheduled_date' => $saturday,
            'scheduled_time_slot' => '11:00',
            'is_completed' => false,
        ]);

        Task::create([
            'tenant_id' => $acme->id,
            'created_by_id' => $laura->id,
            'title' => 'Nākamās nedēļas sociālo tīklu plāna apstiprināšana',
            'category' => 'attistiba',
            'scheduled_date' => $sunday,
            'scheduled_time_slot' => '14:00',
            'is_completed' => false,
        ]);
    }
}
