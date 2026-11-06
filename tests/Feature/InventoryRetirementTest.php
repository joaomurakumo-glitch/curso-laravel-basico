<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryRetirementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_link_to_inventory(): void
    {
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Estoque');
    }

    public function test_inventory_endpoints_are_no_longer_available(): void
    {
        $this->get('/estoque')->assertNotFound();
        $this->post('/estoque')->assertNotFound();
        $this->post('/estoque/1/saidas')->assertNotFound();
        $this->put('/estoque/1/saidas/1')->assertNotFound();
    }
}
