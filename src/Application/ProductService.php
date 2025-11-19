<?php

namespace App\Application;

use App\Domain\Product;

class ProductService
{
    public function getAll(): array
    {
        // Tutaj w przyszłości repozytorium DB, teraz dummy data
        return [
            new Product(1, 'Produkt A'),
            new Product(2, 'Produkt B'),
        ];
    }
}
