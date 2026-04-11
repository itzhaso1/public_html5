<?php

return [
    // When true, blur/crop controls saved in dashboard settings table override .env values.
    // Set to false to use legacy .env-based behavior exactly.
    'use_dashboard_controls' => (bool) env('ACCOUNT_IMAGE_USE_DASHBOARD_CONTROLS', true),

    // Top strip processing for account images (main + gallery):
    // mode: blur | crop | none
    'top_area' => [
        // Backward compatible fallback to old var ACCOUNT_IMAGE_TOP_CROP_PX.
        'size_px' => (int) env('ACCOUNT_IMAGE_TOP_AREA_SIZE_PX', (int) env('ACCOUNT_IMAGE_TOP_CROP_PX', 35)),
        'mode' => env('ACCOUNT_IMAGE_TOP_AREA_MODE', 'blur'),
        'blur_strength' => (int) env('ACCOUNT_IMAGE_TOP_AREA_BLUR_STRENGTH', 35),
        // Control blur/crop width area in top band:
        // - ratio of image width (1.0 = full width)
        // - or fixed pixels (if > 0, overrides ratio)
        'width_ratio' => (float) env('ACCOUNT_IMAGE_TOP_AREA_WIDTH_RATIO', 1.0),
        'width_px' => (int) env('ACCOUNT_IMAGE_TOP_AREA_WIDTH_PX', 0),
        // Right offset for top-area window (px), useful when width < full image width.
        'x_from_right_px' => (int) env('ACCOUNT_IMAGE_TOP_AREA_X_FROM_RIGHT_PX', 0),
    ],

    // Blur account name area on main image only.
    'name_blur' => [
        'enabled' => (bool) env('ACCOUNT_IMAGE_NAME_BLUR_ENABLED', true),
        // Position mode: fixed (px) or adaptive (relative to image dimensions).
        'mode' => env('ACCOUNT_IMAGE_NAME_BLUR_MODE', 'adaptive'),
        'x_offset_from_right' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_X_OFFSET_FROM_RIGHT', 420),
        'y' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_Y', 40),
        'width' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_WIDTH', 350),
        'height' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_HEIGHT', 100),
        // Adaptive ratios (used when mode=adaptive)
        'x_offset_from_right_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_X_OFFSET_FROM_RIGHT_RATIO', 0.39),
        'y_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_Y_RATIO', 0.037),
        'width_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_WIDTH_RATIO', 0.325),
        'height_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_HEIGHT_RATIO', 0.093),
        'strength' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_STRENGTH', 35),
    ],

    // Additional blur box on gallery images only (optional).
    // If x/y are null/0, it is auto-centered.
    'center_blur' => [
        'enabled' => (bool) env('ACCOUNT_IMAGE_CENTER_BLUR_ENABLED', false),
        // fixed = px-based coords, adaptive = ratio-based coords/sizes.
        'mode' => env('ACCOUNT_IMAGE_CENTER_BLUR_MODE', 'fixed'),
        'x' => env('ACCOUNT_IMAGE_CENTER_BLUR_X'),
        'x_ratio' => env('ACCOUNT_IMAGE_CENTER_BLUR_X_RATIO'),
        // If > 0, takes priority over X and places the box from right edge.
        'x_from_right' => (int) env('ACCOUNT_IMAGE_CENTER_BLUR_X_FROM_RIGHT', 0),
        'y' => env('ACCOUNT_IMAGE_CENTER_BLUR_Y'),
        'y_ratio' => env('ACCOUNT_IMAGE_CENTER_BLUR_Y_RATIO'),
        'width' => (int) env('ACCOUNT_IMAGE_CENTER_BLUR_WIDTH', 120),
        'width_ratio' => (float) env('ACCOUNT_IMAGE_CENTER_BLUR_WIDTH_RATIO', 0.2),
        'height' => (int) env('ACCOUNT_IMAGE_CENTER_BLUR_HEIGHT', 120),
        'height_ratio' => (float) env('ACCOUNT_IMAGE_CENTER_BLUR_HEIGHT_RATIO', 0.2),
        'strength' => (int) env('ACCOUNT_IMAGE_CENTER_BLUR_STRENGTH', 35),
    ],
];

