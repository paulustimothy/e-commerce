<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Product;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Product Detail')]

class ProductDetail extends Component
{
    public $slug;
    public $quantity = 1;
    use LivewireAlert;

    public function mount($slug)
    {
        return $this->slug = $slug;
    }

    public function increaseQty()
    {
        $this->quantity++;
    }

    public function decreaseQty()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCartWithQty($product_id, $this->quantity);

        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

        $this->alert('success', 'Product succesfully added!', [
            'position' => 'bottom-end',
            'timer' => 3000,
            'toast' => true,
            'timerProgressBar' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.product-detail', [
            'product' => Product::where('slug', $this->slug)->firstOrFail(),
        ]);
    }
}
