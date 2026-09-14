<?php

namespace Tests\Feature;

use App\Jobs\ProcessTenantOrderJob;
use App\Models\Canteen;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Withdrawal;
use App\Modules\Catalog\Services\PublicCatalogQuery;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Canteen $canteenA;

    protected Canteen $canteenB;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected User $userTenantA;

    protected User $userTenantB;

    protected Menu $menuA;

    protected Menu $menuB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->canteenA = Canteen::create([
            'name' => 'Kantin A',
            'code' => 'KNT-A',
            'status' => 'active',
        ]);

        $this->canteenB = Canteen::create([
            'name' => 'Kantin B',
            'code' => 'KNT-B',
            'status' => 'active',
        ]);

        $this->userTenantA = User::create([
            'name' => 'Owner Tenant A',
            'email' => 'tenantA@kantin.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $this->userTenantB = User::create([
            'name' => 'Owner Tenant B',
            'email' => 'tenantB@kantin.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $this->tenantA = Tenant::create([
            'canteen_id' => $this->canteenA->id,
            'user_id' => $this->userTenantA->id,
            'name' => 'Stan A',
            'slug' => 'stan-a',
            'code' => 'STA',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'canteen_id' => $this->canteenA->id,
            'user_id' => $this->userTenantB->id,
            'name' => 'Stan B',
            'slug' => 'stan-b',
            'code' => 'STB',
            'status' => 'active',
        ]);

        $categoryA = Category::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Kategori A',
        ]);

        $categoryB = Category::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Kategori B',
        ]);

        $this->menuA = Menu::create([
            'tenant_id' => $this->tenantA->id,
            'category_id' => $categoryA->id,
            'name' => 'Menu Milik Stan A',
            'price' => 15000,
            'is_available' => true,
        ]);

        $this->menuB = Menu::create([
            'tenant_id' => $this->tenantB->id,
            'category_id' => $categoryB->id,
            'name' => 'Menu Milik Stan B',
            'price' => 20000,
            'is_available' => true,
        ]);
    }

    /**
     * 1. Test Tenant A cannot view Tenant B's menu in index.
     */
    public function test_tenant_a_cannot_view_tenant_b_menu_in_index(): void
    {
        $response = $this->actingAs($this->userTenantA)
            ->getJson("/tenant/{$this->tenantA->slug}/menus");

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Menu Milik Stan A'])
            ->assertJsonMissing(['name' => 'Menu Milik Stan B']);
    }

    /**
     * 2. Test Tenant A cannot view Tenant B's menu detail.
     */
    public function test_tenant_a_cannot_view_tenant_b_menu_detail(): void
    {
        $response = $this->actingAs($this->userTenantA)
            ->getJson("/tenant/{$this->tenantB->slug}/menus/{$this->menuB->id}");

        $response->assertStatus(403);
    }

    /**
     * 3. Test Tenant A cannot update Tenant B's menu.
     */
    public function test_tenant_a_cannot_update_tenant_b_menu(): void
    {
        $response = $this->actingAs($this->userTenantA)
            ->putJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}", [
                'name' => 'Hacked Name',
                'price' => 99999,
            ]);

        $response->assertNotFound();

        $this->assertDatabaseMissing('menus', [
            'id' => $this->menuB->id,
            'name' => 'Hacked Name',
        ]);
    }

    /**
     * 4. Test Tenant A cannot delete Tenant B's menu.
     */
    public function test_tenant_a_cannot_delete_tenant_b_menu(): void
    {
        $response = $this->actingAs($this->userTenantA)
            ->deleteJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}");

        $response->assertNotFound();

        $this->assertDatabaseHas('menus', [
            'id' => $this->menuB->id,
            'name' => 'Menu Milik Stan B',
        ]);
    }

    /**
     * 5. Test scoped route binding rejects menu belonging to another tenant under parent URL.
     */
    public function test_scoped_route_binding_rejects_menu_belonging_to_another_tenant(): void
    {
        $response = $this->actingAs($this->userTenantA)
            ->getJson("/tenant/{$this->tenantA->slug}/menus/{$this->menuB->id}");

        $response->assertNotFound();
    }

    /**
     * 6. Test Tenant A cannot view or update Tenant B's orders.
     */
    public function test_tenant_a_cannot_view_or_update_tenant_b_orders(): void
    {
        $orderB = Order::create([
            'canteen_id' => $this->canteenA->id,
            'tenant_id' => $this->tenantB->id,
            'order_number' => 'ORD-B-001',
            'idempotency_key' => 'IDEM-B-001',
            'total_amount' => 20000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->userTenantA)
            ->getJson("/tenant/{$this->tenantA->slug}/orders/{$orderB->id}");

        $response->assertNotFound();

        $updateResponse = $this->actingAs($this->userTenantA)
            ->putJson("/tenant/{$this->tenantA->slug}/orders/{$orderB->id}", [
                'status' => 'completed',
            ]);

        $updateResponse->assertNotFound();
        $this->assertEquals('pending', $orderB->fresh()->status);
    }

    /**
     * 7. Test Tenant A cannot access Tenant B's withdrawals.
     */
    public function test_tenant_a_cannot_access_tenant_b_withdrawals(): void
    {
        $withdrawalB = Withdrawal::create([
            'tenant_id' => $this->tenantB->id,
            'amount' => 50000,
            'status' => 'pending',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
        ]);

        $response = $this->actingAs($this->userTenantA)
            ->getJson("/tenant/{$this->tenantA->slug}/withdrawals/{$withdrawalB->id}");

        $response->assertNotFound();
    }

    /**
     * 8. Test inactive or suspended tenant is forbidden.
     */
    public function test_inactive_or_suspended_tenant_is_forbidden(): void
    {
        $suspendedUser = User::create([
            'name' => 'Suspended Owner',
            'email' => 'suspended@kantin.com',
            'password' => bcrypt('password'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $suspendedTenant = Tenant::create([
            'canteen_id' => $this->canteenA->id,
            'user_id' => $suspendedUser->id,
            'name' => 'Stan Suspended',
            'slug' => 'stan-suspended',
            'code' => 'STS',
            'status' => 'suspended',
        ]);

        $response = $this->actingAs($suspendedUser)
            ->getJson("/tenant/{$suspendedTenant->slug}/dashboard");

        $response->assertStatus(403);
    }

    /**
     * 9. Test public catalog query does not leak menus across canteens.
     */
    public function test_public_catalog_query_does_not_leak_menus_across_canteens(): void
    {
        $tenantInCanteenB = Tenant::create([
            'canteen_id' => $this->canteenB->id,
            'name' => 'Stan Kantin B',
            'slug' => 'stan-kantin-b',
            'code' => 'ST-KB',
            'status' => 'active',
        ]);

        $menuInCanteenB = Menu::create([
            'tenant_id' => $tenantInCanteenB->id,
            'name' => 'Menu Spesial Kantin B',
            'price' => 30000,
            'is_available' => true,
        ]);

        $catalogQuery = new PublicCatalogQuery;
        $canteenAMenus = $catalogQuery->getMenusForCanteen($this->canteenA->id);

        $this->assertTrue($canteenAMenus->contains('id', $this->menuA->id));
        $this->assertTrue($canteenAMenus->contains('id', $this->menuB->id));
        $this->assertFalse($canteenAMenus->contains('id', $menuInCanteenB->id));
    }

    /**
     * 10. Test queue jobs maintain isolated tenant contexts sequentially.
     */
    public function test_queue_jobs_maintain_isolated_tenant_contexts_sequentially(): void
    {
        $context = app(TenantContext::class);
        $this->assertFalse($context->has());

        $jobA = new ProcessTenantOrderJob($this->tenantA->id, 1);
        $jobA->handle($context);
        $this->assertFalse($context->has());

        $jobB = new ProcessTenantOrderJob($this->tenantB->id, 2);
        $jobB->handle($context);
        $this->assertFalse($context->has());
    }
}
