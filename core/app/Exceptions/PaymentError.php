<?php

namespace App\Exceptions;

class PaymentError extends AppError
{
    public function render()
    {
        return redirect('/dashboard')->with('error', $this->getMessage());
    }
}
