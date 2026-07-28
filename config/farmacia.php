<?php

return [
    'nombre' => env('FARMACIA_NOMBRE', 'Farmacia Académica'),
    'nit' => env('FARMACIA_NIT'),
    'stock_bajo' => (int) env('FARMACIA_STOCK_BAJO', 5),
];
