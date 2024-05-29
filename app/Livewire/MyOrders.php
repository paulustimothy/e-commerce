<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Orders')]

class MyOrders extends Component
{
    use WithPagination;
    protected $paginationTheme = 'simple-tailwind';

    public function render()
    {
        $my_orders = Order::where('user_id', auth()->id())->latest()->paginate(5);
        return view('livewire.my-orders', [
            'orders' => $my_orders,
        ]);
    }
}
