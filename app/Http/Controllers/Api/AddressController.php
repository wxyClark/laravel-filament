<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;

class AddressController extends Controller
{
    public function __construct(
        private readonly AddressService $addressService
    ) {}

    public function index(): JsonResponse
    {
        $addresses = $this->addressService->getAllAddresses();

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function tree(): JsonResponse
    {
        $tree = $this->addressService->getAddressTree();

        return response()->json([
            'data' => $tree,
        ]);
    }

    public function byLevel(string $level): JsonResponse
    {
        $addresses = $this->addressService->getByLevel($level);

        return response()->json([
            'data' => $addresses,
        ]);
    }

    public function findByCode(string $code): JsonResponse
    {
        $address = $this->addressService->findByCode($code);

        if (! $address) {
            return response()->json([
                'message' => '地址不存在',
            ], 404);
        }

        return response()->json([
            'data' => $address,
        ]);
    }
}
