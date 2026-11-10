<?php

namespace App\Models;

use Database\Factories\LaptopHireRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaptopHireRequest extends Model
{
    /** @use HasFactory<LaptopHireRequestFactory> */
    use HasFactory;

    protected $fillable = ['equipment_type', 'name', 'email', 'phone', 'pickup_date', 'number_of_items', 'desired_specs', 'status', 'admin_notes'];

    protected function casts(): array
    {
        return ['pickup_date' => 'date'];
    }
}
