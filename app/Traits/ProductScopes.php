<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait ProductScopes
{
    public function isLowStock(): bool
    {
        return $this->quantity < 10;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity === 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('quantity', '<', 10);
    }

    public function scopeBestSelling($query)
    {
        return $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.quantity',
                DB::raw('SUM(order_items.quantity) as total_sold')
           )
           ->join('order_items', 'order_items.product_id', '=', 'products.id')
           ->groupBy('products.id', 'products.name', 'products.price', 'products.quantity')
           ->having('total_sold', '>', 10)
           ->orderByDesc('total_sold')
           ->limit(10);
    }

    public function scopeCurrentMonthBestSelling($query)
    {
        return $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.quantity',
                DB::raw('SUM(order_items.quantity) as total_sold')
           )
           ->join('order_items', 'order_items.product_id', '=', 'products.id')
           ->join('orders', 'orders.id', '=', 'order_items.order_id')
           ->whereYear('orders.created_at', now()->year)
           ->whereMonth('orders.created_at', now()->month)
           ->groupBy('products.id', 'products.name', 'products.price', 'products.quantity')
           ->having('total_sold', '>', 500)
           ->orderByDesc('total_sold')
           ->limit(10);
    }

    public function scopePastMonthsHotProducts($query)
    {
        return $query->select(
                'products.id',
                'products.name',
                'products.price',
                'products.quantity',
                DB::raw('SUM(order_items.quantity) as total_sold')
           )
           ->join('order_items', 'order_items.product_id', '=', 'products.id')
           ->join('orders', 'orders.id', '=', 'order_items.order_id')
           ->where('orders.created_at', '>=', now()->subMonths(6))
           ->groupBy('products.id', 'products.name', 'products.price', 'products.quantity')
           ->having('total_sold', '>', 1000)
           ->orderByDesc('total_sold')
           ->limit(10);
    }

    public function scopeSearch($query, $term)
    {
        return $query->when($term, function ($query, $term): void {
            $query->where('name', 'LIKE', "%{$term}%");
        });
    }
}
