<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'client_type_id',
        'document_type',
        'document_number',
        'nic',
        'email',
        'phone',
        'mobile',
        'address',
        'department_id',
        'city_id',
        'monthly_consumption_kwh',
        'tariff_cop_kwh',
        'responsible_user_id',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'monthly_consumption_kwh' => 'decimal:2',
        'tariff_cop_kwh' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the client type that owns the client.
     */
    public function clientType(): BelongsTo
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }

    /**
     * Get the department that owns the client.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the city that owns the client.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the responsible user for the client.
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Get the contact persons for the client.
     */
    public function contactPersons(): HasMany
    {
        return $this->hasMany(ClientContactPerson::class);
    }

    /**
     * Get the projects for the client.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the primary contact person.
     */
    public function primaryContact()
    {
        return $this->contactPersons()->where('is_primary', true)->first();
    }

    /**
     * Scope to get only active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by client type.
     */
    public function scopeOfType($query, $typeSlug)
    {
        return $query->whereHas('clientType', function ($q) use ($typeSlug) {
            $q->where('slug', $typeSlug);
        });
    }

    /**
     * Scope to filter by client type ID.
     */
    public function scopeByType($query, $typeId)
    {
        return $query->where('client_type_id', $typeId);
    }

    /**
     * Scope to filter by department.
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope to filter by city.
     */
    public function scopeByCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope to filter by responsible user.
     */
    public function scopeByResponsibleUser($query, $userId)
    {
        return $query->where('responsible_user_id', $userId);
    }

    /**
     * Scope to filter by consumption range.
     */
    public function scopeConsumptionRange($query, $min, $max = null)
    {
        $query->where('monthly_consumption_kwh', '>=', $min);

        if ($max !== null) {
            $query->where('monthly_consumption_kwh', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope to search by multiple fields.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('nic', 'like', "%{$searchTerm}%")
              ->orWhere('email', 'like', "%{$searchTerm}%")
              ->orWhere('phone', 'like', "%{$searchTerm}%")
              ->orWhere('mobile', 'like', "%{$searchTerm}%")
              ->orWhere('address', 'like', "%{$searchTerm}%")
              ->orWhere('document_number', 'like', "%{$searchTerm}%");
        });
    }


}
