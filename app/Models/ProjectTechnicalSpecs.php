<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTechnicalSpecs extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'system_type_id',
        'grid_type_id',
        'network_operator_id',
        'installed_power_kwp',
        'panel_count',
        'panel_brand',
        'panel_model',
        'panel_power_watts',
        'inverter_count',
        'inverter_brand',
        'inverter_model',
        'inverter_power_kw',
        'battery_count',
        'battery_brand',
        'battery_model',
        'battery_capacity_kwh',
        'estimated_monthly_generation_kwh',
        'estimated_annual_generation_kwh',
        'structure_type',
        'installation_type',
        'tilt_angle',
        'azimuth',
        'technical_notes',
    ];

    protected $casts = [
        'installed_power_kwp' => 'decimal:2',
        'panel_count' => 'integer',
        'panel_power_watts' => 'decimal:2',
        'inverter_count' => 'integer',
        'inverter_power_kw' => 'decimal:2',
        'battery_count' => 'integer',
        'battery_capacity_kwh' => 'decimal:2',
        'estimated_monthly_generation_kwh' => 'decimal:2',
        'estimated_annual_generation_kwh' => 'decimal:2',
        'tilt_angle' => 'decimal:2',
        'azimuth' => 'decimal:2',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function systemType(): BelongsTo
    {
        return $this->belongsTo(SystemType::class, 'system_type_id');
    }

    public function gridType(): BelongsTo
    {
        return $this->belongsTo(GridType::class, 'grid_type_id');
    }

    public function networkOperator(): BelongsTo
    {
        return $this->belongsTo(NetworkOperator::class, 'network_operator_id');
    }

    // Helper methods
    public function getTotalPanelPower(): float
    {
        return ($this->panel_count ?? 0) * ($this->panel_power_watts ?? 0);
    }

    public function getTotalInverterPower(): float
    {
        return ($this->inverter_count ?? 0) * ($this->inverter_power_kw ?? 0);
    }

    public function hasBatteries(): bool
    {
        return !is_null($this->battery_count) && $this->battery_count > 0;
    }

    public function isOnGrid(): bool
    {
        return $this->systemType?->slug === 'on-grid';
    }

    public function isOffGrid(): bool
    {
        return $this->systemType?->slug === 'off-grid';
    }

    public function isHybrid(): bool
    {
        return $this->systemType?->slug === 'hibrido';
    }

    public function isInterconnected(): bool
    {
        return $this->systemType?->slug === 'interconectado';
    }

    public function getEfficiencyRatio(): float
    {
        if (!$this->installed_power_kwp || !$this->estimated_annual_generation_kwh) {
            return 0;
        }

        // Approximate efficiency calculation (kWh/kWp/year)
        return $this->estimated_annual_generation_kwh / $this->installed_power_kwp;
    }

    public function getEstimatedDailyGeneration(): float
    {
        return $this->estimated_monthly_generation_kwh * 12 / 365;
    }

    public function getPanelEfficiency(): float
    {
        // This would require more detailed panel specifications
        // For now, return a placeholder calculation
        return $this->panel_power_watts ? ($this->panel_power_watts / 1000) * 0.2 : 0; // Assuming 20% efficiency
    }
}
