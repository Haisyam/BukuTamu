<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GuestLog extends Model
{
    use HasFactory;

    /**
     * Supported social media platforms.
     *
     * @var array<string, string>
     */
    public const SOCIAL_MEDIA_PLATFORMS = [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'division_id',
        'name',
        'phone',
        'social_media_platform',
        'social_media_handle',
        'institution',
        'person_to_meet',
        'purpose',
        'photo_path',
        'visit_date',
        'visit_time',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visit_date' => 'date:Y-m-d',
        ];
    }

    /**
     * Relationship to Division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get full URL or public asset path to the photo.
     */
    public function getPhotoUrlAttribute(): string
    {
        if (!empty($this->photo_path) && Storage::disk('public')->exists($this->photo_path)) {
            return asset('storage/' . $this->photo_path);
        }

        return asset('images/ekraf.png');
    }

    /**
     * Normalize social media platform & handle before saving.
     *
     * @return array{platform: ?string, handle: ?string}
     */
    public static function normalizeSocialMedia(?string $platform, ?string $handle): array
    {
        $cleanHandle = strip_tags(trim((string) $handle));

        if ($cleanHandle === '') {
            return [
                'platform' => null,
                'handle' => null,
            ];
        }

        $cleanPlatform = strtolower(trim((string) $platform));
        if (!array_key_exists($cleanPlatform, self::SOCIAL_MEDIA_PLATFORMS)) {
            $cleanPlatform = 'instagram';
        }

        $cleanHandle = '@' . ltrim($cleanHandle, '@');

        return [
            'platform' => $cleanPlatform,
            'handle' => $cleanHandle,
        ];
    }

    /**
     * Human-readable social media platform label.
     */
    public function getSocialMediaPlatformLabelAttribute(): ?string
    {
        if (empty($this->social_media_platform)) {
            return null;
        }

        return self::SOCIAL_MEDIA_PLATFORMS[$this->social_media_platform] ?? ucfirst($this->social_media_platform);
    }

    /**
     * Combined display text for reports and exports.
     */
    public function getSocialMediaDisplayAttribute(): ?string
    {
        if (empty($this->social_media_handle)) {
            return null;
        }

        $label = $this->social_media_platform_label;

        return $label ? "{$label}: {$this->social_media_handle}" : $this->social_media_handle;
    }

    /**
     * Direct profile URL for the guest's social media account.
     */
    public function getSocialMediaUrlAttribute(): ?string
    {
        if (empty($this->social_media_handle) || empty($this->social_media_platform)) {
            return null;
        }

        $username = rawurlencode(ltrim($this->social_media_handle, '@'));

        return match ($this->social_media_platform) {
            'instagram' => "https://instagram.com/{$username}",
            'tiktok' => "https://www.tiktok.com/@{$username}",
            'youtube' => "https://www.youtube.com/@{$username}",
            default => null,
        };
    }
}
