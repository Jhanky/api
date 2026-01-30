<?php

namespace Database\Seeders;

use App\Models\Quotation;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['count' => 10, 'status_id' => 3, 'approved_at' => now()], // Approved
            ['count' => 20, 'status_id' => 1, 'approved_at' => null],  // Draft
            ['count' => 15, 'status_id' => 2, 'approved_at' => null],  // Sent
            ['count' => 5,  'status_id' => 4, 'approved_at' => null],  // Rejected
        ];

        foreach ($statuses as $config) {
            $quotations = Quotation::factory()->count($config['count'])->create([
                'status_id' => $config['status_id'],
                'approved_at' => $config['approved_at'],
            ]);

            foreach ($quotations as $quotation) {
                // ADD PRODUCTS (Panels, Inverters, Batteries)
                $this->addProducts($quotation);

                // ADD COMPLEMENTARY ITEMS
                $this->addItems($quotation);

                // RECALCULATE TOTALS
                $this->recalculateTotals($quotation);
            }
        }
    }

    private function addProducts(Quotation $quotation)
    {
        // Add random number of panels
        \App\Models\QuotationProduct::factory()->panel()->count(rand(1, 2))->create([
            'quotation_id' => $quotation->id,
        ]);

        // Add random number of inverters
        \App\Models\QuotationProduct::factory()->inverter()->count(rand(1, 2))->create([
            'quotation_id' => $quotation->id,
        ]);

        // Optionally add batteries (30% chance)
        if (rand(1, 100) <= 30) {
            \App\Models\QuotationProduct::factory()->battery()->count(rand(1, 2))->create([
                'quotation_id' => $quotation->id,
            ]);
        }
    }

    private function addItems(Quotation $quotation)
    {
        // Add random complementary items (cables, structures, etc.)
        \App\Models\QuotationItem::factory()->count(rand(3, 8))->create([
            'quotation_id' => $quotation->id,
        ]);
    }

    private function recalculateTotals(Quotation $quotation)
    {
        // Refresh relations
        $quotation->load(['quotationItems', 'quotationProducts']);

        // Sum products subtotal
        $productsTotal = $quotation->quotationProducts->sum(fn($p) => $p->quantity * $p->unit_price_cop);
        
        // Sum items subtotal
        $itemsTotal = $quotation->quotationItems->sum(fn($i) => $i->quantity * $i->unit_price_cop);

        $subtotal = $productsTotal + $itemsTotal;

        // Default percentages (User mentioned defaults, using typical values)
        // Ensure these match factory defaults if not overwriting
        $profitPct = $quotation->profit_percentage ?? 15.0;
        $adminPct = $quotation->administration_percentage ?? 5.0;
        $imprevPct = $quotation->contingency_percentage ?? 3.0; // Imprevistos
        $commPct = $quotation->commercial_management_percentage ?? 5.0; // Gestion Comercial
        $ivaPct = 19.0; // Standard VAT

        // Calculate values based on subtotal
        $profit = $subtotal * ($profitPct / 100);
        $admin = $subtotal * ($adminPct / 100);
        $contingency = $subtotal * ($imprevPct / 100);
        $comm = $subtotal * ($commPct / 100);
        
        // Calculate IVA on Profit (Utilidad) - assuming IVA applies to Profit only in AIU model, 
        // OR standard IVA on the whole amount. 
        // User request "recuerda que hay unos valores por defecto" suggests specific logic.
        // Looking at QuotationFactory logic:
        // 'iva_profit_percentage' => 19.00
        // 'profit_iva' => ($subtotal * 0.15) * 0.19 
        // It seems IVA is calculated on the Profit amount. 
        
        $profitIva = $profit * ($quotation->iva_profit_percentage / 100);

        // Current Factory Logic for Total:
        // 'total_value' => $subtotal * 1.3
        
        // Let's sum it up correctly:
        // Total = Subtotal + Admin + Improv + Comm + Profit + ProfitIVA
        // (This looks like an AIU + Commercial structure)
        
        $totalValue = $subtotal + $admin + $contingency + $comm + $profit + $profitIva;

        $quotation->update([
            'subtotal' => $subtotal,
            'profit' => $profit,
            'administration' => $admin,
            'contingency' => $contingency,
            'commercial_management' => $comm,
            'profit_iva' => $profitIva,
            'total_value' => $totalValue,
        ]);
    }
}
