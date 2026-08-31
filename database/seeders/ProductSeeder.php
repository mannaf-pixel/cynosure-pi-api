<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['product_code'=>'CSC101','product_name'=>'Casement Outer Frame 60x60','category'=>'Casement','bundle_qty'=>6,'white_rate'=>236,'color_rate'=>487],
            ['product_code'=>'CSC102','product_name'=>'Casement Outer Frame 60x70','category'=>'Casement','bundle_qty'=>4,'white_rate'=>264,'color_rate'=>544],
            ['product_code'=>'CSC103','product_name'=>'Casement T Mullion 60x76','category'=>'Casement','bundle_qty'=>6,'white_rate'=>243,'color_rate'=>500],
            ['product_code'=>'CSC104','product_name'=>'Casement Door Sash 60x104','category'=>'Casement','bundle_qty'=>4,'white_rate'=>363,'color_rate'=>746],
            ['product_code'=>'CSC105','product_name'=>'Casement Window Sash 60x78','category'=>'Casement','bundle_qty'=>6,'white_rate'=>280,'color_rate'=>576],
            ['product_code'=>'CSC106','product_name'=>'Casement Bead 35x18 (5-6)','category'=>'Casement','bundle_qty'=>20,'white_rate'=>72,'color_rate'=>171],
            ['product_code'=>'CSC107','product_name'=>'Panel Profile 15x150','category'=>'Casement','bundle_qty'=>6,'white_rate'=>196,'color_rate'=>700],
            ['product_code'=>'CSC108','product_name'=>'Casement Small Frame 42x45','category'=>'Casement','bundle_qty'=>6,'white_rate'=>160,'color_rate'=>330],
            ['product_code'=>'CSC109','product_name'=>'Casement Bead 29.5x18','category'=>'Casement','bundle_qty'=>20,'white_rate'=>65,'color_rate'=>144],
            ['product_code'=>'CSC110','product_name'=>'Casement Bead 16x18','category'=>'Casement','bundle_qty'=>20,'white_rate'=>50,'color_rate'=>119],
            ['product_code'=>'CSC111','product_name'=>'Connector 2.5 Track and Casement','category'=>'Casement','bundle_qty'=>20,'white_rate'=>100,'color_rate'=>152],
            ['product_code'=>'CSC112','product_name'=>'SQ Bay Pole 60x60','category'=>'Casement','bundle_qty'=>6,'white_rate'=>170,'color_rate'=>500],
            ['product_code'=>'CSC113','product_name'=>'T Coupler 13x18','category'=>'Casement','bundle_qty'=>20,'white_rate'=>57,'color_rate'=>57],
            ['product_code'=>'CSC114','product_name'=>'Georgian Bar 25x8','category'=>'Casement','bundle_qty'=>20,'white_rate'=>50,'color_rate'=>66],
            ['product_code'=>'CSC115','product_name'=>'Louver Bead 48x18','category'=>'Casement','bundle_qty'=>20,'white_rate'=>64,'color_rate'=>132],
            ['product_code'=>'CSS201','product_name'=>'Sliding 2 Track Outer Frame 60x45','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>204,'color_rate'=>421],
            ['product_code'=>'CSS201A','product_name'=>'Sliding 2 Track Outer Frame 60x52','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>235,'color_rate'=>483],
            ['product_code'=>'CSS202','product_name'=>'Sliding 2.5 Track Outer Frame 88x45','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>288,'color_rate'=>595],
            ['product_code'=>'CSS203','product_name'=>'Sliding 3 Track Outer Frame 109x45','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>337,'color_rate'=>695],
            ['product_code'=>'CSS203A','product_name'=>'Sliding 3 Track Outer Frame 109x52','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>368,'color_rate'=>755],
            ['product_code'=>'CSS204','product_name'=>'Sliding Window Sash 57x42','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>203,'color_rate'=>419],
            ['product_code'=>'CSS205','product_name'=>'Sliding Door Sash 92x42','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>311,'color_rate'=>642],
            ['product_code'=>'CSS206','product_name'=>'Sliding T Mullion 70x42','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>198,'color_rate'=>408],
            ['product_code'=>'CSS207','product_name'=>'Sliding Bead 24.5 - 5mm','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>59,'color_rate'=>121],
            ['product_code'=>'CSS208','product_name'=>'Sliding Bead 14.5 - 15mm Panel','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>47,'color_rate'=>128],
            ['product_code'=>'CSS209','product_name'=>'Interlock 44.5x30.75','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>50,'color_rate'=>144],
            ['product_code'=>'CSS210','product_name'=>'Interlock 44.5x40.75','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>58,'color_rate'=>159],
            ['product_code'=>'CSS211','product_name'=>'Sliding Intermediate Sash 42x70','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>250,'color_rate'=>514],
            ['product_code'=>'CSS212','product_name'=>'Sliding Mesh 29x58','category'=>'Sliding','bundle_qty'=>8,'white_rate'=>141,'color_rate'=>290],
            ['product_code'=>'CSS213','product_name'=>'Sliding DG Bead 11.5x16','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>47,'color_rate'=>138],
            ['product_code'=>'CSS214','product_name'=>'Inline Adopter 46x14','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>64,'color_rate'=>172],
            ['product_code'=>'CSS215','product_name'=>'Interlock 44.5x46','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>60,'color_rate'=>163],
            ['product_code'=>'CSS216','product_name'=>'Sliding Intermediate Sash 42x66','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>242,'color_rate'=>500],
            ['product_code'=>'CSS301','product_name'=>'Sliding 2 Track Outer Frame 55x45','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>190,'color_rate'=>389],
            ['product_code'=>'CSS302','product_name'=>'Sliding 2.5 Track Outer Frame 80x45','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>254,'color_rate'=>523],
            ['product_code'=>'CSS303','product_name'=>'Sliding 3 Track Outer Frame 99x45','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>288,'color_rate'=>595],
            ['product_code'=>'CSS304','product_name'=>'Sliding Window Sash 57x37','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>183,'color_rate'=>379],
            ['product_code'=>'CSS305','product_name'=>'Bead 19.5x16 - 5mm','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>50,'color_rate'=>144],
            ['product_code'=>'CSS306','product_name'=>'Sliding Mesh 46x20.5','category'=>'Sliding','bundle_qty'=>8,'white_rate'=>92,'color_rate'=>188],
            ['product_code'=>'CSS307','product_name'=>'Interlock 39x30','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>45,'color_rate'=>134],
            ['product_code'=>'CSS308','product_name'=>'Sliding Door Sash 82x37','category'=>'Sliding','bundle_qty'=>4,'white_rate'=>268,'color_rate'=>553],
            ['product_code'=>'CSS309','product_name'=>'Inline Adopter 41x14','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>62,'color_rate'=>168],
            ['product_code'=>'CSS310','product_name'=>'Interlock 39x42','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>55,'color_rate'=>153],
            ['product_code'=>'CSS311','product_name'=>'Sliding Door Sash 64x37','category'=>'Sliding','bundle_qty'=>6,'white_rate'=>242,'color_rate'=>500],
            ['product_code'=>'CSS312','product_name'=>'Interlock 39x33.5','category'=>'Sliding','bundle_qty'=>20,'white_rate'=>47,'color_rate'=>138],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(
                ['product_code' => $p['product_code']],
                [
                    'product_name'   => $p['product_name'],
                    'category'       => $p['category'],
                    'white_rate'     => $p['white_rate'],
                    'color_rate'     => $p['color_rate'],
                    'unit'           => 'meter',
                    'profile_length' => 5.80,
                    'bundle_qty'     => $p['bundle_qty'],
                    'weight_per_meter' => 0,
                    'mrp'            => 0,
                    'discount_pct'   => 0,
                    'is_active'      => true,
                ]
            );
        }
        $this->command->info('46 products seeded!');
    }
}
