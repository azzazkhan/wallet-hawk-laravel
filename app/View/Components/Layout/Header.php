<?php

namespace App\View\Components\Layout;

use App\Helpers\JSON;
use Illuminate\View\Component;

class Header extends Component
{
    public array|null $cryptos = null;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cryptos = JSON::parseFile('cryptos.json')['available'];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.layout.header');
    }
}
