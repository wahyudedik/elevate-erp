<?php

namespace App\Models\ManagementCRM;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    // Nama tabel yang digunakan oleh model ini
    protected $table = 'sale_items';

    // Atribut yang dapat diisi secara massal
    protected $fillable = [
        'sale_id',
        'product_name',
        'quantity',
        'unit_price',  // Harga per unit
        'total_price',  // quantity * price
    ];

    // Atribut yang harus di-cast ke tipe data tertentu
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relasi dengan tabel sales
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // // Relasi dengan tabel products
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }
}
