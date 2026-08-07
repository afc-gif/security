<?php

namespace Database\Seeders;

use App\Models\CategoryChecklistTemplate;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class InverterChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::updateOrCreate(
            ['name' => 'Inverter'],
            [
                'description' => 'Inverter backup power, off-grid, hybrid solar, upgrade, and load assessment',
                'is_active' => true,
            ]
        );

        foreach ($this->items() as $index => $item) {
            CategoryChecklistTemplate::updateOrCreate(
                [
                    'service_category_id' => $category->id,
                    'title' => $item['title'],
                ],
                [
                    'description' => $item['description'] ?? null,
                    'input_type' => $item['input_type'],
                    'options' => $item['options'] ?? null,
                    'is_required' => $item['is_required'] ?? true,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    private function items(): array
    {
        return [
            $this->multi('Site Information - Property Type', ['Residential', 'Commercial', 'Industrial', 'Office', 'Other']),
            $this->multi('Site Information - Building Type', ['Single Storey', 'Double Storey', 'Apartment', 'Warehouse', 'Other']),
            $this->single('Site Information - Occupancy', ['Owner Occupied', 'Tenant', 'Under Construction']),
            $this->multi('Customer Requirements - Purpose of Installation', ['Backup Power', 'Off-Grid System', 'Hybrid Solar System', 'Existing Solar Upgrade', 'Load Reduction', 'Other']),
            $this->multi('Customer Requirements - Primary Concerns', ['Frequent Power Outages', 'Reduce Electricity Bills', 'Run Entire House', 'Essential Loads Only', 'Future Expansion']),
            $this->long('Customer Requirements - Notes'),
            $this->single('Existing Electrical Supply - Utility Supply', ['Single Phase', 'Three Phase']),
            $this->text('Existing Electrical Supply - Supply Voltage'),
            $this->text('Existing Electrical Supply - Main Breaker Rating'),
            $this->single('Existing Electrical Supply - Distribution Board Condition', ['Excellent', 'Good', 'Fair', 'Poor']),
            $this->single('Existing Electrical Supply - Photograph Taken', ['Yes', 'No']),
            $this->single('Existing Backup System - Existing Inverter?', ['Yes', 'No']),
            $this->text('Existing Backup System - Brand'),
            $this->text('Existing Backup System - Model'),
            $this->text('Existing Backup System - Capacity'),
            $this->single('Existing Backup System - Working Condition', ['Good', 'Faulty', 'Unknown']),
            $this->single('Existing Backup System - Existing Batteries', ['Yes', 'No']),
            $this->multi('Existing Backup System - Battery Type', ['Lithium', 'AGM', 'Gel', 'Tubular', 'Lead Acid']),
            $this->text('Existing Backup System - Battery Capacity'),
            $this->number('Existing Backup System - Number of Batteries'),
            $this->text('Existing Backup System - Battery Age'),
            $this->long('Load Assessment - Essential Loads', 'Enter appliance, quantity, power rating in watts, and hours used daily for lights, fans, TV, refrigerator, freezer, AC, water pump, computer, router, microwave, washing machine, and others.'),
            $this->text('Load Assessment - Total Estimated Load (Watts)'),
            $this->text('Load Assessment - Maximum Starting Load'),
            $this->single('Solar Assessment - Customer Wants Solar?', ['Yes', 'No']),
            $this->multi('Solar Assessment - Roof Type', ['Concrete', 'Corrugated Iron', 'Aluminium', 'Tile', 'Other']),
            $this->single('Solar Assessment - Roof Condition', ['Good', 'Fair', 'Poor']),
            $this->single('Solar Assessment - Roof Shading', ['None', 'Partial', 'Heavy']),
            $this->text('Solar Assessment - Available Roof Space'),
            $this->text('Solar Assessment - Estimated Solar Capacity'),
            $this->multi('Installation Location - Proposed Inverter Location', ['Utility Room', 'Garage', 'Store', 'Outdoor', 'Indoor Wall', 'Other']),
            $this->multi('Installation Location - Location Condition', ['Dry', 'Ventilated', 'Secure', 'Easily Accessible']),
            $this->text('Installation Location - Distance from Distribution Board'),
            $this->text('Installation Location - Distance from Battery Bank'),
            $this->text('Installation Location - Distance from Solar Array'),
            $this->multi('Battery Installation - Battery Location', ['Indoor', 'Outdoor', 'Battery Cabinet', 'Battery Rack']),
            $this->single('Battery Installation - Ventilation', ['Good', 'Poor', 'Needs Improvement']),
            $this->single('Battery Installation - Ambient Temperature', ['Cool', 'Moderate', 'Hot']),
            $this->text('Cabling Requirements - Estimated Cable Run'),
            $this->multi('Cabling Requirements - Cable Route', ['Concealed', 'Surface Mounted', 'Conduit Required']),
            $this->single('Cabling Requirements - Additional Trunking Required', ['Yes', 'No']),
            $this->single('Cabling Requirements - Earthing Available', ['Yes', 'No']),
            $this->single('Cabling Requirements - Lightning Protection Available', ['Yes', 'No']),
            $this->single('Safety Assessment - Working Area Safe', ['Yes', 'No']),
            $this->single('Safety Assessment - Fire Extinguisher Available', ['Yes', 'No']),
            $this->single('Safety Assessment - Adequate Ventilation', ['Yes', 'No']),
            $this->single('Safety Assessment - Access Restrictions', ['Yes', 'No']),
            $this->long('Safety Assessment - Comments'),
            $this->text('Site Measurements - Wall Space Height'),
            $this->text('Site Measurements - Wall Space Width'),
            $this->text('Site Measurements - Battery Space Length'),
            $this->text('Site Measurements - Battery Space Width'),
            $this->text('Site Measurements - Battery Space Height'),
            $this->multi('Photographs Required', ['Front of Building', 'Distribution Board', 'Electricity Meter', 'Proposed Inverter Location', 'Battery Location', 'Roof', 'Roof Access', 'Cable Route', 'Existing Electrical Installation', 'Existing Solar Installation', 'Earthing Point', 'Generator (if applicable)']),
            $this->text('Recommended System - Recommended Inverter Size'),
            $this->text('Recommended System - Battery Capacity'),
            $this->text('Recommended System - Battery Type'),
            $this->text('Recommended System - Solar Capacity'),
            $this->number('Recommended System - Number of Solar Panels'),
            $this->text('Recommended System - Charge Controller'),
            $this->multi('Recommended System - Additional Components Required', ['Changeover Switch', 'Surge Protection', 'Battery Cabinet', 'Mounting Structure', 'Additional Distribution Board', 'Monitoring System', 'Other']),
            $this->multi('Materials Estimate', ['Inverter', 'Batteries', 'Solar Panels', 'Wood', 'DC Cable', 'AC Cable', 'Battery Cable', 'DC Breakers', 'AC Breakers', 'Isolators', 'SPD', 'Conduit', 'Trunking', 'Cable Lugs', 'Earthing Kit', 'Labels', 'Other']),
            $this->long('Inspector Observations'),
            $this->long('Customer Approval', 'Confirm that the provided information is accurate and that the inspection has been carried out.'),
        ];
    }

    private function text(string $title, ?string $description = null): array
    {
        return ['title' => $title, 'description' => $description, 'input_type' => 'text'];
    }

    private function number(string $title): array
    {
        return ['title' => $title, 'input_type' => 'number'];
    }

    private function long(string $title, ?string $description = null): array
    {
        return ['title' => $title, 'description' => $description, 'input_type' => 'textarea'];
    }

    private function single(string $title, array $options): array
    {
        return ['title' => $title, 'input_type' => 'single_choice', 'options' => $options];
    }

    private function multi(string $title, array $options): array
    {
        return ['title' => $title, 'input_type' => 'multi_choice', 'options' => $options];
    }
}
