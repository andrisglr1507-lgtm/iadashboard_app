<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];
    protected $table = 'products';

    /**
     * Accessor for productCategoryCode (camelCase alias)
     */
    public function getProductCategoryCodeAttribute()
    {
        return $this->attributes['product_category_code'] ?? null;
    }

    /**
     * Accessor for companyCode (camelCase alias)
     */
    public function getCompanyCodeAttribute()
    {
        return $this->attributes['company_code'] ?? null;
    }
}
