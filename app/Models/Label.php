<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    /** Form field => [label, required, ANSI MH10.8.2 data identifier or null] */
    public const FIELDS = [
        'part_number'      => ['Part Number', true, 'P'],
        'quantity'         => ['Quantity', true, 'Q'],
        'supplier_number'  => ['Supplier Number', true, 'V'],
        'vendor_batch_lot' => ['Vendor Batch Lot', true, '1T'],
        'part_description' => ['Part Description', false, null],
        'delivery_note'    => ['Delivery Note', false, '16K'],
        'purchase_order'   => ['Purchase Order', false, 'K'],
        'heat_lot'         => ['Heat Lot', false, 'ZA'],
        'date_code'        => ['Date Code', false, '9D'],
        'heat_code'        => ['Heat Code', false, null],
        'revision_level'   => ['Revision Level', false, '2P'],
        'supplier_name'    => ['Supplier Name', false, null],
        'from_location'    => ['From Location', false, null],
        'dana_facility'    => ['Dana Facility', false, null],
        'to_location'      => ['To Location', false, null],
    ];

    public function getFillable(): array
    {
        return [...array_keys(self::FIELDS), 'created_by'];
    }
}
