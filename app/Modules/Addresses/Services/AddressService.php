<?php

namespace App\Modules\Addresses\Services;

use App\Modules\Addresses\Models\Address;
use Illuminate\Database\Eloquent\Model;

class AddressService
{
  /**
   * Create or update address for a model
   */
  public function createOrUpdateAddress(Model $model, array $addressData): ?Address
  {
    if (empty($addressData)) {
      return null;
    }

    // Remove existing address if any
    $model->address()->delete();

    // Create new address
    return $model->address()->create([
      'addressable_id' => $model->id,
      'addressable_type' => get_class($model),
      'street' => $addressData['street'],
      'number' => $addressData['number'] ?? null,
      'complement' => $addressData['complement'] ?? null,
      'neighborhood' => $addressData['neighborhood'] ?? null,
      'city' => $addressData['city'],
      'state' => $addressData['state'],
      'zipcode' => $addressData['zipcode'],
      'country' => $addressData['country'] ?? 'Brasil',
    ]);
  }

  /**
   * Update address for a model
   */
  public function updateAddress(Model $model, array $addressData): ?Address
  {
    if (empty($addressData)) {
      return null;
    }

    $address = $model->address;

    if ($address) {
      $address->update([
        'street' => $addressData['street'],
        'number' => $addressData['number'] ?? null,
        'complement' => $addressData['complement'] ?? null,
        'neighborhood' => $addressData['neighborhood'] ?? null,
        'city' => $addressData['city'],
        'state' => $addressData['state'],
        'zipcode' => $addressData['zipcode'],
        'country' => $addressData['country'] ?? 'Brasil',
      ]);

      return $address->fresh();
    }

    // If no address exists, create one
    return $this->createOrUpdateAddress($model, $addressData);
  }

  /**
   * Delete address for a model
   */
  public function deleteAddress(Model $model): bool
  {
    $address = $model->address;

    if ($address) {
      return $address->delete();
    }

    return false;
  }
}

