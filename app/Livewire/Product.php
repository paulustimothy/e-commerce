<?php

namespace App\Livewire;

use App\Helpers\CartManagement;
use App\Livewire\Partials\Navbar;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product as ModelProduct;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Products')]

class Product extends Component
{

    use WithPagination;
    use LivewireAlert;

    #[Url]
    public $selected_categories = [];
    #[Url]
    public $selected_brands = [];
    public $featured = [];
    public $sale = [];
    public $price_range = 0;
    public $sort = 'latest';

    // add product to cart method
    public function addToCart($product_id)
    {
        $total_count = CartManagement::addItemToCart($product_id);

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
        $products = ModelProduct::query('is_active', 1);

        if (!empty($this->selected_categories)) {
            $products->whereIn('category_id', $this->selected_categories);
        }
        if (!empty($this->selected_brands)) {
            $products->whereIn('brand_id', $this->selected_brands);
        }
        if ($this->featured) {
            $products->where('is_featured', 1);
        }
        if ($this->sale) {
            $products->where('on_sale', 1);
        }
        if ($this->price_range && $this->price_range != 10000000) {
            $products->whereBetween('price', [0, $this->price_range]);
        }

        if ($this->sort == 'latest') {
            $products->latest();
        }
        if ($this->sort == 'price') {
            $products->orderBy('price');
        }

        $categories = Category::where('is_active', 1)->get();
        $brands = Brand::where('is_active', 1)->get();
        return view('livewire.product', [
            'products' => $products->paginate(9),
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }
}
