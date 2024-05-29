<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('My Orders Detail')]

class MyOrdersDetail extends Component
{
    public $order_id;

    public function mount($order_id)
    {
        $this->order_id = $order_id;
    }

    public function render()
    {
        $order_items = OrderItem::with('product')->where('order_id', $this->order_id)->get();
        $address = Address::where('order_id', $this->order_id)->first();
        $order = Order::where('id', $this->order_id)->first();

        if ($order->user_id <> auth()->id()) {
            abort(404);
        }

        return view('livewire.my-orders-detail', [
            'order_items' => $order_items,
            'address' => $address,
            'order' => $order,
        ]);
    }
}
