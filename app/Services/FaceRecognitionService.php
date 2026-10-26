<?php

namespace App\Services;

use Illuminate\Support\Collection;

class FaceRecognitionService
{
    /**
     * Maximum Euclidean distance threshold for a face to be considered a match.
     * face-api.js descriptors typically range 0.0 (identical) to ~1.0+
     * A threshold of 0.5 is strict enough to avoid false positives.
     */
    protected float $threshold = 0.5;

    /**
     * Minimum gap between the best match distance and the second-best match distance
     * to avoid "ambiguous" logins where two faces look too similar.
     */
    protected float $ambiguityGap = 0.1;

    /**
     * Normalize and validate a raw face descriptor array (must be 128 numeric values).
     *
     * @param  array  $raw
     * @return float[]|null  Returns a clean float array, or null if invalid.
     */
    public function normalizeDescriptor(array $raw): ?array
    {
        if (count($raw) !== 128) {
            return null;
        }

        $normalized = array_map('floatval', $raw);

        // Sanity-check: all values must be finite numbers
        foreach ($normalized as $val) {
            if (!is_finite($val)) {
                return null;
            }
        }

        return $normalized;
    }

    /**
     * Find the best matching user from a collection of staff candidates.
     *
     * Each candidate must have a `face_descriptor` JSON column containing a
     * 128-element float array (stored when the staff member registered their face).
     *
     * @param  float[]     $descriptor   The incoming 128-element face descriptor.
     * @param  Collection  $candidates   Eloquent collection of User models.
     * @return array{
     *     match: array{user: \App\Models\User, distance: float, confidence: float}|null,
     *     reason: string
     * }
     */
    public function resolveLoginMatch(array $descriptor, Collection $candidates): array
    {
        $best       = null;
        $bestDist   = PHP_FLOAT_MAX;
        $secondDist = PHP_FLOAT_MAX;

        foreach ($candidates as $user) {
            $stored = $user->face_descriptor;

            // face_descriptor may be stored as JSON string
            if (is_string($stored)) {
                $stored = json_decode($stored, true);
            }

            if (!is_array($stored) || count($stored) !== 128) {
                continue;
            }

            $distance = $this->euclideanDistance($descriptor, array_map('floatval', $stored));

            if ($distance < $bestDist) {
                $secondDist = $bestDist;
                $bestDist   = $distance;
                $best       = $user;
            } elseif ($distance < $secondDist) {
                $secondDist = $distance;
            }
        }

        if ($best === null || $bestDist > $this->threshold) {
            return [
                'match'  => $best ? [
                    'user'       => $best,
                    'distance'   => $bestDist,
                    'confidence' => $this->distanceToConfidence($bestDist),
                ] : null,
                'reason' => 'no_match',
            ];
        }

        // Ambiguity check: if two faces are too close together, refuse the match
        if (($secondDist - $bestDist) < $this->ambiguityGap && $secondDist <= $this->threshold) {
            return [
                'match' => [
                    'user'       => $best,
                    'distance'   => $bestDist,
                    'confidence' => $this->distanceToConfidence($bestDist),
                ],
                'reason' => 'ambiguous_match',
            ];
        }

        return [
            'match' => [
                'user'       => $best,
                'distance'   => $bestDist,
                'confidence' => $this->distanceToConfidence($bestDist),
            ],
            'reason' => 'matched',
        ];
    }

    /**
     * Compute the Euclidean distance between two 128-element descriptor vectors.
     */
    protected function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff  = ($a[$i] ?? 0.0) - ($b[$i] ?? 0.0);
            $sum  += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Convert an Euclidean distance to a 0–100 confidence score.
     * Distance 0 → 100 %, distance >= threshold → 0 %.
     */
    protected function distanceToConfidence(float $distance): float
    {
        if ($distance >= $this->threshold) {
            return 0.0;
        }
        return round((1 - ($distance / $this->threshold)) * 100, 2);
    }
}
