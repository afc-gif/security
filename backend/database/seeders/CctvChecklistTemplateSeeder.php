<?php

namespace Database\Seeders;

use App\Models\CategoryChecklistTemplate;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CctvChecklistTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $category = ServiceCategory::updateOrCreate(
            ['name' => 'CCTV'],
            [
                'description' => 'CCTV installation, maintenance, repair, upgrade, and site assessment',
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
            $this->multi('Site Information - Property Type', ['Residential', 'Commercial', 'Industrial', 'Office', 'School', 'Estate', 'Farm', 'Warehouse', 'Other']),
            $this->multi('Site Information - Building Type', ['Single Storey', 'Multi-Storey', 'Apartment', 'Open Land', 'Construction Site', 'Other']),
            $this->single('Installation Type', ['New Installation', 'Existing System Upgrade', 'Expansion of Existing System', 'System Replacement', 'Maintenance / Repair']),
            $this->text('Existing System - Current Brand'),
            $this->text('Existing System - Current DVR/NVR Model'),
            $this->number('Existing System - Current Number of Cameras'),
            $this->long('Existing System - Reason for Upgrade'),
            $this->number('Camera Requirements - Indoor Cameras'),
            $this->number('Camera Requirements - Outdoor Cameras'),
            $this->number('Camera Requirements - Total Cameras'),
            $this->multi('Camera Types Required', ['Dome Camera', 'Bullet Camera', 'PTZ Camera', 'Turret Camera', 'Solar Camera', 'Wi-Fi Camera', 'IP Camera', 'Analog Camera', 'Thermal Camera', 'Other']),
            $this->single('Resolution Required', ['2MP', '4MP', '5MP', '8MP (4K)', 'Other']),
            $this->multi('Special Features Required', ['Night Vision', 'Colour Night Vision', 'Motion Detection', 'Human Detection', 'Vehicle Detection', 'Two-Way Audio', 'Built-in Siren', 'Spotlight', 'Facial Recognition', 'License Plate Recognition', 'Other']),
            $this->long('Camera Locations', 'Enter camera number, area to cover, mounting height, and indoor/outdoor for each point.'),
            $this->multi('Recording System Required', ['DVR', 'NVR', 'Cloud Recording', 'SD Card Recording', 'Hybrid System']),
            $this->multi('DVR/NVR Location Condition', ['Secure', 'Dry', 'Well Ventilated', 'Lockable']),
            $this->long('Distance from Camera Network'),
            $this->single('Has Cabling Already Been Installed?', ['Yes', 'No']),
            $this->multi('Existing Cable Type', ['Cat6', 'Cat5e', 'Coaxial Cable', 'Fibre', 'Unknown']),
            $this->single('Existing Cabling Condition', ['Excellent', 'Good', 'Fair', 'Poor']),
            $this->single('Additional Cabling Required?', ['Yes', 'No']),
            $this->multi('Cable Route', ['Ceiling', 'Wall', 'Underground', 'Conduit', 'Surface Mounted']),
            $this->text('Estimated Cable Length'),
            $this->single('Power Available at Installation Site?', ['Yes', 'No']),
            $this->multi('Power Source', ['Grid', 'Generator', 'Solar', 'Inverter', 'Other']),
            $this->single('Need Backup Power?', ['Yes', 'No']),
            $this->multi('Backup Type', ['UPS', 'Inverter', 'Solar Backup', 'Generator']),
            $this->text('Estimated Backup Duration'),
            $this->single('Internet Available?', ['Yes', 'No']),
            $this->multi('Connection Type', ['Fibre', 'Wi-Fi', 'Ethernet', 'Mobile Network', 'Starlink', 'Other']),
            $this->single('Remote Viewing Required?', ['Yes', 'No']),
            $this->single('Static IP Required?', ['Yes', 'No']),
            $this->single('SIM Card Required?', ['Yes', 'No']),
            $this->text('SIM Provider'),
            $this->single('Signal Strength', ['Excellent', 'Good', 'Fair', 'Poor']),
            $this->single('Monitor Required?', ['Yes', 'No']),
            $this->single('Customer Providing Monitor?', ['Yes', 'No']),
            $this->single('Installer to Supply Monitor?', ['Yes', 'No']),
            $this->single('Monitor Mount Required?', ['Yes', 'No']),
            $this->multi('Preferred Storage Method', ['Hard Drive', 'SSD', 'SD Cards', 'Cloud Storage']),
            $this->text('Estimated Storage Capacity'),
            $this->single('Solar Camera Required?', ['Yes', 'No']),
            $this->single('Pole Required?', ['Yes', 'No']),
            $this->text('Pole Height'),
            $this->single('Pole Foundation Required?', ['Yes', 'No']),
            $this->multi('Solar Panel Position', ['Roof', 'Pole', 'Wall']),
            $this->single('Sunlight Availability', ['Excellent', 'Good', 'Fair', 'Poor']),
            $this->single('Any Shading?', ['Yes', 'No']),
            $this->single('Current Security Level', ['Low', 'Medium', 'High']),
            $this->single('Site Occupied?', ['Yes', 'No']),
            $this->single('Site Access', ['Easy', 'Restricted', 'Difficult']),
            $this->multi('Risk Factors', ['Theft', 'Vandalism', 'Trespassing', 'Wildlife', 'Flooding', 'Dust', 'Extreme Heat', 'Other']),
            $this->multi('Accessories Required', ['Junction Boxes', 'Waterproof Boxes', 'Pole Brackets', 'Wall Brackets', 'Camera Mounts', 'Conduit', 'Trunking', 'Cable Clips', 'Cat6 Cable', 'Coaxial Cable', 'RJ45 Connectors', 'BNC Connectors', 'PoE Switch', 'Network Switch', 'Router', 'UPS', 'Surge Protector', 'Lightning Protection', 'Hard Drive', 'SD Cards', 'Monitor', 'TV Wall Mount', 'SIM Card', 'Solar Mounting Kit', 'Pole', 'Concrete Base', 'Warning Signage', 'Labels', 'Other']),
            $this->multi('Site Challenges / Special Considerations', ['Long Cable Runs', 'High Mounting Points', 'Difficult Roof Access', 'No Existing Power', 'Poor Internet', 'Weak Mobile Signal', 'Underground Obstacles', 'Security Restrictions', 'Weather Exposure', 'Other']),
            $this->long('Site Challenges - Comments'),
            $this->multi('Photographs Required', ['Front of Building', 'Entire Site', 'Camera Locations', 'DVR/NVR Location', 'Existing Cabling', 'Electrical Distribution Board', 'Power Points', 'Internet Router', 'Monitor Location', 'Pole Location', 'Roof (for Solar Cameras)', 'Access Routes', 'Existing CCTV Equipment']),
            $this->text('Recommended Camera Type'),
            $this->number('Recommended Number of Cameras'),
            $this->text('Recommended Recording System'),
            $this->text('Recommended Storage Capacity'),
            $this->text('Recommended Network Equipment'),
            $this->text('Recommended Power Solution'),
            $this->long('Recommended Accessories Required'),
            $this->long('Additional Recommendations'),
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
