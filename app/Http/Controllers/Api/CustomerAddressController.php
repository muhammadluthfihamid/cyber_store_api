<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'addresses' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateAddress($request);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'address' => $address,
        ], 201);
    }

    public function show(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorizeCustomerAddress($request, $address);

        return response()->json(['address' => $address]);
    }

    public function update(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorizeCustomerAddress($request, $address);

        $validated = $this->validateAddress($request);

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Alamat berhasil diperbarui.',
            'address' => $address->fresh(),
        ]);
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorizeCustomerAddress($request, $address);
        $address->delete();

        return response()->json(['message' => 'Alamat berhasil dihapus.']);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'receiver_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'min:9', 'max:30'],
            'address' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_default' => ['sometimes', 'boolean'],
        ], [
            'label.required' => 'Label alamat wajib diisi.',
            'receiver_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor handphone wajib diisi.',
            'phone.min' => 'Nomor handphone minimal 9 digit.',
            'address.required' => 'Alamat lengkap wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
            'city.required' => 'Kota/Kabupaten wajib diisi.',
        ]);
    }

    private function authorizeCustomerAddress(Request $request, CustomerAddress $address): void
    {
        abort_if($address->user_id !== $request->user()->id, 403, 'Alamat bukan milik user ini.');
    }
}
