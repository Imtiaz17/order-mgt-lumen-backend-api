<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    
    public static function makeOrder($data)
    {
        $business        =  Order::create([
            'customer_name'       => $data['customer_name'],
            'customer_email'   => $data['customer_email'],
            'customer_phone'      => $data['customer_phone'],
            'customer_address' => $data['customer_address'],
            'amount'   => $data['amount'],
            'product_name'   => $data['product_name'],
            'product_details'   => $data['product_details'],
        ]);

        return $business;
    }

    /**
     * Get model validation rules.
     *
     * @return array
     */
    public static function getValidationRules()
    {
        return [
            'name' => 'required',
            'price' => 'required',
            'description' => 'required',
        ];
    }
}
