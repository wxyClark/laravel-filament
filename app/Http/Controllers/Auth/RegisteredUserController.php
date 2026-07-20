<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CustomerRegisterRequest;
use App\Services\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(CustomerRegisterRequest $request): RedirectResponse
    {
        $customer = $this->customerService->register($request->validated());

        Auth::guard('customer')->login($customer);

        return redirect('/');
    }
}
