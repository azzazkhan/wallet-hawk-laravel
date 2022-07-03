<?php

namespace App\View\Components\Forms\Filters\Desktop;

use App\Helpers\JSON;
use Illuminate\View\Component;

class Form extends Component
{
    public array $filters = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->filters = JSON::parseFile('filters.json');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.forms.filters.desktop.form');
    }
}
